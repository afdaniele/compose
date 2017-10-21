<?php

function buildPaginator( $action, $available_pages, $current_page ){

	echo '<li ' . (( $current_page <= 1 )? 'class="disabled"' : '') . '><a href="' . (( $current_page > 1 )? $action.'page='.($current_page-1) : '#') . '" style="border-right:2px solid #ddd"><span aria-hidden="true">&laquo;</span><span class="sr-only">Precedente</span></a></li>';


	if( $available_pages <= 5 ){
		// little paginator: (size:1+(0 <= x <= 5)+1)    | << | 1 | 2 | 3 | 4 | 5 | >> |
		for( $i = 1; $i <= $available_pages; $i++ ){
			echo '<li ' . (( $current_page == $i )? 'class="active"' : '') . '><a href="' . $action .'page='.$i .'">' . $i . '</a></li>';
		}
	}else{
		if( $current_page >= 5 && $current_page <= $available_pages-4 ){
			// default paginator: (size:1+7+1)    | << | 1 | ... | i-1 | (i) | i+1 | ... | n | >> |
			/*  1  */ echo '<li><a href="' . $action .'page=1' .'">1</a></li>';
			/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
			/* i-1 */ echo '<li><a href="' . $action .'page=' . ($current_page-1) .'">' . ($current_page-1) . '</a></li>';
			/*  i  */ echo '<li class="active"><a>' . $current_page . '</a></li>';
			/* i+1 */ echo '<li><a href="' . $action .'page=' . ($current_page+1) .'">' . ($current_page+1) . '</a></li>';
			/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
			/*  n  */ echo '<li><a href="' . $action .'page='.$available_pages .'">' . $available_pages . '</a></li>';
		}else{
			if( $current_page < 5 ){
				// left paginator: (size:1+(5 <= x <= 7)+1)    | << | 1 | 2 | 3 | (4) | 5 | ... | n | >> |
				for( $i = 1; $i <= (($current_page == 1)? ($current_page+2) : ($current_page+1)); $i++ ){
					echo '<li ' . (( $current_page == $i )? 'class="active"' : '') . '><a href="' . $action .'page='.$i .'">' . $i . '</a></li>';
				}
				/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
				/*  n  */ echo '<li><a href="' . $action .'page='.$available_pages .'">' . $available_pages . '</a></li>';
			}else{
				// right paginator: (size:1+(5 <= x <= 7)+1)    | << | 1 | ... | n-4 | (n-3) | n-2 | n-1 | n | >> |
				/*  1  */ echo '<li><a href="' . $action .'page=1' .'">1</a></li>';
				/* ... */ echo '<li class="disabled"><a class="no-hover">...</a></li>';
				for( $i = (($current_page == $available_pages)? ($current_page-2) : ($current_page-1)); $i <= $available_pages; $i++ ){
					echo '<li ' . (( $current_page == $i )? 'class="active"' : '') . '><a href="' . $action .'page='.$i .'">' . $i . '</a></li>';
				}
			}
		}
	}

	echo '<li ' . (( $current_page >= $available_pages )? 'class="disabled"' : '') . '><a href="' . (( $current_page < $available_pages )? $action.'page='.($current_page+1) : '#') . '" style="border-left:2px solid #ddd"><span aria-hidden="true">&raquo;</span><span class="sr-only">Successiva</span></a></li>';

}

?>
