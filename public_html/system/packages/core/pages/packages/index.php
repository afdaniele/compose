<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;

?>


<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:22px">
	<tr>
		<td style="width:100%">
			<h2>Packages</h2>
		</td>
	</tr>
</table>

<?php
$assets_index_url = sprintf('%s/%s/index', Configuration::$ASSETS_STORE_URL, 'master');
$assets_index = file_get_contents($assets_index_url);

echoArray($assets_index);
?>
