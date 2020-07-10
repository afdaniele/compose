<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\classes\Core;
use \system\classes\Configuration;

?>

<style type="text/css">

#credits_table_center {
  margin: auto;
  margin-top: 10px;
}

#credits_table_right {
  float: right;
  margin: 10px 10px 0 0;
}

#footer_developer_credit > p{
  font-size: 8pt;
  margin-right: 20px;
  text-align: left;
  margin-bottom: 0;
  line-height: 11pt;
}

#footer_compose_credit{
  padding-left: 30px;
}

#footer_compose_credit > p{
  padding-right: 20px;
  margin-bottom: 0;
}

#footer_compose_credit > p > span{
  font-size: 8pt;
  font-weight: bold;
}

#footer_compose_credit > p > a > img{
  height: 24px;
}

#footer_compose_separator{
  font-size: 16pt;
  padding-right: 20px;
}

</style>



<?php

function footer_user_menu(){
  // create a whitelist/blacklist of pages
  $pages_whitelist = null;
  $pages_blacklist = null;

  // check if compose was configured correctly
  if (!Core::isComposeConfigured()){
    $pages_whitelist = ['setup'];
  }else{
    $pages_blacklist = ['setup'];
  }

  // get user info
  $user = Core::getUserLogged();
  ?>
  <ul class="nav navbar-nav" style="margin-left:10px;">

    <li class="dropup">

      <a class="dropdown-toggle cursor-pointer" data-toggle="dropdown" style="padding-top:12px; padding-bottom:0; z-index:999999">
        <span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span>
        <?php
        $picture_url = $user['picture'];
        if (preg_match('#^https?://#i', $picture_url) !== 1) {
          $picture_url = sanitize_url(sprintf(
            "%s%s", Configuration::$BASE, $picture_url
          ));
        }
        ?>
        <img src="<?php echo $picture_url; ?>" id="avatar">
      </a>

      <ul class="dropdown-menu dropup" role="menu" style="background-color:#3c3c3c; color:white">
        <?php
        // get pages
        $main_user_role = Core::getUserRole();
        $user_roles = Core::getUserRolesList();
        $pages = Core::getFilteredPagesList(
          'by-menuorder',
          true /* enabledOnly */,
          $user_roles /* accessibleBy */
        );

        foreach($pages as &$elem) {
          if (!is_null($pages_whitelist) && !in_array($elem['id'], $pages_whitelist)) {
            continue;
          }
          if (!is_null($pages_blacklist) && in_array($elem['id'], $pages_blacklist)) {
            continue;
          }
          // hide pages if maintenance mode is enabled
          if ($main_user_role != 'administrator' && Core::getSetting('maintenance_mode', 'core') && $elem['id']!='login') {
            continue;
          }
          // hide page if the current user' role is excluded
          if (count(array_intersect($user_roles, $elem['menu_entry']['exclude_roles'])) > 0) {
            continue;
          }
          $icon = sprintf('%s %s-%s', $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['class'], $elem['menu_entry']['icon']['name']);
          ?>
          <li role="presentation">
            <a
            role="menuitem"
            tabindex="-1"
            href="<?php echo Configuration::$BASE . $elem['id'] ?>"
            style="color:white"
            onMouseOver="this.style.color='#000'"
            onMouseOut="this.style.color='#FFF'"
            >
              <span class="<?php echo $icon ?>" aria-hidden="true"></span>
              &nbsp; <?php echo $elem['name'] ?>
            </a>
          </li>
          <?php
        }
        ?>
        <li class="divider"></li>
        <!-- Logout button -->
        <li role="presentation" style="margin-bottom:20px">
          <a role="menuitem" tabindex="-1" href="#"
          onclick="userLogOut('<?php echo Configuration::$BASE ?>', '<?php echo Configuration::$BASE ?>', '<?php echo Configuration::$WEBAPI_VERSION ?>', '<?php echo $_SESSION['TOKEN'] ?>');"
          style="color:#ffc864" onMouseOver="this.style.color='#000'" onMouseOut="this.style.color='#ffc864'">
          <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> &nbsp;Log out
        </a>
      </li>
    </ul>

  </li>

  </ul>

  <div style="float:left">
    <p style="margin:0; font-size:16px; margin-top:8px"><strong><?php echo $user['name'] ?></strong></p>
    <p style="margin:0; font-size:12px; margin-top:-4px"><?php echo $user['email'] ?></p>
  </div>
<?php
}


function footer_credits( $float) {
  ?>
  <table id="credits_table_<?php echo $float ?>">
    <tr>
      <td id="footer_compose_credit">
        <p>
          <span>
            powered by &nbsp;
          </span>
          <a href="https://github.com/afdaniele/compose" target="_blank">
            <img src="<?php echo Configuration::$BASE ?>images/compose-white-logo.svg"></img>
          </a>
        </p>
      </td>

      <td id="footer_compose_separator">
        &nbsp;|&nbsp;
      </td>

      <td id="footer_developer_credit">
        <?php
        $codebase_info = Core::getCodebaseInfo();
        $codebase_hash = $codebase_info['head_hash'];
        $codebase_tag = $codebase_info['head_tag'];
        $codebase_latest_tag = $codebase_info['latest_tag'];
        $codebase_tag = ( strcasecmp($codebase_tag, $codebase_latest_tag) === 0 )? $codebase_tag : 'devel';
        $codebase_str = ( in_array($codebase_tag, ['ND', null]) )? '' : sprintf("%s | ", $codebase_tag);
        ?>
        <p>
          <strong>developed by</strong> &nbsp; <a href="http://www.afdaniele.com" style="color:white">Andrea F. Daniele</a>
          <br/>
          <strong>serial</strong> &nbsp; <span style="font-family:monospace">git | <?php echo $codebase_str.$codebase_hash ?></span>
        </p>
      </td>

      <?php
      if (Core::getSetting('cache_enabled')) {
        ?>
        <td style="text-align:right; color: grey">
          <span
            id="emergency-clear-cache"
            class="glyphicon glyphicon-fire focus-on-hover pointer-hand"
            aria-hidden="true"
            data-toggle="tooltip"
            data-placement="top"
            title="Burn cache"
            ></span>
        </td>
        <?php
      }
      ?>
    </tr>
  </table>

  <script type="text/javascript">
    $('#emergency-clear-cache').on('click', function(){
			let url = "<?php echo Configuration::$BASE ?>script.php?script=clearcache";
      successDialog = true;
			reload = true;
      callType = 'GET';
      resultDataType = 'text';
      callExternalAPI(url, callType, resultDataType, successDialog, reload );
    });
  </script>
  <?php
}
?>


<footer id="footer" class="footer navbar-inverse" style="height:50px; color:#c3c3c3">

  <table style="width:100%">
    <tr>

      <?php
      if (Core::isUserLoggedIn()) {
        ?>
        <td class="text-left">
          <?php footer_user_menu(); ?>
        </td>

        <td class="text-right">
          <?php footer_credits('right'); ?>
        </td>
        <?php
      }else{
        ?>
        <td>
          <?php footer_credits('center'); ?>
        </td>
        <?php
      }
      ?>

    </tr>
  </table>


</footer>
