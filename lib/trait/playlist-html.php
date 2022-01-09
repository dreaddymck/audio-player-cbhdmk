<?php
namespace DMCK_WP_MEDIA_PLUGIN;
trait DMCK_playlist_html {

    function dmck_playlist_content(){
        $playlist_data = $this->playlist_data_get();
		$playlist_html_tabs = "";
        foreach($playlist_data["playlist_json"] as $p) {
			if(isset($p->id) && isset($p->title)) {
				$playlist_html_tabs .= $this->nav_item($p);
			}else
			if(isset($p->top_request) && filter_var($p->top_request, FILTER_VALIDATE_BOOLEAN)){
				$playlist_html_tabs .= $this->nav_item_top_request($p);
			}
		}
        // NOTE: playlist_html_tabs is registered in admin.php so it can be unregistered
        update_option("playlist_html_tabs",$playlist_html_tabs,true);

        $playlist_html_pane = "";
		foreach($playlist_data["playlist_json"] as $p) {
			if(isset($p->id) && isset($p->title)) {
				$playlist_html_pane .= $this->nav_pane($this, $p);
			}else
			if(isset($p->top_request) && filter_var($p->top_request, FILTER_VALIDATE_BOOLEAN)){
				$playlist_html_pane .= $this->nav_pane_top_request($playlist_data, $p);
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
    function nav_item_top_request($p){
        if(!isset($p->id)){return;}
        return <<<EOF

        <li class="nav-item">
            <a class="nav-link" id="tab-{$p->id}" data-toggle="tab" href="#{$p->id}" role="tab" aria-controls="{$p->id}" aria-selected="true">
                <p>{$p->top_title}</p>
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
        $chart_array = array();
        $chart_title_array = array();        
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
                    <td  class="dmck-row-content" title="{$p->title}\nClick to play">
                        <div>
                            <p class="track-title">$p->title</p>
                            <span class=""> {$p->tags} </span>
                        </div>
                    </td>
                    <td title="Click for details" class="text-center dmck-row-cover">
                        <div style="background-image: url('{$p->cover}')"></div>
                    </td>
                </tr>

EOF;

            if (get_option('charts_enabled')) {
                $response = $this->get_chart_json_mths($p->ID,1);
                if($response){
                    array_push($chart_array, $response);
                    $chart_title_array = array_unique(array_merge($chart_title_array, $response->labels));
                }            
            }
        }

        $html .= "</tbody></table></li>";

        if (get_option('charts_enabled')) {
            usort($chart_title_array, function ($a, $b) {
                return strtotime($a) - strtotime($b);
            });           
            $html .= "
            <script>
            dmck_chart_object['".$pj->id."'] = {
                labels: ".json_encode($chart_title_array).",
                datasets: ".json_encode($chart_array).",
                options: {
                    plugins: {
                        title: {
                            text: \"Past month request history\"
                        }
                    }
                }
            };            
            </script>
";
        }              

        return $html;

    }
    function nav_pane_top_request($playlist_data, $p){
        if(!isset($p->id)){return;}
        $html = <<<EOF

            <div class="tab-pane" id="{$p->id}" role="tabpanel" aria-labelledby="tab-{$p->id}">
            <table class="table table-responsive-lg top-requests-data">
            <thead>
                <tr>
                    <th></th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody>

EOF;

        $unset_queue = array();
        $chart_array = array();
        $chart_title_array = array();

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
                    tags="{$value["tags"]}}
                    title="{$value["title"]}">
                    <td class="dmck-row-content" title="{$value["title"]}\nClick to play">
                        <div>
                            <p class="track-title">{$value["title"]}</p>
                            <span class="">{$value["tags"]} {$value["moreinfo"]}</span>
                        </div>
                    </td>
                    <td class="dmck-row-cover text-center " title="{$value["date"]}">
                        <div><h1 class="top-count">{$value["count"]}</h1></div>
                    </td>
                </tr>

EOF;
            if (get_option('charts_enabled')) {
                $response = $this->get_chart_json_mths($value["ID"],1);
                array_push($chart_array, $response);
                $chart_title_array = array_unique(array_merge($chart_title_array, $response->labels));            
            }
        }
        foreach ( $unset_queue as $index ){ unset($playlist_data["top_10_json"][$index]); }
        $playlist_data["top_10_json"] = array_values($playlist_data["top_10_json"]); // rebase the array
        $html .= "</tbody></table>";       
        
        if (get_option('charts_enabled')) {
            usort($chart_title_array, function ($a, $b) {
                return strtotime($a) - strtotime($b);
            }); 
            $html .= "
            <script>
                dmck_chart_object['".$p->id."'] = {
                    labels: ".json_encode($chart_title_array).",
                    datasets: ".json_encode($chart_array).",
                    options: {
                        plugins: {
                            title: {
                                text: \"Past month request history\"
                            }
                        }
                    }
                };                              
                const top_10_json = {
                    data: ".($playlist_data["top_10_json"] ? json_encode($playlist_data["top_10_json"]) : "[]" ).",
                    title: ".json_encode($p->top_title).",
                    id: '".(isset($p->id) ? $p->id : 0)."',
                }
            </script>
";
        }
        $html .= "</div>";

        return $html;
    }
}