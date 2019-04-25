<?php
use \system\classes\Core;
use \system\classes\BlockRenderer;

class EmptyRenderer extends BlockRenderer{

  static protected $PRIVATE = true;


  protected static function render($id, &$args){
    if( !is_null($args) && isset($args['message']) ){
      ?>
      <p style="position:relative; top:50%; transform:translateY(-50%)">
        <?php echo $args['message'] ?>
      </p>
      <?php
    }else{
      ?>
      <div id="image_placeholder"></div>

      <style type="text/css">
        #<?php echo $id ?> #image_placeholder{
          width:100%;
          height:100%;
          max-height: 100px;
          background-image: url('<?php echo Core::getImageURL('placeholder.png') ?>');
          background-position: center center;
          background-size: auto 100%;
          background-repeat: no-repeat;
        }
      </style>
      <?php
    }
  }//render

}//EmptyRenderer
?>
