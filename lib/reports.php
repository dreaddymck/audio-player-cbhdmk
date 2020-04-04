<?php
/*
    Manage acces_log activities.
    Command line support for cron calls.
*/

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
        if($this::parameters()) {
            exit( $this->requests($this->options) );
        }
    }
    function parameters()
    {
        if( get_option("access_log") ){ $this->filepath = get_option("access_log"); }        
        if( !empty( $_SERVER["REQUEST_METHOD"] ) ){
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $this->debug			= !empty($_POST["debug"]) ? htmlspecialchars($_POST["debug"] ) : false;
                $this->options			= !empty($_POST["options"]) ? htmlspecialchars($_POST["options"] ) : "";
            }else{
                $this->debug			= !empty($_GET["debug"]) ? htmlspecialchars($_GET["debug"] ) : false;
                $this->options			= !empty($_GET["options"]) ? htmlspecialchars($_GET["options"] ) : "";
            }
            return true;
        }else
        if( !empty( $_SERVER["argv"] ) ){
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

