<?php
use \system\classes\Core as Core;
use \system\classes\BlockRenderer as BlockRenderer;

class PlaceholderRenderer extends BlockRenderer{

    public function draw( $class, $id, $title, $subtitle, &$shape, &$args=[], &$opts=[], $DEPRECATED_show_header=True ){
        ?>
        <div class="block_renderer_canvas text-center <?php echo $class ?>"
            id="<?php echo $id ?>"
            data-renderer="<?php echo get_called_class() ?>"
            style="background:transparent; border:1px solid transparent"
        >
        </div>
        <?php
    }//draw

    protected static function render( $id, &$args ){}//render

}//PlaceholderRenderer
?>
