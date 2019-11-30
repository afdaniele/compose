<?php
use \system\classes\Core;
use \system\classes\Configuration;

if (!Core::getSetting('developer_mode')) {
  return;
}

$icon_url = Core::getImageURL('developer.jpg');
?>

<button type="button" class="login-button">
  <span class="login-button-icon">
    <img src="<?php echo $icon_url ?>"/>
  </span>
  <span class="login-button-text" style="background-color: #ffb296; color: #545454" onclick="developerLogIn()">
    Sign in as Developer
  </span>
</button>
