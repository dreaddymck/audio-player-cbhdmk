<div class="<?php echo self::PLUGIN_SLUG ?>">

	<div class="panel panel-box box-background <?php if (0 == get_option('audio_control_enabled')) echo "hidden"; ?>">
		<div class="heading">
			<h1 class="title" title="click for more information"></h1>
		</div>
		<div class="audio_control_box" style="height:<?php echo esc_attr( get_option('audio_control_slider_height') ); ?>px">				
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
			<div class="pure-g controls" style="text-align: center">
				<div class="pure-u-1-4">
					<div class="play fa fa-play fa-2x"  aria-hidden="true" title="Play"></div>
				</div>
				<div class="pure-u-1-4">
					<div class="pause fa fa-stop fa-2x"  aria-hidden="true" title="Pause"></div>
				</div>
				<div class="pure-u-1-4">
					<div class="rew fa fa-step-backward fa-2x" aria-hidden="true" title="Back"></div>
				</div>
				<div class="pure-u-1-4">
					<div class="fwd fa fa-step-forward fa-2x"  aria-hidden="true" title="Forward"></div>
				</div>
			</div>			
		</div>			
	</div>
<?php 
if(get_option('visualizer_enabled')){?>
	<i id="now-playing" class="fa fa-music fa-2x" style="display:none"></i>
	<canvas id="canvas_visualizer"  style="display:none"></canvas>
<?php
} 
$html_tabs = get_option("playlist_html_tabs");
$html_pane = get_option("playlist_html_pane");
$playlist_data = "";

if( ! $html_tabs || ! $html_pane ){
	$playlist_data = $this->playlist_data_get();
}

?>
	
	<ul class="tabs" id="info-tabs" role="tablist">

<?php

if(!$playlist_data){
	echo $html_tabs;
}else{
	foreach($playlist_data["playlist_json"] as $p) { 
		if(isset($p->id) && $p->title) {
			echo nav_item($p);
		}else
		if(isset($p->top_request) && filter_var($p->top_request, FILTER_VALIDATE_BOOLEAN)){
			echo nav_item_top_request($p);
		}			
	}
}
		
?>

	</ul>
	<ul class="tabs-content">

<?php

if(!$playlist_data){
	echo $html_pane;
}else{
	foreach($playlist_data["playlist_json"] as $p) { 
		if(isset($p->id) && $p->title) {
			nav_pane($this, $p);
		}else
		if(isset($p->top_request) && filter_var($p->top_request, FILTER_VALIDATE_BOOLEAN)){
			nav_pane_top_request($playlist_data, $p->top_title);
		}
	}
}

?>
	</ul>
</div>

