<?php
/*
    Manage acces_log activities.
    Command line support for cron calls.
*/

try{
    require_once dirname(__FILE__) . "/../../../../wp-load.php";
    require_once(dirname(__FILE__) . "/../trait/access-logs.php");
	require_once(dirname(__FILE__) . "/../trait/wavform.php");
    require_once(dirname(__FILE__) . "/../trait/utilities.php");   
    require_once(dirname(__FILE__) . "/../trait/requests.php"); 
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
        if($this::parameters()) {
            exit($this::run());
        }
    }
    function parameters()
    {
        if( !empty( $_SERVER["REQUEST_METHOD"] ) ){
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $this->debug			= !empty($_POST["debug"]) ? htmlspecialchars($_POST["debug"] ) : false;
                $this->options			= !empty($_POST["options"]) ? htmlspecialchars($_POST["options"] ) : "";
                $this->filepath		    = !empty($_POST["accesslog"]) ? htmlspecialchars($_POST["accesslog"] ) : "";
                $this->filename		    = !empty($_POST["filename"]) ? htmlspecialchars($_POST["filename"] ) : "";
            }else{
                $this->debug			= !empty($_GET["debug"]) ? htmlspecialchars($_GET["debug"] ) : false;
                $this->options			= !empty($_GET["options"]) ? htmlspecialchars($_GET["options"] ) : "";
                $this->filepath		    = !empty($_GET["accesslog"]) ? htmlspecialchars($_GET["accesslog"] ) : "";
                $this->filename		    = !empty($_GET["filename"]) ? htmlspecialchars($_GET["filename"] ) : "";
            }
            if( get_option("access_log") ){ 
                $this->filepath = get_option("access_log");
            }
            return true;
        }else
        if( !empty( $_SERVER["argv"] ) ){
            $this->options = !empty($_SERVER["argv"][1]) ? $_SERVER["argv"][1] : "";
            $this->filepath = !empty($_SERVER["argv"][2]) ? $_SERVER["argv"][2] : "" ;
            $this->filename = !empty($_SERVER["argv"][3]) ? $_SERVER["argv"][3] : "" ;
            if( get_option("access_log") ){ 
                $this->filepath = get_option("access_log");
            }
            return true;
        }                    
        return false;
    }
    function run(){
        $response = "{}";
        switch ($this->options) {
            case "put":
                $this->accesslog_activity_purge();
                $response = $this->accesslog_activity_put();
                break;
            case "get":
                $response = $this->accesslog_activity_get();
                break;
            case "get-week":
                $response = $this->accesslog_activity_get_week();
                break;    
            case "get-month":
                $response = $this->accesslog_activity_get_month();
                break;                                
            case "purge":
                $response = $this->accesslog_activity_purge();
                break;
            case "playlist-create":
                $response = $this->playlist_create();
                break;
            case "wavform":
                $response = _wavform::wavform();        
                break;                                
            default:
        }
        return $response;
    }
}
new dmck_reports();

