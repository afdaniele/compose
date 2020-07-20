<?php

use \system\classes\Configuration;
use \system\classes\Color;


// blue
//$_THEME_COLOR_1 = new Color(44, 86, 134);
// yellow
//$_THEME_COLOR_2 = new Color(247, 99, 59);
// orange
//$_THEME_COLOR_3 = new Color(255, 181, 0);


//// blue
//$_THEME_COLOR_1 = new Color(44, 86, 134);
//
//// brick
////$_THEME_COLOR_1 = new Color(140, 20, 20);
//// yellow
//$_THEME_COLOR_2 = new Color(255, 198, 17);
//// orange
//$_THEME_COLOR_3 = new Color(100, 100, 100);
//
//$_THEME_FG_COLOR_1 = new Color(30, 30, 30);
////// grey
//$_THEME_FG_COLOR_2 = new Color(30, 30, 30);

$_THEME_COLOR_1 = Color::from_hex(Configuration::$THEME_CONFIG['colors']['primary']['background']);
$_THEME_COLOR_2 = Color::from_hex(Configuration::$THEME_CONFIG['colors']['secondary']['background']);
$_THEME_COLOR_3 = Color::from_hex(Configuration::$THEME_CONFIG['colors']['tertiary']);



$_THEME_FG_COLOR_1 = Color::from_hex(Configuration::$THEME_CONFIG['colors']['primary']['foreground']);
$_THEME_FG_COLOR_2 = Color::from_hex(Configuration::$THEME_CONFIG['colors']['secondary']['foreground']);


$_THEME_GRADIENT_FMT = "
background: rgb({color1});
background: -moz-linear-gradient({angle}deg, rgba({color1},1) 0%, rgba({color2},1) 100%);
background: -webkit-linear-gradient({angle}deg, rgba({color1},1) 0%, rgba({color2},1) 100%);
background: linear-gradient({angle}deg, rgba({color1},1) 0%, rgba({color2},1) 100%);
";

function _get_gradient_color(Color $color1, Color $color2, $angle=0) {
    global $_THEME_GRADIENT_FMT;
    $data = array(
        '{angle}' => $angle,
        '{color1}' => implode(',', array_values($color1->get_array())),
        '{color2}' => implode(',', array_values($color2->get_array()))
    );
    return str_replace(array_keys($data), array_values($data), $_THEME_GRADIENT_FMT);
}

?>