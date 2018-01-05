<?php

class Section{

	public static function begin( $name, $cmdName = null, $cmdGlyph = null, $cmdExtra = null, $actionNameList = null, $actionGlyphList = null, $actionExtraList = null ){
		$cmdExtra = ( ($cmdExtra == null)? '' : $cmdExtra );
		$actionExtraList = ( ($actionExtraList == null)? array() : $actionExtraList );
		//
		echo '<ul class="nav nav-tabs">
			<li role="presentation" class="active" style="margin-left:10px; font-size:12pt"><a>'.$name.'</a></li>'.
			( ( $cmdName !== null )? '<li role="presentation" style="margin-left:10px"><a '. ( (strpos($cmdExtra, 'href') === false)? 'href="#" '.$cmdExtra : $cmdExtra ) .' style="background-color:lightskyblue; color:#ffffff"><span class="glyphicon '.$cmdGlyph.'" aria-hidden="true"></span>&nbsp; '.$cmdName.'</a></li>' : '' );

		if( $actionNameList !== null ){
			echo '<li role="presentation" class="dropdown pull-right">
				<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" href="#">Actions <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu" style="width:180px">';
			for( $i = 0; $i < sizeof($actionNameList); $i++ ){
				echo '<li><a '. ( (strpos($actionExtraList[$i], 'href') === false)? 'href="#" '.$actionExtraList[$i] : $actionExtraList[$i] ) .' >'.$actionNameList[$i].' <span class="glyphicon '.$actionGlyphList[$i].' pull-right"></span></a></li>';
			}
			echo '</ul>
				</li>
			';
		}

		echo '</ul>';
		echo '<div style="width:100%; border-bottom:1px solid #ddd; padding:20px; background-color:white">';
	}//begin

	public static function end( $footerMsg = null ){
		echo ($footerMsg !== null) ? '<h6 class="text-right" style="padding-right:30px; margin-top:10px; border-top:1px dashed lightgrey; padding-top:10px; margin-bottom:0">'.$footerMsg.'</h6>' : '';
		echo '</div>';
	}//end

}//Section

?>
