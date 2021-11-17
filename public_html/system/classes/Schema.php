<?php

namespace system\classes;

use exceptions\InvalidSchemaException;
use exceptions\SchemaViolationException;
use stdClass;
use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema as JSONSchema;

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
            $schema = json_decode($schema);
        }
        $this->schema_array = $schema;
        $schema_obj = self::arrayToObj($this->schema_array);
        try {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->schema = JSONSchema::import($schema_obj);
        } catch (InvalidValue | \Exception $e) {
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
     * @return mixed
     */
    public function sanitize(array|stdClass $data): mixed {
        $data = self::arrayToObj($data);
        $data = $this->schema->in($data);
        return $data->toArray();
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
        return is_array($a)? json_decode(json_encode($a)) : $a;
    }
    
    
}