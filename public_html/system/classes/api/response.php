<?php

namespace system\classes\api;


use Throwable;

class APIResponse {
    public int $code;
    public string $status;
    public string $message;
    public array|null $data;
    
    protected static array $statuses = [
        200 => 'OK',
        401 => 'Unauthorized',
        400 => 'Bad Request',
        412 => 'Precondition Failed',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];
    
    function __construct(int $code, string $status, string $message, array|null $data) {
        $this->code = $code;
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }
    
    public static function fromException(Throwable $e, int $code): APIResponse {
        $status =  array_key_exists($code, self::$statuses)? self::$statuses[$code] : "Error";
        $exception_name = get_class($e);
        $message = "{$exception_name}: {$e->getMessage()}";
        return APIUtils::createAPIResponse($code, $status, $message, null);
    }
}