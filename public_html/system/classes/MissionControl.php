<?php

use \system\classes\Core;
use \system\classes\Database;
use \system\classes\BlockRenderer;


class MissionControl{

  private $grid_id;
  private $grid_width;
  private $resolution;
  private $block_gutter;
  private $block_size;
  private $block_border_thickness;
  private $sizes_available;
  private $blocks;

  function __construct($grid_id, $grid_width, $resolution, $block_gutter, $block_border_thickness, $sizes_available, $blocks=[]){
    $this->grid_id = $grid_id;
    $this->grid_width = $grid_width;
    $this->resolution = $resolution;
    $this->block_gutter = $block_gutter;
    $this->block_size = $block_size;
    $this->block_border_thickness = $block_border_thickness;
    $this->block_size = ( $grid_width - ($resolution-1)*$block_gutter ) / $resolution;
    $this->sizes_available = $sizes_available;
    $this->blocks = $blocks;
  }//__construct

  function get_ID(){
    return $this->grid_id;
  }//get_ID

  function create(){
    $header_h = 40;
    // load all block renderers registered
    Core::loadPackagesModules('renderers/blocks');
    // get all block renderers registered
    $renderers_available = Core::getClasses('system\classes\BlockRenderer');
    $blocks_ids = [];
    ?>
    <div id="<?php echo $this->grid_id ?>" class="mission-control-grid">
      <?php
      $empty_renderer = new EmptyRenderer($this);
      foreach ($this->blocks as $block){
        $rand_id = sprintf('block_%s', Core::generateRandomString(8));
        $block_args = $block['args'];
        if (!in_array($block['renderer'], $renderers_available)) {
          $renderer = $empty_renderer;
          $block_args = [
            'message' => sprintf('Renderer <code>%s</code> not found!', $block['renderer'])
          ];
        }else{
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
          $block['options']
        );
        array_push($blocks_ids, $rand_id);
        ?>
        <?php
      }
      ?>
    </div>

    <script type="text/javascript">
      $(document).ready(function() {
        // create grid
        var grid = $('#<?php echo $this->grid_id ?>').packery({
          itemSelector: '.mission-control-item',
          columnWidth: <?php echo $this->block_size ?>,
          gutter: <?php echo $this->block_gutter ?>
        });

        // make all grid-items draggable
        grid.find('.mission-control-item').each( function( i, gridItem ) {
          var draggie = new Draggabilly( gridItem );
          // bind drag events to Packery
          grid.packery( 'bindDraggabillyEvents', draggie );
        });
      });

      function mission_control_switch_shape(box_id, new_rows, new_cols){
        // get box
        var box = $('#'+box_id);
        // remove previous class
        box.removeClass( function(_, classes){
          classes_to_remove = (classes.match(/\s*mission-control-item-r([0-9]+)-c([0-9]+)\s*/g) || []).join(' ');
          return classes_to_remove;
        } );
        // add new class
        box.addClass("mission-control-item-r{0}-c{1}".format(new_rows, new_cols));
        // update block data
        var new_shape = {'rows': new_rows, 'cols': new_cols};
        box.data(
          'shape',
          CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(JSON.stringify(new_shape)))
        );
        // update the grid
        box.closest('.mission-control-grid').packery();
      }//mission_control_switch_shape

      function mission_control_dispose_block( block_id ){
        var grid = $('#<?php echo $this->grid_id ?>');
        var block = $('#{0}'.format(block_id));
        grid.packery('remove', block).packery();
        // highlight the Save button in the menu
        $('#mission-control-side-menu-save-button').removeClass('btn-default');
        $('#mission-control-side-menu-save-button').addClass('btn-warning');
      }//mission_control_dispose_block

      function mission_control_serialize_block(box_id){
        // get box
        var box = $('#'+box_id);
        // create JSON string
        var json_str = `
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
        return json_str;
      }
    </script>


    <style type="text/css">
      #<?php echo $this->grid_id ?> {
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
        border: <?php echo $this->block_border_thickness ?>px solid hsla(0, 0%, 80%, 0.5);
      }

      <?php
      for ($i = 1; $i < $this->resolution+1; $i++) {
        for ($j = 1; $j < $this->resolution+1; $j++) {
          $h = $i*$this->block_size+($i-1)*$this->block_gutter;
          $w = $j*$this->block_size+($j-1)*$this->block_gutter;
          $header_w = $w - 2*40 - 2*$this->block_border_thickness;
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
            $h-$header_h  //TODO: we may want to consider the case in which the header is hidden
          );
        }
      }
      ?>

      .mission-control-item:hover {
        cursor: move;
      }

      .mission-control-item.is-dragging,
      .mission-control-item.is-positioning-post-drag {
        background: #fec612;
        z-index: 2;
      }

      .packery-drop-placeholder{
        outline: 3px dashed hsla(0, 0%, 0%, 0.5);
        outline-offset: -6px;
        -webkit-transition: -webkit-transform 0.2s;
        transition: transform 0.2s;
      }

      .block_renderer_canvas table{
        width: 100%;
        height: 100%;
      }

      .block_renderer_canvas .block_renderer_header > .block_renderer_icon,
      .block_renderer_canvas .block_renderer_header > .block_renderer_menu_icon{
        width: <?php echo $header_h ?>px;
        padding: 10px;
        text-align: center;
      }

      .block_renderer_canvas .block_renderer_header > td:last-of-type{
        padding: 0;
      }

      .block_renderer_canvas .block_renderer_header > td:first-of-type i{
        font-size: 20px
      }

      .block_renderer_canvas .block_renderer_header > td:last-of-type a.dropdown-toggle{
        font-size: 18px;
        color: inherit;
      }

      .block_renderer_canvas .block_renderer_header > td:last-of-type .dropdown-menu li.divider{
        margin: 5px 0;
      }

      .block_renderer_canvas .block_renderer_header > td h5,
      .block_renderer_canvas .block_renderer_header > td h6{
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .block_renderer_canvas .block_renderer_container{
        height: 100%;
      }

      .block_renderer_canvas .block_renderer_container td:first-of-type{
        text-align: center;
        vertical-align: middle;
      }


      .block_renderer_canvas .block_renderer_header .dropdown-toggle:active,
      .block_renderer_canvas .block_renderer_header .dropdown-toggle.active{
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

  public function get_available_shapes(){
    return $this->sizes_available;
  }//get_available_shapes

}//MissionControl


class MissionControlMenu{

  function __construct($grid_id, $side, $package_name, $mission_db_name, $mission_name=NULL, $mission_regex=null){
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

  public static function render_menu($grid_id, $side, $mission_name){
    $is_mission_loaded = !is_null($mission_name);
    ?>
    <style type="text/css">
      .mission-control-side-menu{
        position: fixed;
        top: 90px;
        <?php echo $side ?>: 10px;
        width: 70px;
      }

      .mission-control-side-menu-button{
        background-image: none;
        padding: 10px 0;
      }

      .mission-control-side-menu-button.disabled{
        background-color: lightgray;
      }

      .mission-control-side-menu-button .glyphicon{
        font-size: 18px;
      }

      .mission-control-side-menu-button #label{
        padding-right: 3px;
        margin-top: 6px;
      }
    </style>

    <div class="btn-group-vertical mission-control-side-menu" id="mission-control-side-menu" role="group" aria-label="...">
      <button type="button" class="btn btn-default mission-control-side-menu-button" onclick="mission_control_new_mission_fcn()">
        <div>
          <span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
        </div>
        <div id="label">
          New
        </div>
      </button>
      <button type="button" class="btn btn-default mission-control-side-menu-button" data-toggle="modal" data-target="#mission-control-load-modal">
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
        class="btn btn-default mission-control-side-menu-button <?php echo ($is_mission_loaded)? '' : 'disabled' ?>"
        <?php echo ($is_mission_loaded)? 'onclick="mission_control_save_fcn()"' : '' ?>
        >
        <div>
          <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
        </div>
        <div id="label">
          Save
        </div>
      </button>
      <button type="button" class="btn btn-default mission-control-side-menu-button" onclick="mission_control_save_as_fcn()">
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
        class="btn btn-default mission-control-side-menu-button <?php echo ($is_mission_loaded)? '' : 'disabled' ?>"
        data-toggle="modal"
        <?php echo ($is_mission_loaded)? 'data-target="#mission-control-add-block-modal"' : '' ?>
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

      function mission_control_get_blocks_json(){
        // get blocks order
        var blocks = $("#<?php echo $grid_id ?>").packery('getItemElements');
        // turn each block into a JSON string
        var blocks_json = [];
        $.each(blocks, function(_, block){
          blocks_json.push(mission_control_serialize_block(block.id));
        });
        // ---
        return blocks_json;
      }//mission_control_get_blocks_json

      function mission_control_save_confirm_fcn(mission_name){
        var blocks_json = mission_control_get_blocks_json();
        // compile blocks into a single JSON string
        var mission_str = '{"blocks": [{0}]}'.format(
          blocks_json.join(',')
        );
        // trigger save event
        $(window).trigger('MISSION_CONTROL_MENU_SAVE', [mission_name, mission_str]);
      }//mission_control_save_confirm_fcn

      function mission_control_save_as_fcn(){
        var mission_name = prompt("Name of the new mission", "");
        if (mission_name != null) {
          mission_control_save_confirm_fcn(mission_name);
        }
      }//mission_control_save_as_fcn

      function mission_control_new_mission_fcn(){
        var mission_name = prompt("Name of the new mission", "");
        if (mission_name != null) {
          var empty_mission = '{"blocks": []}';
          // trigger save event
          $(window).trigger('MISSION_CONTROL_MENU_SAVE', [mission_name, empty_mission]);
        }
      }//mission_control_new_mission_fcn

      function mission_control_load_fcn(mission_name){
        // trigger load event
        $(window).trigger('MISSION_CONTROL_MENU_LOAD', [mission_name]);
        // close modal
        $('#mission-control-load-modal').modal('hide');
      }//mission_control_load_fcn

      function mission_control_save_fcn(){
        var question = "Are you sure you want to save?";
        openYesNoModal(
          question,
          function(){
            mission_control_save_confirm_fcn("<?php echo $mission_name ?>")
          },
          false
        );
      }//mission_control_save_fcn

      function mission_control_delete_confirm_fcn(mission_name){
        // trigger delete event
        $(window).trigger('MISSION_CONTROL_MENU_DELETE', [mission_name]);
      }//mission_control_delete_confirm_fcn

      function mission_control_delete_fcn(mission_name){
        var question = "Are you sure you want to save?";
        openYesNoModal(
          question,
          function(){
            mission_control_delete_confirm_fcn(mission_name)
          },
          false
        );
      }//mission_control_delete_fcn

      function mission_control_add_block_fcn(mission_name, block_json_b64){
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
        $(window).on('MISSION_CONTROL_SAVE', function(grid_id){
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


  private static function add_load_modal($missions_list, $qs_key='mission'){
    ?>
    <div class="modal fade" id="mission-control-load-modal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                    <a class="btn btn-default btn-xs" onclick="mission_control_load_fcn('<?php echo $mission ?>')" role="button">
                      <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
                      Open
                    </a>
                    &nbsp; | &nbsp;
                    <a class="btn btn-danger btn-xs" role="button" onclick="mission_control_delete_fcn('<?php echo $mission ?>')">
                      <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
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
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php
  }//add_load_modal


  private static function add_new_block_modal($mission_name){
    // load all block renderers registered
    Core::loadPackagesModules('renderers/blocks');
    // get all block renderers registered
    $renderers_available = Core::getClasses('system\classes\BlockRenderer');
    ?>
    <div class="modal fade" id="mission-control-add-block-modal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                    <a class="btn btn-default btn-xs" onclick="mission_control_add_block_fcn('<?php echo $mission_name ?>', '<?php echo $default_json ?>')" role="button">
                      <i class="fa fa-plus" aria-hidden="true"></i>
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
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php
  }//add_new_block_modal

}//MissionControlMenu


?>
