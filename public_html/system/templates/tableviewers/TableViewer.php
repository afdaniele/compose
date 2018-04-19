<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Wednesday, December 28th 2016
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Monday, January 15th 2018


/**
 * Created by PhpStorm.
 * User: andrea
 * Date: 1/15/15
 * Time: 5:39 PM
 */

namespace system\templates\tableviewers;


use system\classes\Configuration;
use system\classes\Core;
use system\classes\enum\StringType;

class TableViewer {


	public static function parseFeatures( &$features, &$values ){
		$features['_valid'] = array();
		//
		foreach( $features as $key => $feature ){
			$val = null;
			$default = false;
			//
			if( isset($values[$key]) && strlen($values[$key]) > 0 ){
				$val = $values[$key];
			}else{
				$val = $feature['default'];
				$default = true;
			}
			// type parsing
			if( !$default && $val != null ){
				//
				switch( $feature['type'] ){
					case 'integer':
						if( StringType::isValid($val, StringType::$NUMERIC) ){
							$val = intval( $val );
							if( isset($feature['minValue']) && ( $val < $feature['minValue'] ) ){
								$val = $feature['default'];
								$default = true;
							}
							if( isset($feature['maxValue']) && ( $val > $feature['maxValue'] ) ){
								$val = $feature['default'];
								$default = true;
							}
						}else{
							$val = $feature['default'];
							$default = true;
						}
						break;
					case 'float':
						if( StringType::isValid($val, StringType::$FLOAT) ){
							$val = floatval( $val );
							if( isset($feature['minValue']) && ( $val < $feature['minValue'] ) ){
								$val = $feature['default'];
								$default = true;
							}
							if( isset($feature['maxValue']) && ( $val > $feature['maxValue'] ) ){
								$val = $feature['default'];
								$default = true;
							}
						}else{
							$val = $feature['default'];
							$default = true;
						}
						break;
					case 'alpha':
						if( !StringType::isValid($val, StringType::$ALPHABETIC) ){
							$val = $feature['default'];
							$default = true;
						}
						break;
					default:
						// nothing to do
						break;
				}// switch
			}
			// enum parsing
			if( !$default && isset($feature['values']) && is_array($feature['values']) ){
				if( !in_array( $val, $feature['values'] ) ){
					$val = $feature['default'];
					$default = true;
				}
			}
			// at the end
			$features[$key]['value'] = $val;
			//
			if( !$default ){
				$features['_valid'][$key] = $val;
			}
		}// foreach
		//
		// compute offset and limit if needed
		$pagination = ( isset($features['results']) && isset($features['page']) );
		$features['offset'] = array( 'value' => ($features['results']['value'] * ($features['page']['value']-1)) );
		$features['limit'] = array( 'value' => $features['results']['value'] );
	}//parseFeatures







	public static function generateTableViewer( $baseurl, $res, $features, $table, $formID='the-form' ){
		// extract informations
		$features_values = array();
		foreach( $features as $key => $feat ){
			$features_values[$key] = $feat['value'];
		}
		//
		$filtered_features = array();
		$querystrings = array();
		foreach( $features_values as $key => $value ){
			$filtered_features[$key] = $features_values;
			unset( $filtered_features[$key][$key] );
			$filtered_features[$key] = array_keys( $filtered_features[$key] );
			//
			$resource = Configuration::$BASE.$baseurl.toQueryString( $filtered_features[$key], $features['_valid'], true, true );
			//
			$querystrings[$key] = $resource;
		}
		// get informations
		$pagination = ( isset($features['page']) );
		$offset = ( ($pagination)? $features['offset']['value'] : 0 );
		$result_per_page = ( ($pagination)? $features['results']['value'] : $res['size'] );
		$current_page = ( ($pagination)? $features['page']['value'] : 1 );
		$total_count = ( ($pagination)? $res['total'] : $res['size'] );
		$res_count = $res['size'];
		$data = $res['data'];
		//
		$tagfilter_in_use = (isset($features['tag']) && $features_values['tag'] != null);
		$keywordsfilter_in_use = (isset($features['keywords']) && $features_values['keywords'] != null);
		$filter_in_use = ( $tagfilter_in_use || $keywordsfilter_in_use );
		//
		$filter_enabled = ( isset($features['tag']) || isset($features['keywords']) );
		//
		$order_enabled = ( isset($features['order']) );
		//
		$available_pages = ceil( $total_count / $result_per_page );
		//
		if( $available_pages == 0 ){
			// NO RESULTS
			$res_count = 0;
		}else{
			if( $current_page > $available_pages ){
				// Invalid page number, redirect to the last possible one
				\system\classes\Core::redirectTo( $querystrings['page'].'page='.$available_pages );
				echo $querystrings['page'].'page='.$available_pages;
			}
		}
		//
		$table_viewer_unique_id = generateRandomString(4);

		?>
		<div class="col-md-12" style="padding:0">

		<!-- === Begin Results Bar ================================================================================= -->
		<nav class="navbar navbar-default" role="navigation" style="margin-bottom:6px">
			<div class="container-fluid" style="padding-left:0; padding-right:0">

				<div class="collapse navbar-collapse navbar-left" style="padding-left:10px; padding-right:0">

					<ul class="nav navbar-nav navbar-left">
						<li>
							<a style="padding-right:0; padding-left:5px">
								<strong>Results:</strong>
							</a>
						</li>
					</ul>


					<ul class="nav navbar-nav navbar-left">

						<?php
						if( $pagination ){
							?>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" id="results_options_dropdown">
									<?php echo '( ' . ($offset+ ( ($res_count > 0)? 1 : 0)) . '-' . ($offset+$res_count) . ' )&nbsp; |&nbsp; '.$total_count . ' total' ?>
									<span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu" style="width:200px">
									<div style="padding:6px 8px 0 8px">
										<div class="text-left">
											<strong>Results per page:</strong>
										</div>
										<div style="padding-left:22px">
											<form method="get" action="<?php echo \system\classes\Configuration::$BASE.$baseurl ?>">
												<?php
												$options = array(5, 10, 20, 30);
												foreach( $options as $qty ){
													?>
													<div class="radio">
														<label>
															<input type="radio" name="results" id="option_<?php echo $qty ?>" value="<?php echo $qty ?>" <?php echo ( ($result_per_page == $qty)? 'checked' : '' ) ?> onclick="this.form.submit();">
															<label for="option_<?php echo $qty ?>" style="padding-left:4px"><?php echo $qty ?> results</label>
														</label>
													</div>
												<?php
												}
												//
												if( !in_array($result_per_page, $options) ){
													// add an extra row
													?>
													<li role="presentation" class="divider"></li>
													<li role="presentation" class="dropdown-header">Custom:</li>
													<input type="radio" id="option_custom" checked>
													<label for="option_custom" style="padding-left:4px"><?php echo $result_per_page ?> results</label>
												<?php
												}
												//
												foreach( $filtered_features['results'] as $param ){
													if( isset($features['_valid'][$param]) ){
														echo '<input type="hidden" name="'.$param.'" value="'.$features_values[$param].'"></input>';
													}
												}
												?>
											</form>
										</div>
									</div>
								</ul>
							</li>
						<?php
						}else{
							?>
							<li>
								<a style="padding-right:0">
									<?php echo '( ' . ($offset+ ( ($res_count > 0)? 1 : 0)) . '-' . ($offset+$res_count) . ' )&nbsp; |&nbsp; '.$total_count . ' Total' ?>
								</a>
							</li>
						<?php
						}
						?>
					</ul>

				</div>


				<?php
				if( $filter_enabled ){
					?>
					<div class="collapse navbar-collapse navbar-right" style="padding-right:0; padding-left:5px">

						<ul class="nav navbar-nav navbar-left">
							<li style="border-left:1px solid #ddd"><a style="padding-right:5px"><strong>Filter:</strong></a></li>
						</ul>

						<?php
						if( array_key_exists( 'tag', $features ) ){
							?>
							<ul class="nav navbar-nav navbar-left">
								<li>
									<a style="padding-right:0">
										<span class="glyphicon glyphicon-tag" aria-hidden="true"></span>
										<strong><?php echo $features['tag']['translation'] ?>:</strong>
									</a>
								</li>
							</ul>

							<ul class="nav navbar-nav">
								<li class="dropdown">

									<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="color:#337ab7" id="tag_selector_dropdown">
										choose...
										<span class="caret"></span>
									</a>

									<?php
									$tags4 = array();
									//
									for( $i = 0; $i < min(4, sizeof($features['tag']['values'])); $i++){
										$tags4[$i] = $features['tag']['values'][$i];
									}
									?>

									<ul class="dropdown-menu" role="menu">
										<?php
										if( sizeof($tags4) > 0 ){
											for( $i = 0; $i < sizeof($tags4); $i++ ){
												?>
												<li><a href="<?php echo $querystrings['tag'].'tag='.$tags4[$i] ?>"><?php echo ucfirst($tags4[$i]) ?></a></li>
											<?php
											}
											if( sizeof($features['tag']['values']) > 4 ){
												?>
												<li class="divider"></li>
												<li><a href="#" data-toggle="modal" class="tags_modal_button" data-target="#table-viewer-tag-selector-modal-<?php echo $table_viewer_unique_id ?>">Show all...</a></li>
											<?php
											}
										}else{
											echo '<a class="text-center">Nothing</a>';
										}
										?>
									</ul>

								</li>
							</ul>
						<?php
						}

						if( array_key_exists( 'keywords', $features ) ){
							?>
							<ul class="nav navbar-nav navbar-left">
								<li>
									<a style="padding-right:0">
										<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
										<strong>Search:</strong>
									</a>
								</li>
							</ul>

							<form class="navbar-form navbar-left" role="search" method="get" action="<?php echo \system\classes\Configuration::$BASE . $baseurl ?>" style="padding-right:10px">
								<?php
								?>
								<div class="form-group">
									<?php
									foreach( $filtered_features['keywords'] as $param ){
										if( isset($features['_valid'][$param]) ){
											echo '<input type="hidden" name="'.$param.'" value="'.$features_values[$param].'"></input>';
										}
									}
									?>
									<input type="text" class="form-control" placeholder="<?php echo $features['keywords']['placeholder'] ?>" name="keywords" style="width:160px" id="keywords_input">
								</div>
								<button type="submit" class="btn btn-default" style="margin-left:10px">Go</button>
							</form>

						<?php
						}
						?>

					</div>
				<?php
				}
				?>

			</div>
		</nav>

		<div class="col-md-12" style="border-bottom:1px solid #efefef">
			<table style="float:right">
				<tr>
					<td>
						<?php
						if( $filter_in_use ){
							$tag_queryString = $querystrings['tag'];
							$keywords_queryString = $querystrings['keywords'];
							//
							echo 'Active filters:'.
								( ($tagfilter_in_use)? '&nbsp; <span class="glyphicon glyphicon-tag" aria-hidden="true"></span><a href="#"> '.ucfirst($features_values['tag']).'</a> (<a href="'.$tag_queryString.'" style="color:red"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>)' : '' ) .
								( ($tagfilter_in_use && $keywordsfilter_in_use)? ' , &nbsp;' : '&nbsp; '  ) .
								( ($keywordsfilter_in_use)? '<span class="glyphicon glyphicon-search" aria-hidden="true"></span><a href="#"> "'.$features_values['keywords'].'"</a> (<a href="'.$keywords_queryString.'" style="color:red"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>)' : '' );
						}
						echo ( ($order_enabled)? ( (($filter_in_use)? ', S' : 'Results s' ) . 'orted by:' ) : '' );
						?>
					</td>
					<?php
					if($order_enabled){
						?>
						<td>
							<div class="dropdown" style="margin-left:3px">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#" id="ordering_options_dropdown">
									<?php echo $features['order']['details'][$features_values['order']]['translation'] ?>
									<span class="caret"></span>
								</a>

								<ul class="dropdown-menu"  role="menu">
									<?php
									foreach( $features['order']['values'] as $ord ){
										echo '<li role="presentation"><a role="menuitem" tabindex="-1" href="'.$querystrings['order'].'order='.$ord.'">'.$features['order']['details'][$ord]['translation'].'</a></li>';
									}
									?>
								</ul>
							</div>
						</td>
					<?php
					}
					?>
				</tr>
			</table>
		</div>
		<!-- === End Results Bar =================================================================================== -->


		<br/>


		<!-- === Begin Results Table + Paginator =================================================================== -->

		<?php
		if( $res_count > 0 ){
			// enable the features
			$counter_column_enabled = in_array( '_counter_column', $table['features'] );
			$actions_column_enabled = in_array( '_actions_column', $table['features'] ) && isset($table['actions']);
			?>

			<table class="table <?php echo $table['style'] ?>">
				<thead>
				<tr>
					<?php
					if( $counter_column_enabled ){
						?>
						<th class="col-md-1 text-center">#</th>
					<?php
					}
					//
					foreach( $table['layout'] as $key => $column ){
						if( !$column['show'] ) continue;
						?>
						<th class="col-<?php echo $column['width']; ?> text-<?php echo $column['align']; ?>"><?php echo $column['translation'] ?></th>
					<?php
					}
					//
					if( $actions_column_enabled ){
						?>
						<th class="col-<?php echo $table['actions']['_width'] ?> text-center">Actions</th>
					<?php
					}
					?>
				</tr>
				</thead>

				<tbody id="list-body">
				<?php

				for( $i = 0; $i < $res_count; $i++ ){
					$record_raw = $data[$i];
					// filter the record
					$record = array_intersect_key( $record_raw, array_flip(array_keys($table['layout'])) );
					?>

					<tr>

						<?php
						if( $counter_column_enabled ){
							?>
							<td class="text-center">
								<?php echo ($i+1); ?>
							</td>
						<?php
						}
						//
						foreach( $table['layout'] as $key => $column ){
							if( !$column['show'] ) continue;
							?>
							<td class="text-<?php echo $column['align']; ?>">
								<?php
								$red_color = ( isset($column['red-color-limit']) && $record[$key] >= $column['red-color-limit'] );
								if( $red_color ){
									echo '<span style="font-weight:bold; color:orangered">';
								}
								//
								if( isset($column['meaning']) ){
									echo $column['meaning'][$record[$key]];
								}else{
									echo format( $record[$key], $column['type'] );
								}
								//
								if( $red_color ){
									echo '</span>';
								}
								?>
							</td>
						<?php
						}
						//
						if( $actions_column_enabled ){
							?>
							<td class="text-center">
								<div style="margin:auto">
									<?php
									foreach( $table['actions'] as $action ){
										if( is_string($action) ) continue;
										if( $action['type'] == 'separator' ){
											?>
											<span>&nbsp;|&nbsp;</span>
											<?php
											continue;
										}
										//
										$tooltip_enabled = isset($action['tooltip']);
										$modal_toggle_enabled = ($action['function']['type'] == '_toggle_modal');
										$attach_record = ( isset($action['function']['static_data']['modal-mode']) && $action['function']['static_data']['modal-mode'] === 'edit' );
										$url_data = ( isset($action['function']['API_resource']) && isset($action['function']['API_action']) );
										//
										$toggle_param = ( ($tooltip_enabled)? 'tooltip ' : '' );
										$toggle_param .= ( ($modal_toggle_enabled)? 'dialog' : '' );

										?>
										<button
											class="btn btn-<?php echo $action['type'] ?> <?php echo ( (isset($action['condition']))? ( (!in_array($record[$action['condition']['field']], $action['condition']['values']))? 'disabled' : '' ) : '' ) ?>"
											type="button"
											<?php echo ( ($tooltip_enabled || $modal_toggle_enabled)? 'data-toggle="'.$toggle_param.'" ' : '' ) ?>
											<?php echo ( ($tooltip_enabled)? ' data-placement="bottom" title="'.$action['tooltip'].'" ' : '' ) ?>
											<?php
											foreach( $action['function']['arguments'] as $argument ){
												echo ' data-'.$argument.'="'.$record[$argument].'" ';
											}
											if( isset($action['function']['static_data']) && is_array($action['function']['static_data']) ){
												foreach( $action['function']['static_data'] as $argument => $value ){
													echo ' data-'.$argument.'="'.$value.'" ';
												}
											}
											if( $modal_toggle_enabled ){
												if( $action['function']['class'] == 'yes-no-modal' ){
													echo ' data-target="#yes-no-modal" ';
												}elseif( $action['function']['class'] == 'record-editor-modal' ){
													echo ' data-target="#record-editor-modal-'.$formID.'" ';
												}else{
													echo ' data-target="#'.$action['function']['class'].'" ';
												}
											}
											if( $url_data ){
												echo ' data-url="'.Configuration::$BASE_URL.'web-api/'.Configuration::$WEBAPI_VERSION.'/'.$action['function']['API_resource'].'/'.$action['function']['API_action'].'/json?token='.$_SESSION['TOKEN'].'&'.toQueryString( $action['function']['arguments'], $record ).'&'.toQueryString( array_keys($action['function']['arguments_override']), $action['function']['arguments_override'] ).'" ';
											}
											if( $attach_record ){
												echo ' data-record=\''. json_encode($record_raw, JSON_HEX_APOS) .'\' ';
											}
											//
											if( isset($action['function']['custom_html']) ){
												echo $action['function']['custom_html'];
											}
											?>
											style="
											<?php
											if( isset($action['text']) ){
												echo 'height: 28px; padding-top: 3px; ';
											}
											//
											if( isset($action['condition']) && !in_array($record[$action['condition']['field']], $action['condition']['values']) ){
												echo "background-image:none; background-color:rgb(189, 188, 188); border:1px solid; ";
											}
											?>
												"
											>
											<span
												class="glyphicon glyphicon-<?php echo $action['glyphicon'] ?>"
												aria-hidden="true"
												<?php echo ( (isset($action['color']))? 'style="color:'.$action['color'].'"' : '' ) ?>
												>
											</span>
											<?php echo ( (isset($action['text']))? '&nbsp;'.$action['text'] : '' ) ?>
										</button>
									<?php
									}
									?>
								</div>
							</td>
						<?php
						}
						?>

					</tr>

				<?php
				}

				?>
				</tbody>

			</table>

			<br/>

			<?php
			if( $pagination ){
				?>
				<nav class="text-center">
					<ul class="pagination">

						<?php
						buildPaginator( $querystrings['page'], $available_pages, $current_page );
						?>

					</ul>
				</nav>
			<?php
			}
			?>

		<?php
		}else{
			?>
			<br/>
			<h3 class="text-center">No Results!</h3>
			<br/>
		<?php
		}
		?>

		</div>

		<!-- === End Results Table + Paginator ===================================================================== -->




		<?php

		if( array_key_exists( 'tag', $features ) && sizeof($features['tag']['values']) > 4 ){
			// Tag selector modal
			?>

			<!-- Tags Modal Dialog -->
			<div class="modal fade" id="table-viewer-tag-selector-modal-<?php echo $table_viewer_unique_id ?>" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title">Choose <?php echo $features['tag']['translation'] ?></h4>
						</div>


						<form method="get" action="<?php echo \system\classes\Configuration::$BASE . $baseurl ?>">

							<div class="modal-body">

								<table style="width:100%">
									<tr>

										<td style="width:45%">
											<div class="checkboxes-div" style="padding-left:25px; height:200px; overflow:auto">
												<?php

												$tags = $features['tag']['values'];

												for( $i = 0; $i < sizeof($tags); $i++ ){
													?>
													<div class="radio">
														<label>
															<input type="radio" name="tag" id="<?php echo $tags[$i] ?>" value="<?php echo $tags[$i] ?>" <?php echo ( (isset($_GET['tag']) && strcasecmp($_GET['tag'], $tags[$i]) == 0 )? 'checked' : '' ) ?>>
															<?php echo ucfirst($tags[$i]) ?>
														</label>
													</div>
												<?php
												}
												?>
											</div>
										</td>

										<td style="width:5%"></td>
										<td style="border-left:1px solid #ddd; width:5%"></td>

										<td style="width:40%">
											<div style="vertical-align:top;">
												<form role="form">
													<div class="form-group">
														<label>Tags available:</label>
														<div class="input-group">
															<div class="input-group-addon">Filter: </div>
															<input type="text" class="form-control" id="tag-filter-input" placeholder="">
														</div>
													</div>
												</form>
											</div>
										</td>

										<td style="width:5%"></td>

									</tr>
								</table>

							</div>

							<?php
							foreach( $filtered_features['tag'] as $param ){
								if( isset($features['_valid'][$param]) ){
									echo '<input type="hidden" name="'.$param.'" value="'.$features_values[$param].'"></input>';
								}
							}
							?>

							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-primary">Apply</button>
							</div>

						</form>

					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal -->



			<script type="text/javascript">

				$('#table-viewer-tag-selector-modal-<?php echo $table_viewer_unique_id ?> #tag-filter-input').on("keyup", function () {
					var keywords = $(this).val().toLowerCase();
					filterCheckboxList( "table-viewer-tag-selector-modal-<?php echo $table_viewer_unique_id ?>", null , keywords );
				});

			</script>



		<?php
		}

	}

}


?>
