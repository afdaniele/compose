<?php
use \system\classes\Configuration;
use \system\classes\Core;

function _api_page_reference_section( &$api_setup, &$version, &$sget, &$aget ){
    $api_enabled = $api_setup[$version]['enabled'];
    $service_enabled = $api_setup[$version]['services'][$sget]['enabled'];
    $action_enabled = $api_setup[$version]['services'][$sget]['actions'][$aget]['enabled'];
    $services = $api_setup[$version]['services'];
    $service = ( ($sget !== null)? $api_setup[$version]['services'][$sget] : array() );
    $action = $service['actions'][$aget];
    $action['parameters']['mandatory'] = array_merge( $api_setup[$version]['global']['parameters']['embedded'], $action['parameters']['mandatory'] );
    $action['parameters']['optional'] = array_merge( $api_setup[$version]['global']['parameters']['optional'], $action['parameters']['optional'] );

    include_once __DIR__.'/../parts/common_style.php';
    ?>

    <div class="api-breadcrumb">
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
                            <a href="#authentication">Authentication methods</a>
                        </li>
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
            </div>
        </div>

        <div class="api-service-section">
            <a class="anchor" id="service"></a>
            <h3>
                The <span class="mono emph"><?php echo $sget ?></span> service &nbsp;<span style="font-size:70%; color:#bababa">( <span class="<?php echo ( ($service_enabled)? 'on' : 'off' ) ?>"> <span class="glyphicon glyphicon-log-in"></span> &nbsp;<?php echo ( ($service_enabled)? 'OnLine' : 'OffLine' ) ?> </span> )</span>
            </h3>
            <div>
                <p>
                    <?php echo $service['details'] ?>.
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
                    <?php echo $action['details'] ?>.
                </p>
            </div>

            <div class="api-service-subsection">
                <a class="anchor" id="authentication"></a>
                <h3>
                    Authentication methods
                </h3>
                <div>
                    <p>
                        The following table shows the authentication mode supported by the action <span class="mono emph"><?php echo $sget ?></span>/<span class="mono emph"><?php echo $aget ?></span>.
                    </p>

                    <table class="table-rounded gray-table table-text-centered" style="width:450px; margin:auto; margin-top:20px">
                        <thead>
                            <tr>
                                <th>
                                    Authentication
                                </th>
                                <th>
                                    Supported
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $modes = [
                                'web' => 'Web Browser Cookies',
                                'app' => 'API Application ID/Secret'
                            ];
                            foreach( $modes as $m => $t ){
                                $supported = in_array($m, $action['authentication']);
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $t ?>
                                    </td>
                                    <td>
                                        <span class="glyphicon glyphicon-<?php echo ( $supported )? 'ok-sign on' : 'minus-sign off' ?>"></span>
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
                                    Package
                                </th>
                                <th>
                                    User role
                                </th>
                                <th>
                                    Authorized to execute
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            // get roles in packages
                        	$packages = array_keys( Core::getPackagesList() );
                        	foreach($packages as $package) {
                                $roles = Core::getPackageRegisteredUserRoles( $package );
                                foreach( $roles as $role ){
                                    $full_role = boolval($package == 'core')? $role : sprintf('%s:%s', $package, $role);
                                    $granted =  in_array($full_role, $action['access_level']);
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo $package ?>
                                        </td>
                                        <td>
                                            <?php echo $role ?>
                                        </td>

                                        <td>
                                            <span class="glyphicon glyphicon-<?php echo ( $granted )? 'ok-sign on' : 'minus-sign off' ?>"></span>
                                        </td>
                                    </tr>
                                    <?php
                                }
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
                            <?php echo Configuration::$BASE.'web-api/'.$version.'/'.$sget.'/'.$aget.'/<span class="emph param">format</span>?' ?><span class="emph param">parameters</span>
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
                    $mandatory = &$action['parameters']['mandatory'];
                    $num_mandatory = count($mandatory);
                    if($num_mandatory == 0){
                        ?>
                        <p>
                            The <span class="mono emph"><?php echo $aget ?></span> action does not define mandatory parameters.
                        </p>
                        <?php
                    }else{
                        ?>
                        The <span class="mono emph"><?php echo $aget ?></span> action requires <span class="param"><?php echo $num_mandatory ?></span> mandatory parameter<?php echo (($num_mandatory > 1)? 's' : '') ?>.

                        <div>
                            <?php
                            createParametersTable($mandatory);
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
                    $optional = &$action['parameters']['optional'];
                    $num_optional = count($optional);
                    if($num_optional == 0){
                        ?>
                        <p>
                            The <span class="mono emph"><?php echo $aget ?></span> action does not define optional parameters.
                        </p>
                        <?php
                    }else{
                        ?>
                        The <span class="mono emph"><?php echo $aget ?></span> action defines <span class="param"><?php echo $num_optional ?></span> optional parameter<?php echo (($num_optional > 1)? 's' : '') ?>.

                        <div>
                            <?php
                            createParametersTable($optional);
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
                                    'data' => array( 'type' => 'object' , 'details' => 'Data returned as result of the execution of the API call', '_data' => $action['return']['values'] )
                                );

                                echo arrayToPrettyJson( $reply, 0 );
                                ?>
                            </p>
                        </div>
                    </p>
                    <?php
                    if(count($action['return']['values']) > 0){
                        ?>
                        The following table contains the details of the structure of the <span class="param">data</span> object:
                        <br/>
                        <div>
                            <?php
                            createParametersTable( $action['return']['values'] );
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <script type="text/javascript">
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
}



// Utility functions

function arrayToPrettyJson( $data, $level ){
    // tmp data
    //TODO: we should move/get these from the StringType class
    $data_type_example = [
        'text' => 'Free text. Can contain letters, numbers, symbols, etc..',
        'alphabetic' => 'Alphabetic string. Contains letters only.',
        'alphanumeric' => 'Alphanumeric string. Can contain letters and numbers.',
        'numeric' => 'A positive integer',
        'float' => 'A floating-point number',
        'boolean' => 'Boolean values, /true/ or /false/'
    ];
    $next_of_something = false;
    //
    $result = is_assoc($data)? '{' : '[';
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
}//_newLine

function _computeTab( $level ){
    return str_repeat( "&nbsp;&nbsp;&nbsp;", $level );
}//_computeTab

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
}//createParametersTable

?>
