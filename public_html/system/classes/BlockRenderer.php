<?php

namespace system\classes;

class BlockRenderer{

    static protected $ICON = [
        "class" => "fa",
        "name" => "window-maximize"
    ];

    static protected $ARGUMENTS = [];

    private $mission_control;

    function __construct( $mission_control ){
        $this->mission_control = $mission_control;
    }//__construct

    public function draw( $class, $id, $title, $subtitle, &$shape, &$args=[], &$opts=[], $DEPRECATED_show_header=True ){
        $args['title'] = $title;
        $args['subtitle'] = $subtitle;
        ?>
        <div class="block_renderer_canvas text-center <?php echo $class ?>" id="<?php echo $id ?>" data-renderer="<?php echo get_called_class() ?>">
            <table>
                <tr class="block_renderer_header text-left">
                    <td>
                        <?php
                        if( $DEPRECATED_show_header ){
                            ?>
                            <i class="<?php echo self::get_icon_class() ?>" aria-hidden="true"></i>
                            <?php
                        }
                        ?>
                    </td>
                    <td>
                        <h5 class="block_renderer_title"><?php echo $title ?></h5>
                        <h6 class="block_renderer_subtitle"><?php echo $subtitle ?></h6>
                    </td>
                    <td>
                        <?php
                        if( !isset($opts['show_menu']) || boolval($opts['show_menu']) ){
                            ?>
                            <div class="btn-group">
                                <a class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" style="color:black">

                                    <?php
                                    $options = $this->mission_control->get_available_shapes();
                                    if( (!isset($opts['show_size_selector']) || boolval($opts['show_size_selector'])) && count($options) > 1 ){
                                        ?>
                                        <h4 style="padding-left:12px; font-weight:normal">Size:</h4>

                                        <div class="btn-group" data-toggle="buttons" style="width:100%">
                                            <?php
                                            $num_options = count($options);
                                            $num_cols = 3;
                                            $num_rows = intval(ceil($num_options/$num_cols));
                                            $figure_max_size_px = 22;
                                            $option_max_rc = 1;
                                            foreach ($options as $opt) {
                                                if( $opt[0] > $option_max_rc )
                                                    $option_max_rc = $opt[0];
                                                if( $opt[1] > $option_max_rc )
                                                    $option_max_rc = $opt[1];
                                            }
                                            ?>
                                            <table>
                                                <?php
                                                for ($i = 0; $i < $num_rows; $i++) {
                                                    echo "<tr>";
                                                    for ($j = 0; $j < $num_cols; $j++) {
                                                        $k = $j + $i*$num_cols;
                                                        if( $k >= $num_options ){
                                                            echo "<td></td>";
                                                            continue;
                                                        }
                                                        // create option button
                                                        $option = $options[$k];
                                                        $w = ($option[1] / $option_max_rc) * $figure_max_size_px;
                                                        $h = ($option[0] / $option_max_rc) * $figure_max_size_px;
                                                        $is_active = boolval( $shape['rows']==$option[0] && $shape['cols']==$option[1] );
                                                        ?>
                                                        <td class="text-center" style="width:<?php echo 100/$num_cols ?>%">
                                                            <label class="btn btn-default <?php echo $is_active? 'active' : '' ?>"
                                                                style="width:<?php echo $figure_max_size_px+20 ?>px; padding:5px; margin:4px auto"
                                                                onclick="mission_control_switch_shape('<?php echo $id ?>', '<?php echo $option[0] ?>', '<?php echo $option[1] ?>' );">

                                                                <div style="width:100%; height:<?php echo $figure_max_size_px ?>px; vertical-align:middle; display:grid">
                                                                    <div style="width:<?php echo $w ?>px; height:<?php echo $h ?>px; background-color:black; margin:auto"></div>
                                                                </div>
                                                                <input type="radio" name="options" id="option<?php echo $k ?>" autocomplete="off" style="display:none" <?php echo $is_active? 'checked' : '' ?>>
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
                                    if( !isset($opts['show_properties']) || boolval($opts['show_properties']) ){
                                    ?>
                                        <li>
                                            <a role="button" data-toggle="modal" data-target="#<?php echo $id ?>_properties_modal">
                                                <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>
                                                &nbsp;
                                                Properties
                                            </a>
                                        </li>
                                        <li role="separator" class="divider"></li>
                                    <?php
                                    }
                                    if( !isset($opts['show_dispose']) || boolval($opts['show_dispose']) ){
                                    ?>
                                        <li>
                                            <a href="#">
                                                <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                                &nbsp;
                                                Dispose
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
                <tr class="block_renderer_container">
                    <td colspan="3">
                        <?php static::render( $id, $args ); ?>
                    </td>
                </tr>
            </table>


            <?php
            if( !isset($opts['show_properties']) || boolval($opts['show_properties']) ){
            ?>
                <div class="modal fade mission-control-block-properties-modal" id="<?php echo $id ?>_properties_modal" tabindex="-1" role="dialog" aria-hidden="true">
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
                                    // add item to the layout
                                    $layout[$layout_key] = $layout_desc;
                                }
                                $formID = null;
                                $formClass = 'mission-control-block-properties-form';
                                $method = 'post';
                                // generate form
            					generateFormByLayout( $layout, $formID, $formClass, $method, $action=null, $values=$args );
            					?>
            				</div>

            				<div class="modal-footer">
            					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            					<button type="button" class="btn btn-success" id="save-button">Save</button>
            				</div>
            			</div>
            		</div>
            	</div>
            <?php
            }
            ?>
        </div>

        <script type="text/javascript">
            document.getElementById("<?php echo $id ?>").addEventListener(
                'DOMAttrModified',
                function(e){
                    console.log('prevValue: ' + e.prevValue, 'newValue: ' + e.newValue);
                },
                false
            );
        </script>
        <?php
    }//draw

    protected static function render( $id, &$args ){
        ?>
        <p style="position:relative; top:50%; transform:translateY(-50%)">
            Your renderer must implement the method <code>render()</code>.
        </p>
        <?php
    }//render

    final public function get_icon_class(){
        return sprintf('%s %s-%s', static::$ICON['class'], static::$ICON['class'], static::$ICON['name']);
    }//get_icon_class

}//BlockRenderer
?>
