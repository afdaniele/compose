<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;

$is_login_enabled = Core::getSetting('login_enabled', 'core');
$is_google_client_id_set = strlen(Core::getSetting('google_client_id', 'core')) > 4;
$login_with_google = $is_login_enabled && $is_google_client_id_set;
?>

<script type="text/javascript">

  <?php
  if(Core::isUserLoggedIn()){
    ?>
    $(window).on('COMPOSE_LOGGED_OUT', function(evt){
      <?php
      if($login_with_google){
        ?>
        // Sign-out from Google
        var auth2 = gapi.auth2.getAuthInstance();
        auth2.signOut().then(function(){
          $(window).trigger('GOOGLE_LOGGED_OUT');
          hidePleaseWait();
          window.location.href = '<?php echo Configuration::$BASE ?>';
        });
        <?php
      }else{
        echo "window.location.href = '<?php echo Configuration::$BASE ?>';";
      }
      ?>
    });
    <?php
  }else{
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
    <?php
  }
  ?>

  function logOutButtonClick(){
    userLogOut(
      '<?php echo Configuration::$BASE_URL ?>',
      '<?php echo Configuration::$WEBAPI_VERSION ?>',
      '<?php echo $_SESSION['TOKEN'] ?>',
      function(){ /* successFcn: on success function */
        $(window).trigger('COMPOSE_LOGGED_OUT');
      }
    );
  }//logOutButtonClick

  function onGoogleLoginError(error){
    var msg = "An error occurred while authenticating with Google. The server returns: '{0}'.";
    msg += "<br/>Make sure the hostname <strong>{1}</strong> is whitelisted on";
    msg += " <a href=\"https://console.developers.google.com/\">https://console.developers.google.com/</a>"
    msg += " for your project's client ID."
    msg = msg.format(JSON.stringify(error), "<?php echo $_SERVER['HTTP_HOST'] ?>");
    openAlert('danger', msg);
  }//onGoogleLoginError

  function renderGoogleLoginButton() {
    gapi.auth2.init();
    if ($("#g-signin" ).length) {
      gapi.signin2.render('g-signin', {
        'scope': 'profile email',
        'width': 240,
        'height': 50,
        'longtitle': true,
        'onsuccess': function(user){$(window).trigger("GOOGLE_LOGGED_IN", [user])},
        'onfailure': onGoogleLoginError
      });
    }
  }//renderGoogleLoginButton

  $(document).on('ready', function(){
    // initialize Google Sign-in library and there is at least one Google button
		gapi.load('signin2', renderGoogleLoginButton);
  });

</script>
