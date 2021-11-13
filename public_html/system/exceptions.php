<?php

namespace exceptions;

use Exception;
use Throwable;

class BaseException extends Exception {}

class CircularDependencyException extends BaseException {}

class NoVCSFoundException extends BaseException {}

class PackageNotFoundException extends BaseException {}

class PageNotFoundException extends BaseException {}

class ThemeNotFoundException extends BaseException {}

class ModuleNotFoundException extends BaseException {}

class FileNotFoundException extends BaseException {
    public function __construct($fpath, $code = 0, Throwable $previous = null) {
        parent::__construct("File '$fpath' not found.", $code, $previous);
    }
}

