<?php
use \system\classes\Core;
use \system\classes\Configuration;

if (!Core::isUserLoggedIn() || Core::getLoginSystem() == '__GOOGLE_SIGNIN__') {
  include(__DIR__ . '/google_signin.php');
}
?>

<script type="text/javascript">

  function logOutButtonClick(){
    userLogOut(
      '<?php echo Configuration::$BASE ?>',
      '<?php echo Configuration::$WEBAPI_VERSION ?>',
      '<?php echo Configuration::$TOKEN ?>',
      function(){ /* successFcn: on success function */
        $(window).trigger('COMPOSE_LOGGED_OUT');
      }
    );
  }//logOutButtonClick


  $(window).on('COMPOSE_LOGGED_OUT', function(evt){
    <?php
    $resource = strlen(trim(Configuration::$BASE)) == 0? './' : Configuration::$BASE;
    if(Core::getLoginSystem() == '__GOOGLE_SIGNIN__'){
      ?>
      // Sign-out from Google
      const auth2 = gapi.auth2.getAuthInstance();
      auth2.signOut().then(function(){
        $(window).trigger('GOOGLE_LOGGED_OUT');
        hidePleaseWait();
        window.location.href = '<?php echo $resource ?>';
      });
      <?php
    }else{
      printf("window.location.href = '%s';", $resource);
    }
    ?>
  });

</script>
