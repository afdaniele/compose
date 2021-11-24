<?php

namespace system\classes;

class BlockRenderer {
    
    static protected $ICON = [
        "class" => "fa",
        "name" => "window-maximize"
    ];
    
    static protected $ARGUMENTS = [];
    
    static protected $PRIVATE = false;
    
    static protected $DEFAULT_SIZE = ['rows' => 2, 'cols' => 2];
    
    private $mission_control;
    
    function __construct($mission_control) {
        $this->mission_control = $mission_control;
    }//__construct
    
    public function draw($class, $id, $title, $subtitle, &$shape, &$args = [], &$opts = []) {
        $grid_id = $this->mission_control->get_ID();
        ?>
        <div
                class="block_renderer_canvas text-center <?php echo $class ?>"
                id="<?php echo $id ?>"
                data-title="<?php echo base64_encode($title) ?>"
                data-subtitle="<?php echo base64_encode($subtitle) ?>"
                data-shape='<?php echo base64_encode(json_encode($shape)) ?>'
                data-renderer="<?php echo get_called_class() ?>"
                data-properties='<?php echo base64_encode(json_encode($args)) ?>'
        >
            <?php
            $args['title'] = $title;
            $args['subtitle'] = $subtitle;
            ?>
            <table>
                <?php
                if (!isset($opts['show_header']) || boolval($opts['show_header'])) {
                    ?>
                    <tr class="block_renderer_header text-left" style="height:32px">
                        <?php
                        if (!isset($opts['show_icon']) || boolval($opts['show_icon'])) {
                            ?>
                            <td class="block_renderer_icon">
                                <i class="<?php echo self::get_icon_class() ?>"
                                   aria-hidden="true"></i>
                            </td>
                            <?php
                        }
                        ?>
                        <td>
                            <h5 class="block_renderer_title"><?php echo $title ?></h5>
                            <h6 class="block_renderer_subtitle"><?php echo $subtitle ?></h6>
                        </td>
                        <td class="block_renderer_menu_icon">
                            <?php
                            if (!isset($opts['show_menu']) || boolval($opts['show_menu'])) {
                                ?>
                                <div class="btn-group">
                                    <a class="btn dropdown-toggle" data-toggle="dropdown"
                                       aria-haspopup="true" aria-expanded="false">
                                        <i class="bi bi-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu" style="color:black">
                                        
                                        <?php
                                        $options = $this->mission_control->get_available_shapes();
                                        if ((!isset($opts['show_size_selector']) || boolval($opts['show_size_selector'])) && count($options) > 1) {
                                            ?>
                                            <h4 style="padding-left:12px; font-weight:normal">
                                                Size:</h4>

                                            <div class="btn-group" data-toggle="buttons"
                                                 style="width:100%">
                                                <?php
                                                $num_options = count($options);
                                                $num_cols = 3;
                                                $num_rows = intval(ceil($num_options / $num_cols));
                                                $figure_max_size_px = 22;
                                                $option_max_rc = 1;
                                                foreach ($options as $opt) {
                                                    if ($opt[0] > $option_max_rc) {
                                                        $option_max_rc = $opt[0];
                                                    }
                                                    if ($opt[1] > $option_max_rc) {
                                                        $option_max_rc = $opt[1];
                                                    }
                                                }
                                                ?>
                                                <table>
                                                    <?php
                                                    for ($i = 0; $i < $num_rows; $i++) {
                                                        echo "<tr>";
                                                        for ($j = 0; $j < $num_cols; $j++) {
                                                            $k = $j + $i * $num_cols;
                                                            if ($k >= $num_options) {
                                                                echo "<td></td>";
                                                                continue;
                                                            }
                                                            // create option button
                                                            $option = $options[$k];
                                                            $w = ($option[1] / $option_max_rc) * $figure_max_size_px;
                                                            $h = ($option[0] / $option_max_rc) * $figure_max_size_px;
                                                            $is_active = boolval($shape['rows'] == $option[0] && $shape['cols'] == $option[1]);
                                                            ?>
                                                            <td class="text-center"
                                                                style="width:<?php echo 100 / $num_cols ?>%">
                                                                <label class="btn btn-default <?php echo $is_active ? 'active' : '' ?>"
                                                                       style="width:<?php echo $figure_max_size_px + 20 ?>px; padding:5px; margin:4px auto"
                                                                       onclick="mission_control_switch_shape('<?php echo $id ?>', '<?php echo $option[0] ?>', '<?php echo $option[1] ?>' );">

                                                                    <div style="width:100%; height:<?php echo $figure_max_size_px ?>px; vertical-align:middle; display:grid">
                                                                        <div style="width:<?php echo $w ?>px; height:<?php echo $h ?>px; background-color:black; margin:auto"></div>
                                                                    </div>
                                                                    <input type="radio"
                                                                           name="options"
                                                                           id="option<?php echo $k ?>"
                                                                           autocomplete="off"
                                                                           style="display:none" <?php echo $is_active ? 'checked' : '' ?>>
                                                                    <?php echo sprintf("%dx%d", $option[0], $option[1]) ?>

                                                                </label>
                                                            </td>
                                                            <?php
                                                        }
                                                        echo "</tr>";
                                                    }
                                                    ?>
                                                </table>
                                            </div>
                                            <li role="separator" class="divider"></li>
                                            <?php
                                        }
                                        ?>
                                        
                                        <?php
                                        if (!isset($opts['show_properties']) || boolval($opts['show_properties'])) {
                                            ?>
                                            <li>
                                                <a role="button" data-toggle="modal"
                                                   data-target="#<?php echo $id ?>_properties_modal">
                                                    <span class="glyphicon glyphicon-wrench"
                                                          aria-hidden="true"></span>
                                                    &nbsp;
                                                    Properties
                                                </a>
                                            </li>
                                            <li role="separator" class="divider"></li>
                                            <?php
                                        }
                                        if (!isset($opts['show_dispose']) || boolval($opts['show_dispose'])) {
                                            ?>
                                            <li>
                                                <a href="#"
                                                   onclick="mission_control_dispose_block('<?php echo $id ?>')">
                                                    <span class="glyphicon glyphicon-trash"
                                                          aria-hidden="true"></span>
                                                    &nbsp;
                                                    Remove
                                                </a>
                                            </li>
                                            <?php
                                        }
                                        ?>

                                    </ul>
                                </div>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                } // show_header ?
                ?>
                <tr class="block_renderer_container">
                    <td colspan="3">
                        <?php static::render($id, $args); ?>
                    </td>
                </tr>
            </table>
            
            
            <?php
            if (!isset($opts['show_properties']) || boolval($opts['show_properties'])) {
                ?>
                <div class="modal fade mission-control-block-properties-modal"
                     id="<?php echo $id ?>_properties_modal" tabindex="-1" role="dialog"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                    <span class="sr-only">Close</span>
                                </button>
                                <h4 class="modal-title">
                                    Properties - "<?php echo $title ?>"
                                </h4>
                            </div>

                            <div class="modal-body text-left">
                                <?php
                                // create layout for form
                                $layout = [
                                    'title' => [
                                        'type' => 'text',
                                        'editable' => true,
                                        'value' => $title,
                                        'name' => 'Block title'
                                    ],
                                    'subtitle' => [
                                        'type' => 'text',
                                        'editable' => true,
                                        'value' => $subtitle,
                                        'name' => 'Block subtitle'
                                    ]
                                ];
                                // append custom arguments to the layout
                                foreach (static::$ARGUMENTS as $key => $desc) {
                                    $layout_key = $key;
                                    $layout_desc = [
                                        'type' => $desc['type'],
                                        'editable' => True,
                                        'value' => $args[$key],
                                        'name' => $desc['name']
                                    ];
                                    if ($desc['type'] == 'enum') {
                                        $layout_desc['placeholder_id'] = $desc['enum_values'];
                                        $layout_desc['placeholder'] = array_map("ucfirst", $desc['enum_values']);
                                    }
                                    // add item to the layout
                                    $layout[$layout_key] = $layout_desc;
                                }
                                $formID = sprintf('mission-control-block-%s-form', $id);
                                $formClass = 'mission-control-block-properties-form';
                                $method = 'post';
                                // generate form
                                generateFormByLayout($layout, $formID, $formClass, $method, $action = null, $values = $args);
                                ?>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="button" class="btn btn-success" id="save-button">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                    $('#<?php echo $id ?>_properties_modal #save-button').on('click', function () {
                        // get properties from form object
                        const form = '#mission-control-block-<?php echo $id ?>-form';
                        const key_value_pairs = $(form).serializeArray();
                        // turn list of pairs into object
                        const properties = {};
                        const block = "#<?php echo $id ?>";
                        const reserved_keys = ['title', 'subtitle'];
                        $.each(key_value_pairs, function (_, pair) {
                            if (reserved_keys.indexOf(pair['name']) !== -1) {
                                $(block).data(
                                    pair['name'],
                                    CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(pair['value']))
                                );
                                return;
                            }
                            properties[pair['name']] = pair['value'];
                        });
                        // serialize properties as JSON
                        $('#<?php echo $id ?>').data(
                            'properties',
                            CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(JSON.stringify(properties)))
                        );
                        // close modal
                        let modal = new bootstrap.Modal(document.getElementById('<?php echo $id ?>_properties_modal'));
                        modal.hide();
                        // save mission control
                        $(window).trigger('MISSION_CONTROL_SAVE', ["<?php echo $grid_id ?>"]);
                    });
                </script>
                <?php
            }
            ?>
        </div>
        <?php
    }//draw
    
    protected static function render($id, &$args) {
        ?>
        <p style="position:relative; top:50%; transform:translateY(-50%)">
            Your renderer must implement the method <code>render()</code>.
        </p>
        <?php
    }//render
    
    final public function get_default_JSON() {
        return sprintf('
      {
        "shape": %s,
        "renderer": "%s",
        "title": "%s",
        "subtitle": "%s",
        "args": %s
      }
      ',
            json_encode(static::$DEFAULT_SIZE),
            get_called_class(),
            sprintf('New %s block...', get_called_class()),
            sprintf('New %s block subtitle.', get_called_class()),
            json_encode(array_map(function ($arg) {
                return $arg['default'];
            }, static::$ARGUMENTS))
        );
    }//get_default_JSON
    
    final public function get_icon_class() {
        return sprintf('%s %s-%s', static::$ICON['class'], static::$ICON['class'], static::$ICON['name']);
    }//get_icon_class
    
    final public function is_private() {
        return boolval(static::$PRIVATE);
    }//is_private
    
}//BlockRenderer
?>
