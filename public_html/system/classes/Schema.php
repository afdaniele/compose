<?php

namespace system\classes;

use exceptions\FileNotFoundException;
use exceptions\InvalidSchemaException;
use exceptions\IOException;
use exceptions\SchemaViolationException;
use stdClass;
use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\Exception\TypeException;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema as JSONSchema;
use Swaggest\JsonSchema\Structure\ObjectItem;

class Schema {
    
    protected JSONSchema $schema;
    protected array $schema_array;
    
    // Constructor
    
    /**
     * Database constructor.
     * @param string|array $schema
     * @throws InvalidSchemaException
     */
    function __construct(string|array $schema) {
        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }
        $this->schema_array = $schema;
        $schema_obj = self::arrayToObj($this->schema_array);
        try {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->schema = JSONSchema::import($schema_obj);
        } catch (InvalidValue | \Exception | TypeException $e) {
            throw new InvalidSchemaException(previous: $e);
        }
    }//__construct
    
    //============================================================
    // PUBLIC FUNCTIONS
    //============================================================
    
    /** TODO
     * @return array
     */
    public function asArray(): array {
        return $this->schema_array;
    }
    
    /** TODO
     * @param array|stdClass $data
     * @return mixed
     * @throws SchemaViolationException
     */
    public function validate(array|stdClass $data): void {
        $data = self::arrayToObj($data);
        try {
            $this->schema->in($data);
        } catch (InvalidValue | Exception $e) {
            throw new SchemaViolationException(previous: $e);
        }
    }
    
    /** TODO
     * @param array|stdClass $data
     * @return array
     */
    public function sanitize(array|stdClass $data): array {
        $data = self::arrayToObj($data);
        $data = $this->schema->in($data);
        return self::objectItemToArray($data);
    }
    
    /** TODO
     * @param array|stdClass $data
     * @return mixed
     */
    public function defaults() {
        return $this->sanitize(new \stdClass());
    }
    
    /** TODO
     * @param array|stdClass $data
     * @return mixed
     */
    public function test(array|stdClass $data): mixed {
//        $data = self::arrayToObj($data);
//
//        $data = $schema->in(new \stdClass());
//        $this->assertSame('{"foo":null}', json_encode($data));
//
//
//        return $this->schema->properties;
    }
    
    //============================================================
    // PRIVATE FUNCTIONS
    //============================================================
    
    private static function arrayToObj(array|stdClass $a): stdClass {
        $out = $a;
        if (is_array($a)) {
            $out = (count($a) == 0)? new stdClass() : json_decode(json_encode($a));
        }
        return $out;
    }
    
    private static function objectItemToArray(ObjectItem|array $objectItem): array {
        $a = ($objectItem instanceof ObjectItem)? $objectItem->toArray() : $objectItem;
        foreach ($a as $k => $vObj) {
            if ($vObj instanceof ObjectItem)
                $a[$k] = $vObj->toArray();
            if (is_array($vObj))
                $a[$k] = self::objectItemToArray($vObj);
        }
        return $a;
    }
    
    //============================================================
    // PUBLIC STATIC FUNCTIONS
    //============================================================
    
    /** Loads a schema from the system/schemas/ directory.
     *
     * @param string $schema Name of the schema to load.
     * @return Schema
     * @throws FileNotFoundException
     * @throws IOException
     * @throws InvalidSchemaException
     */
    public static function load(string $schema): Schema {
        $fpath = join_path(__DIR__, "..", "schemas", "$schema.json");
        $content = Core::loadFile($fpath);
        return new Schema($content);
    }
    
}