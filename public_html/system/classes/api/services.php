<?php

namespace system\classes\api;

use exceptions\APIActionNotFoundException;
use exceptions\FileNotFoundException;
use exceptions\InvalidSchemaException;
use exceptions\IOException;
use exceptions\SchemaViolationException;
use JetBrains\PhpStorm\Pure;
use system\classes\Core;
use system\classes\Database;
use system\classes\Schema;


/** RESTfulAPIServiceConfiguration class: provides info about a RESTfulAPI action.
 * @package system\classes\api
 */
class RESTfulAPIServiceConfiguration {
    protected string $description;
    
    function __construct(string $description) {
        $this->description = $description;
    }
    
    /** Loads an API action configuration from disk.
     *
     * @param string $fpath Configuration file to load.
     * @return RESTfulAPIServiceConfiguration    The configuration.
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    public static function fromFile(string $fpath): RESTfulAPIServiceConfiguration {
        $service = Core::loadFile($fpath, "json");
        // get schema
        $schema = Schema::load("api_service");
        // validate
        $schema->validate($service);
        // load
        return new RESTfulAPIServiceConfiguration($service["description"]);
    }
    
}

/** RESTfulAPIService class: provides an interface to a RESTfulAPI service.
 * @package system\classes\api
 */
class RESTfulAPIService {
    protected string $version;
    protected string $path;
    protected string $name;
    protected array $actions;
    protected RESTfulAPIServiceConfiguration $configuration;
    protected bool $enabled;
    
    /**
     * RESTfulAPIService constructor.
     *
     * @param string $version               Version of the API this action belongs to.
     * @param string $path                  Path to directory containing the service.
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    function __construct(string $version, string $path) {
        $this->version = $version;
        $this->path = $path;
        $this->name = basename($path);
        // read API service status from database
        $db_name_srv = sprintf('api_%s_disabled_service', $version);
        $service_status_db = new Database('core', $db_name_srv);
        $this->enabled = !$service_status_db->key_exists($this->name);
        // load service info
        $info_fpath = join_path($this->path, "service.json");
        $this->configuration = RESTfulAPIServiceConfiguration::fromFile($info_fpath);
        // load actions
        $action_pattern = join_path($this->path, '*', 'action.json');
        $action_matches = glob($action_pattern);
        // iterate over the API actions
        foreach ($action_matches as $action_match) {
            // get action name
            $action_name = basename(dirname($action_match));
            // load action
            $action = new RESTfulAPIAction($version, $this, $action_name);
            $this->actions[$action_name] = $action;
        }
    }
    
    // Properties
    
    public function version(): string {return $this->version;}
    public function path(): string {return $this->path;}
    public function name(): string {return $this->name;}
    public function enabled(): bool {return $this->enabled;}

    
    // Public function
    
    /** Get an action.
     *
     * @param string $action                Name of the action to get.
     * @return RESTfulAPIAction             The action.
     * @throws APIActionNotFoundException
     */
    public function getAction(string $action): RESTfulAPIAction {
        if (!array_key_exists($action, $this->actions))
            throw new APIActionNotFoundException($this->name, $action);
        return $this->actions[$action];
    }
    
    /** Checks whether an action exists.
     *
     * @param string $action Name of the action to check for.
     * @return bool Whether the action exists.
     */
    #[Pure] public function hasAction(string $action): bool {
        return array_key_exists($action, $this->actions);
    }
    
    /** Get list of actions for this API service.
     *
     * @return array                        List of actions.
     */
    public function getActions(): array {
        return $this->actions;
    }
    
    /** Sets whether the API service is enabled.
     *
     * @param bool $status      New status.
     */
    public function setEnabled(bool $status) {
        $this->enabled = $status;
    }
    
    /** Returns whether the API service is enabled.
     *
     * @return bool             Current status.
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }
    
}
