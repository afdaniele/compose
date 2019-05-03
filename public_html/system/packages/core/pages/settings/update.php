<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Cache;
use \system\classes\enum\StringType;


$version = null;
if (isset($_GET['version']) && StringType::isValid($_GET['version'], StringType::VERSION)) {
  $version = trim($_GET['version']);
}
?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>Updating <b>&bsol;compose&bsol;</b>...</h2>
			</td>
		</tr>

	</table>

  <div class="progress">
    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
    </div>
  </div>

</div>

<?php
$res = Core::updateBase($version);
if (!$res['success']) {
  Core::throwError($res['data']);
}else{
  Core::requestAlert('INFO', '<b>&bsol;compose&bsol;</b> is now updated!');
  Cache::clearAll();
  ?>
  <script type="text/javascript">
    $(document).ready(function(){
      clearUpdatesCache();
      redirectTo('settings');
    });
  </script>
  <?php
}
?>
