<?php

use \system\classes\Core as Core;
use \system\classes\BlockRenderer as BlockRenderer;


class MissionControl{

    private $grid_id;
    private $grid_width;
    private $resolution;
    private $block_gutter;
    private $block_size;
    private $block_border_thickness;
    private $sizes_available;
    private $blocks;

    function __construct( $grid_id, $grid_width, $resolution, $block_gutter, $block_border_thickness, $sizes_available, $blocks=[] ){
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
            $( document ).ready(function() {
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

            /* ---- grid ---- */

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

            /* ---- .mission-control-item ---- */

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
                        ", $i, $j,
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



?>
