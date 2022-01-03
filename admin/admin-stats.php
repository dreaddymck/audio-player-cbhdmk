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
<div id="admin-charts"></div>
(<i><small>Double-click input to expand</small></i>)
<textarea 
    class="pure-input-1 rounded-0" 
    rows="5"
    ondblclick="this.style.height = '';this.style.height = (this.scrollHeight + 12) + 'px'"><?php echo $activity ?></textarea>
<label><?php echo "Total: ".sizeof($access_log_activity) ?><label>