<?php

use \system\classes\Core;
use \system\classes\Database;
use system\classes\Utils;


class MissionControl {

    private $grid_id;
    private $default_canvas_size = 970;
    private $sizes_available;
    private $blocks;

    function __construct($grid_id, $sizes_available, $blocks = []) {
        $this->grid_id = $grid_id;
        $this->sizes_available = $sizes_available;
        $this->blocks = $blocks;
    }//__construct

    function get_ID() {
        return $this->grid_id;
    }//get_ID

    function create($opts = []) {
        $header_h = 40;
        if (isset($opts['show_header']) && !boolval($opts['show_header'])) {
            $header_h = 0;
        }
        $scale_factor = min(max($opts['scale_factor'], 0.2), 2.0);
        $block_size = $scale_factor * ($this->default_canvas_size -
                ($opts['resolution'] - 1) * $opts['block_gutter']) / $opts['resolution'];
        // load all block renderers registered
        Core::loadPackagesModules('renderers/blocks');
        // get all block renderers registered
        $renderers_available = Core::getClasses('system\classes\BlockRenderer');
        $blocks_ids = [];
        ?>
        <div id="<?php echo $this->grid_id ?>" class="mission-control-grid">
            <?php
            $empty_renderer = new EmptyRenderer($this);
            foreach ($this->blocks as $block) {
                $rand_id = sprintf('block_%s', Utils::generateRandomString(8));
                $block_args = $block['args'];
                if (!in_array($block['renderer'], $renderers_available)) {
                    $renderer = $empty_renderer;
                    $block_args = [
                        'message' => sprintf('Renderer <code>%s</code> not found!', $block['renderer'])
                    ];
                } else {
                    $renderer = new $block['renderer']($this);
                }
                // draw block
                $renderer->draw(
                    sprintf(
                        'mission-control-item mission-control-item-r%d-c%d',
                        $block['shape']['rows'],
                        $block['shape']['cols']
                    ),
                    $rand_id,
                    $block['title'],
                    $block['subtitle'],
                    $block['shape'],
                    $block_args,
                    $opts
                );
                array_push($blocks_ids, $rand_id);
                ?>
                <?php
            }
            ?>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {
                // create grid
                let grid = $('#<?php echo $this->grid_id ?>').packery({
                    itemSelector: '.mission-control-item',
                    columnWidth: <?php echo $block_size ?>,
                    gutter: <?php echo $opts['block_gutter'] ?>
                });
                <?php if($opts['draggable']){ ?>
                    // make all grid-items draggable
                    grid.find('.mission-control-item').each(function (i, gridItem) {
                        let draggie = new Draggabilly(gridItem);
                        // bind drag events to Packery
                        grid.packery('bindDraggabillyEvents', draggie);
                    });
                <?php
                }
                ?>
            });

            function mission_control_switch_shape(box_id, new_rows, new_cols) {
                // get box
                let box = $('#' + box_id);
                // remove previous class
                box.removeClass(function (_, classes) {
                    return (classes.match(/\s*mission-control-item-r([0-9]+)-c([0-9]+)\s*/g) || [])
                        .join(' ');
                });
                // add new class
                box.addClass("mission-control-item-r{0}-c{1}".format(new_rows, new_cols));
                // update block data
                let new_shape = {'rows': new_rows, 'cols': new_cols};
                box.data(
                    'shape',
                    CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(JSON.stringify(new_shape)))
                );
                // update the grid
                box.closest('.mission-control-grid').packery();
            }//mission_control_switch_shape

            function mission_control_dispose_block(block_id) {
                let grid = $('#<?php echo $this->grid_id ?>');
                let block = $('#{0}'.format(block_id));
                grid.packery('remove', block).packery();
                // highlight the Save button in the menu
                $('#mission-control-side-menu-save-button').removeClass('btn-default');
                $('#mission-control-side-menu-save-button').addClass('btn-warning');
            }//mission_control_dispose_block

            function mission_control_serialize_block(box_id) {
                // get box
                let box = $('#' + box_id);
                // create JSON string
                return `
                    {
                        "shape": {0},
                        "renderer": "{1}",
                        "title": "{2}",
                        "subtitle": "{3}",
                        "args": {4}
                    }
                    `.format(
                    CryptoJS.enc.Base64.parse(box.data('shape')).toString(CryptoJS.enc.Utf8),
                    box.data('renderer'),
                    CryptoJS.enc.Base64.parse(box.data('title')).toString(CryptoJS.enc.Utf8),
                    CryptoJS.enc.Base64.parse(box.data('subtitle')).toString(CryptoJS.enc.Utf8),
                    CryptoJS.enc.Base64.parse(box.data('properties')).toString(CryptoJS.enc.Utf8)
                );
            }
        </script>


        <style type="text/css">

            <?php
            if ($opts['wide_mode']) {?>
                /* enlarge page container */
                #page_container {
                    min-width: 100%;
                }

                /* adjust grid margin to leave space for the menu */
                .mission-control-grid {
                    margin: 0 80px;
                }
            <?php
            }
            ?>

            #<?php echo $this->grid_id ?>{
                background: inherit;
                max-width: 100%;
            }

            /* clear fix */
            #<?php echo $this->grid_id ?>:after {
                content: '';
                display: block;
                clear: both;
            }

            .mission-control-item {
                float: left;
                background: #fff;
                border: <?php echo $opts['block_border_thickness'] ?>px solid hsla(0, 0%, 80%, 0.5);
            }

            <?php
            for ($i = 1; $i < $opts['resolution']+1; $i++) {
              for ($j = 1; $j < $opts['resolution']+1; $j++) {
                 $h = $i * $block_size + ($i - 1) * $opts['block_gutter'];
                 $w = $j * $block_size + ($j - 1) * $opts['block_gutter'];

                $header_w = $w - 2*$header_h - 2*$opts['block_border_thickness'];

                echo sprintf("
                  .mission-control-item-r%d-c%d{
                    min-height: %dpx;
                    min-width: %dpx;
                    height: %dpx;
                    width: %dpx;
                    max-height: %dpx;
                    max-width: %dpx;
                  }
      
                  .mission-control-item-r%d-c%d .block_renderer_header > td h5,
                  .mission-control-item-r%d-c%d .block_renderer_header > td h6{
                    min-width: %dpx;
                    width: %dpx;
                    max-width: %dpx;
                  }
      
                  .mission-control-item-r%d-c%d .block_renderer_container .resizable{
                    max-width: %dpx;
                    max-height: %dpx;
                  }
                  ",
                  $i, $j,
                  $h,
                  $w,
                  $h,
                  $w,
                  $h,
                  $w,
                  $i, $j,
                  $i, $j,
                  $header_w,
                  $header_w,
                  $header_w,
                  $i, $j,
                  $w,
                  $h-$header_h
                );
              }
            }
            ?>

            <?php
            if($opts['draggable']){
                ?>
                .mission-control-item:hover {
                    cursor: move;
                }
            <?php
            }
            ?>

            .mission-control-item.is-dragging,
            .mission-control-item.is-positioning-post-drag {
                background: #fec612;
                z-index: 2;
            }

            .packery-drop-placeholder {
                outline: 3px dashed hsla(0, 0%, 0%, 0.5);
                outline-offset: -6px;
                -webkit-transition: -webkit-transform 0.2s;
                transition: transform 0.2s;
            }

            .block_renderer_canvas table {
                width: 100%;
                height: 100%;
            }

            .block_renderer_canvas .block_renderer_header > .block_renderer_icon,
            .block_renderer_canvas .block_renderer_header > .block_renderer_menu_icon {
                width: <?php echo $header_h ?>px;
                padding: 10px;
                text-align: center;
            }

            .block_renderer_canvas .block_renderer_header > td:last-of-type {
                padding: 0;
            }

            .block_renderer_canvas .block_renderer_header > td:first-of-type i {
                font-size: 20px
            }

            .block_renderer_canvas .block_renderer_header > td:last-of-type a.dropdown-toggle {
                font-size: 18px;
                color: inherit;
            }

            .block_renderer_canvas .block_renderer_header > td:last-of-type .dropdown-menu li.divider {
                margin: 5px 0;
            }

            .block_renderer_canvas .block_renderer_header > td h5,
            .block_renderer_canvas .block_renderer_header > td h6 {
                margin: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .block_renderer_canvas .block_renderer_container {
                height: 100%;
            }

            .block_renderer_canvas .block_renderer_container td:first-of-type {
                text-align: center;
                vertical-align: middle;
            }


            .block_renderer_canvas .block_renderer_header .dropdown-toggle:active,
            .block_renderer_canvas .block_renderer_header .dropdown-toggle.active {
                background-image: none;
                outline: 0;
                -webkit-box-shadow: none;
                box-shadow: none;
            }
        </style>
        <?php
        return [
            'blocks_ids' => $blocks_ids
        ];
    }//create

    public function get_available_shapes() {
        return $this->sizes_available;
    }//get_available_shapes

}//MissionControl


class MissionControlMenu {

    function __construct($grid_id, $side, $package_name, $mission_db_name, $mission_name = NULL, $mission_regex = NULL) {
        $db = new Database($package_name, $mission_db_name, $mission_regex);
        // get list of missions available
        $missions_list = $db->list_keys();
        // render side menu
        self::render_menu($grid_id, $side, $mission_name);
        // add load mission modal
        self::add_load_modal($missions_list);
        // add new block modal
        if (!is_null($mission_name)) {
            self::add_new_block_modal($mission_name);
        }
    }//__construct

    public static function render_menu($grid_id, $side, $mission_name) {
        $is_mission_loaded = !is_null($mission_name);
        ?>
        <style type="text/css">

            .mission-control-side-menu {
                position: absolute;
                top: 90px;
            <?php echo $side ?>: 10px;
                width: 70px;
            }

            .mission-control-side-menu-button {
                background-image: none;
                padding: 10px 0;
            }

            .mission-control-side-menu-button.disabled {
                background-color: lightgray;
            }

            .mission-control-side-menu-button .glyphicon {
                font-size: 18px;
            }

            .mission-control-side-menu-button #label {
                padding-right: 3px;
                margin-top: 6px;
            }
        </style>

        <div class="btn-group-vertical mission-control-side-menu" id="mission-control-side-menu"
             role="group" aria-label="...">
            <button type="button" class="btn btn-default mission-control-side-menu-button"
                    onclick="mission_control_new_mission_fcn()">
                <div>
                    <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
                </div>
                <div id="label">
                    New
                </div>
            </button>
            <button type="button" class="btn btn-default mission-control-side-menu-button"
                    data-toggle="modal" data-target="#mission-control-load-modal">
                <div>
                    <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
                </div>
                <div id="label">
                    Open
                </div>
            </button>
            <button
                    type="button"
                    id="mission-control-side-menu-save-button"
                    class="btn btn-default mission-control-side-menu-button <?php echo ($is_mission_loaded) ? '' : 'disabled' ?>"
                <?php echo ($is_mission_loaded) ? 'onclick="mission_control_save_fcn()"' : '' ?>
            >
                <div>
                    <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
                </div>
                <div id="label">
                    Save
                </div>
            </button>
            <button type="button" class="btn btn-default mission-control-side-menu-button"
                    onclick="mission_control_save_as_fcn()">
                <div>
                    <span class="glyphicon glyphicon-floppy-save" aria-hidden="true"></span>
                </div>
                <div id="label">
                    Save as
                </div>
            </button>

            <legend style="margin: 0; margin-top: 4px; border: 0"></legend>

            <button
                    type="button"
                    class="btn btn-default mission-control-side-menu-button <?php echo ($is_mission_loaded) ? '' : 'disabled' ?>"
                    data-toggle="modal"
                <?php echo ($is_mission_loaded) ? 'data-target="#mission-control-add-block-modal"' : '' ?>
            >
                <div>
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </div>
                <div id="label">
                    Add
                </div>
            </button>
        </div>

        <script type="text/javascript">

            function mission_control_get_blocks_json() {
                // get blocks order
                var blocks = $("#<?php echo $grid_id ?>").packery('getItemElements');
                // turn each block into a JSON string
                var blocks_json = [];
                $.each(blocks, function (_, block) {
                    blocks_json.push(mission_control_serialize_block(block.id));
                });
                // ---
                return blocks_json;
            }//mission_control_get_blocks_json

            function mission_control_save_confirm_fcn(mission_name) {
                var blocks_json = mission_control_get_blocks_json();
                // compile blocks into a single JSON string
                var mission_str = '{"blocks": [{0}]}'.format(
                    blocks_json.join(',')
                );
                // trigger save event
                $(window).trigger('MISSION_CONTROL_MENU_SAVE', [mission_name, mission_str]);
            }//mission_control_save_confirm_fcn

            function mission_control_save_as_fcn() {
                var mission_name = prompt("Name of the new mission", "");
                if (mission_name != null) {
                    mission_control_save_confirm_fcn(mission_name);
                }
            }//mission_control_save_as_fcn

            function mission_control_new_mission_fcn() {
                var mission_name = prompt("Name of the new mission", "");
                if (mission_name != null) {
                    var empty_mission = '{"blocks": []}';
                    // trigger save event
                    $(window).trigger('MISSION_CONTROL_MENU_SAVE', [mission_name, empty_mission]);
                }
            }//mission_control_new_mission_fcn

            function mission_control_load_fcn(mission_name) {
                // trigger load event
                $(window).trigger('MISSION_CONTROL_MENU_LOAD', [mission_name]);
                // close modal
                const modal = new bootstrap.Modal(document.getElementById('mission-control-load-modal'));
                modal.hide();
            }//mission_control_load_fcn

            function mission_control_save_fcn() {
                const question = "Are you sure you want to save?";
                openYesNoModal(
                    question,
                    function () {
                        mission_control_save_confirm_fcn("<?php echo $mission_name ?>")
                    },
                    false
                );
            }//mission_control_save_fcn

            function mission_control_delete_confirm_fcn(mission_name) {
                // trigger delete event
                $(window).trigger('MISSION_CONTROL_MENU_DELETE', [mission_name]);
            }//mission_control_delete_confirm_fcn

            function mission_control_delete_fcn(mission_name) {
                var question = "Are you sure you want to save?";
                openYesNoModal(
                    question,
                    function () {
                        mission_control_delete_confirm_fcn(mission_name)
                    },
                    false
                );
            }//mission_control_delete_fcn

            function mission_control_add_block_fcn(mission_name, block_json_b64) {
                var new_block_json = CryptoJS.enc.Base64.parse(block_json_b64).toString(CryptoJS.enc.Utf8);
                // get mission blocks json
                var blocks_json = mission_control_get_blocks_json();
                blocks_json.push(new_block_json);
                // compile blocks into a single JSON string
                var mission_str = '{"blocks": [{0}]}'.format(
                    blocks_json.join(',')
                );
                // trigger save event
                $(window).trigger('MISSION_CONTROL_MENU_SAVE', [mission_name, mission_str]);
            }//mission_control_add_block_fcn

            <?php if ($is_mission_loaded) {
            ?>
            $(window).on('MISSION_CONTROL_SAVE', function (grid_id) {
                mission_control_save_confirm_fcn("<?php echo $mission_name ?>");
            });
            <?php
            }
            ?>

            function mission_control_center_toolbox() {
                var side_menu = $('#mission-control-side-menu');
                var offset = ($(window).height() - side_menu.height()) / 2;
                offset = Math.max(90, offset);
                side_menu.css("top", offset);
            }//mission_control_center_toolbox

            $(window).on("resize", mission_control_center_toolbox);
            $(document).on("ready", mission_control_center_toolbox);

        </script>
        <?php
    }


    private static function add_load_modal($missions_list, $qs_key = 'mission') {
        ?>
        <div class="modal fade" id="mission-control-load-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Load Mission</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped">
                            <tr>
                                <td class="col-md-1 text-center text-bold">#</td>
                                <td class="col-md-7 text-bold">Name</td>
                                <td class="col-md-4 text-center text-bold">Actions</td>
                            </tr>
                            <?php
                            $i = 1;
                            foreach ($missions_list as $mission) {
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i ?></td>
                                    <td><?php echo $mission ?></td>
                                    <td class="text-center">
                                        <a class="btn btn-default btn-sm"
                                           onclick="mission_control_load_fcn('<?php echo $mission ?>')"
                                           role="button">
                                            <span class="glyphicon glyphicon-download-alt"
                                                  aria-hidden="true"></span>
                                            Open
                                        </a>
                                        &nbsp; | &nbsp;
                                        <a class="btn btn-danger btn-sm" role="button"
                                           onclick="mission_control_delete_fcn('<?php echo $mission ?>')">
                                            <span class="glyphicon glyphicon-trash"
                                                  aria-hidden="true"></span>
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                $i += 1;
                            }
                            ?>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close
                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <?php
    }//add_load_modal


    private static function add_new_block_modal($mission_name) {
        // load all block renderers registered
        Core::loadPackagesModules('renderers/blocks');
        // get all block renderers registered
        $renderers_available = Core::getClasses('system\classes\BlockRenderer');
        ?>
        <div class="modal fade" id="mission-control-add-block-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Add Block</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped">
                            <tr>
                                <td class="col-md-1 text-center text-bold">#</td>
                                <td class="col-md-7 text-bold">Name</td>
                                <td class="col-md-4 text-center text-bold">Actions</td>
                            </tr>
                            <?php
                            $i = 1;
                            foreach ($renderers_available as $renderer) {
                                if ($renderer::is_private()) {
                                    continue;
                                }
                                $default_json = base64_encode($renderer::get_default_JSON());
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i ?></td>
                                    <td><?php echo $renderer ?></td>
                                    <td class="text-center">
                                        <a class="btn btn-default btn-sm"
                                           onclick="mission_control_add_block_fcn('<?php echo $mission_name ?>', '<?php echo $default_json ?>')"
                                           role="button">
                                            <i class="bi bi-plus" aria-hidden="true"></i>
                                            Add
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                $i += 1;
                            }
                            ?>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close
                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <?php
    }//add_new_block_modal

}//MissionControlMenu


class MissionControlConfiguration {

    static protected $OPTIONS = [
        "draggable"              => [
            "name"    => "Enable draggables",
            "type"    => "boolean",
            "default" => TRUE
        ],
        "show_header"            => [
            "name"    => "Show blocks' headers",
            "type"    => "boolean",
            "default" => TRUE
        ],
        "show_icon"              => [
            "name"    => "Show blocks' icons",
            "type"    => "boolean",
            "default" => TRUE
        ],
        "show_menu"              => [
            "name"    => "Show blocks' menus",
            "type"    => "boolean",
            "default" => TRUE
        ],
        "show_size_selector"     => [
            "name"    => "Allow block resize",
            "type"    => "boolean",
            "default" => TRUE
        ],
        "show_properties"        => [
            "name"    => "Allow blocks configuration",
            "type"    => "boolean",
            "default" => TRUE
        ],
        "show_dispose"           => [
            "name"    => "Allow blocks deletion",
            "type"    => "boolean",
            "default" => TRUE
        ],
        "wide_mode"              => [
            "name"    => "Wide mode",
            "type"    => "boolean",
            "default" => FALSE
        ],
        "resolution"             => [
            "name"    => "Horizontal resolution of the grid",
            "type"    => "number",
            "default" => 10
        ],
        "block_gutter"           => [
            "name"    => "Space (in px) between blocks",
            "type"    => "number",
            "default" => 10
        ],
        "block_border_thickness" => [
            "name"    => "Thickness (in px) of the block borders",
            "type"    => "number",
            "default" => 1
        ],
        "scale_factor"           => [
            "name"    => "Scale factor [0.2 - 2.0]",
            "type"    => "float",
            "default" => 1.0
        ]
    ];

    function __construct($grid_id, $package_name, $mission_db_name, $mission_name = NULL) {
        if (is_null($mission_name)) {
            return;
        }
        $opts = self::get_options($package_name, $mission_db_name, $mission_name);
        // render modal
        self::render_modal($grid_id, $mission_name, $opts);
        self::render_button($grid_id);
    }//__construct

    public static function get_options($package_name, $mission_db_name, $mission_name) {
        $db = new Database($package_name, $mission_db_name . '_opts');
        // load mission options
        $opts = self::get_default_options();
        // load opts
        if ($db->key_exists($mission_name)) {
            $res = $db->read($mission_name);
            if (!$res['success']) {
                Core::throwError($res['data']);
            } else {
                $opts = array_merge($opts, $res['data']);
            }
        }
        return $opts;
    }//get_default_options

    private static function get_default_options() {
        $defaults = [];
        foreach (self::$OPTIONS as $key => $opt) {
            $defaults[$key] = $opt['default'];
        }
        return $defaults;
    }//get_default_options

    public static function render_modal($grid_id, $mission_name, $opts) {
        ?>
        <div class="modal fade mission-control-block-properties-modal"
             id="mission_<?php echo $grid_id ?>_options_modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                            <span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title text-center">
                            Mission Properties
                        </h4>
                    </div>

                    <div class="modal-body text-left">
                        <?php
                        // create layout for form
                        $layout = [];
                        // append custom arguments to the layout
                        foreach (static::$OPTIONS as $key => $opt) {
                            $layout_key = $key;
                            $layout_desc = [
                                'type'     => $opt['type'],
                                'editable' => TRUE,
                                'value'    => $opts[$key],
                                'name'     => $opt['name']
                            ];
                            if ($opt['type'] == 'enum') {
                                $layout_desc['placeholder_id'] = $opt['enum_values'];
                                $layout_desc['placeholder'] = array_map(ucfirst, $opt['enum_values']);
                            }
                            // add item to the layout
                            $layout[$layout_key] = $layout_desc;
                        }
                        $formID = sprintf('mission-%s-form', $grid_id);
                        $formClass = 'mission-properties-form';
                        $method = 'post';
                        // generate form
                        generateFormByLayout($layout, $formID, $formClass, $method, $action = NULL, $values = $opts);
                        ?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close
                        </button>
                        <button type="button" class="btn btn-success" id="save-button">Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $('#mission_<?php echo $grid_id ?>_options_modal #save-button').on('click', function () {
                let mission_name = '<?php echo $mission_name ?>';
                // get options from form object
                let form = '#mission-<?php echo $grid_id ?>-form';
                // turn list of pairs into object
                let options = serializeFormToJSON(form, excludeDisabled = false, blacklist_keys = ['token']);
                let options_json = JSON.stringify(options);
                // close modal
                let modal = new bootstrap.Modal(document.getElementById('mission_<?php echo $grid_id ?>_options_modal'));
                modal.hide();
                // save mission control
                $(window).trigger('MISSION_CONTROL_OPTIONS_SAVE', [mission_name, options_json]);
            });
        </script>
        <?php
    }//render_modal

    public static function render_button($grid_id, $class = 'default', $size = 'default') {
        ?>
        <button
                type="button"
                class="btn btn-<?php echo $class ?> btn-<?php echo $size ?>"
                data-toggle="modal"
                data-target="#mission_<?php echo $grid_id ?>_options_modal"
        >
            <i class="bi bi-cog" aria-hidden="true"></i>&nbsp;
            Settings
        </button>
        <?php
    }//render_button

}//MissionControlConfiguration


?>
