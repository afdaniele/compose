<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\classes\Configuration;
use \system\classes\Core;
?>

<section>
  <div class="container login">
    <div class="row" style="width:480px; margin:auto">
      <div class="center span4 well">
        <div class="col-md-6">
          <h3 style="margin-top:0"><strong><span class="glyphicon glyphicon-lock" aria-hidden="true"></span> &nbsp;Sign in</strong></h3>
        </div>
        <div class="col-md-6">
          <img id="loginLogo" src="<?php echo Core::getSetting('logo_black') ?>"/>
        </div>
        <br>
        <br>
        <br>
        <legend></legend>

        <div class="text-center" style="padding:25px 0 35px 0">
          <?php
          $login_enabled = Core::getSetting('login_enabled', 'core', False);
          if( $login_enabled ){
            ?>
            <div id="g-signin" class="text-left" style="margin-left:100px;"></div>
            <!--  -->
            <img id="signin-loader" src="<?php echo Configuration::$BASE_URL ?>images/loading_blue.gif" style="display:none; width:32px; height:32px; margin-top:10px">
            <?php
            // get list of login plugins files
            $login_addon_files_per_pkg = Core::getPackagesModules('login', null);
            if(count($login_addon_files_per_pkg) > 0){
              echo '<legend style="width: 100px; margin: 20px auto"></legend>';
            }
            // render add-ons
            foreach ($login_addon_files_per_pkg as $pkg_id => $login_addon_files) {
              require_once $login_addon_files[0];
            }
          }else{
            ?>
            <h3>Login disabled.</h3>
            <?php
          }
          ?>
        </div>

        <legend style="margin-top:4px"></legend>

        <p style="color:grey">
          <?php echo Core::getSiteName() ?> uses the <a href="https://developers.google.com/identity/">Google Sign-In API</a>
          authentication service.
        </p>
      </div>
    </div>
  </div>
  <p class="text-center muted" style="color:grey; margin-top:-10px">&copy; Copyright <?php echo date("Y"); ?> - <?php echo Core::getSiteName() ?></p>
</section>

<script type="text/javascript">
  $(window).on('COMPOSE_LOGGED_IN', function(){
    window.open("<?php echo Configuration::$BASE_URL.base64_decode($_GET['q']) ?>", "_top");
  });
</script>
