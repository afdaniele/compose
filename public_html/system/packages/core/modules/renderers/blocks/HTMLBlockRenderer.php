<?php
use \system\classes\Core;
use \system\classes\BlockRenderer;

class HTMLBlockRenderer extends BlockRenderer{

  static protected $ARGUMENTS = [
    "service" => [
      "name" => "ROS Service",
      "type" => "text",
      "mandatory" => True
    ],
    "trim_param" => [
      "name" => "Trim parameter name",
      "type" => "text",
      "mandatory" => True
    ],
    "topic" => [
      "name" => "ROS Topic",
      "type" => "text",
      "mandatory" => True
    ],
    "background_color" => [
      "name" => "Background color",
      "type" => "color",
      "mandatory" => True,
      "default" => "#fff"
    ]
  ];

  protected static function render($id, &$args){

  }//render

}//EmptyRenderer
?>
