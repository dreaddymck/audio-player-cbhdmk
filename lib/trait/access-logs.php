<?php

namespace DMCK_WP_MEDIA_PLUGIN;

require_once("playlist-html.php");

trait _accesslog {   

    use DMCK_playlist_html;

    public $filepath;

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
    function accesslog_activity_put()
    {
        if($this->debug){ echo __FUNCTION__. " | ". $this->memory_usage()."\n\r"; } 
        update_option("access_logs_message","");        
        // expecting a string: filepath OR json array 
        if(!$this->json_validate($this->filepath)){
            $this->filepath = json_encode(array($this->filepath));          
        }
        $access_log_pattern = get_option('access_log_pattern') ? get_option('access_log_pattern') : "";
        $pattern = $access_log_pattern ? $access_log_pattern : "/.mp3/i";
        if($this->debug){
            $pattern = $this->filename  ? $this->filename : $pattern;
            echo ("PATTERN: " . $pattern."\n\r");
        }   
        $arr    = array();   
        $ignore_ip_json = get_option('ignore_ip_json') ? get_option('ignore_ip_json') : "";
        $ignore_ip_enabled = get_option('ignore_ip_enabled') ? esc_attr( get_option('ignore_ip_enabled') ) : ""; 
        $results = "";
        $regex = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) "([^"]*)" "([^"]*)"$/';         

        foreach(json_decode($this->filepath) as $value) {

            if( !file_exists( $value ) ){
                update_option("access_logs_message","Invalid access log location:".$value);
                continue;
            }         
            try{
                $handle = fopen($value,'r');
                if ( !$handle ) { throw new \Exception('File open failed: ' . $value); }
            }
            catch (\Exception $e) {
                echo 'Caught \Exception: ', $e->getMessage(), "\n";
                return;
            }
            if($this->debug){ echo "file open | ".$value. " | ".__FUNCTION__." | ". $this->memory_usage()."\n\r"; }
            try {
                while (!feof($handle)) {
                    $dd = fgets($handle);
                    preg_match($regex , urldecode($dd), $matches);
                    if(!empty($matches) && preg_match( $pattern , $matches[8] ) ){
                        if( $ignore_ip_enabled && $ignore_ip_json ){
                            $found_ip = false;
                            foreach(json_decode($ignore_ip_json) as $key=>$value) {
                                if( $value->ip == $matches[1] ){
                                    $found_ip = true;
                                    break;
                                }
                            }
                            if($found_ip){ continue; }
                        }
                        if($this->debug){ echo json_encode($matches)."\n"; }
                        $name = basename($matches[8]);
                        $time = $matches[4] .":".$matches[5]." ".$matches[6];
                        $time = strtotime( $time );
                        $referer = $matches[1]." ".$matches[2]." ".$matches[3];

                        /*   Make sure this is todays data, not sure if this is necessary tho.*/
                        $today = new \DateTime("today");
                        $match_date = new \DateTime();;
                        $match_date->setTimestamp($time);
                        $match_date->setTime(0, 0, 0 );
                        $diff = $today->diff( $match_date );  
                        if( (integer)$diff->format( "%R%a" ) != 0 ){ continue; }// Extract days count in interval
                        /* */                    
                        
                        if( isset( $arr[$name] ) ) {                        
                            if( $arr[$name]["time"] == $time && $arr[$name]["referer"] == $referer ){ break; }                        
                            $arr[$name]["count"]    += 1;
                            $arr[$name]["time"]     =  $time;
                            $arr[$name]["referer"]  =  $referer;
                        } else {
                            $arr[$name] = array(
                                "count" => 1,
                                "time" => $time,
                                "name" => $name,
                                "referer" => $referer
                            );
                        }                    
                    }
                }
                fclose($handle); 
            }
            catch (\Exception $e) { echo 'Caught \Exception: ', $e->getMessage(), "\n"; }            
        }

        if($this->debug){ echo "array Length: ". count($arr) ." | ".__FUNCTION__." | ".$this->memory_usage()."\n\r"; }

        foreach($arr as $a){
            $aname = $a["name"];
            $elements = json_decode($this->obj_request( (object) array('s' => $aname) ));
            foreach($elements as $e){
                //TODO Follow up on this bit of comparison this method is slow, find out why the string compare difference
                if(strtolower(basename($e->mp3)) == strtolower($aname) || strcasecmp(basename($e->mp3), $aname) == 0 ){        
                    $arr[$aname]["ID"] = $e->ID;
                    $results = $this->dmck_media_activity_tables( (object) $arr[$aname] );
                }
            }   
        }
        $this->dmck_playlist_html_run();
        if($this->debug){ echo "finished | ".__FUNCTION__. " | ".$this->memory_usage()."\n\r"; }
        return;
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

}