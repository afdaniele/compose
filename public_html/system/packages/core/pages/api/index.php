<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018


$api_setup = \system\classes\Core::getAPIsetup();

// parse the version argument
$version = ( ( isset($_GET['version']) && in_array(strtolower($_GET['version']), array_keys($api_setup), true) )? strtolower($_GET['version']) : ( (!isset($_GET['version']))? \system\classes\Configuration::$WEBAPI_VERSION : null ) );
if( $version == null ){
	// The required version is not valid
	\system\classes\Core::redirectTo( 'api?version='.\system\classes\Configuration::$WEBAPI_VERSION );
}else{
	$web_api_specification = $api_setup[$version];

	$sget = ( (isset($_GET['service']) && in_array($_GET['service'], array_keys($web_api_specification['services'])) )? $_GET['service'] : null );
	$aget = ( ($sget !== null && isset($_GET['action']) && in_array($_GET['action'], array_keys($web_api_specification['services'][$sget]['actions'])) )? $_GET['action'] : null );

	// load the api-service-specifications
	$service_specification = ( ($sget !== null)? $web_api_specification['services'][$sget] : array() );
	$action_specification = $service_specification['actions'][$aget];

	$action_specification['parameters']['mandatory'] = array_merge( $web_api_specification['global']['parameters']['mandatory'], $action_specification['parameters']['mandatory'] );

	// if the APIvXX is offLine then also its services are offLine
	if( $sget!== null && !$web_api_specification['enabled'] ){
		$web_api_specification['services'][$sget]['enabled'] = false;
	}

	// if the service SS is offLine then also its actions are offLine
	if( $sget!== null && $aget!== null && !$web_api_specification['services'][$sget]['enabled'] ){
		$web_api_specification['services'][$sget]['actions'][$aget]['enabled'] = false;
	}

	$api_enabled = $web_api_specification['enabled'];
	$service_enabled = $web_api_specification['services'][$sget]['enabled'];
	$action_enabled = $web_api_specification['services'][$sget]['actions'][$aget]['enabled'];

	$access_level = $web_api_specification['services'][$sget]['actions'][$aget]['access_level'];

	?>


	<div style="width:100%; margin:auto">

	<table style="width:100%; border-bottom:1px solid #ddd; margin-bottom:32px">

		<tr>
			<td style="width:100%">
				<h2>RESTful API Documentation</h2>
			</td>
		</tr>

	</table>

	<p>
		Versions available:
		&nbsp;
		<?php
		foreach( $api_setup as $key => $_ ){
			?>
			<span class="label label-<?php echo ( ($version == $key)? 'primary' : 'default' ) ?> api-version-label">
					<a href="<?php echo \system\classes\Configuration::$BASE.'api?version='.$key ?>">
						<?php echo $key ?>
					</a>
				</span>
			<span style="padding-left:4px"></span>
		<?php
		}
		?>
	</p>

	<p class="text-right" style="display:table; clear:both; width:100%; margin-top:16px; margin-bottom:4px">
		<span style="float:left">
			<?php
			if( $sget !== null && $aget !== null ){
				?>
				<a href="<?php echo \system\classes\Configuration::$BASE.'api?version='.$version ?>"><span class="glyphicon glyphicon-arrow-left"></span> &nbsp;Back</a>
			<?php
			}else{
				$servs_count = sizeof( array_keys($web_api_specification['services']) );
				$acts_count = 0;
				//
				foreach( $web_api_specification['services'] as $k => $s ){
					$acts_count += sizeof( array_keys($s['actions']) );
				}
				?>
				Total: <span class="param"><?php echo $acts_count ?></span> actions in <span class="param"><?php echo $servs_count ?></span> services
			<?php
			}
			?>
		</span>

		<span style="float:right">
			API (v<?php echo $version ?>)&nbsp; | &nbsp;Status: <?php echo ( ($api_enabled)? '<span class="on">OnLine</span>' : '<span class="off">OffLine</span>' ) ?>
		</span>
	</p>



	<table class="api-box-container" style="width:100%">
	<tbody>
	<tr>
	<td style="width:243px">

		<div class="api-box">

			<p>
				<strong>
					API Services
				</strong>
			</p>


			<div class="panel-group" id="services-list" role="tablist" aria-multiselectable="true" style="padding-left:4px">

				<?php
				foreach( $web_api_specification['services'] as $sname => $service ){
					?>
					<div class="panel">
						<div class="panel-heading" role="tab" id="service-panel-head-<?php echo $sname ?>">
							<h5 class="panel-title">
								<a href="#service-panel-body-<?php echo $sname ?>" <?php echo ( ($sget == $sname)? 'class="active"' : '' ) ?> data-toggle="collapse" data-parent="#services-list" aria-expanded="true" aria-controls="service-panel-body-<?php echo $sname ?>">
									<table>
										<tr>
											<td>
												<span class="glyphicon glyphicon-tasks" aria-hidden="true"></span>
											</td>
											<td>&nbsp;</td>
											<td>
												<span><?php echo $sname ?></span>
											</td>
										</tr>
									</table>
								</a>
							</h5>
						</div>
						<div id="service-panel-body-<?php echo $sname ?>" class="panel-collapse collapse <?php echo ( ($sget == $sname)? 'in' : '' ) ?>" role="tabpanel" aria-labelledby="service-panel-head-<?php echo $sname ?>">
							<div class="panel-body">
								<?php
								foreach( $service['actions'] as $aname => $action ){
									?>
									<a href="<?php echo \system\classes\Configuration::$BASE ?>api?version=<?php echo $version ?>&service=<?php echo $sname ?>&action=<?php echo $aname ?>" <?php echo ( ($sget == $sname && $aget == $aname)? 'class="active"' : '' ) ?>>
										<table>
											<tr>
												<td style="padding-bottom:2px">
													<span class="glyphicon glyphicon-cog" aria-hidden="true" style="font-size:10pt"></span>
												</td>
												<td style="width:6px">&nbsp;</td>
												<td>
													<span><?php echo $aname ?></span>
												</td>
											</tr>
										</table>
									</a>
								<?php
								}
								?>
							</div>
						</div>
					</div>
				<?php
				}
				?>

			</div>

		</div>


		<p class="text-right" style="margin-top:8px; font-size:12px">
			<strong style="color:#337ab7">Session token: </strong><?php echo $_SESSION["TOKEN"] ?>
		</p>

	</td>

	<td style="width:727px">

	<div class="api-box api-service-box">

	<div>

	<?php
	//
	if( $sget == null && $aget == null ){
		?>

		<div class="api-service-breadcrumb">
			<table>
				<tr>
					<td>
						<h2 style="margin:0; padding-right:24px">
							<span class="glyphicon glyphicon-book" aria-hidden="true"></span>
						</h2>
					</td>
					<td class="mono">
						<h3 class="text-right" style="margin:0">API v<?php echo $version ?> - Documentation</h3>
					</td>
				</tr>
			</table>
		</div>


		<div class="api-service-box-container">

		<div class="api-service-section" style="padding-top:40px">
			<h3>
				Table of contents
			</h3>
			<div class="api-service-toc">
				<ul>
					<li>
						<a href="#begin">Introduction</a>
					</li>
					<ul>
						<li>
							<a href="#service-help">What is a Service?</a>
						</li>
						<li>
							<a href="#action-help">What is an Action?</a>
						</li>
					</ul>
				</ul>
				<ul>
					<li>
						<a href="#request">The HTTP Request</a>
					</li>
					<ul>
						<li>
							<a href="#reply-format">Response format</a>
						</li>
						<li>
							<a href="#reply-parameters">Parameters</a>
						</li>
					</ul>
				</ul>
				<ul>
					<li>
						<a href="#reply">The HTTP Response</a>
					</li>
					<ul>
						<li>
							<a href="#success-codes">Success codes</a>
						</li>
						<li>
							<a href="#error-codes">Error codes</a>
						</li>
					</ul>
				</ul>
			</div>
		</div>


		<div class="api-service-section">
			<a class="anchor" id="begin"></a>
			<h3>
				Introduction
			</h3>
			<div>
				Let's start with a quick introduction to the terms <bold>Service</bold> and <bold>Action</bold>.

				<div class="api-service-subsection">
					<a class="anchor" id="service-help"></a>
					<h3>
						What is a Service?
					</h3>
					<div>
						<p>
							A <bold>Service</bold> is a collection of functions accessible through HTTP requests.
							The functions of a service are called <bold>Actions</bold>.
						</p>
						<p>
							A service can be temporarily or permanently disabled by the Administrator.
							A service can be either <bold>Online</bold>, thus ready to accept requests, or <bold>Offline</bold>, in which case it will result as unreachable via HTTP.
						</p>
						<p>
							When the Administrator disables a service, all the actions belonging to that service will go Offline.
						</p>
					</div>
				</div>

				<div class="api-service-subsection">
					<a class="anchor" id="action-help"></a>
					<h3>
						What is an Action?
					</h3>
					<div>
						<p>
							An <bold>Action</bold> defines a possible interaction between the user (who generates the HTTP request) and the system (that answers the HTTP request).
							It is executed by the server on the server itself.
						</p>
						<p>
							An action can be temporarily or permanently disabled by the Administrator.
							An action can be either <bold>Online</bold>, thus ready to accept requests, or <bold>Offline</bold>, in which case it will result as unreachable via HTTP.
						</p>
						<p>
							Each action defines a list of <emph>parameters</emph>. Parameters are used by the user to pass arguments to the action.
						</p>
					</div>
				</div>
			</div>
		</div>




		<div class="api-service-section">
			<a class="anchor" id="request"></a>
			<h3>
				The HTTP Request
			</h3>
			<div>
				<p>
					The following box shows the structure of the URL for a generic API request executed using the HTTP protocol.
				</p>
				<div class="mono api-url-container">
					<p>
						<?php echo \system\classes\Configuration::$BASE.'web-api/' ?><span class="emph param">version</span>/<span class="emph param">service</span>/<span class="emph param">action</span>/<span class="emph param">format</span>?<span class="emph param">parameters</span>
					</p>
				</div>

				<p>
					<br/>
					A URL for an API request is defined by 6 parts:
					<span class="param emph" style="color:black">base_url</span>,
					<span class="param emph">version</span>,
					<span class="param emph">service</span>,
					<span class="param emph">action</span>,
					<span class="param emph">format</span>,
					<span class="param emph">parameters</span>
					.
				</p>


				<div class="api-service-subsection">
					<a class="anchor" id="reply-base_url"></a>
					<h4>
						&bullet; <span class="param emph" style="color:black">base_url</span>:
					</h4>
					<div>
						<p>
							The first part of the URL is the base URL and it is fixed. It has the form <span class="mono">your_domain.tld/web-api</span>&nbsp;
							(e.g., "<span class="mono"><?php echo \system\classes\Configuration::$BASE.'web-api/' ?></span>").
						</p>
					</div>
				</div>

				<div class="api-service-subsection">
					<a class="anchor" id="reply-version"></a>
					<h4>
						&bullet; <span class="param emph">version</span>:
					</h4>
					<div>
						<p>
							Indicates the version of the API to use (e.g., <span class="emph param">1.0</span>).
						</p>
					</div>
				</div>


				<div class="api-service-subsection">
					<a class="anchor" id="reply-service"></a>
					<h4>
						&bullet; <span class="param emph">service</span>:
					</h4>
					<div>
						<p>
							Indicates the service requested by the user among those exported by the API selected (e.g., <span class="param emph">duckiebot</span>).
						</p>
					</div>
				</div>


				<div class="api-service-subsection">
					<a class="anchor" id="reply-action"></a>
					<h4>
						&bullet; <span class="param emph">action</span>:
					</h4>
					<div>
						<p>
							Indicates the action that the user wants to perform among those published by the service selected (e.g., <span class="param emph">status</span>).
						</p>
					</div>
				</div>


				<div class="api-service-subsection">
					<a class="anchor" id="reply-format"></a>
					<h4>
						&bullet; <span class="param emph">format</span>:
					</h4>
					<div>
						<p>
							Specifies how to format the response provided by the server. The formats available are:
						</p>
						<p>
					<span style="padding-left:30px">
						{ <?php echo '<span class="param">' . implode( '</span>, <span class="param">', $web_api_specification['global']['parameters']['embedded']['format']['values'] ) . '</span>' ?> }
					</span>
						</p>
					</div>
				</div>


				<div class="api-service-subsection">
					<a class="anchor" id="reply-parameters"></a>
					<h4>
						&bullet; <span class="param emph">parameters</span>:
					</h4>
					<div>
						<p>
							It is a string containing a contenation of <span class="param emph">parameter=value</span> pairs expressed in the standard Query string format.
							A generic Query string has the form:
						</p>
						<p style="padding-left:30px">
							<span class="param">parameter1</span>=<span class="param">value1</span>&<span class="param">parameter2</span>=<span class="param">value2</span>&...</span>
						</p>
						<p>
							Select a service and an action from the menu on the left hand side of this page to see the list of parameters for a specific action.
						</p>
					</div>
				</div>

			</div>
		</div>




		<div class="api-service-section">
			<a class="anchor" id="reply"></a>
			<h3>
				The HTTP Response
			</h3>
			<div>
				<p>
					A generic response generated by the server as result of an action is the following:
				</p>
				<p>
					When <span class="param emph">format</span>=<span class="param emph" style="color:black">json</span>:
				</p>
				<div class="mono api-url-container">
					<p>
						{<br/>
						&nbsp;&nbsp;&nbsp;"code" : 200,<br/>
						&nbsp;&nbsp;&nbsp;"status" : "OK",<br/>
						&nbsp;&nbsp;&nbsp;"data" : {<br/>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br/>
						&nbsp;&nbsp;&nbsp;}<br/>
						}
					</p>
				</div>
				<br>
				<p>
					When <span class="param emph">format</span>=<span class="param emph" style="color:black">xml</span>:
				</p>
				<div class="mono api-url-container">
					<p>
						&lt;?xml version="1.0" encoding="UTF-8"?&gt;<br/>
						&lt;result&gt;<br/>
						&nbsp;&nbsp;&nbsp;&lt;code&gt;200&lt;/code&gt;<br/>
						&nbsp;&nbsp;&nbsp;&lt;status&gt;OK&lt;/status&gt;<br/>
						&nbsp;&nbsp;&nbsp;&lt;data&gt;<br/>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br/>
						&nbsp;&nbsp;&nbsp;&lt;/data&gt;<br/>
						&lt;/result&gt;
					</p>
				</div>

				<p>
					<br/>
					The object <span class="param emph">code</span> contains the status of the API call using standard HTTP status codes. The sections <a href="#success-codes">Success codes</a> and <a href="#error-codes">Error codes</a> explains the meaning of the status codes used by this API implementation.
				</p>
				<p>
					The object <span class="param emph">status</span> contains the status of the API call as a status string. Each error code is associated with a status string. The sections <a href="#success-codes">Success codes</a> and <a href="#error-codes">Error codes</a> contains a list of status strings used by this API implementation.
				</p>
				<p>
					The object <span class="param emph">data</span> contains the data returned by the execution of the selected action. The structure of this object depends on the specific action called. Use the menu on the left hand side of this page to access the documentation for specific actions to see the format of this object.
				</p>

				<div class="api-service-subsection">
					<a class="anchor" id="success-codes"></a>
					<h3>
						Success codes
					</h3>
					<div>
						<p>
							All the HTTP success codes belong to the family of codes <span class="mono">20X</span>.
							The success codes used in this API are:
						</p>
						<p style="padding-left:30px">
							<span class="param">200</span> &nbsp;&nbsp;&nbsp;&nbsp; "OK"
							<br/>
							<small>The API call was successfull.</small>
						</p>
						<br/>
						<p style="padding-left:30px">
							<span class="param">204</span> &nbsp;&nbsp;&nbsp;&nbsp; "No Content"
							<br/>
							<small>The API call was successfull but no data was retrieved. The server returns an empty response.</small>
						</p>
					</div>
				</div>

				<div class="api-service-subsection">
					<a class="anchor" id="error-codes"></a>
					<h3>
						Error codes
					</h3>
					<div>
						<p>
							We distinguish between client-side errors (<span class="mono">4XX</span> erros) ed server-side errors (<span class="mono">5XX</span> errors).
							The error codes used in this API are:
						</p>
						<p style="padding-left:30px">
							<span class="param">400</span> &nbsp;&nbsp;&nbsp;&nbsp; "Bad Request"
							<br/>
							<small>The server cannot or will not process the request due to an apparent client error (e.g., malformed request syntax)</small>
						</p>
						<br/>
						<p style="padding-left:30px">
							<span class="param">401</span> &nbsp;&nbsp;&nbsp;&nbsp; "Unauthorized"
							<br/>
							<small>The request was valid, but the server is refusing action. The user might not have the necessary permissions.</small>
						</p>
						<br/>
						<p style="padding-left:30px">
							<span class="param">404</span> &nbsp;&nbsp;&nbsp;&nbsp; "Not Found"
							<br/>
							<small>The server does not recognize the requested service/action.</small>
						</p>
						<br/>
						<p style="padding-left:30px">
							<span class="param">426</span> &nbsp;&nbsp;&nbsp;&nbsp; "Upgrade Required"
							<br/>
							<small>The client should switch to a different version of the API. The API used is obsolete.</small>
						</p>
						<br/>
						<p style="padding-left:30px">
							<span class="param">500</span> &nbsp;&nbsp;&nbsp;&nbsp; "Internal Server Error"
							<br/>
							<small>A generic error message, given when an unexpected condition was encountered.</small>
						</p>
						<br/>
						<p style="padding-left:30px">
							<span class="param">503</span> &nbsp;&nbsp;&nbsp;&nbsp; "Service Unavailable"
							<br/>
							<small>The requested service/action is currently Offline.</small>
						</p>
					</div>
				</div>
			</div>
		</div>

		</div>

	<?php
	}else{
		?>

		<div class="api-service-breadcrumb">
			<table>
				<tr>
					<td rowspan="2">
						<h2 style="margin:0; padding-right:24px">
							<span class="glyphicon glyphicon-book" aria-hidden="true"></span>
						</h2>
					</td>
					<td class="mono" style="padding-right:14px; border-right:1px solid #d8d8d8">
						<h3 class="text-right" style="margin:0"><?php echo $sget ?></h3>
					</td>
					<td class="mono" style="padding-left:14px">
						<h3 class="text-left" style="margin:0"><?php echo $aget ?></h3>
					</td>
				</tr>
				<tr>
					<td class="mono" style="padding-right:14px;  border-right:1px solid #d8d8d8">
						<h6 class="text-right" style="color:#d3d3d3; margin:0">service</h6>
					</td>
					<td class="mono" style="padding-left:14px">
						<h6 class="text-left" style="color:#d3d3d3; margin:0">action</h6>
					</td>
				</tr>
			</table>
		</div>



		<div class="api-service-box-container" style="position:relative">

		<table style="position:absolute; right:0; top:40px">
			<tbody>
			<tr id="cp_api_tr">
				<td class="mono" style="width:76px">
					<h4 class="text-center" style="margin:0; margin-bottom:6px">
						<span class="glyphicon glyphicon-record <?php echo ( ($api_enabled)? 'on' : 'off' ) ?>" aria-hidden="true"></span>
					</h4>
				</td>
			</tr>
			<tr>
				<td class="mono" style="padding-bottom:8px">
					<h6 class="text-center" style="color:#8b8b8b; margin:0">APIv<?php echo $version ?></h6>
				</td>
			</tr>

			<tr>
				<td style="border-bottom:1px solid #d8d8d8"></td>
			</tr>

			<tr id="cp_service_tr">
				<td class="mono" style="width:76px; padding-top:8px">
					<h4 class="text-center" style="margin:0; margin-bottom:6px">
						<span class="glyphicon glyphicon-record <?php echo ( ($service_enabled)? 'on' : 'off' ) ?>" aria-hidden="true"></span>
					</h4>
				</td>
			</tr>
			<tr>
				<td class="mono" style="padding-bottom:8px">
					<h6 class="text-center" style="color:#8b8b8b; margin:0">service</h6>
				</td>
			</tr>

			<tr>
				<td style="border-bottom:1px solid #d8d8d8"></td>
			</tr>

			<tr id="cp_action_tr">
				<td class="mono" style="width:76px; padding-top:8px">
					<h4 class="text-center" style="margin:0; margin-bottom:6px">
						<span class="glyphicon glyphicon-record <?php echo ( ($action_enabled)? 'on' : 'off' ) ?>" aria-hidden="true"></span>
					</h4>
				</td>
			</tr>
			<tr>
				<td class="mono">
					<h6 class="text-center" style="color:#8b8b8b; margin:0">action</h6>
				</td>
			</tr>
			</tbody>
		</table>



		<div class="api-service-section" style="padding-top:40px">
			<h3>
				Table of contents
			</h3>
			<div class="api-service-toc">
				<ul>
					<li>
						<a href="#service">The <span class="mono"><?php echo $sget ?></span> service</a>
					</li>
				</ul>

				<ul>
					<li>
						<a href="#action">The <span class="mono"><?php echo $aget ?></span> action</a>
					</li>
					<ul>
						<li>
							<a href="#restrictions">Access privileges</a>
						</li>
						<li>
							<a href="#request">How to execute it</a>
						</li>
						<li>
							<a href="#mandatory">Mandatory parameters</a>
						</li>
						<li>
							<a href="#optional">Optional parameters</a>
						</li>
						<li>
							<a href="#reply">Response</a>
						</li>
					</ul>
				</ul>

				<!-- <ul>
					<li>
						<a href="#tiy">Try it yourself</a>
					</li>
				</ul> -->
			</div>
		</div>

		<div class="api-service-section">
			<a class="anchor" id="service"></a>
			<h3>
				The <span class="mono emph"><?php echo $sget ?></span> service &nbsp;<span style="font-size:70%; color:#bababa">( <span class="<?php echo ( ($service_enabled)? 'on' : 'off' ) ?>"> <span class="glyphicon glyphicon-log-in"></span> &nbsp;<?php echo ( ($service_enabled)? 'OnLine' : 'OffLine' ) ?> </span> )</span>
			</h3>
			<div>
				<p>
					<?php echo $service_specification['details'] ?>.
				</p>
			</div>
		</div>


		<div class="api-service-section">
		<a class="anchor" id="action"></a>
		<h3>
			The <span class="mono emph"><?php echo $aget ?></span> action &nbsp;<span style="font-size:70%; color:#bababa">( <span class="<?php echo ( ($action_enabled)? 'on' : 'off' ) ?>"> <span class="glyphicon glyphicon-log-in"></span> &nbsp;<?php echo ( ($action_enabled)? 'OnLine' : 'OffLine' ) ?> </span> )</span>
		</h3>
		<div>
		<p>
			<?php echo $action_specification['details'] ?>.

		</p>

		<div class="api-service-subsection">
			<a class="anchor" id="restrictions"></a>
			<h3>
				Access privileges
			</h3>
			<div>
				<p>
					The following table shows the access privileges needed to be able to execute the action <span class="mono emph"><?php echo $sget ?></span>/<span class="mono emph"><?php echo $aget ?></span>.
				</p>

				<table class="table-rounded gray-table table-text-centered" style="width:450px; margin:auto; margin-top:20px">
					<thead>
					<tr>
						<th>
							Access level
						</th>
						<th>
							Authorized to execute
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
					$who = \system\classes\Core::getUserTypesList();

					foreach( $who as $w ){
						$granted = in_array($w, $action_specification['access_level']);
						?>
						<tr>
							<td>
								<?php echo ucfirst($w) ?>
							</td>
							<td>
								<span class="glyphicon glyphicon-<?php echo ( $granted )? 'ok-sign on' : 'minus-sign off' ?>"></span>
							</td>
						</tr>
					<?php
					}
					?>
					</tbody>
				</table>

			</div>
		</div>


		<div class="api-service-subsection">
			<a class="anchor" id="request"></a>
			<h3>
				How to execute it
			</h3>
			<div>
				<p>
					The box below shows an example of how to call the action <span class="mono emph"><?php echo $sget ?></span>/<span class="mono emph"><?php echo $aget ?></span>.
				</p>
				<div class="mono api-url-container">
					<p>
						<?php echo \system\classes\Configuration::$BASE.'web-api/'.$version.'/'.$sget.'/'.$aget.'/<span class="emph param">format</span>?' ?><span class="emph param">parameters</span>
					</p>
				</div>
			</div>
		</div>


		<div class="api-service-subsection">
			<a class="anchor" id="mandatory"></a>
			<h3>
				Mandatory parameters
			</h3>
			<div>
				<?php
				if( intval(sizeof($action_specification['parameters']['mandatory'])) == 0 ){
					?>
					<p>
						The <span class="mono emph"><?php echo $aget ?></span> action does not define mandatory parameters.
					</p>
				<?php
				}else{
					?>
					The <span class="mono emph"><?php echo $aget ?></span> action requires <span class="param"><?php echo intval(sizeof($action_specification['parameters']['mandatory'])) ?></span> mandatory parameter<?php echo ( (intval(sizeof($action_specification['parameters']['mandatory'])) > 1)? 's' : '' ) ?>.

					<div>
						<?php
						createParametersTable( $action_specification['parameters']['mandatory'] );
						?>
					</div>

				<?php
				}
				?>

			</div>
		</div>



		<div class="api-service-subsection">
			<a class="anchor" id="optional"></a>
			<h3>
				Optional parameters
			</h3>
			<div>
				<p>
					Some actions allow the use of optional parameters. These parameters are usually used for impagination, filtering, or sorting purposes.
				</p>
				<?php
				if( intval(sizeof($action_specification['parameters']['optional'])) == 0 ){
					?>
					<p>
						The <span class="mono emph"><?php echo $aget ?></span> action does not define optional parameters.
					</p>
				<?php
				}else{
					?>
					The <span class="mono emph"><?php echo $aget ?></span> action defines <span class="param"><?php echo intval(sizeof($action_specification['parameters']['optional'])) ?></span> optional parameter<?php echo ( (intval(sizeof($action_specification['parameters']['optional'])) > 1)? 's' : '' ) ?>.

					<div>
						<?php
						createParametersTable( $action_specification['parameters']['optional'] );
						?>
					</div>

				<?php
				}
				?>

			</div>
		</div>



		<div class="api-service-subsection">
			<a class="anchor" id="reply"></a>
			<h3>
				Response
			</h3>
			<div>
				<p>
					The box below shows an example of response returned by the server as result of the execution of the <span class="mono emph"><?php echo $sget ?></span>/<span class="mono emph"><?php echo $aget ?></span> action.
				</p>
				<p>
					The response format used in the example below is <span class="param">json</span>.
				</p>
				<p>
				<div class="mono api-reply-container" style="margin-bottom:14px">
					<p>
						<?php
						$reply = array(
							'code' => array( 'type' => 'numeric' , 'details' => 'A code indicating the status of the API call', '_value' => 200 ),
							'status' => array( 'type' => 'text' , 'details' => 'A string indicating the status of the API call', '_value' => 'OK' ),
							'data' => array( 'type' => 'object' , 'details' => 'Data returned as result of the execution of the API call', '_data' => $action_specification['return']['values'] )
						);

						echo arrayToPrettyJson( $reply, 0 );
						?>
					</p>
				</div>
				</p>
				<?php
				if( sizeof($action_specification['return']['values']) > 0 ){
					?>
					The following table contains the details of the structure of the <span class="param">data</span> object:
					<br/>
					<div>
						<?php
						createParametersTable( $action_specification['return']['values'] );
						?>
					</div>
				<?php
				}
				?>
			</div>
		</div>
		</div>
		</div>





			<!-- <div class="api-service-section">
				<a class="anchor" id="action"></a>
				<h3>
					Try it yourself
				</h3>
				<div>
					<p>
						Use the following form to submit an API call.
					</p>


					<?php
					$table = array(
						'layout' => array(
							'productID' => array(
								'type' => 'key',
								'show' => true,
								'width' => 'md-1',
								'align' => 'center',
								'translation' => '#Prodotto',
								'editable' => false
							),
							'merchID' => array(
								'show' => false,
								'translation' => '#Esercente',
								'editable' => false
							),
							'name' => array(
								'type' => 'alphaspace',
								'show' => true,
								'width' => 'md-4',
								'align' => 'left',
								'translation' => 'Nome',
								'placeholder' => 'es. Pizza Margherita',
								'editable' => true
							),
							'price' => array(
								'type' => 'money',
								'show' => true,
								'width' => 'md-1',
								'align' => 'center',
								'translation' => 'Prezzo',
								'placeholder' => 'es. 5.99',
								'editable' => true
							),
							'isAvailable' => array(
								'type' => 'boolean',
								'show' => true,
								'width' => 'md-1',
								'align' => 'center',
								'translation' => 'Disponibile',
								'editable' => true
							)
						)
					);
					?>

					<div class="api-service-subsection">
						<a class="anchor" id="tiy"></a>
						<h3>
							Complete the form below
						</h3>
						<div>
							<p>
								<?php
								// // load modals
								// require_once $__CORE__DIR__.'modules/modals/record_editor_modal.php';
								// generateFormByLayout( $table['layout'] );

								?>
							</p>

						</div>
					</div>
				</div>
			</div> -->
		</div>

	<?php
	}
	?>
	</div>

	</div>

	</td>
	</tr>
	</tbody>
	</table>

	</div>


<?php
}
?>

	<script type="text/javascript">
		var service = "<?php echo $sget ?>";

		$('#services-list .panel-body').hover(
			function(){
				var body = $(this).closest('.panel');
				$('#services-list .panel-heading h5 a.active').each(function(){
					if( !$(this).closest('.panel').is(body) ){
						$(this).attr('class', 'disabled');
					}
				});
			},
			function(){
				$('#service-panel-head-'+service+' h5 a').attr('class', 'active');
			}
		);


		$(window).load( function(){
			<?php
			if( !$api_enabled ){
				?>
			openPop( 'cp_api_tr', 'Warning!', 'This page contains information about an API that is not available anymore.', 'left', 1000, 10000 );
			<?php
			}elseif( !$service_enabled ){
			?>
			openPop( 'cp_service_tr', 'Warning!', 'This page contains information about an API Service that is not available anymore.', 'left', 1000, 10000 );
			<?php
			}elseif( !$action_enabled ){
			?>
			openPop( 'cp_action_tr', 'Warning!', 'This page contains information about an API Action that is not available anymore.', 'left', 1000, 10000 );
			<?php
			}
			?>
		});

	</script>


<?php

// Utility functions


function arrayToPrettyJson( $data, $level ){
	// tmp data
	$data_type_example = array('text' => 'Free text. Can contain letters, numbers, symbols, etc..', 'alphabetic' => 'Alphabetic string. Contains letters only.', 'alphanumeric' => 'Alphanumeric string. Can contain letters and numbers.', 'numeric' => 'A positive integer', 'float' => 'A floating-point number', 'boolean' => 'Boolean values, /true/ or /false/' );
	$next_of_something = false;
	//
	$result = ( (is_assoc($data))? '{' : '[' );
	//
	foreach( $data as $key => $val ){
		$result .= ( ($next_of_something)? ',' : '' ) . _newLine();
		//
		$result .= _computeTab( $level+1 ) . ( (is_string($key))? '"<span class="param default-cursor-on-hover" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="left" data-content="'.$val['details'].'">'.$key.'</span>" : ' : '' );
		//
		if( is_array($val) && ($val['type']=='array' || $val['type']=='object') ){
			$result .= arrayToPrettyJson( ( ($val['type']=='array' && !isset($val['_data']))? array('...') : $val['_data'] ) , $level+1 );
		}else{
			//
			if( is_numeric($key) && is_string($val) && $val == '...' ){
				$result .= '<span class="default-cursor-on-hover" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="other elements of the array">'.$val.'</span>';
			}else{
				$q = '';
				if( is_string($val) || in_array($val['type'], array('alphabetic', 'alphanumeric', 'text', 'email', 'enum')) ){
					$q = '"';
				}
				//
				$popover_content = $data_type_example[$val['type']];
				if( isset($val['_value']) ){
					$popover_content = '';
				}elseif( is_string($val) ){
					$popover_content = '';
				}elseif( $val['type'] == 'enum' ){
					$popover_content = 'It can be: [\''.implode('\', \'', $val['values']).'\']';
				}elseif( is_numeric($key) ){
					$popover_content = $val['details'];
				}
				//
				$span_content = '/'.$val['type'].'/';
				if( isset($val['_value']) ){
					$span_content = $val['_value'];
				}elseif( is_string($val) ){
					$span_content = $val;
				}
				$span_classes = ( (isset($val['_value']))? '' : 'return-type-text default-cursor-on-hover' );
				//
				$result .= $q.'<span class="'.$span_classes.'" data-container="body" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="'.$popover_content.'">'.$span_content.'</span>'.$q;
			}
		}
		//
		$next_of_something = true;
	}
	//
	$result .= ( (sizeof($data) > 0)? _newLine() . _computeTab($level) : '' ) . ( (is_assoc($data))? '}' : ']' );
	return $result;
}//arrayToPrettyJson



function _newLine(){
	return '<br/>';
}

function _computeTab( $level ){
	return str_repeat( "&nbsp;&nbsp;&nbsp;", $level );
}


function createParametersTable( $parameters, $pre='', $post='', $i=0 ){
	$j = -1;
	foreach( $parameters as $p => $param_spec ){
		$j++;
		if( !is_array($param_spec) ) continue;
		?>
		<table style="width:600px; margin: 14px 0 20px 20px">
			<tr>
				<td style="width:12px; border-bottom:1px dashed #d3d3d3">
					<strong>&bull;</strong>
				</td>
				<td style="padding-right:16px; width:150px; border-bottom:1px dashed #d3d3d3; white-space:nowrap">
					<?php

					$is_finite = ($parameters[sizeof($parameters)-1]!=='...');
					$current_sel = $pre . ( (is_numeric($p))? ( ($is_finite)? $p : '&bull;' ) : '<span class="param">'.$p.'</span>' ) . $post;
					echo $current_sel;

					?>
				</td>
				<td style="border-bottom:1px dashed #d3d3d3">
					<span style="color:grey">
					Type: <span class="emph"><?php echo $param_spec['type'] ?></span>
						<?php
						if( isset($param_spec['length']) && $param_spec['length'] !== null ){
							?>
							&nbsp; - &nbsp; Length: <span class="emph"><?php echo $param_spec['length'] ?></span>
						<?php
						}
						?>
						<?php
						if( $param_spec['type'] == 'enum' ){
							?>
							&nbsp; - &nbsp; Values: (<span class="emph"><?php echo '`'.implode('`, `', $param_spec['values']).'`' ?>)</span>
						<?php
						}
						?>
						<?php
						if( isset($param_spec['domain']) && $param_spec['domain'] !== null ){
							?>
							&nbsp; - &nbsp; Domain: [<span class="emph"><?php echo $param_spec['domain'][0].', '.$param_spec['domain'][1] ?>]</span>
						<?php
						}
						?>
					</span>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<?php echo $param_spec['details'] ?>
				</td>
			</tr>
		</table>
		<?php
		if( $param_spec['type'] == 'array' ){
			$is_finite = ($parameters[sizeof($parameters)-1] !== '...');
			$newdata = ( ($is_finite)? $param_spec['_data'] : $param_spec['_data'][0]['_data'] );
			$selector = ( ($is_finite)? $j : '&bull;' );
			//
			$sel = $current_sel.'[';
			createParametersTable( $newdata, $sel, ']', $j );
		}elseif( $param_spec['type'] == 'object' ){
			$sel = $current_sel.'[';
			createParametersTable( $param_spec['_data'], $sel, ']', $j );
		}
	}
}

?>
