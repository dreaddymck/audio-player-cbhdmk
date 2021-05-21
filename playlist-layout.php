<?php
require_once(plugin_dir_path(__FILE__)."playlist-layout-functions.php");
$playlist_data = $this->playlist_data_get(); 
?>

<div class="<?php echo self::PLUGIN_SLUG ?>">

	<div class="panel panel-box box-background <?php if (0 == get_option('audio_control_enabled')) echo "hidden"; ?>">
		<div class="heading">
			<h1 class="title" title="click for more information"></h1>
		</div>
		<div class="body" style="height:<?php echo esc_attr( get_option('audio_control_slider_height') ); ?>px">				
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
		<div class="options">
			<div class="controls">
				<div class="play fa fa-play fa-3x"  aria-hidden="true" title="Play"></div>
				<div class="pause fa fa-stop fa-3x"  aria-hidden="true" title="Pause"></div>	
				<div class="rew fa fa-step-backward fa-3x" aria-hidden="true" title="Back"></div>
				<div class="fwd fa fa-step-forward fa-3x"  aria-hidden="true" title="Forward"></div>
			</div> 
		</div>			
	</div>

	<i id="now-playing" class="fa fa-music fa-3x" style="display:none"></i>
	<canvas id="canvas_visualizer"  style="display:none"></canvas>
	
	<ul class="tabs" id="info-tabs" role="tablist">
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

	<ul class="tabs-content">
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
	</ul>
</div>

