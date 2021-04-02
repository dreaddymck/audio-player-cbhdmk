<?php
/*
Migrates access log activity content to the latest version.
option:         Arg[1] - \"migrate\"
value months:   Arg[2] - null value or number > 0
drop tables:    Arg[3] - null value or 1
debug:          Arg[4] - null value or 1
NOTICE: if plugin is symlinked, then require '.../wp-load.php' path MUST modified to reflect sandbox being migrated

*/

namespace DMCK_WP_MEDIA_PLUGIN;
try{
    require_once(preg_replace('/wp-content.*$/','',__DIR__)."/wp-load.php");
    require_once(dirname(__FILE__) . "/trait/access-logs.php");
	require_once(dirname(__FILE__) . "/trait/wavform.php");
    require_once(dirname(__FILE__) . "/trait/utilities.php");
    require_once(dirname(__FILE__) . "/trait/requests.php");
    require_once(dirname(__FILE__) . "/trait/tables.php");
}
catch (Exception $e) { exit($e); }

ini_set("memory_limit","512M");

class dmck_reports_migrate{

    use _accesslog;
    use _utilities;
    use _requests;
    use _tables;

    public $response;

    function __construct() {
        $this->setTimezone();

        if ( isset($_SERVER['REQUEST_METHOD'] )) { exit( header("Location: ".get_bloginfo('url')) ); }
        if($this::parameters()) {
            switch ($this->option) {
                case "migrate":
                    $table_exists = $this->table_exists();
                    if(!empty($table_exists)){
                        $this->table();                    
                        $this->migrate();
                        $this->response = "\r\nFinished\r\n";
                    }else{
                        $this->response = "\r\n". DB_NAME . ".dmck_audio_log_reports does not exist. Exiting\r\n";
                    }
                    break;
                default:
            }
            
        }else{
            $this->response = "
Migration script for ". DB_NAME . ".(dmck_media_activity_log|dmck_media_activity_referer_log). 
Plugin directory: ".ABSPATH."
Arguments below subject to change.
option:         Arg[1] - \"migrate\"
value months:   Arg[2] - null value or number > 0
drop tables:    Arg[3] - null value or 1
debug:          Arg[4] - null value or 1
NOTICE: if plugin is symlinked, then require '.../wp-load.php' path MUST modified to reflect sandbox being migrated
";
        }
        exit($this->response);
    }
    function parameters()
    {
        if( !empty( $_SERVER["argv"] ) ){
            $this->option  = !empty($_SERVER["argv"][1]) ? $_SERVER["argv"][1] : "";
            $this->value  = !empty($_SERVER["argv"][2]) ? $_SERVER["argv"][2] : "";
            $this->flag  = !empty($_SERVER["argv"][3]) ? $_SERVER["argv"][3] : "";
            $this->debug  = !empty($_SERVER["argv"][4]) ? $_SERVER["argv"][4] : "";
            if( !empty($this->option) && !empty($this->value) ){ return true; }
        }
        return false;
    }    
    function table_exists(){ return $this->query("SHOW TABLES LIKE 'dmck_audio_log_reports'"); }
    function table(){

        if($this->flag){ 
            echo "Dropping Migration Tables If Exists\r\n";
            $this->_tables_drop();
        }        
        echo "Creating Migration Tables If Not Exist\r\n";
        $this->_tables_dmck_media();
    }
    function migrate(){

        $elements = json_decode( $this->obj_request((object) array()) );
        $x=0;
        $size=sizeof($elements);

        echo "Attempting to Migrate DMCK meta to new table format from $size posts\r\n";

        foreach($elements as $e){
            if($e->mp3){                
                $basename = basename($e->mp3);
                $filename = urldecode($basename);
                $pattern = "(".preg_quote($basename)."|".preg_quote($filename).")";
                $resp = $this->accesslog_activity_get_month( $pattern, $this->value);
                if($resp){
                    $x=$x + 1;
                    foreach($resp as $key=>$value){
                        $json = json_decode($value[0]);
                        foreach($json as $jkey=>$jval){
                            if( preg_match("/".$pattern."/", $jval->name) ){
                                $jval->referer = isset($jval->referer) ? $jval->referer : "";
                                $jval->ID = $e->ID;
                                $results = $this->dmck_media_activity_tables($jval);
                            }
                        }
                    }
                }
            }
            if(!$this->debug){
                $this->progressBar($x, $size);
            }            
        }
        return;
    }


}

new dmck_reports_migrate;
