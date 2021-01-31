<?php

function nav_item($p){
	
echo <<<EOF

	<li class="nav-item">
		<a class="nav-link" id="tab-{$p->id}" data-toggle="tab" href="#{$p->id}" role="tab" aria-controls="{$p->id}" aria-selected="true">
			<h4>{$p->title}</h4> 
		</a>			
	</li>

EOF;

}
function nav_item_topten(){

echo <<<EOF

	<li class="nav-item">
		<a class="nav-link" id="tab-top-10" data-toggle="tab" href="#top-10" role="tab" aria-controls="top-10" aria-selected="true">
			<h4>Today's Top 10</h4>
		</a>
	</li>

EOF;

}
function nav_pane($obj, $pj){

echo <<<EOF

	<div class="tab-pane" id="{$pj->id}" role="tabpanel" aria-labelledby="tab-{$pj->id}">
	<div id="{$pj->id}-rss"></div>
	<table class="table table-responsive-lg top-requests-data">
	<tbody>	

EOF;
		$resp = $obj->obj_request( $pj );
		$playlist = json_decode( $resp );
		foreach($playlist as $p) { 

echo <<<EOF

		<tr id="{$pj->id}-{$p->ID}" 
			class="{$pj->id}-track dmck-audio-playlist-track" 
			post-id="{$p->ID}"
			audiourl="{$p->mp3}" 
			cover="{$p->cover}" 
			permalink="{$p->permalink}" 
			wavformpng="{$p->wavformpng}"
			title="{$p->title}">
			<td title="{$p->title}\nClick to play">
				<h5 class="">$p->title</h5>
				<span class=""> {$p->tags} {$p->moreinfo} </span>
			</td>	
			<td title="Click for details" class="text-center dmck-row-cover">
				<div style="background-image: url('{$p->cover}')"></div>
			</td>
		</tr>

EOF;

	}

echo "</tbody></table></div>";

}

function nav_pane_topten($playlist_data){

echo <<<EOF

	<div class="tab-pane" id="top-10" role="tabpanel" aria-labelledby="top-10-tab">				
	<table class="table table-responsive-lg top-requests-data">
	<thead>
		<tr>
			<th>Track</th>
			<th class="text-center">Requests</th>
		</tr>
	</thead>
	<tbody>

EOF;
	
$unset_queue = array();

foreach($playlist_data["top_10_json"] as $key => $value) { 
	if( !$value["ID"] ){
		$unset_queue[] = $key;
		continue;
	}

echo <<<EOF

		<tr id="top-10-{$value["ID"]}"
			class="top-10-track dmck-audio-playlist-track" 
			post-id="{$value["ID"]}"
			audiourl="{$value["mp3"]}" 
			cover="{$value["cover"]}" 					
			permalink="{$value["permalink"]}" 
			wavformpng="{$value["wavformpng"]}"
			style="color:"
			title="{$value["title"]}">
			<td title="{$value["title"]}\nClick to play">
				<h5>{$value["title"]}</h5>
				<span class="">{$value["tags"]} {$value["moreinfo"]}</span>
			</td>
			<td class="text-center dmck-row-cover" title="{$value["date"]}">
				<div><h1 class="dmck_top10_count">{$value["count"]}</h1></div>							
			</td> 
		</tr>

EOF;

}		
foreach ( $unset_queue as $index ){ unset($playlist_data["top_10_json"][$index]); }
$playlist_data["top_10_json"] = array_values($playlist_data["top_10_json"]); // rebase the array
echo "</tbody></table>";
if (get_option('charts_enabled')) {
    echo "<script>let top_10_json = ".json_encode($playlist_data["top_10_json"])."</script>"; 
}	
echo "</div>";


}

?>
