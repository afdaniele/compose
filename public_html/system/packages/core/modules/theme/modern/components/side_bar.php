<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

// get pages
$pages_list = Core::getPagesList();

// get the current user's role
$main_user_role = Core::getUserRole();
$user_roles = Core::getUserRolesList();

// get list of visible buttons
$pages = Core::getFilteredPagesList(
    'by-menuorder',
    true /* enabledOnly */,
    $user_roles /* accessibleBy */
);

// create a whitelist/blacklist of pages
$pages_whitelist = null;
$pages_blacklist = null;

// check if compose was configured
if (!Core::isComposeConfigured()) {
    $pages_whitelist = ['setup'];
} else {
    $pages_blacklist = ['setup'];
}

// remove login if the functionality is not enabled
$login_enabled = Core::getSetting('login_enabled', 'core');
?>

<style type="text/css">
    ._ctheme_page ._ctheme_side_bar {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        padding: 0 10px;
        font-size: x-large;
        overflow-x: hidden;
        
        box-shadow: inset 20px 20px 20px 0 <?php echo $_THEME_COLOR_2->get_hex() ?>,
            1px 1px 20px 0 #989898;
        
        width: <?php echo Configuration::$THEME_CONFIG['dimensions']['sidebar_full_width'] ?>px;

        <?php
        echo _get_gradient_color($_THEME_COLOR_2->darken(0.1), $_THEME_COLOR_2, 143)
        ?>
    }
    
    ._ctheme_page ._ctheme_side_bar a,
    ._ctheme_page ._ctheme_side_bar .btn {
        color: <?php echo $_THEME_FG_COLOR_2->get_hex() ?>;
    }
    
    ._ctheme_page ._ctheme_side_bar hr {
        border-color: <?php echo $_THEME_FG_COLOR_2->get_hex() ?>;
    }
    
    ._ctheme_page ._ctheme_side_bar hr._ctheme_logo_hr {
        position: absolute;
        top: <?php echo Configuration::$THEME_CONFIG['dimensions']['topbar_height'] ?>px;
        left: 10%;
        right: 10%;
        margin: 0;
    }
    
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div {
        padding: 10px 5px;
    }
    
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div,
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div table{
        float: left;
        width: 100%;
    }
    
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div table td:first-child{
        width: 1%;
    }
    
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div table td:last-child{
        width: 99%;
        text-align: center;
    }
    
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div table td:last-child{
        padding-right: 10px;
    }
    
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div table td:last-child h3{
        margin: 10px 0 0 0;
    }
    
    ._ctheme_page ._ctheme_side_bar ._ctheme_logo_div table td:last-child h6{
        margin: 0;
    }
    
    ._ctheme_side_bar_buttons_group_container {
        margin: 10px 0;
        padding: 0 6px 0 0;
        position: absolute;
        top: <?php echo Configuration::$THEME_CONFIG['dimensions']['topbar_height'] ?>px;
        bottom: <?php echo Configuration::$THEME_CONFIG['dimensions']['footer_height'] ?>px;
        left: 0;
        right: 0;
    }
    
    ._ctheme_side_bar_buttons_group {
        padding-left: 20px;
        height: 100%;
        overflow: auto;
    }
    
    ._ctheme_side_bar_buttons_group .btn {
        margin: 4px 0;
        width: 100%;
        height: 42px;
        font-size: 11pt;
        text-transform: uppercase;
        text-align: left;
        font-family: monospace;
        border-radius: 10px;
        padding: 10px 2px;
    }
    
    ._ctheme_side_bar_buttons_group .btn:hover {
        text-decoration: none;
        background-color: <?php echo $_THEME_COLOR_2->darken(0.2)->get_hex() ?>;
    }
    
    ._ctheme_side_bar_buttons_group .btn.active {
        background-color: #fff3ec;
        color: #030303;
    }
    
    ._ctheme_side_bar_buttons_group .btn span:first-child {
        font-size: 13pt;
        width: 40px;
        text-align: center;
    }
    
    ._ctheme_page ._ctheme_side_bar hr._ctheme_footer_hr {
        position: absolute;
        bottom: <?php echo Configuration::$THEME_CONFIG['dimensions']['footer_height'] ?>px;
        left: 10%;
        right: 10%;
        margin: 0;
    }
    
    ._ctheme_footer {
        position: absolute;
        bottom: 20px;
        left: 0;
        right: 0;
        margin: 0;
        font-size: 8pt;
        width: 100%;
    }
    
    ._ctheme_footer #_sidebar_user_btn {
        margin: 0 0 0 20px;
        padding: 0;
    }
    
    ._ctheme_footer #_sidebar_user_btn:hover {
        text-decoration: none;
    }
    
    ._ctheme_footer #_sidebar_user_btn img {
        border-radius: 50%;
        width: 40px;
        height: 40px;
        margin-top: -4px;
        border: 1px solid black;
    }
    
    ._ctheme_footer #_sidebar_user_btn span {
        font-size: medium;
        padding-left: 8px;
    }
    
    ._ctheme_page ._ctheme_side_bar hr._ctheme_footer_credits_hr {
        margin: 22px 10% 0 10%;
    }
    
    ._ctheme_footer ._ctheme_footer_credits {
        height: 75px;
        color: <?php echo Configuration::$THEME_CONFIG['colors']['secondary']['foreground'] ?>;
    }
    
    ._ctheme_footer ._ctheme_footer_credits td {
        width: 100%;
        text-align: center;
        padding-top: 14px;
    }
    
    ._ctheme_footer ._ctheme_footer_credits td img {
        height: 18px;
    }
    
    ._ctheme_footer ._ctheme_footer_credits td ._ctheme_footer_credit_row {
        line-height: 20px;
    }
</style>

<a class="_ctheme_logo_div" href="<?php echo Configuration::$BASE ?>">
    <table>
        <tr>
            <td>
                <?php
                $logo = Core::getSetting('logo_white');
                $base = Configuration::$BASE;
                $logo = str_replace('~', $base, str_replace('~/', '~', $logo));
                ?>
                <img id="navbarLogo" src="<?php echo $logo ?>" alt=""/>
            </td>
            <td class="_ctheme_side_bar_off">
                <h3><?php echo Core::getSetting('navbar_title') ?></h3>
                <?php
                $subtitle = Core::getSetting('navbar_subtitle', 'core', null);
                if (!is_null($subtitle) && strlen(trim($subtitle)) > 0) {
                    ?>
                    <h6><?php echo $subtitle ?></h6>
                    <?php
                }
                ?>
            </td>
        </tr>
    </table>
</a>

<hr class="_ctheme_logo_hr">


<div class="_ctheme_side_bar_buttons_group_container col-md-12">
    <div class="_ctheme_side_bar_buttons_group col-md-12">
    <?php
    $active_page_btn_id = '';
    foreach ($pages as &$page) {
        if (!$login_enabled && $page['id'] == 'login') {
            continue;
        }
        if (!is_null($pages_whitelist) && !in_array($page['id'], $pages_whitelist)) {
            continue;
        }
        if (!is_null($pages_blacklist) && in_array($page['id'], $pages_blacklist)) {
            continue;
        }
        // hide pages if maintenance mode is enabled
        if ($main_user_role != 'administrator' && Core::getSetting('maintenance_mode', 'core') && $page['id'] != 'login') {
            continue;
        }
        // hide page if the current user' role is excluded
        if (count(array_intersect($user_roles, $page['menu_entry']['exclude_roles'])) > 0) {
            continue;
        }
        $is_last = boolval($i == count($pages) - 1);
        $icon = sprintf('%s %s-%s', $page['menu_entry']['icon']['class'], $page['menu_entry']['icon']['class'], $page['menu_entry']['icon']['name']);
        $active = (Configuration::$PAGE == $page['id']) || in_array(Configuration::$PAGE, $page['child_pages']);
        $active_page_btn_id = $active? sprintf("_sidebar_page_btn_%s", $page['id']) : '';
        //
        ?>
        <a  role="button" id="_sidebar_page_btn_<?php echo $page['id'] ?>"
            class="btn btn-link _sidebar_page_btn <?php echo ($active) ? 'active' : '' ?>"
            href="<?php echo Core::getURL($page['id']) ?>"
            >
            <span class="<?php echo $icon ?>" aria-hidden="true" style=""></span>
            <span class="_ctheme_side_bar_off">
                <?php echo $page['name'] ?>
            </span>
        </a>
        <?php
    }
    ?>
    </div>
</div>

<hr class="_ctheme_footer_hr">

<table class="_ctheme_footer">
    <?php
    if (Core::isUserLoggedIn()) {
        ?>
        <tr>
            <td>
                <a  role="button" id="_sidebar_user_btn"
                    class="btn btn-link"
                    href="<?php echo Core::getURL('profile') ?>"
                    >
                    <?php
                    // get user info
                    $user = Core::getUserLogged();
                    $picture_url = $user['picture'];
                    if (preg_match('#^https?://#i', $picture_url) !== 1) {
                      $picture_url = sanitize_url(sprintf(
                        "%s%s", Configuration::$BASE, $picture_url
                      ));
                    }
                    ?>
                    <img src="<?php echo $picture_url; ?>" alt="">
                    <span class="_ctheme_side_bar_off">
                        <?php echo $user['name'] ?>
                    </span>
                </a>
                <hr class="_ctheme_footer_credits_hr">
            </td>
        </tr>
    <?php
    }
    ?>
    
    <tr class="_ctheme_footer_credits">
        <td class="_ctheme_side_bar_off">
            <span class="_ctheme_footer_credit_row">
                developed by
                <a href="http://www.afdaniele.com"><b>Andrea F. Daniele</b></a>
            </span>
            <br/>
            
            <span class="_ctheme_footer_credit_row">
                powered by
                <a href="https://github.com/afdaniele/compose" target="_blank">
                    <img src="<?php echo Configuration::$BASE ?>images/compose-black-logo.svg" alt=""/>
                </a>
            </span>
            <br/>
            
            <?php
            $codebase_info = Core::getCodebaseInfo();
            $codebase_hash = $codebase_info['head_hash'];
            $codebase_tag = $codebase_info['head_tag'];
            $codebase_latest_tag = $codebase_info['latest_tag'];
            $codebase_tag = (strcasecmp($codebase_tag, $codebase_latest_tag) === 0) ? $codebase_tag : 'devel';
            $codebase_str = (in_array($codebase_tag, ['ND', null])) ? '' : sprintf("%s | ", $codebase_tag);
            ?>
            
            <span class="_ctheme_footer_credit_row">
                <b>serial</b>&nbsp;
                <span style="font-family:monospace">
                    git | <?php echo $codebase_str . $codebase_hash ?>
                </span>
            </span>
    
            <?php
            if (Core::getSetting('cache_enabled')) {
                ?>
                |&nbsp; <span
                        onclick="clearCache()"
                        class="glyphicon glyphicon-fire focus-on-hover pointer-hand"
                        aria-hidden="true"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Burn cache"
                ></span>
                <?php
            }
            ?>
        </td>
    </tr>
</table>

<script type="text/javascript">
    $(document).ready(function(){
        $('._sidebar_page_btn.active')[0].scrollIntoView();
        _ctheme_side_bar_set(localStorage.getItem('_CTHEME_SIDEBAR_STATUS'));
    });
    
    function _ctheme_side_bar_toggle(){
        let sidebar = $('._ctheme_side_bar');
        let status = sidebar.data('_ctheme_status');
        if (status === undefined) {
            status = localStorage.getItem('_CTHEME_SIDEBAR_STATUS');
        }
        if (status === undefined || status === 'full') {
            status = 'small';
        } else {
            status = 'full';
        }
        _ctheme_side_bar_set(status);
        if (['small', 'full'].includes(status)) {
            sidebar.data('_ctheme_status', status);
            localStorage.setItem('_CTHEME_SIDEBAR_STATUS', status);
        }
    }
    
    function _ctheme_side_bar_set(status){
        if (status === undefined) return;
        let sidebar = $('._ctheme_side_bar');
        let topbar = $('._ctheme_top_bar');
        let container = $('._ctheme_container');
        let button = $('._ctheme_side_bar_btn a');
        let size = '';
        let chevron = '';
        if (status === 'small') {
            chevron = 'right';
            size = '<?php echo Configuration::$THEME_CONFIG['dimensions']['sidebar_small_width'] ?>px';
            $('._ctheme_side_bar_off').css('display', 'none');
        } else {
            chevron = 'left';
            size = '<?php echo Configuration::$THEME_CONFIG['dimensions']['sidebar_full_width'] ?>px';
            $('._ctheme_side_bar_off').css('display', 'inline-block');
        }
        sidebar.css('width', size);
        topbar.css('left', size);
        container.css('left', size);
        button.removeClass();
        button.addClass('glyphicon glyphicon-chevron-{0}'.format(chevron));
    }
</script>

