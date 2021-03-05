<?php

namespace DMCK_WP_MEDIA_PLUGIN;

trait _accesslog {

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
//     function accesslog_activity_purge(){
//         $query = "
// DELETE FROM
//     dmck_audio_log_reports
// WHERE
//     updated <  DATE_SUB(NOW(), INTERVAL 2 YEAR)
// ";
//         $results = $this->query( $query );
//         return $results;
//     }
    //TODO: depracate
//     function accesslog_activity_get() {

//         $query = "
// SELECT
//     data FROM dmck_audio_log_reports
// WHERE
//     DATE(`updated`) = CURDATE()
// ORDER BY
//     updated DESC
// LIMIT 1
// ";
//         $conn = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
//         if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
//         $resp = $conn->query($query);
//         if( $resp instanceof \mysqli_result ) { $results = mysqli_fetch_assoc($resp); }
//         $conn->close();
//         return $results ? $results["data"] : "";
//     }
    //TODO: depracate
//     function accesslog_activity_get_week($name="",$num=1) {

//         $filter = "";
//         if($name){ $filter = " AND JSON_EXTRACT(data, '$.*.name')  REGEXP'$name'"; }

//         $query = "
// SELECT
//     data FROM dmck_audio_log_reports
// WHERE
//     DATE(`updated`) >= DATE_SUB(NOW(), INTERVAL $num WEEK)
//     $filter
// order by
//     updated ASC
// ";

//         $results = $this->query($query);
//         return $results ? $results : "";
//     }
    //TODO: remove after migration
    function accesslog_activity_get_month($name="", $num=1) {
        $filter = "";
        if($name){ $filter = " AND JSON_EXTRACT(data, '$.*.name') REGEXP'$name' "; }

        $query = "
SELECT
    data FROM dmck_audio_log_reports
WHERE
    DATE(`updated`) >= DATE_SUB(NOW(), INTERVAL $num MONTH)
    $filter
order by
    updated ASC
";
        $results = $this->query($query);
        return $results ? $results : "";
    }
    function accesslog_activity_put()
    {
        if( !file_exists( $this->filepath ) ){ die("Missing access log location"); }
        $access_log_pattern = get_option('access_log_pattern') ? get_option('access_log_pattern') : "";
        $pattern = $access_log_pattern ? $access_log_pattern : "/.mp3/i";
        if($this->debug){
            $pattern = $this->filename  ? $this->filename : $pattern;
            $this->_log("PATTERN: " . $pattern);
        }
        try{
            $handle = fopen($this->filepath,'r');
            if ( !$handle ) { throw new \Exception('File open failed: ' . $this->filepath); }
        }
        catch (\Exception $e) {
            echo 'Caught \Exception: ', $e->getMessage(), "\n";
            return;
        }
        $ignore_ip_json = get_option('ignore_ip_json') ? get_option('ignore_ip_json') : "";
        $ignore_ip_enabled = get_option('ignore_ip_enabled') ? esc_attr( get_option('ignore_ip_enabled') ) : "";

        $arr    = array();
        $results = "";
        $regex = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) "([^"]*)" "([^"]*)"$/';

        try {
            while (!feof($handle)) {
                $dd = fgets($handle);
                preg_match($regex , urldecode($dd), $matches);
                // echo( urldecode($dd) ."\n\r");
                // echo( print_r($matches,1) );
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
                    if($this->debug){ $this->_log($matches); }
                    $name = basename($matches[8]);
                    $time = $matches[4] .":".$matches[5]." ".$matches[6];
                    $time = strtotime( $time );
                    $referer = $matches[1]." ".$matches[2]." ".$matches[3];
                    
                    if( isset( $arr[$name] ) ) {
                        $arr[$name]["count"] += 1;
                        $arr[$name]["time"]  =  $time ? $time : $arr[$name]["time"];
                        $arr[$name]["referer"]  =  $referer;
                    } else {
                        $arr[$name] = array(
                            "count" => 1,
                            "time" => $time,
                            "name" => $name,
                            "referer" => $referer );
                    }
                }
            }

            fclose($handle);
            /*
                Old Method
            */
            if(!empty($arr)){
                $results = $this->dmck_audio_log_report_table($arr);
            }
            /*
                New Method
            */
            foreach($arr as $a){
                $elements = json_decode($this->obj_request( (object) array('s' => $a["name"]) ));
                foreach($elements as $e){
                    if(basename($e->mp3) == $a["name"]){
                        $a["ID"] = $e->ID;
                        $this->dmck_media_activity_tables($a);
                    }
                }
            }
            return json_encode($results);
        }
        catch (\Exception $e) { echo 'Caught \Exception: ', $e->getMessage(), "\n"; }
        return;
    }
    //TODO: depracate after migration
    function dmck_audio_log_report_table($arr){
        $json = json_encode($arr,JSON_FORCE_OBJECT);
        $results = $this->query( "SELECT id FROM dmck_audio_log_reports WHERE DATE(`updated`) = CURDATE()" );
        if(empty($results)){
            $results = $this->query( "INSERT INTO dmck_audio_log_reports (data) VALUES ( '" . $json . "' )" );
        }else{
            $results = $this->query( "UPDATE dmck_audio_log_reports SET data = '" . $json . "' WHERE id=".$results[0][0]  );
        }
        return $results;
    }
    function dmck_media_activity_tables($a){

        $a = (object) $a;

        $query = "SELECT id FROM dmck_media_activity_log WHERE `post_id`=$a->ID AND media = '$a->name' AND DATE(time)=DATE(FROM_UNIXTIME($a->time))";
        if($this->debug){error_log($query);}
        $results = $this->query($query);
        if(empty($results)){
            $query = "INSERT INTO dmck_media_activity_log (post_id,media,time,count) VALUES($a->ID,'$a->name',FROM_UNIXTIME($a->time),'$a->count')";
            if($this->debug){error_log($query);}
            $results = $this->query( $query );
        }else{
            $query = "UPDATE dmck_media_activity_log SET count={$a->count}, time=FROM_UNIXTIME({$a->time}) WHERE id={$results[0][0]}";
            if($this->debug){error_log($query);}
            $results = $this->query( $query );
        }

        $query = "SELECT id FROM dmck_media_activity_referer_log WHERE post_id={$a->ID} AND UNIX_TIMESTAMP(time)='{$a->time}' AND referer='{$a->referer}'";
        if($this->debug){error_log($query);}
        $results = $this->query($query);
        if(empty($results)){
            $query = "INSERT INTO dmck_media_activity_referer_log (post_id,referer,time) VALUES ({$a->ID},'{$a->referer}',FROM_UNIXTIME({$a->time}))";
            if($this->debug){error_log($query);}
            $results = $this->query( $query );
        }
        return $results;
    }
}