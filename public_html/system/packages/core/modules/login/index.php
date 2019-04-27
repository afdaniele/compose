<?php
use \system\classes\Core;
use \system\classes\Configuration;

if (!Core::getSetting('developer_mode')) {
  return;
}

if (isset($_GET['developer']) && boolval($_GET['developer'])) {
  // login as developer
  $res = Core::logInAsDeveloper();
  if (!$res['success']) {
    Core::throwError($res['data']);
  }else{
    unset($_GET['developer']);
    Core::redirectTo('', true);
  }
}

$icon_url = Core::getImageURL('developer.jpg');
?>

<button type="button" class="login-button">
  <span class="login-button-icon">
    <img src="<?php echo $icon_url ?>"/>
  </span>
  <span class="login-button-text" style="background-color: #ffb296; color: #545454" onclick="login_as_developer()">
    Sign in as Developer
  </span>
</button>


<script type="text/javascript">

  function login_as_developer(){
    var url = "<?php echo Core::getURL('login', null, null, null, ['q' => $_GET['q'], 'developer' => 1]) ?>";
    window.open(url, "_top");
  }//login_as_developer

</script>
