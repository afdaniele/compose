<?php

function renderPaginator(string $action, int $total, int $current, int $visible = 5){
	$lower = intval($current - floor($visible / 2.0));
	$higher = intval($current + floor($visible / 2.0));
	if ($lower <= 0) {
		$higher = 1 - $lower;
		$lower = 1;
	}
	$higher = min($total, $higher);
	$options = range($lower, $higher);
	$prev_enabler = ($current > 1)? "" : "disabled";
	$next_enabler = ($current < $total)? "" : "disabled";
	$prev_href = $action . "page=" . ($current - 1);
	$next_href = $action . "page=" . ($current + 1);
	?>
 
	<nav aria-label="Page navigation">
		<ul class="pagination justify-content-center">
			<li class="page-item <?php echo $prev_enabler; ?>">
				<a class="page-link" href="<?php echo $prev_href; ?>">Previous</a>
			</li>
			
			<?php
			foreach ($options as $option){
				$href = $action . "page=" . $option;
				?>
				<li class="page-item">
					<a class="page-link" href="<?php echo $href; ?>">
						<?php echo $option; ?>
					</a>
				</li>
				<?php
			}
			?>
			
			<li class="page-item <?php echo $next_enabler; ?>">
				<a class="page-link" href="<?php echo $next_href; ?>">Next</a>
			</li>
		</ul>
	</nav>
	<?php
}





//function buildPaginator( $action, $available_pages, $current_page ){
//
//	echo '<li ' . (( $current_page <= 1 )? 'class="disabled"' : '') . '><a href="' . (( $current_page > 1 )? $action.'page='.($current_page-1) : '#') . '" style="border-right:2px solid #ddd"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>';
//
//
//	if( $available_pages <= 5 ){
//		// little paginator: (size:1+(0 <= x <= 5)+1)    | << | 1 | 2 | 3 | 4 | 5 | >> |
//		for( $i = 1; $i <= $available_pages; $i++ ){
//			echo '<li ' . (( $current_page == $i )? 'class="active"' : '') . '><a href="' . $action .'page='.$i .'">' . $i . '</a></li>';
//		}
//	}else{
//		if( $current_page >= 5 && $current_page <= $available_pages-4 ){
//			// default paginator: (size:1+7+1)    | << | 1 | ... | i-1 | (i) | i+1 | ... | n | >> |
//			/*  1  */ echo '<li><a href="' . $action .'page=1' .'">1</a></li>';
//			/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
//			/* i-1 */ echo '<li><a href="' . $action .'page=' . ($current_page-1) .'">' . ($current_page-1) . '</a></li>';
//			/*  i  */ echo '<li class="active"><a>' . $current_page . '</a></li>';
//			/* i+1 */ echo '<li><a href="' . $action .'page=' . ($current_page+1) .'">' . ($current_page+1) . '</a></li>';
//			/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
//			/*  n  */ echo '<li><a href="' . $action .'page='.$available_pages .'">' . $available_pages . '</a></li>';
//		}else{
//			if( $current_page < 5 ){
//				// left paginator: (size:1+(5 <= x <= 7)+1)    | << | 1 | 2 | 3 | (4) | 5 | ... | n | >> |
//				for( $i = 1; $i <= (($current_page == 1)? ($current_page+2) : ($current_page+1)); $i++ ){
//					echo '<li ' . (( $current_page == $i )? 'class="active"' : '') . '><a href="' . $action .'page='.$i .'">' . $i . '</a></li>';
//				}
//				/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
//				/*  n  */ echo '<li><a href="' . $action .'page='.$available_pages .'">' . $available_pages . '</a></li>';
//			}else{
//				// right paginator: (size:1+(5 <= x <= 7)+1)    | << | 1 | ... | n-4 | (n-3) | n-2 | n-1 | n | >> |
//				/*  1  */ echo '<li><a href="' . $action .'page=1' .'">1</a></li>';
//				/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
//				for( $i = (($current_page == $available_pages)? ($current_page-2) : ($current_page-1)); $i <= $available_pages; $i++ ){
//					echo '<li ' . (( $current_page == $i )? 'class="active"' : '') . '><a href="' . $action .'page='.$i .'">' . $i . '</a></li>';
//				}
//			}
//		}
//	}
//
//	echo '<li ' . (( $current_page >= $available_pages )? 'class="disabled"' : '') . '><a href="' . (( $current_page < $available_pages )? $action.'page='.($current_page+1) : '#') . '" style="border-left:2px solid #ddd"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>';
//
//}

?>
