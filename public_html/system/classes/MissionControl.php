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

  function create(){
    $header_h = 40;
    // load all block renderers registered
    Core::loadPackagesModules( 'renderers/blocks' );
    // get all block renderers registered
    $renderers_available = Core::getClasses( 'system\classes\BlockRenderer' );
    $blocks_ids = [];
    ?>
    <div id="<?php echo $this->grid_id ?>" class="mission-control-grid">
      <?php
      $empty_renderer = new EmptyRenderer( $this );
      foreach ($this->blocks as $block){
        $rand_id = Core::generateRandomString(8);
        $block_args = $block['args'];
        if( !in_array($block['renderer'], $renderers_available) ){
          $renderer = $empty_renderer;
          $block_args = [
            'message' => sprintf('Renderer <code>%s</code> not found!', $block['renderer'])
          ];
        }else{
          $renderer = new $block['renderer']( $this );
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

      function mission_control_switch_shape( box_id, new_rows, new_cols ){
        // get box
        box = $('#'+box_id);
        // remove previous class
        box.removeClass( function(_, classes){
          classes_to_remove = (classes.match(/\s*mission-control-item-r([0-9]+)-c([0-9]+)\s*/g) || []).join(' ');
          return classes_to_remove;
        } );
        // add new class
        box.addClass( "mission-control-item-r{0}-c{1}".format( new_rows, new_cols ) );
        // update the grid
        box.closest('.mission-control-grid').packery();
      }

      function mission_control_dispose_block( block_id ){
        $('.block_renderer_canvas').remove( '#{0}'.format(block_id) );
        $('#<?php echo $this->grid_id ?>').packery();
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

  function __construct($grid_id, $side, $package_name, $mission_db_name, $mission_name=NULL){
    $db = new Database($package_name, $mission_db_name);
    // get list of missions available
    $missions_list = $db->list_keys();
    // render side menu
    self::render_menu($grid_id, $side, $mission_name);
    // add load mission modal
    self::add_load_modal($missions_list);
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
      <button type="button" class="btn btn-default mission-control-side-menu-button">
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
      <button type="button" class="btn btn-default mission-control-side-menu-button">
        <div>
          <span class="glyphicon glyphicon-floppy-save" aria-hidden="true"></span>
        </div>
        <div id="label">
          Save as
        </div>
      </button>

      <legend style="margin: 0; margin-top: 4px; border: 0"></legend>

      <button type="button" class="btn btn-default mission-control-side-menu-button">
        <div>
          <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
        </div>
        <div id="label">
          Add
        </div>
      </button>
    </div>

    <script type="text/javascript">

      function mission_control_save_confirm_fcn(){
        alert('SAVED!');
      }//mission_control_save_confirm_fcn

      function mission_control_save_fcn(){
        var question = "Are you sure you want to save?";
        openYesNoModal(
          question,
          mission_control_save_confirm_fcn,
          false
        );
      }//mission_control_save_fcn

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


  public static function add_load_modal($missions_list, $qs_key='mission'){
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
                // render a table row
                $resource_url = Core::getCurrentResourceURL(
                  [$qs_key => $mission]
                );
                ?>
                <tr>
                  <td class="text-center"><?php echo $i ?></td>
                  <td><?php echo $mission ?></td>
                  <td class="text-center">
                    <a class="btn btn-default btn-xs" href="<?php echo $resource_url ?>" role="button">
                      <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
                      Open
                    </a>
                    &nbsp; | &nbsp;
                    <a class="btn btn-danger btn-xs" href="#" role="button">
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
            <button type="button" class="btn btn-primary">Load</button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php
  }

}//MissionControlMenu


?>
