<?php
require_once(plugin_dir_path(__FILE__)."playlist-layout-functions.php");
$playlist_data = $this->playlist_data_get(); 
?>

<div id="<?php echo self::PLUGIN_SLUG ?>">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-box box-background <?php if (0 == get_option('audio_control_enabled')) echo "hidden"; ?>">
				<div class="panel-heading">
					<h1 class="title" title="click for more information"></h1>
				</div>
				<div class="panel-body" style="height:<?php echo esc_attr( get_option('audio_control_slider_height') ); ?>px">				
					<div class="cover" style="background-image: url('<?php echo esc_attr( get_option('audio_control_slider_height') ); ?>');">
						<div class="h-100">
							<div class="volume" style="display:none"></div>						
							<div class="duration h-100">							
								<h2 class="artist"></h2>	
								<div class="tracktime"> 0 / 0</div>					
							</div>						
						</div>
					</div>
				</div>
				<div class="panel-heading options">
					<div class="controls row ">
						<div class="play fas fa-play-circle fa-3x col-xs-3"  aria-hidden="true" title="Play"></div>
						<div class="pause fas fa-stop-circle fa-3x col-xs-3 hidden"  aria-hidden="true" title="Pause"></div>	
						<div class="rew fas fa-step-backward fa-3x col-xs-3" aria-hidden="true" title="Back"></div>
						<div class="fwd fas fa-step-forward fa-3x col-xs-3"  aria-hidden="true" title="Forward"></div>
					</div> 
				</div>			
			</div>
		</div>
	</div>

	<canvas id="canvas_visualizer"  style="display:none"></canvas>
	
	<ul class="nav nav-tabs" id="info-tabs" role="tablist">
<?php 
		foreach($playlist_data["playlist_json"] as $p) { 
			if(isset($p->id) && $p->title) {
				nav_item($p);
			}else
			if(isset($p->topten) && filter_var($p->topten, FILTER_VALIDATE_BOOLEAN)){
				nav_item_topten();
			}			
		}
		
?>
	</ul>

	<div class="tab-content">
<?php
		foreach($playlist_data["playlist_json"] as $p) { 
			if(isset($p->id) && $p->title) {
				nav_pane($this, $p);
			}else
			if(isset($p->topten) && filter_var($p->topten, FILTER_VALIDATE_BOOLEAN)){
				nav_pane_topten($playlist_data);
			}
		}
		?>
	</div>
</div>

