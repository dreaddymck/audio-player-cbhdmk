<?php
/*
    Command line support for cron calls.
*/
namespace DMCK_WP_MEDIA_PLUGIN;
try{
    require_once dirname(__FILE__) . "/../../../../wp-load.php";
    require_once(dirname(__FILE__) . "/trait/access-logs.php");
	require_once(dirname(__FILE__) . "/trait/wavform.php");
    require_once(dirname(__FILE__) . "/trait/utilities.php");   
    require_once(dirname(__FILE__) . "/trait/requests.php"); 
}
catch (Exception $e) { exit($e); }
class dmck_reports{
    use _accesslog;
    use _wavform;
    use _utilities;
    use _requests;
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
                case "put":
                    $response = $this->accesslog_activity_put();
                    break;
                case "wavform":
                    $response = $this->wavform();        
                    break;                                
                default:
                    $response = "
Supported parameters.
argv[0]: put|wavform ( commands )
argv[1]: path ( wavform / access_log )
argv[2]: filename ( wavform / access_log custom regex pattern )
argv[3]: true/1 ( debug )
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
}
new dmck_reports();

