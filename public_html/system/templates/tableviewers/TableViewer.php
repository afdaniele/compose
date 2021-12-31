<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 1/15/15
 * Time: 5:39 PM
 */

namespace system\templates\tableviewers;


use system\classes\Configuration;
use system\classes\Core;
use system\classes\enum\StringType;
use system\classes\Utils;


class TableViewer {
    
    public static function parseFeatures(&$features, $values) {
        $features['_valid'] = [];
        //
        foreach ($features as $key => $feature) {
            if ($key == '_valid') {
                continue;
            }
            $val = null;
            $default = false;
            //
            if (isset($values[$key]) && strlen($values[$key]) > 0) {
                $val = $values[$key];
            } else {
                $val = $feature['default'];
                $default = true;
            }
            // type parsing
            if (!$default && $val != null) {
                //
                switch ($feature['type']) {
                    case 'integer':
                        if (StringType::isValid($val, StringType::NUMERIC)) {
                            $val = intval($val);
                            if (isset($feature['minValue']) && ($val < $feature['minValue'])) {
                                $val = $feature['default'];
                                $default = true;
                            }
                            if (isset($feature['maxValue']) && ($val > $feature['maxValue'])) {
                                $val = $feature['default'];
                                $default = true;
                            }
                        } else {
                            $val = $feature['default'];
                            $default = true;
                        }
                        break;
                    case 'float':
                        if (StringType::isValid($val, StringType::FLOAT)) {
                            $val = floatval($val);
                            if (isset($feature['minValue']) && ($val < $feature['minValue'])) {
                                $val = $feature['default'];
                                $default = true;
                            }
                            if (isset($feature['maxValue']) && ($val > $feature['maxValue'])) {
                                $val = $feature['default'];
                                $default = true;
                            }
                        } else {
                            $val = $feature['default'];
                            $default = true;
                        }
                        break;
                    case 'alpha':
                        if (!StringType::isValid($val, StringType::ALPHABETIC)) {
                            $val = $feature['default'];
                            $default = true;
                        }
                        break;
                    default:
                        // nothing to do
                        break;
                }// switch
            }
            // enum parsing
            if (!$default && isset($feature['values']) && is_array($feature['values'])) {
                if (!in_array($val, $feature['values'])) {
                    $val = $feature['default'];
                    $default = true;
                }
            }
            if ($val == $feature['default']) {
                $default = true;
            }
            // at the end
            $features[$key]['value'] = $val;
            //
            if (!$default) {
                $features['_valid'][$key] = $val;
            }
        }// foreach
        //
        // compute offset and limit if needed
        $pagination = (isset($features['results']) && isset($features['page']));
        $features['offset'] = array('value' => ($features['results']['value'] * ($features['page']['value'] - 1)));
        $features['limit'] = array('value' => $features['results']['value']);
    }//parseFeatures
    
    
    public static function generateTableViewer($baseurl, $res, $features, $table, $formID = 'the-form') {
        $features = $features ?? [];
        // extract information
        $features_values = [];
        foreach ($features as $key => $feature) {
            $features_values[$key] = $feature['value'] ?? null;
        }
        //
        $filtered_features = [];
        $querystrings = [];
        foreach ($features_values as $key => $_) {
            $filtered_features[$key] = $features_values;
            unset($filtered_features[$key][$key]);
            $filtered_features[$key] = array_keys($filtered_features[$key]);
            //
            $resource = Configuration::$BASE . $baseurl . toQueryString($filtered_features[$key], $features['_valid'], true, true);
            //
            $querystrings[$key] = $resource;
        }
        // get informations
        $pagination = (isset($features['page']));
        $offset = (($pagination) ? $features['offset']['value'] : 0);
        $result_per_page = (($pagination) ? $features['results']['value'] : $res['size']);
        $current_page = (($pagination) ? $features['page']['value'] : 1);
        $total_count = (($pagination) ? $res['total'] : $res['size']);
        $res_count = $res['size'];
        $data = $res['data'];
        //
        $filter_in_use = (isset($features['keywords']) && $features_values['keywords'] != null);
        //
        $filter_enabled = isset($features['keywords']);
        //
        $order_enabled = isset($features['order']);
        //
        $available_pages = ceil($total_count / $result_per_page);
        //
        if ($available_pages == 0) {
            // NO RESULTS
            $res_count = 0;
        } else {
            if ($current_page > $available_pages) {
                // Invalid page number, redirect to the last possible one
                Core::redirectTo($querystrings['page'] . 'page=' . $available_pages);
                echo $querystrings['page'] . 'page=' . $available_pages;
            }
        }
        //
        $table_viewer_unique_id = Utils::generateRandomString(4);
        
        ?>

        <style>
            .btn-table-viewer-action {
                padding-top: 2px;
                padding-bottom: 2px;
            }
        </style>

        <div class="col-md-12" style="padding:0">
            
            <?php
            if (count($features) > 0) {
                ?>
                <!-- === Begin Results Bar ================================================================================= -->


                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <strong>Results:</strong>
                            
                            <div class="dropdown d-inline">
                                <a class="dropdown-toggle" href="#" id="results_options_dropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo '( ' . ($offset + (($res_count > 0) ? 1 : 0)) . '-' . ($offset + $res_count) . ' )&nbsp; |&nbsp; ' . $total_count . ' total' ?>
                                    <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink" style="width:200px">
                                    <li><a class="dropdown-item disabled" href="#" tabindex="-1" aria-disabled="true"><strong>Results per page:</strong></a></li>
                                    <form method="get" action="<?php echo Configuration::$BASE . $baseurl ?>" style="padding-left: 20px">
                                        <?php
                                        $options = [5, 10, 20, 50];
                                        foreach ($options as $qty) {
                                            ?>
                                            <li>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio"
                                                               name="results"
                                                               id="option_<?php echo $qty ?>"
                                                               value="<?php echo $qty ?>" <?php echo(($result_per_page == $qty) ? 'checked' : '') ?>
                                                               onclick="this.form.submit();">
                                                        <label for="option_<?php echo $qty ?>"
                                                               style="padding-left:4px"><?php echo $qty ?>
                                                            results</label>
                                                    </label>
                                                </div>
                                            </li>
                                            <?php
                                        }
                                        //
                                        if (!in_array($result_per_page, $options)) {
                                            // add an extra row
                                            ?>
                                            <li role="presentation"
                                                class="divider"></li>
                                            <li role="presentation"
                                                class="dropdown-header">Custom:
                                            </li>
                                            <input type="radio" id="option_custom"
                                                   checked>
                                            <label for="option_custom"
                                                   style="padding-left:4px"><?php echo $result_per_page ?>
                                                results</label>
                                            <?php
                                        }
                                        //
                                        foreach ($filtered_features['results'] as $param) {
                                            if (isset($features['_valid'][$param])) {
                                                echo "<input type=\"hidden\" name=\"$param\" value=\"$features_values[$param]\"/>";
                                            }
                                        }
                                        ?>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form class="navbar-form navbar-left" role="search"
                                  method="get"
                                  action="<?php echo Configuration::$BASE . $baseurl ?>"
                                  style="padding-right:10px">
                                <?php
                                ?>
                                <div class="form-group">
                                    <?php
                                    foreach ($filtered_features['keywords'] as $param) {
                                        if (isset($features['_valid'][$param])) {
                                            echo "<input type=\"hidden\" name=\"$param\" value=\"$features_values[$param]\"/>";
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="table-viewer-search-label">Search</span>
                                    <input type="text"
                                           class="form-control"
                                           name="keywords"
                                           placeholder="<?php echo $features['keywords']['placeholder'] ?>"
                                           aria-label="<?php echo $features['keywords']['placeholder'] ?>"
                                           aria-describedby="table-viewer-search-btn">
                                    <button class="btn btn-outline-secondary" type="submit" id="table-viewer-search-btn">Go</button>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="border-bottom:1px solid #efefef">
                    <table style="float:right">
                        <tr>
                            <td>
                                <?php
                                if ($filter_in_use) {
                                    $keywords_queryString = $querystrings['keywords'];
                                    ?>
                                    Filters:
                                    <a href="#">"<?php echo $features_values['keywords']; ?>"</a>
                                    (<a href="<?php echo $keywords_queryString; ?>" style="color:red">
                                        <i class="bi bi-backspace"></i>
                                    </a>)
                                    <?php
                                }
                                echo(($order_enabled) ? (($filter_in_use ? ', S' : 'Results s') . 'orted by:') : '');
                                ?>
                            </td>
                            <?php
                            if ($order_enabled) {
                                ?>
                                <td>
                                    <div class="dropdown" style="margin-left:3px">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"
                                           id="ordering_options_dropdown">
                                            <?php echo $features['order']['details'][$features_values['order']]['translation'] ?>
                                            <span class="caret"></span>
                                        </a>

                                        <ul class="dropdown-menu" role="menu">
                                            <?php
                                            foreach ($features['order']['values'] as $ord) {
                                                echo '<li role="presentation"><a role="menuitem" tabindex="-1" href="' . $querystrings['order'] . 'order=' . $ord . '">' . $features['order']['details'][$ord]['translation'] . '</a></li>';
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </td>
                                <?php
                            }
                            ?>
                        </tr>
                    </table>
                </div>
                <!-- === End Results Bar =================================================================================== -->
                <?php
            }
            ?>


            <br/>
            <br/>


            <!-- === Begin Results Table + Paginator =================================================================== -->
            
            <?php
            if ($res_count > 0) {
                // enable the features
                $counter_column_enabled = in_array('_counter_column', $table['features']);
                $actions_column_enabled = in_array('_actions_column', $table['features']) && isset($table['actions']);
                ?>

                <table class="table <?php echo $table['style'] ?>">
                    <thead>
                    <tr>
                        <?php
                        if ($counter_column_enabled) {
                            ?>
                            <th class="col-md-1 text-center">#</th>
                            <?php
                        }
                        //
                        foreach ($table['layout'] as $key => $column) {
                            if (!$column['show']) {
                                continue;
                            }
                            ?>
                            <th class="col-<?php echo $column['width']; ?> text-<?php echo $column['align']; ?>"><?php echo $column['translation'] ?></th>
                            <?php
                        }
                        //
                        if ($actions_column_enabled) {
                            ?>
                            <th class="col-<?php echo $table['actions']['_width'] ?> text-center">
                                Actions
                            </th>
                            <?php
                        }
                        ?>
                    </tr>
                    </thead>

                    <tbody id="list-body">
                    <?php
                    
                    for ($i = 0; $i < $res_count; $i++) {
                        $record_raw = $data[$i];
                        // filter the record
                        $record = array_intersect_key($record_raw, array_flip(array_keys($table['layout'])));
                        ?>
                        <tr>
                            <?php
                            if ($counter_column_enabled) {
                                ?>
                                <td class="text-center">
                                    <?php echo($i + 1); ?>
                                </td>
                                <?php
                            }
                            //
                            foreach ($table['layout'] as $key => $column) {
                                if (!$column['show']) {
                                    continue;
                                }
                                ?>
                                <td class="align-middle text-<?php echo $column['align']; ?>">
                                    <?php
                                    $red_color = (isset($column['red-color-limit']) && $record[$key] >= $column['red-color-limit']);
                                    if ($red_color) {
                                        echo '<span style="font-weight:bold; color:orangered">';
                                    }
                                    //
                                    if (isset($column['meaning'])) {
                                        echo $column['meaning'][$record[$key]];
                                    } else {
                                        echo format($record[$key], $column['type']);
                                    }
                                    //
                                    if ($red_color) {
                                        echo '</span>';
                                    }
                                    ?>
                                </td>
                                <?php
                            }
                            //
                            if ($actions_column_enabled) {
                                ?>
                                <td class="text-center">
                                    <div style="margin:auto">
                                        <?php
                                        foreach ($table['actions'] as $action) {
                                            if (is_string($action)) {
                                                continue;
                                            }
                                            // action properties
                                            $action_type = $action["type"] ?? null;
                                            $action_has_condition = isset($action["condition"]);
                                            $action_condition = $action["condition"] ?? null;
                                            $action_has_tooltip = isset($action['tooltip']);
                                            $action_tooltip = $action["tooltip"] ?? null;
                                            $action_icon = $action["icon"] ?? null;
                                            $action_color = $action["color"] ?? null;
                                            $action_text = $action["text"] ?? null;
                                            $action_condition_html_class = $action_has_condition ? (!in_array($record[$action_condition['field']], $action_condition['values']) ? 'disabled' : '') : '';
                                            // ---
                                            if ($action_type == 'separator') {
                                                ?>
                                                <span>&nbsp;|&nbsp;</span>
                                                <?php
                                                continue;
                                            }
                                            // function properties
                                            $function = $action['function'] ?? [];
                                            $function_type = $function["type"] ?? null;
                                            $function_target = $function["target"] ?? null;
                                            $function_custom_html = $function["custom_html"] ?? "";
                                            $function_static_data = $function["static_data"] ?? [];
                                            $function_arguments = $function["arguments"] ?? [];
                                            $function_has_url = isset($function['API_resource']) && isset($function['API_action']);
                                            $function_has_record = ($function_static_data["modal-mode"] ?? null) == "edit";
                                            $function_toggle_modal = $function_type == '_toggle_modal';
                                            $function_arguments_override = $function['arguments_override'] ?? [];
                                            // data-toggle
                                            $toggle_param = (($action_has_tooltip) ? 'tooltip' : '');
                                            // modal
                                            $function_modal_html = "";
                                            if ($function_toggle_modal) {
                                                // target
                                                $function_target_id = $function_target;
                                                if ($function_target == 'record-editor-modal') {
                                                    $function_target_id .= "-$formID";
                                                }
                                                $function_modal_html = "data-bs-toggle=\"modal\" data-bs-target=\"#$function_target_id\"";
                                            }
                                            // tooltip
                                            $action_toggle_html = ($action_has_tooltip || $function_toggle_modal) ? "data-toggle=\"$toggle_param\" " : '';
                                            $action_tooltip_html = ($action_has_tooltip) ? ' data-placement="bottom" title="' . $action_tooltip . '" ' : '';
                                            // data-{key}={value}
                                            $action_custom_data_html = "";
                                            foreach ($function_arguments as $argument) {
                                                $val = addslashes($record[$argument]);
                                                $action_custom_data_html .= " data-$argument=\"$val\"";
                                            }
                                            // static data-{key}={value}
                                            foreach ($function_static_data as $argument => $value) {
                                                $val = addslashes($value);
                                                $action_custom_data_html .= " data-$argument=\"$val\"";
                                            }
                                            // function URL
                                            $function_url_html = "";
                                            if ($function_has_url) {
                                                $api_resource = $function['API_resource'];
                                                $api_action = $function['API_action'];
                                                $function_url = sprintf(
                                                    "%sweb-api/%s/%s/%s/json?token=%s&%s&%s",
                                                    Configuration::$BASE,
                                                    Configuration::$WEBAPI_VERSION,
                                                    $api_resource,
                                                    $api_action,
                                                    $_SESSION['TOKEN'],
                                                    toQueryString($function_arguments, $record),
                                                    toQueryString(array_keys($function_arguments_override), $function_arguments_override)
                                                );
                                                $function_url_html = " data-url=\"$function_url\" ";
                                            }
                                            // function record
                                            $function_record_html = "";
                                            if ($function_has_record) {
                                                $function_record = json_encode($record_raw, JSON_HEX_APOS);
                                                $function_record_html = ' data-record=\'' . $function_record . '\' ';
                                            }
                                            ?>
                                            <button
                                                    class="btn btn-sm btn-table-viewer-action btn-<?php echo $action_type ?> <?php echo $action_condition_html_class ?>"
                                                    type="button"
                                                <?php
                                                echo $action_toggle_html;
                                                echo $action_tooltip_html;
                                                echo $function_url_html;
                                                echo $function_record_html;
                                                echo $function_custom_html;
                                                echo $action_custom_data_html;
                                                echo $function_modal_html;
                                                ?>
                                                    style="<?php
                                                    if ($action_has_condition && !in_array($record[$action_condition['field']], $action_condition['values'])) {
                                                        echo "background-image:none; background-color:rgb(189, 188, 188); border:1px solid; ";
                                                    }
                                                    ?>
                                                            "
                                            >
                                                <i class="bi bi-<?php echo $action_icon ?>"
                                                    <?php echo is_null($action_color) ? "" : "style=\"color:$action_color\"" ?>
                                                ></i>
                                                <?php echo is_null($action_text) ? "" : "&nbsp;$action_text" ?>
                                            </button>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>

                </table>

                <br/>
                <br/>
                
                <?php
                if ($pagination) {
                    include_once $GLOBALS['__SYSTEM__DIR__'] . "/templates/paginators/default.php";
                    renderPaginator($querystrings['page'], $available_pages, $current_page);
                }
            } else {
                ?>
                <br/>
                <h3 class="text-center">No Results!</h3>
                <br/>
                <?php
            }
            ?>
        </div>

        <!-- === End Results Table + Paginator ===================================================================== -->
        <?php
    }
}

?>
