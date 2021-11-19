<?php

namespace system\classes\api;


use exceptions\FileNotFoundException;
use exceptions\InvalidSchemaException;
use exceptions\IOException;
use exceptions\SchemaViolationException;
use JetBrains\PhpStorm\Pure;
use system\classes\Core;
use system\classes\Schema;


/** RESTfulAPIVersionSettings class: provides info about a RESTfulAPI version.
 * @package system\classes\api
 */
class RESTfulAPIVersionSettings {
    protected bool $enabled;
    
    function __construct($enabled) {
        $this->enabled = $enabled;
    }
    
    // Properties
    
    public function enabled(): bool {
        return $this->enabled;
    }
    
    // Public static functions
    
    #[Pure] public static function fromArray(array $array): RESTfulAPIVersionSettings {
        return new RESTfulAPIVersionSettings($array['enabled']);
    }
}


/** RESTfulAPISettings class: provides info about a RESTfulAPI.
 * @package system\classes\api
 */
class RESTfulAPISettings {
    
    protected array $parameters;
    protected array $versions;
    protected bool $enabled;
    
    function __construct(array $parameters, array $versions, bool $enabled) {
        $this->parameters = $parameters;
        $this->versions =  array_map(fn($v) => RESTfulAPIVersionSettings::fromArray($v), $versions);
        $this->enabled = $enabled;
    }
    
    // Properties
    
    public function parameters(): array {
        return $this->parameters;
    }
    
    public function versions(): array {
        return $this->versions;
    }
    
    public function enabled(): bool {
        return $this->enabled;
    }
    
    // Public static functions
    
    /** Loads an API configuration from disk.
     *
     * @param string $fpath          Settings file to load.
     * @return RESTfulAPISettings    The configuration.
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     * @throws SchemaViolationException
     */
    public static function fromFile(string $fpath): RESTfulAPISettings {
        $settings = Core::loadFile($fpath, "json");
        // get schema
        $schema = Schema::load("api_settings");
        // validate
        $schema->validate($settings);
        // load
        return new RESTfulAPISettings(
            $settings["parameters"],
            $settings["versions"],
            $settings["enabled"]
        );
    }
    
}