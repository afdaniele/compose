<?php

namespace exceptions;

use Exception;
use Throwable;

class BaseException extends Exception {
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $code, $previous);
        if (!is_null($previous)) {
            $this->message = $previous->getMessage();
            $this->code = $previous->getCode();
            $this->file = $previous->getFile();
            $this->line = $previous->getLine();
        }
    }
}

class GenericException extends BaseException {}

class CircularDependencyException extends BaseException {}

class NoVCSFoundException extends BaseException {}

class PackageNotFoundException extends BaseException {
    public function __construct(string $package, $code = 0, Throwable $previous = null) {
        parent::__construct("Package '$package' not found.", $code, $previous);
    }
}

class PageNotFoundException extends BaseException {
    public function __construct(string $package, string $page, $code = 0, Throwable $previous = null) {
        parent::__construct("Page '$page' in package '$package' not found.", $code, $previous);
    }
}

class ThemeNotFoundException extends BaseException {
    public function __construct(string $package, string $theme, $code = 0, Throwable $previous = null) {
        parent::__construct("Theme '$theme' in package '$package' not found.", $code, $previous);
    }
}

class ModuleNotFoundException extends BaseException {
    public function __construct(string $package, string $module, $code = 0, Throwable $previous = null) {
        parent::__construct("Module '$module' in package '$package' not found.", $code, $previous);
    }
}

class APIApplicationNotFoundException extends BaseException {
    public function __construct(string $app_id, $code = 0, Throwable $previous = null) {
        parent::__construct("API Application with ID '$app_id' not found.", $code, $previous);
    }
}

class FileNotFoundException extends BaseException {
    public function __construct($fpath, $code = 0, Throwable $previous = null) {
        parent::__construct("File '$fpath' not found.", $code, $previous);
    }
}

class UserNotFoundException extends BaseException {
    public function __construct($user, $code = 0, Throwable $previous = null) {
        parent::__construct("User '$user' not found.", $code, $previous);
    }
}

class InvalidTokenException extends BaseException {}

class InactiveUserException extends BaseException {}

class DatabaseContentException extends BaseException {}

class DatabaseKeyNotFoundException extends BaseException {
    public function __construct($package, $database, $key, $code = 0, Throwable $previous = null) {
        parent::__construct("Key '$key' not found in database '$database' of package '$package'.", $code, $previous);
    }
}

class InvalidAuthenticationException extends BaseException {}

class IOException extends BaseException {}

class ConfigurationException extends BaseException {}

class URLRewriteException extends BaseException {}

class InvalidSchemaException extends BaseException {}

class SchemaViolationException extends BaseException {}
