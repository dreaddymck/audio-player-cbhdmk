<?php
/*
    Manage acces_log activities.
    Command line support for cron calls.
*/
error_reporting(E_ALL);
ini_set("display_errors","On");
try{
    require_once dirname(__FILE__) . '/../../../../wp-load.php';
    require_once dirname(__FILE__) . "/../playlist_utilities_class.php";
}
catch (Exception $e) { exit($e); }

class dreaddymck_com_accesslog extends playlist_utilities_class{

    public $debug;
    public $options;
    public $accesslog;

    function __construct() {
        if($this::parameters()) {
            exit($this::run());
        }
    }
    function parameters()
    {
        if( isset( $_SERVER['REQUEST_METHOD'] ) ){
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->debug			= isset($_POST["debug"]) ? htmlspecialchars($_POST["debug"] ) : false;
                $this->options			= isset($_POST["options"]) ? htmlspecialchars($_POST["options"] ) : "";
                $this->accesslog		= isset($_POST["accesslog"]) ? htmlspecialchars($_POST["accesslog"] ) : "";
            }else{
                $this->debug			= isset($_GET["debug"]) ? htmlspecialchars($_GET["debug"] ) : false;
                $this->options			= isset($_GET["options"]) ? htmlspecialchars($_GET["options"] ) : "";
                $this->accesslog		= isset($_GET["accesslog"]) ? htmlspecialchars($_GET["accesslog"] ) : "";
            }
            if( get_option('access_log') ){ 
                $this->accesslog = get_option('access_log');
            }
            return true;
        }else
        if( isset( $_SERVER['argv'] ) ){
            $this->options = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : "";
            $this->accesslog = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : "" ;
            if( get_option('access_log') ){ 
                $this->accesslog = get_option('access_log');
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
            case "get-today":
                $response = $this->accesslog_activity_get_today();
                break;                
            case "purge":
                $response = $this->accesslog_activity_purge();
                break;  
            case "wavform":
                $response = $this->wavform();
                break;                                
            default:
        }
        return $response;
    }
}

new dreaddymck_com_accesslog;

