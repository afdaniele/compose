<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu

// simplify namespaces
use system\classes\Core;
use system\classes\Configuration;

?>

<style type="text/css">
    ._ctheme_page ._ctheme_top_bar {
        position: absolute;
        top: 0;
        left: <?php echo Configuration::$THEME_CONFIG['dimensions']['sidebar_full_width'] ?>px;
        right: 0;
        height: <?php echo Configuration::$THEME_CONFIG['dimensions']['topbar_height'] ?>px;
        border-left: 1px solid black;
        color: <?php echo Configuration::$THEME_CONFIG['colors']['primary']['foreground'] ?>;
        padding: 10px 10px 10px 30px;
        font-size: x-large;
        border-left: 1px solid lightgrey;

        box-shadow: inset -20px 20px 20px 0 <?php echo $_THEME_COLOR_1->get_hex() ?>, 20px 3px 20px 0 #989898;

    <?php
    echo _get_gradient_color($_THEME_COLOR_1->darken(0.3), $_THEME_COLOR_1, 22)
    ?>
    }

    ._ctheme_page a {
        color: <?php echo Configuration::$THEME_CONFIG['colors']['primary']['foreground'] ?>;
    }

    ._ctheme_page a:hover {
        text-decoration: none;
    }

    ._ctheme_page ._ctheme_top_bar ._ctheme_top_bar_button_cell {
        font-size: medium;
        width: 1px;
        white-space: nowrap;
    }

    ._ctheme_page ._ctheme_top_bar ._ctheme_top_bar_button {
        padding: 0 22px
    }

    ._ctheme_top_bar .breadcrumb {
        background-color: unset;
        font-size: small;
        padding: 0;
        margin: 8px 0 0;
    }
    
    ._ctheme_top_bar ._ctheme_progress_bar {
        position: absolute;
        width: 100%;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
    }
    
    ._ctheme_top_bar ._ctheme_progress_bar #compose_progress_bar.progress {
        height: 5px;
        border-radius: 0;
    }
</style>

<table style="width: 100%">
    <tr>
        <td>
            <?php
            // show website name
            echo ucfirst(Core::getPageDetails(Configuration::$PAGE, 'name'));
            ?>
        </td>
        <td class="text-center _ctheme_top_bar_button_cell">
            <?php
            if (Core::isUserLoggedIn()) {
                ?>
                <span class="_ctheme_top_bar_button">
                    <a href="<?php echo Core::getURL('settings') ?>"
                       data-toggle="tooltip" data-placement="bottom" title="Settings">
                        <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                    </a>
                    &nbsp;
                </span>
                <?php
            }
            ?>
        </td>
        <td class="text-center _ctheme_top_bar_button_cell">
            <?php
            if (Core::isUserLoggedIn()) {
                ?>
                <span class="_ctheme_top_bar_button">
                    <a href="<?php echo Core::getURL('profile') ?>"
                       data-toggle="tooltip" data-placement="bottom" title="Your profile">
                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                        &nbsp;
                        <?php echo Core::getUserLogged('name') ?>
                    </a>
                </span>
                <?php
            }
            ?>
        </td>
        <td class="text-center _ctheme_top_bar_button_cell">
            <span class="_ctheme_top_bar_button">
            <?php
            if (Core::isUserLoggedIn()) {
                ?>
                <a href="#" onclick="logOutButtonClick();"
                   data-toggle="tooltip" data-placement="bottom" title="Sign out">
                    <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
                    &nbsp;
                    Sign out
                </a>
                <?php
            }
            else {
                ?>
                <a href="<?php echo Core::getURL('login') ?>"
                   data-toggle="tooltip" data-placement="bottom" title="Sign in / Sign up">
                    <span class="glyphicon glyphicon-log-in" aria-hidden="true"></span>
                    &nbsp;
                    Sign in / Sign up
                </a>
                <?php
            }
            ?>
            </span>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <ol class="breadcrumb">
                <?php
                $parts = [
                    '',
                    Configuration::$PAGE,
                    Configuration::$ACTION,
                    Configuration::$ARG1,
                    Configuration::$ARG2,
                    null
                ];
                $cur = [];
                for ($i = 0; $i < count($parts) - 1; $i++) {
                    $part = $parts[$i];
                    $npart = $parts[$i + 1];
                    if (is_null($part)) {
                        break;
                    }
                    // ---
                    array_push($cur, $part);
                    $active = is_null($npart) ? 'class="active"' : '';
                    $url = is_null($npart) ? ucfirst($part) :
                        sprintf('<a href="%s">%s</a>', Core::getURL(...$cur), ucfirst($part));
                    printf('<li %s>%s</li>', $active, $url);
                }
                ?>
            </ol>
        </td>
    </tr>
</table>

<div class="_ctheme_progress_bar">
    <?php
    // load progress bar
    include(join_path($CORE_PKG_DIR, 'modules/progress_bar.php'));
    ?>
</div>