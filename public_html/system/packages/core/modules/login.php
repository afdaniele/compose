<?php
use \system\classes\Core;
use \system\classes\Configuration;

if (!Core::isUserLoggedIn() || Core::getLoginSystem() == '__GOOGLE_SIGNIN__') {
  include('google_signin.php');
}
?>

<script type="text/javascript">

  function logOutButtonClick(){
    userLogOut(
      '<?php echo Configuration::$BASE ?>',
      '<?php echo Configuration::$WEBAPI_VERSION ?>',
      '<?php echo $_SESSION['TOKEN'] ?>',
      function(){ /* successFcn: on success function */
        $(window).trigger('COMPOSE_LOGGED_OUT');
      }
    );
  }//logOutButtonClick


  $(window).on('COMPOSE_LOGGED_OUT', function(evt){
    <?php
    if(Core::getLoginSystem() == '__GOOGLE_SIGNIN__'){
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
      printf("window.location.href = '%s';", Configuration::$BASE);
    }
    ?>
  });

</script>
