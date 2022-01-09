<?php
/*
    Command line support for cron calls.
*/
namespace DMCK_WP_MEDIA_PLUGIN;
if (!class_exists("dmck_reports")) {
try{
    require_once dirname(__FILE__) . "/../../../../wp-load.php";
    require_once(dirname(__FILE__) . "/trait/data.php");
	require_once(dirname(__FILE__) . "/trait/wavform.php");
    require_once(dirname(__FILE__) . "/trait/utilities.php");   
    require_once(dirname(__FILE__) . "/trait/requests.php"); 
    require_once(dirname(__FILE__) . "/trait/attachments.php"); 
    require_once(dirname(__FILE__) . "/trait/rss.php"); 
}
catch (Exception $e) { exit($e); }
class dmck_reports{
    use _data;
    use _wavform;
    use _utilities;
    use _requests;
    use _attachments;
    use _rss;

    public $debug;
    public $options;
    public $filepath;
    public $filename;
    function __construct() {
        $this->setTimezone();
        if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] ) { exit( header("Location: ".get_bloginfo('url')) ); }        
        if($this::parameters()) {
            if($this->debug){ echo __FUNCTION__. " | ". $this->memory_usage()."\n\r"; } 
            $response = "{}";
            switch ($this->options) {
                case "logs":
                    $response = $this->accesslog_activity_put();
                    $response = $this->_rss_feed_cache(); 
                    break;
                case "stats":
                    $response = $this->stats();
                    break;                    
                case "attach":
                    $response = $this->attachments();
                    break;                    
                case "wavform":
                    $response = $this->wavform();        
                    break; 
                case "feed":
                    $response = $this->_rss_feed_cache();        
                    break;                                                    
                default:
                    $response = "
Supported parameters.
argv[0]: logs|wavform|attach ( commands )
argv[1]: path ( wavform / access_log )
argv[2]: filename ( wavform / access_log custom regex pattern )
argv[3]: 0/1 ( debug )

Commands
--------
logs: parse access logs for charts.
wavform: generate audio wavform.
attach: attach embeded media to post.  
";
            }   
            exit($response);
        }
    }
    function parameters()
    {                
        if( !empty( $_SERVER["argv"] ) ){
            if( get_option("access_log") ){ $this->filepath = get_option("access_log"); }
            $this->options  = !empty($_SERVER["argv"][1]) ? $_SERVER["argv"][1] : "";
            $this->filepath = !empty($_SERVER["argv"][2]) ? $_SERVER["argv"][2] : $this->filepath ;
            $this->filename = !empty($_SERVER["argv"][3]) ? $_SERVER["argv"][3] : "" ;
            $this->debug    = !empty($_SERVER["argv"][4]) ? $_SERVER["argv"][4] : false ;
            return true;
        }                    
        return false;
    }
    function accesslog_activity_put()
    {        
        if($this->debug){ echo __FUNCTION__. " | ". $this->memory_usage()."\n\r"; } 
        update_option("access_logs_message","");      
        if(!get_option('charts_enabled')){return;}
          
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
                if(strcasecmp(basename($e->mp3), $aname) == 0 ){        
                    $arr[$aname]["ID"] = $e->ID;
                    $results = $this->dmck_media_activity_tables( (object) $arr[$aname] );
                }
            }   
        }
        $this->dmck_playlist_content();
        if($this->debug){ echo "finished | ".__FUNCTION__. " | ".$this->memory_usage()."\n\r"; }
        return;
    }

}
new dmck_reports();
}

