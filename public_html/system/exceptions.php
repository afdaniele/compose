<?php

namespace exceptions;

use Exception;

class BaseException extends Exception {}

class CircularDependencyException extends BaseException {}

class NoVCSFoundException extends BaseException {}

class PackageNotFoundException extends BaseException {}

class PageNotFoundException extends BaseException {}

class ThemeNotFoundException extends BaseException {}

class ModuleNotFoundException extends BaseException {}

class FileNotFoundException extends BaseException {}
