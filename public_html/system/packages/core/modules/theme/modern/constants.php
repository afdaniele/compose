<?php

use system\classes\Configuration;
use system\classes\Color;

# colors
$colors = Configuration::$THEME_CONFIG['colors'] ?? [];
$primary = $colors["primary"] ?? [];
$secondary = $colors["secondary"] ?? [];
$tertiary = $colors["tertiary"] ?? "#000f";
# - background
$THEME_COLOR_1 = Color::from_hex($primary['background'] ?? "#000f");
$THEME_COLOR_2 = Color::from_hex($secondary['background'] ?? "#000f");
$THEME_COLOR_3 = Color::from_hex($tertiary);
# - foreground
$THEME_FG_COLOR_1 = Color::from_hex($primary['foreground'] ?? "#ffff");
$THEME_FG_COLOR_2 = Color::from_hex($secondary['foreground'] ?? "#ffff");


# dimensions
$THEME_DIMS = Configuration::$THEME_CONFIG["dimensions"] ?? [];

# default values
$THEME_DIM_PAGE_PADDING = $THEME_DIMS['page_padding'] ?? 20;
$THEME_DIM_PAGE_RADIUS = $THEME_DIMS['page_radius'] ?? 4;
$THEME_DIM_TOPBAR_HEIGHT = $THEME_DIMS['topbar_height'] ?? 50;
$THEME_DIM_FOOTER_HEIGHT = $THEME_DIMS['footer_height'] ?? 50;
$THEME_DIM_SIDEBAR_FULL_WIDTH = $THEME_DIMS['sidebar_full_width'] ?? 120;
$THEME_DIM_SIDEBAR_SMALL_WIDTH = $THEME_DIMS['sidebar_small_width'] ?? 120;


# gradients
$THEME_GRADIENT_FMT = "
background: rgb({color1});
background: -moz-linear-gradient({angle}deg, rgba({color1},1) 0%, rgba({color2},1) 100%);
background: -webkit-linear-gradient({angle}deg, rgba({color1},1) 0%, rgba({color2},1) 100%);
background: linear-gradient({angle}deg, rgba({color1},1) 0%, rgba({color2},1) 100%);
";

function get_gradient_color(Color $color1, Color $color2, $angle=0) {
    global $THEME_GRADIENT_FMT;
    $data = array(
        '{angle}' => $angle,
        '{color1}' => implode(',', array_values($color1->get_array())),
        '{color2}' => implode(',', array_values($color2->get_array()))
    );
    return str_replace(array_keys($data), array_values($data), $THEME_GRADIENT_FMT);
}
