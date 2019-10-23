<div class="<?php echo $this->plugin_slug ?>">
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
						<div class="pause fa fa-pause fa-3x col-xs-3"  aria-hidden="true" title="Pause"></div>	
						<div class="rew fa fa-step-backward fa-3x col-xs-3" aria-hidden="true" title="Back"></div>
						<div class="fwd fa fa-step-forward fa-3x col-xs-3"  aria-hidden="true" title="Forward"></div>
					</div> 
				</div>			
			</div>
		</div>
	</div>
	<ul class="nav nav-tabs" id="info-tabs" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" id="tab-playlist" data-toggle="tab" href="#playlist" role="tab" aria-controls="playlist" aria-selected="true">
				<h3>Featured</h3> 
			</a>
		</li>
		<!-- <li class="nav-item">
			<a class="nav-link" id="tab-playlist" data-toggle="tab" href="#rebrixed" role="tab" aria-controls="rebrixed" aria-selected="true">
			<h3>Rebrixed</h3> 
			</a>
		</li>	 -->
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="playlist" role="tabpanel" aria-labelledby="tab-playlist">
	<?php

		$playlist = json_decode($this->fetch_playList_from_posts());
		foreach($playlist as $p) { 

			echo <<<EOF

	<div  id="{$p->ID}" class="ui-li-item featured-track" audiourl="{$p->mp3}" cover="{$p->cover}" artist="{$p->artist}" title="{$p->title}" permalink="{$p->permalink}" wavformpng="{$p->wavformpng}">
		<div class="track-content row">
			<div class="col-lg-10">
				<h5 class="ui-li-title">$p->title</h5>
				<span class="ui-li-tags">
				{$p->tags} <a class="ui-li-moreinfo" title="more info" href="{$p->permalink}" target="_top"> {$p->moreinfo} </a>
				</span>
			</div>
			<div class="col-lg-2 text-center">
				<img class="ui-li-img" src="{$p->cover}" height="100" width="100">
			</div>
		</div>
	</div>		
EOF;

}
	?>	
		</div>
		<!-- <div class="tab-pane" id="rebrixed" role="tabpanel" aria-labelledby="tab-playlist"></div>	 -->
	</div>
</div>


