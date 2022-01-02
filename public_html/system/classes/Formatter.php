<?php

namespace system\classes;

class Formatter {
    
    // constants
    const ALPHABETIC = 0;
    const ALPHABETIC_SPACE = 1;
    const ALPHANUMERIC = 2;
    const ALPHANUMERIC_SPACE = 3;
    const NUMERIC = 4;
    const FLOAT = 5;
    const PASSWORD = 6;
    const TEXT = 7;
    const EMAIL = 8;
    const KEY = 9;
    const VERSION = 10;
    const MONEY = 11;
    const BOOLEAN = 12;
    const DATE = 13;
    const DATETIME = 14;
    const DISTANCE = 15;
    const COLOR = 16;
    const PERCENTAGE = 17;
    const PLACEHOLDER = 18;
    const AVATAR_IMAGE = 19;
    const AVATAR_IMAGE_SMALL = 20;
    const ARRAY = 21;
    
    
    public static function format($val, $type) {
        switch ($type) {
            case self::ALPHABETIC:
            case self::ALPHABETIC_SPACE:
            case self::ALPHANUMERIC:
            case self::ALPHANUMERIC_SPACE:
            case self::NUMERIC:
            case self::TEXT:
            case self::EMAIL:
            case self::VERSION:
                return $val . '';
            case self::PASSWORD:
                return $val . ''; //TODO: maybe obscure this by using '*' * strlen($val)
            case self::KEY:
                return 'ID:&nbsp;' . $val;
            case self::FLOAT:
                return number_format($val, 2, '.', '');
            case self::MONEY:
                if (is_string($val)) {
                    $val = floatval($val);
                }
                return sprintf('$%s', number_format($val, 2, '.', ''));
            case self::BOOLEAN:
                return ((booleanval($val)) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" style="color:green" data-toggle="tooltip" data-placement="right" title="On"></span>' : '<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" style="color:red" data-toggle="tooltip" data-placement="right" title="Off"></span>');
            case self::DATE:
                return date_format(date_create($val), 'Y-m-d');
            case self::DATETIME:
                return date_format(date_create($val), 'Y-m-d H:i:s');
            case self::DISTANCE:
                return (($val >= 1000) ? number_format($val / 1000, 1, ',', '') : number_format($val, 0, '', '')) . (($val >= 1000) ? ' Km' : ' m');
            case self::COLOR:
                return (($val == null) ? '<span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-bookmark" aria-hidden="true" style="color:' . $val . '"></span>');
            case self::PERCENTAGE:
                return $val . ' %';
            case self::PLACEHOLDER:
                return '<div id="_format_placeholder_' . $val . '"><img src="' . \system\classes\Configuration::$BASE . 'images/loading_blue.gif" style="width:22px; height:22px;"></div>';
            case self::AVATAR_IMAGE:
                return sprintf('<img src="%s" class="formatted-avatar">', $val);
            case self::AVATAR_IMAGE_SMALL:
                return sprintf('<img src="%s" class="formatted-avatar formatted-avatar-small">', $val);
            case self::ARRAY:
                return sprintf('<pre class="text-justify">%s</pre>', print_r($val, true));
            default:
                return sprintf('UNFORMATTED(%s)', $val);
        }
    }//format
    
}//Formatter
?>
