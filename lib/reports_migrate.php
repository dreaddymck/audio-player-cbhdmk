<?php
/*
Migrates access log activity content to the latest version.
Option: migrate
Value(months history) : 12
Flag(drop tables if exist):1 
*/

namespace DMCK_WP_MEDIA_PLUGIN;
try{
    require_once(dirname(__FILE__) . "/../../../../wp-load.php");
    require_once(dirname(__FILE__) . "/trait/access-logs.php");
	require_once(dirname(__FILE__) . "/trait/wavform.php");
    require_once(dirname(__FILE__) . "/trait/utilities.php");
    require_once(dirname(__FILE__) . "/trait/requests.php");
}
catch (Exception $e) { exit($e); }
class dmck_reports_migrate{

    use _accesslog;
    use _utilities;
    use _requests;

    public $response;

    function __construct() {
        if ( isset($_SERVER['REQUEST_METHOD'] )) { exit( header("Location: ".get_bloginfo('url')) ); }
        if($this::parameters()) {
            switch ($this->option) {
                case "migrate":
                    $this->table();                    
                    $this->migrate();
                    $this->response = "\r\nFinished\r\n";
                    break;
                default:
            }
            
        }else{
            $this->response = "Missing required parameters: [1]=option [2]=value\r\n";
        }
        exit($this->response);
    }
    function table(){

        if($this->flag){ 
            echo "Dropping Tables If Exists\r\n";
            $results = $this->query("DROP TABLE IF EXISTS dmck_media_activity_log;");
            $results = $this->query("DROP TABLE IF EXISTS dmck_media_activity_referer_log;");            
        }        
        echo "Creating Tables If Not Exist\r\n";
        $query = <<<EOF
create table IF NOT EXISTS dmck_media_activity_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id INT,
    media text,
    count int,
    time TIMESTAMP,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

EOF;
        $results = $this->query($query);

        $query = <<<EOF
create table IF NOT EXISTS dmck_media_activity_referer_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id INT,
    referer text,
    time TIMESTAMP,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci

EOF;
        $results = $this->query($query);
    }
    function migrate(){

        $elements = json_decode( $this->obj_request((object) array()) );
        $x=0;
        $size=sizeof($elements);

        echo "Migrating $size media items extracted from posts\r\n";

        foreach($elements as $e){
            if($e->mp3){                
                $basename = basename($e->mp3);
                $filename = urldecode($basename);
                $pattern = "(".preg_quote($basename)."|".preg_quote($filename).")";
                $resp = $this->accesslog_activity_get_month( $pattern, $this->value);
                if($resp){
                    foreach($resp as $key=>$value){
                        $json = json_decode($value[0]);
                        foreach($json as $jkey=>$jvalue){
                            if( preg_match("/".$pattern."/", $jvalue->name) ){
                                $jvalue->referer = isset($jvalue->referer) ? $jvalue->referer : "";
                                $results = $this->dmck_media_activity_tables($e, $jvalue);
                            }
                        }
                    }
                }
            }
            $x=$x + 1;
            $this->progressBar($x, $size);
        }
        return;
    }
    function parameters()
    {
        if( !empty( $_SERVER["argv"] ) ){
            $this->option  = !empty($_SERVER["argv"][1]) ? $_SERVER["argv"][1] : "";
            $this->value  = !empty($_SERVER["argv"][2]) ? $_SERVER["argv"][2] : "";
            $this->flag  = !empty($_SERVER["argv"][3]) ? $_SERVER["argv"][3] : "";
            $this->debug  = !empty($_SERVER["argv"][4]) ? $_SERVER["argv"][4] : false;            
            if( !empty($this->option) && !empty($this->value) ){ return true; }
        }
        return false;
    }
    function progressBar($done, $total) {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }    

}

new dmck_reports_migrate;
