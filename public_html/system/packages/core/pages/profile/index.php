<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Saturday, January 13th 2018

?>

<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:50%">
				<h2>Your account</h2>
			</td>
		</tr>

	</table>

	<?php

	$user = \system\classes\Core::getUserLogged();

	$labelName = array('Name', 'E-mail address', 'Account type' );
	$fieldValue = array( $user['name'], $user['email'], ucfirst($user['role']) );

	?>

	<h4>Personal Information</h4>
	<nav class="navbar navbar-default" role="navigation" style="margin-bottom:36px">
		<div class="container-fluid" style="padding-left:0; padding-right:0">

			<div class="collapse navbar-collapse navbar-left" style="padding:0; width:100%">

				<table style="width:100%">
					<tr>
						<td class="col-md-3 text-center" style="border-right:1px solid lightgray">
							<h3 style="margin:0">
								<div class="text-center col-md-12" id="profile_page_avatar">
									<img src="<?php echo $user['picture']; ?>" id="avatar">
								</div>
							</h3>
						</td>
						<td class="col-md-9" style="padding:20px">
							<?php
							generateView( $labelName, $fieldValue, 'md-3', 'md-9' );
							?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</nav>

</div>
