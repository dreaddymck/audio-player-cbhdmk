<?php
//TODO: get chart total
//TODO: ip / country / track association.
namespace DMCK_WP_MEDIA_PLUGIN;

require_once("playlist-html.php");

trait _data {   

    use DMCK_playlist_html;

    function get_chart_json_default(){
        $borderColor = get_option("chart_rgb");
        $borderColor = get_option("chart_color_array") ? json_decode(get_option("chart_color_array")) : $borderColor;
        return (object) array( 
            "label" => "",  
            "labels" => array(), 
            "data" => array(),
            "borderColor" => $borderColor,
            "cubicInterpolationMode" => "monotone",
            "dmck_request_count" => array(),
            "dmck_request_referrer" => "",
            "post_id" => "",
        );        
    }
    function create_chart_json($resp){
        $chart_json = $this->get_chart_json_default();
        foreach($resp as $key=>$value){
            $json = (object)($value);
            if( $chart_json->post_id != $json->post_id ){ 
                $chart_json->post_id = $json->post_id;
                $post = get_post($json->post_id);
                $chart_json->label = $post->post_title;
                $media_filename_regex = esc_attr( get_option('media_filename_regex') );
                //support admin regex option for track label
                if($media_filename_regex){
                    $chart_json->label = preg_replace($media_filename_regex, '', $chart_json->label);
                } 
                // sum request total object initialization
                if(!isset($chart_json->dmck_request_count[$chart_json->label]) ){
                    $chart_json->dmck_request_count[$chart_json->label] = 0;
                }
                $chart_json->dmck_request_referrer = $json->referer;                
            }
            $json->time = date('d-m-Y', $json->time);
            array_push($chart_json->labels, $json->time);
            array_push($chart_json->data, (object) array(
                "x" => $json->time,
                "y" => $json->count
            ));
            $chart_json->dmck_request_count[$chart_json->label] += $json->count;
        }
        return $chart_json;
    }    
    function get_chart_json($json){
		$chart_json = "";
		$opts_enabled = ( get_option('charts_enabled') ||  get_option('playlist_top_media'));
		if( $opts_enabled ){          
			$resp = $this->dmck_media_activity_between($json);			
            if($resp){ $chart_json = $this->create_chart_json($resp); }
		}
		return $chart_json;
	}
    function get_chart_json_mths($post_id,$mths=1){
		$chart_json = "";
		$opts_enabled = ( get_option('charts_enabled') ||  get_option('playlist_top_media'));
		if( $opts_enabled ){
			$resp = $this->dmck_media_activity_month($post_id,$mths);
			if($resp){ $chart_json = $this->create_chart_json($resp);	}
		}
		return $chart_json;
	}
    function dmck_media_activity_tables($a){

        $a = (object) $a;

        $query = "SELECT id FROM dmck_media_activity_log WHERE LOWER(media) = LOWER('$a->name') AND DATE(time)=DATE(FROM_UNIXTIME($a->time))";
        if($this->debug){error_log($query. " | ".__FUNCTION__. " | ".$this->memory_usage());}
        $results = $this->query($query);
        
        $query = "INSERT INTO dmck_media_activity_log (post_id,media,time,count) VALUES($a->ID,'$a->name',FROM_UNIXTIME($a->time),'$a->count')";
        if(!empty($results)){
            $query = "UPDATE dmck_media_activity_log SET media='{$a->name}', count={$a->count}, time=FROM_UNIXTIME({$a->time}) WHERE id={$results[0][0]}";   
        }
        if($this->debug){error_log($query. " | ".__FUNCTION__. " | ".$this->memory_usage());}
        $this->query( $query );

        $query = "SELECT id FROM dmck_media_activity_referer_log WHERE post_id={$a->ID} AND referer = '{$a->referer}' AND UNIX_TIMESTAMP(time)='{$a->time}'";
        if($this->debug){error_log($query. " | ".__FUNCTION__. " | ".$this->memory_usage());}
        $results = $this->query($query);

        $query = "INSERT INTO dmck_media_activity_referer_log (post_id,referer,time) VALUES ({$a->ID},'{$a->referer}',FROM_UNIXTIME({$a->time}))";
        if(! empty($results)){
            $query = "UPDATE dmck_media_activity_referer_log set referer='{$a->referer}', time=FROM_UNIXTIME({$a->time})) WHERE id={$results[0][0]}";
        }        
        if($this->debug){error_log($query. " | ".__FUNCTION__. " | ".$this->memory_usage());}
        $this->query( $query );

        return $results;
    }
    function dmck_media_activity_today() {

    $query = "
SELECT
    dmck_media_activity_log.post_id,
    dmck_media_activity_log.media as name,
    dmck_media_activity_log.count,
    UNIX_TIMESTAMP(dmck_media_activity_log.time) as time,
    GROUP_CONCAT(dmck_media_activity_referer_log.referer separator ' ,') as referer
FROM
    dmck_media_activity_log
LEFT JOIN
    dmck_media_activity_referer_log  on (dmck_media_activity_log.post_id = dmck_media_activity_referer_log.post_id)
WHERE DATE(dmck_media_activity_log.time) = CURDATE() AND DATE(dmck_media_activity_referer_log.time) = CURDATE()
GROUP BY 1,2,3,4
ORDER BY time ASC
";
        return $this->mysqli_query($query);
    }
    function dmck_media_activity_month($post_id="", $months=1) {

        $filter = "";
        if($post_id){ $filter = " AND dmck_media_activity_log.post_id = $post_id"; }

        $query = "
SELECT
    dmck_media_activity_log.post_id,
    dmck_media_activity_log.media as name,
    dmck_media_activity_log.count,
    UNIX_TIMESTAMP(dmck_media_activity_log.time) as time,
    GROUP_CONCAT(dmck_media_activity_referer_log.referer separator ' ,') as referer
FROM
    dmck_media_activity_log
LEFT JOIN
    dmck_media_activity_referer_log on (dmck_media_activity_log.post_id = dmck_media_activity_referer_log.post_id)
WHERE
    DATE(dmck_media_activity_log.time) >= DATE_SUB(NOW(), INTERVAL $months MONTH) AND
    DATE(dmck_media_activity_referer_log.time) >= DATE_SUB(NOW(), INTERVAL $months MONTH)
    $filter
GROUP BY 1,2,3,4
ORDER BY time ASC
";
        return $this->mysqli_query($query);
    }
    function dmck_media_activity_between($json) {

        $query = "
SELECT
    dmck_media_activity_log.post_id,
    dmck_media_activity_log.media as name,
    dmck_media_activity_log.count,
    UNIX_TIMESTAMP(dmck_media_activity_log.time) as time,
    GROUP_CONCAT(dmck_media_activity_referer_log.referer separator ' ,') as referer
FROM
    dmck_media_activity_log
LEFT JOIN
    dmck_media_activity_referer_log  on (dmck_media_activity_log.post_id = dmck_media_activity_referer_log.post_id)
WHERE 
    dmck_media_activity_log.post_id IN (".$json->value.")
    AND
    ( DATE(dmck_media_activity_log.time) BETWEEN DATE('".$json->from."') AND DATE('".$json->to."'))
GROUP BY 1,2,3,4
ORDER BY time ASC
";
    
        return $this->mysqli_query($query);
    }
}