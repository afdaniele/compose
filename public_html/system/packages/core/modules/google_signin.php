<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

$is_login_enabled = Core::getSetting('login_enabled', 'core');
$is_google_client_id_set = strlen(Core::getSetting('google_client_id', 'core')) > 4;
$login_with_google_enabled = $is_login_enabled && $is_google_client_id_set;
$is_logged_in = Core::isUserLoggedIn();
?>

<script type="text/javascript">

  <?php
  if (!$is_logged_in && $login_with_google_enabled) {
    ?>
    $(window).on('GOOGLE_LOGGED_IN', function(evt){
			// sign-in with Google and get the temporary id_token
			var googleUser = gapi.auth2.getAuthInstance().currentUser.get();
			var id_token = googleUser.getAuthResponse().id_token;
			// Sign-in in the back-end server by verifying the id_token with Google
			userLogInWithGoogle(
				'<?php echo Configuration::$BASE_URL ?>',
				'<?php echo Configuration::$WEBAPI_VERSION ?>',
				'<?php echo $_SESSION['TOKEN'] ?>',
				id_token,
        function(){
          $(window).trigger('COMPOSE_LOGGED_IN', ['google']);
        }
			);
    });


    function onGoogleLoginError(error){
      if (error.hasOwnProperty('error') && error.error == 'popup_closed_by_user')
        return;
      var msg = "An error occurred while authenticating with Google. The server returns: '{0}'.";
      msg += "<br/>Make sure the hostname <strong>{1}</strong> is whitelisted on";
      msg += " <a href=\"https://console.developers.google.com/\">https://console.developers.google.com/</a>"
      msg += " for your project's client ID."
      msg = msg.format(JSON.stringify(error), "<?php echo $_SERVER['HTTP_HOST'] ?>");
      openAlert('danger', msg);
    }//onGoogleLoginError
    <?php
  }
  ?>

  $(window).on('GOOGLE_SIGNIN_LOADED', function(evt){
    // initialize Google Sign-in library
    gapi.auth2.init();
    <?php
    if (Configuration::$PAGE == 'login') {
      ?>
      // render Sign-in button
      if ($("#g-signin").length) {
        gapi.signin2.render('g-signin', {
          'scope': 'profile email',
          'width': 240,
          'height': 50,
          'longtitle': true,
          'onsuccess': function(user){$(window).trigger("GOOGLE_LOGGED_IN", [user])},
          'onfailure': onGoogleLoginError
        });
      }
      <?php
    }
    ?>
  });

  $(document).on('ready', function(){
    <?php
    if($login_with_google_enabled){
      ?>
      // load google sign-in library
  		gapi.load('signin2', function(){
        $(window).trigger('GOOGLE_SIGNIN_LOADED', ['google']);
      });
    <?php
    }
    if (Configuration::$PAGE == 'login' && !$login_with_google_enabled) {
    ?>
      $("#g-signin").html('Login with Google is not configured yet');
    <?php
    }
    ?>
  });

</script>
