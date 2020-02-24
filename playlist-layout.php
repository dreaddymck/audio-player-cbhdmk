<?php

if( empty( get_option("playlist_config") ) ){ return; }
$settings = json_decode(get_option("playlist_config"));

?>

<div id="<?php echo $this->plugin_slug ?>">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-box box-background">
				<div class="panel-heading">
					<h1 class="title" title="click for more information"></h1>
				</div>
				<div class="panel-body">				
					<div class="cover">
						<div class="h-100">
							<div class="volume" style="display:none"></div>						
							<div class="duration h-100">							
								<h3 class="artist"></h3>	
								<div class="tracktime"> 0 / 0</div>					
							</div>						
						</div>
					</div>
				</div>
				<div class="panel-heading options">
					<div class="controls row ">
						<div class="play fa fa-play-circle fa-3x col-xs-3"  aria-hidden="true" title="Play"></div>
						<div class="pause fa fa-pause fa-3x col-xs-3 hidden"  aria-hidden="true" title="Pause"></div>	
						<div class="rew fa fa-step-backward fa-3x col-xs-3" aria-hidden="true" title="Back"></div>
						<div class="fwd fa fa-step-forward fa-3x col-xs-3"  aria-hidden="true" title="Forward"></div>
					</div> 
				</div>			
			</div>
		</div>
	</div>
	<ul class="nav nav-tabs" id="info-tabs" role="tablist">

	<?php
		foreach($settings as $s) { 

echo <<<EOF

		<li class="nav-item">
			<a class="nav-link" id="tab-{$s->id}" data-toggle="tab" href="#{$s->id}" role="tab" aria-controls="{$s->id}" aria-selected="true">
				<h3>{$s->title}</h3> 
			</a>
		</li>
EOF;

}
	?>		
		<li class="nav-item">
			<a class="nav-link" id="top-10-tab" data-toggle="tab" href="#top-10" role="tab" aria-controls="top-10" aria-selected="true">
				<h3>Today's Top 10</h3>
			</a>
		</li>		
	</ul>

	<div class="tab-content">

	<?php
		foreach($settings as $s) { 

echo <<<EOF
<div class="tab-pane" id="{$s->id}" role="tabpanel" aria-labelledby="tab-{$s->id}">
EOF;
			$playlist = json_decode( $this->_utilities_shortcode_playlist( $s ) );

			foreach($playlist as $p) { 

echo <<<EOF
<div  id="{$p->ID}" class="{$s->id}-track dmck-audio-playlist-track" audiourl="{$p->mp3}" cover="{$p->cover}" artist="{$p->artist}" title="{$p->title}" permalink="{$p->permalink}" wavformpng="{$p->wavformpng}">
	<div class="track-content row">
		<div class="col-lg-10">
			<h5 class="">$p->title</h5>
			<span class="">
				{$p->tags} {$p->moreinfo}
			</span>
		</div>
		<div class="col-lg-2 text-center">
			<img class="" src="{$p->cover}" height="100" width="100">
		</div>
	</div>
</div>
EOF;

		}

echo <<<EOF
</div>
EOF;

}
	?>

		<div class="tab-pane" id="top-10" role="tabpanel" aria-labelledby="top-10-tab"></div>  
	</div>
</div>


