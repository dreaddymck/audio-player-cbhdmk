<?php
namespace DMCK_WP_MEDIA_PLUGIN;
trait DMCK_playlist_html {

    function dmck_playlist_html_run(){
        $playlist_data = $this->playlist_data_get();
		$playlist_html_tabs = "";
        foreach($playlist_data["playlist_json"] as $p) {
			if(isset($p->id) && $p->title) {
				$playlist_html_tabs .= $this->nav_item($p);
			}else
			if(isset($p->topten) && filter_var($p->topten, FILTER_VALIDATE_BOOLEAN)){
				$playlist_html_tabs .= $this->nav_item_topten();
			}
		}
        // NOTE: playlist_html_tabs is registered in admin.php so it can be unregistered
        update_option("playlist_html_tabs",$playlist_html_tabs,true);

        $playlist_html_pane = "";
		foreach($playlist_data["playlist_json"] as $p) {
			if(isset($p->id) && $p->title) {
				$playlist_html_pane .= $this->nav_pane($this, $p);
			}else
			if(isset($p->topten) && filter_var($p->topten, FILTER_VALIDATE_BOOLEAN)){
				$playlist_html_pane .= $this->nav_pane_topten($playlist_data);
			}
		}
        // NOTE: playlist_html_pane is registered in admin.php so it can be unregistered        
        update_option("playlist_html_pane",$playlist_html_pane,true);
    }
    function nav_item($p){
        return <<<EOF

        <li class="nav-item">
            <a class="nav-link" id="tab-{$p->id}" data-toggle="tab" href="#{$p->id}" role="tab" aria-controls="{$p->id}" aria-selected="true">
                <p>{$p->title}</p>
            </a>
        </li>
EOF;
    }
    function nav_item_topten(){
        return <<<EOF

        <li class="nav-item">
            <a class="nav-link" id="tab-top-10" data-toggle="tab" href="#top-10" role="tab" aria-controls="top-10" aria-selected="true">
                <p>Today's Top 10</p>
            </a>
        </li>

EOF;

    }
    function nav_pane($obj, $pj){

        $html = <<<EOF

            <li class="tab-pane" id="{$pj->id}" role="tabpanel" aria-labelledby="tab-{$pj->id}">
            <div id="{$pj->id}-rss"></div>
            <table class="table top-requests-data">
            <thead>
                <tr>
                    <th></th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody>

EOF;
        $resp = $obj->obj_request( $pj );
        $playlist = json_decode( $resp );
        foreach($playlist as $p) {

            $html .= <<<EOF

                <tr id="{$pj->id}-{$p->ID}"
                    class="{$pj->id}-track dmck-audio-playlist-track"
                    post-id="{$p->ID}"
                    audiourl="{$p->mp3}"
                    cover="{$p->cover}"
                    permalink="{$p->permalink}"
                    wavformpng="{$p->wavformpng}"
                    tags="{$p->tags}"
                    title="{$p->title}">
                    <td title="{$p->title}\nClick to play">
                        <p class="track-title">$p->title</p>
                        <span class=""> {$p->tags} </span>
                    </td>
                    <td title="Click for details" class="text-center dmck-row-cover">
                        <div style="background-image: url('{$p->cover}')"></div>
                    </td>
                </tr>

EOF;

            }

        $html .= "</tbody></table></li>";

        return $html;

    }
    function nav_pane_topten($playlist_data){

        $html = <<<EOF

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
            if( !isset($value["ID"]) || empty($value["ID"]) ){
                $unset_queue[] = $key;
                continue;
            }

            $html .= <<<EOF

                <tr id="top-10-{$value["ID"]}"
                    class="top-10-track dmck-audio-playlist-track"
                    post-id="{$value["ID"]}"
                    audiourl="{$value["mp3"]}"
                    cover="{$value["cover"]}"
                    permalink="{$value["permalink"]}"
                    wavformpng="{$value["wavformpng"]}"
                    style="color:"
                    tags="{$value["tags"]}}
                    title="{$value["title"]}">
                    <td title="{$value["title"]}\nClick to play">
                        <p class="track-title">{$value["title"]}</p>
                        <span class="">{$value["tags"]} {$value["moreinfo"]}</span>
                    </td>
                    <td class="text-center dmck-row-cover" title="{$value["date"]}">
                        <div><h1 class="top-count">{$value["count"]}</h1></div>
                    </td>
                </tr>

EOF;

        }
        foreach ( $unset_queue as $index ){ unset($playlist_data["top_10_json"][$index]); }
        $playlist_data["top_10_json"] = array_values($playlist_data["top_10_json"]); // rebase the array
        $html .= "</tbody></table>";
        if (get_option('charts_enabled')) {
            $html .= "<script>let top_10_json = ".json_encode($playlist_data["top_10_json"])."</script>";
        }
        $html .= "</div>";

        return $html;
    }
}