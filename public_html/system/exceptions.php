<?php

namespace exceptions;

use Exception;
use RuntimeException;
use Throwable;

class BaseException extends Exception {
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
        if (!is_null($previous)) {
            $this->message .= " Previous exception returned the message: {$previous->getMessage()}";
            $this->code = $previous->getCode();
        }
    }
}

class BaseRuntimeException extends RuntimeException {
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
        if (!is_null($previous)) {
            $this->message .= " Previous exception returned the message: {$previous->getMessage()}";
            $this->code = $previous->getCode();
        }
    }
}

class GenericException extends BaseRuntimeException {}

class CircularDependencyException extends BaseRuntimeException {}

class NoVCSFoundException extends BaseRuntimeException {}

class PackageNotFoundException extends BaseRuntimeException {
    public function __construct(string $package, $code = 0, Throwable $previous = null) {
        parent::__construct("Package '$package' not found.", $code, $previous);
    }
}

class PageNotFoundException extends BaseRuntimeException {
    public function __construct(string $page, string $package = null, $code = 0, Throwable $previous = null) {
        $extra = is_null($package)? "" : " in package '$package'";
        parent::__construct("Page '$page'$extra not found.", $code, $previous);
    }
}

class ThemeNotFoundException extends BaseRuntimeException {
    public function __construct(string $package, string $theme, $code = 0, Throwable $previous = null) {
        parent::__construct("Theme '$theme' in package '$package' not found.", $code, $previous);
    }
}

class ModuleNotFoundException extends BaseRuntimeException {
    public function __construct(string $package, string $module, $code = 0, Throwable $previous = null) {
        parent::__construct("Module '$module' in package '$package' not found.", $code, $previous);
    }
}

class APIApplicationNotFoundException extends BaseRuntimeException {
    public function __construct(string $app_id, $code = 0, Throwable $previous = null) {
        parent::__construct("API Application with ID '$app_id' not found.", $code, $previous);
    }
}

class FileNotFoundException extends BaseRuntimeException {
    public function __construct($fpath, $code = 0, Throwable $previous = null) {
        parent::__construct("File '$fpath' not found.", $code, $previous);
    }
}

class UserNotFoundException extends BaseRuntimeException {
    public function __construct($user, $code = 0, Throwable $previous = null) {
        parent::__construct("User '$user' not found.", $code, $previous);
    }
}

class InvalidTokenException extends BaseRuntimeException {}

class InactiveUserException extends BaseRuntimeException {}

class DatabaseContentException extends BaseRuntimeException {}

class DatabaseKeyNotFoundException extends BaseRuntimeException {
    public function __construct($package, $database, $key, $code = 0, Throwable $previous = null) {
        parent::__construct("Key '$key' not found in database '$database' of package '$package'.", $code, $previous);
    }
}

class InvalidAuthenticationException extends BaseRuntimeException {}

class IOException extends BaseRuntimeException {}

class ConfigurationException extends BaseRuntimeException {}

class URLRewriteException extends BaseRuntimeException {}

class InvalidSchemaException extends BaseRuntimeException {}

class SchemaViolationException extends BaseRuntimeException {}

class NotLoggedInException extends BaseRuntimeException {}

class ModuleNotInitializedException extends BaseRuntimeException {
    public function __construct($module, $attribute = null, $code = 0, Throwable $previous = null) {
        $property_str = is_null($attribute)? "" : " You cannot access attribute '{$attribute}'.";
        parent::__construct("Module '$module' not initialized.$property_str", $code, $previous);
    }
}

class APIServiceNotFoundException extends BaseRuntimeException {
    public function __construct($service, $code = 0, Throwable $previous = null) {
        parent::__construct("API service '$service' not found.", $code, $previous);
    }
}

class APIActionNotFoundException extends BaseRuntimeException {
    public function __construct($service, $action, $code = 0, Throwable $previous = null) {
        parent::__construct("API action '$service/$action' not found.", $code, $previous);
    }
}
