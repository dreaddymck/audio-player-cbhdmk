<?php
$access_log_activity = array();
$chart_array = array();
$chart_title_array = array();
$activity="";
if( get_option('charts_enabled') ){
    $access_log_activity = $this->media_activity_today();
    if(is_array($access_log_activity)){
        $activity = json_encode($access_log_activity, JSON_PRETTY_PRINT);
        foreach($access_log_activity as $a){
            $response = $this->chart_data_obj($a["ID"],12);
            array_push($chart_array, $response);
            $chart_title_array = array_unique(array_merge($chart_title_array, $response->labels));							
        }
        usort($chart_title_array, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });								
    }
}
?>
<script>
dmck_chart_object['admin-charts'] = {
    labels: <?php echo json_encode($chart_title_array) ?>,
    datasets: <?php echo json_encode($chart_array) ?>
};												
let top_10_json = {
    data : <?php echo ($activity ? $activity :"[]") ?>,
}
</script>

<div class="pure-g">
    <div class="pure-u-1 pure-u-md-1-3">
        <div class="pure-padding-box">
<?php if ($playlist_config_selection) : ?>
    <label for="playlist_stats_selection">Playlists</label>    
<select name="playlist_stats_selection" size="4" title="Playlist selection">    
    <?php if (1 == get_option('playlist_top_media')) { ?>        
        <option value='top-media-requests' draggable=true ><?php echo $playlist_top_media_title ?></option>        
    <?php  } ?> 
    <?php echo $playlist_config_selection ?>
</select>
<?php endif; ?>
        </div>
    </div>
    <div class="pure-u-1 pure-u-md-1-3">
        <div class="pure-padding-box">
            <label for="select_posts_in">Select Posts</label>
            <select name='select_posts_in' multiple onchange='' title="Post selection">
                <?php echo playlist_config_options($post__in, array(), "id(array)") ?>
            </select>
        </div>
    </div>
    <div class="pure-u-1 pure-u-md-1-3">
        <div class="pure-padding-box">
        <label for="playlist_stats_selection">Options</label> 
            <div class="pure-g">
                <div class="pure-u-1-2">                
                    <input type="date" name="post_in_date_from" id="post_in_date_from" title="From date">
                </div>         
                <div class="pure-u-1-2">                
                    <input type="date" name="post_in_date_to" id="post_in_date_to" title="To date">
                </div>  
                <div class="pure-u-1-1">                
                    <input type="text" name="post_in_stats" id="post_in_stats"  class="pure-input-1" value="" title="Select posts"/>
                </div>                                           
            </div>   
        </div>
    </div>         
</div>

<div id="admin-charts"></div>
(<i><small>Double-click input to expand</small></i>)
<textarea 
    class="pure-input-1 rounded-0" 
    rows="5"
    ondblclick="this.style.height = '';this.style.height = (this.scrollHeight + 12) + 'px'"><?php echo $activity ?></textarea>
<label><?php echo "Total: ".sizeof($access_log_activity) ?><label>