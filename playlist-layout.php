<?php
/**
 * playlist generated from json configuration data
 */
if( empty( get_option("playlist_config") ) ){ return; }
$playlist_json = json_decode(get_option("playlist_config"));
/**
 * playlist generated from access log data
 */
$top_10_json = json_decode(_accesslog::accesslog_activity_get(), true);
usort($top_10_json, function($a, $b) {
	if( $b["count"] > $a["count"] ) return 1;
	if( $b["count"] < $a["count"] ) return -1;
	return ($b["time"] < $a["time"]) ? -1 : 1;
});
$top_10_json = array_slice($top_10_json,0,10);
?>

<div id="<?php echo $this->plugin_slug ?>">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-box box-background">
				<div class="panel-heading">
					<h1 class="title" title="click for more information"></h1>
				</div>
				<div class="panel-body">				
					<div class="cover" style="background-image: url('<?php echo esc_attr( get_option('default_album_cover') ); ?>'); background-size: 100%; opacity: 0.8; cursor: pointer;"">
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
		foreach($playlist_json as $p) { 

echo <<<EOF

		<li class="nav-item">
			<a class="nav-link" id="tab-{$p->id}" data-toggle="tab" href="#{$p->id}" role="tab" aria-controls="{$p->id}" aria-selected="true">
				<h3>{$p->title}</h3> 
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
		foreach($playlist_json as $pj) { 

echo <<<EOF
		<div class="tab-pane" id="{$pj->id}" role="tabpanel" aria-labelledby="tab-{$pj->id}">
EOF;
			$playlist = json_decode( $this->_utilities_playlist( $pj ) );

			foreach($playlist as $p) { 

echo <<<EOF
			<div  id="{$pj->id}-{$p->ID}" class="{$pj->id}-track dmck-audio-playlist-track" audiourl="{$p->mp3}" cover="{$p->cover}" artist="{$p->artist}" title="{$p->title}" permalink="{$p->permalink}" wavformpng="{$p->wavformpng}">
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

		<div class="tab-pane" id="top-10" role="tabpanel" aria-labelledby="top-10-tab">
		<script>let top_10_json = <?php echo json_encode($top_10_json) ?></script>		
		<table class="table table-responsive-lg top-requests-data"><thead><tr><th>Track</th><th class="text-center">Requests</th></tr></thead><tbody>

<?php	

		foreach($top_10_json as $t) { 

			$param = (object) array('s' => $t["name"]);
			$p = json_decode($this->_utilities_playlist( $param ));

			// var_dump($t);

			$title = !empty($p[0]->title) ? $p[0]->title : urldecode($t["name"]);
			$date = date('m/d/Y h:i:s a', $t["time"]);

echo <<<EOF

			<tr id="top-10-{$p[0]->ID}"
				class="top-10-track" 
				audiourl="{$p[0]->mp3}" 
				cover="{$p[0]->cover}" 
				artist="{$p[0]->artist}" 
				title="{$p[0]->title}"
				permalink="{$p[0]->permalink}" 
				wavformpng="{$p[0]->wavformpng}"
				style="color:">
				<td>
					<h5>$title</h5>
					<span class="">
						{$p[0]->tags} {$p[0]->moreinfo}
					</span>
				</td>
				<td class="text-center" title="$date">
					<h1 class="dmck_top10_count">{$t["count"]}</h1>
				</td> 
			</tr>

EOF;

		}		
?>		
		</tbody></table> 
		</div>  
	</div>
</div>


