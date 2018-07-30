<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Saturday, January 13th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, February 5th 2018

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Formatter;

if( isset($_GET['delete']) ){
	$res = Core::deleteErrorRecord( $_GET['delete'] );
	if( !$res['success'] ) Core::throwError( $res['data'] );
}

$errors_ids = Core::getErrorRecordsList();
?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">
		<tr>
			<td style="width:50%">
				<h2>Debug</h2>
			</td>
		</tr>
	</table>

	<p>
	    This table shows errors reported by the users.
	</p>

	<div class="text-center" style="padding:10px 0">
	    <table class="table table-bordered table-striped" style="margin:auto">
	        <tr style="font-weight:bold">
	            <td class="col-md-2">Reported (GMT)</td>
	            <td class="col-md-8">Error</td>
	            <td class="col-md-2">Actions</td>
	        </tr>
	        <?php
	        foreach( $errors_ids as &$error_id ){
				// get error
				$res = Core::getErrorRecord( $error_id );
				if( !$res['success'] ) Core::throwError( $res['data'] );
				$error_data = $res['data'];
				// build `delete` url
				$url = sprintf(
					'%s%s?delete=%s',
					Configuration::$BASE_URL,
					'debug',
					$error_id
				);?>
                <tr>
                    <td style="vertical-align:middle"><?php echo $error_data['datetime'] ?></td>
                    <td class="mono" style="color:orangered"><?php echo $error_data['message'] ?></td>
                    <td style="vertical-align:middle">
						<?php
						$github_issue_url = sprintf(
							'https://github.com/afdaniele/compose/issues/new?labels=bug&title=%s&body=%s',
							urlencode(sprintf('Auto-Error: #%s', $error_id)),
							sprintf(
								'%s%s%s%s%s',
								urlencode(sprintf('Error reported at `%s GMT`.',$error_data['datetime'])),
								'%0A%0A',
								urlencode('**Error message:**'),
								'%0A',
								urlencode(sprintf('```%s```',$error_data['message']))
							)
						);
						?>
						<a class="btn btn-default " role="button" data-toggle="tooltip"
							href="<?php echo $github_issue_url ?>" target="_blank"
							data-placement="bottom" data-original-title="Create new issue on GitHub">
							<span class="fa fa-github" aria-hidden="true"></span> New
						</a>
						<span>&nbsp;|&nbsp;</span>
						<button class="btn btn-warning " type="button" data-toggle="tooltip"
							data-placement="bottom" data-original-title="Delete error record"
							onclick="openYesNoModal('Are you sure you want to delete this error record?', function(){location.href='<?php echo $url ?>';} )">
							&nbsp;<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;
						</button>
					</td>
                </tr>
	            <?php
	        }
	        ?>
	    </table>
	</div>
</div>
