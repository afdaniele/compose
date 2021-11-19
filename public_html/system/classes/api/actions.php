<?php

namespace system\classes\api;


use exceptions\FileNotFoundException;
use exceptions\GenericException;
use exceptions\InvalidSchemaException;
use exceptions\IOException;
use exceptions\SchemaViolationException;
use JetBrains\PhpStorm\Pure;
use system\classes\Core;
use system\classes\Database;
use system\classes\Schema;


/** RESTfulAPIActionConfiguration class: provides info about a RESTfulAPI action.
 * @package system\classes\api
 */
class RESTfulAPIActionConfiguration {
    
    protected string $description;
    protected array $access_level;
    protected array $authentication;
    
    function __construct(string $description, array $access_level, array $authentication) {
        $this->description = $description;
        $this->access_level = $access_level;
        $this->authentication = $authentication;
    }
    
    // Properties
    
    public function description(): string {return $this->description;}
    public function access_level(): array {return $this->access_level;}
    public function authentication(): array {return $this->authentication;}
    
    /** Loads an API action configuration from disk.
     *
     * @param string $fpath Configuration file to load.
     * @return RESTfulAPIActionConfiguration    The configuration.
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    public static function fromFile(string $fpath): RESTfulAPIActionConfiguration {
        $action = Core::loadFile($fpath, "json");
        // get schema
        $schema = Schema::load("api_action");
        // validate
        $schema->validate($action);
        // collect user types
        foreach ($action['access_level'] as $lvl) {
            $parts = explode(':', $lvl);
            $package = (count($parts) == 1) ? 'core' : $parts[0];
            $role = (count($parts) == 1) ? $parts[0] : $parts[1];
            Core::registerNewUserRole($package, $role);
        }
        // load
        return new RESTfulAPIActionConfiguration(
            $action["description"],
            $action["access_level"],
            $action["authentication"]
        );
    }
    
}


/** IAPIAction interface: provides an interface to a RESTfulAPI action.
 * @package system\classes\api
 */
abstract class IAPIAction {
    
    /** Implementation of the action.
     *
     * @param RESTfulAPIAction $action  API action.
     * @param array $input              The inputs.
     * @return APIResponse              The outputs.
     */
    static abstract function execute(RESTfulAPIAction $action, array $input): APIResponse;
    
}


/** RESTfulAPIAction class: provides an interface to a RESTfulAPI action.
 * @package system\classes\api
 */
class RESTfulAPIAction {
    protected string $path;
    protected string $name;
    protected bool $enabled;
    protected RESTfulAPIService $service;
    protected RESTfulAPIActionConfiguration $configuration;
    protected Schema $input_schema;
    protected Schema $output_schema;
    
    /**
     * RESTfulAPIAction constructor.
     *
     * @param string $version               Version of the API this action belongs to.
     * @param RESTfulAPIService $service    Service this API action belongs to.
     * @param string $action                Name of this action.
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    function __construct(string $version, RESTfulAPIService $service, string $action) {
        $this->service = $service;
        $this->path = join_path($service->path(), $action);
        $this->name = basename($this->path);
        // read API service status from database
        $db_name_act = sprintf('api_%s_disabled_action', $version);
        $action_status_db = new Database('core', $db_name_act);
        $api_service_id = $service->name();
        $api_action_id = $this->name();
        $action_key = "{$api_service_id}:{$api_action_id}";
        $this->enabled = !$action_status_db->key_exists($action_key);
        // load action info
        $info_fpath = join_path($this->path, "action.json");
        $this->configuration = RESTfulAPIActionConfiguration::fromFile($info_fpath);
        // load input schema
        $input_schema_fpath = join_path($this->path, "input.json");
        if (!file_exists($input_schema_fpath))
            throw new FileNotFoundException($input_schema_fpath);
        $input_schema = file_get_contents($input_schema_fpath);
        $this->input_schema = new Schema($input_schema);
        // load output schema
        $output_schema_fpath = join_path($this->path, "output.json");
        if (!file_exists($output_schema_fpath))
            throw new FileNotFoundException($output_schema_fpath);
        $output_schema = file_get_contents($output_schema_fpath);
        $this->output_schema = new Schema($output_schema);
    }
    
    // Properties
    
    #[Pure] public function version(): string {return $this->service->version();}
    public function path(): string {return $this->path;}
    public function name(): string {return $this->name;}
    #[Pure] public function enabled(): bool {
        return $this->service->enabled() && $this->enabled;
    }
    public function service(): RESTfulAPIService {return $this->service;}
    public function configuration(): RESTfulAPIActionConfiguration {return $this->configuration;}
    
    
    // Public function
    
    /** Executes the action.
     *
     * @param array $input      The inputs.
     * @return APIResponse      The response.
     * @throws SchemaViolationException
     */
    public function execute(array $input): APIResponse {
        // validate input
        $input = $this->_sanitize_input($input);
        // get the path to the action script
        $action_fpath = join_path($this->path, "action.php");
        // make sure the action file exists
        if (!file_exists($action_fpath))
            throw new FileNotFoundException($action_fpath);
        // make sure no other actions are loaded
        $classes = Core::getClasses(parent_class: '\system\classes\api\IAPIAction');
        if (count($classes) > 0)
            throw new GenericException("Multiple API actions loaded at once, this is wrong.");
        // load the action
        include_once $action_fpath;
        // make sure the class is now loaded
        $classes = Core::getClasses(parent_class: '\system\classes\api\IAPIAction');
        if (count($classes) != 1)
            throw new GenericException("API action file '$action_fpath' does not contain an 'APIAction' class.");
        // execute action
        /** @noinspection PhpUndefinedClassInspection */
        $output = \system\classes\api\endpoints\APIAction::execute($this, $input);
        // validate output
        $output->data = $output->data ?? [];
        if ($output->code == 200)
            $output->data = $this->_sanitize_output($output->data);
        // ---
        return $output;
    }
    
    /** Sets whether the API service is enabled.
     *
     * @param bool $status      New status.
     */
    public function setEnabled(bool $status) {
        $this->enabled = $status;
        // TODO: write to DB
    }
    
    
    // Protected functions
    
    /** Validates inputs.
     *
     * @param array $input The inputs.
     * @return mixed
     */
    protected function _sanitize_input(array $input): mixed {
        return $this->input_schema->sanitize($input);
    }
    
    /** Validates outputs.
     *
     * @param array $output The outputs.
     * @return mixed
     */
    protected function _sanitize_output(array $output): mixed {
        return $this->output_schema->sanitize($output);
    }
}
