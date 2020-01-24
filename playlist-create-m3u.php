<?php

try{
    require_once dirname(__FILE__) . "/../../../../wp-config.php";
    require_once(plugin_dir_path(__FILE__)."trait/utilities.php");
}
catch (Exception $e) { exit($e); }


class dmck_create_playlist {

    use _utilities;
    
    public $debug;
    public $option;
    public $value;

    function __construct() {
        $this::parameters();
        $this::run();
        exit;
    }
    function parameters()
    {
        if( isset( $_SERVER['REQUEST_METHOD'] ) ){

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->debug			= isset($_POST["debug"]) ? htmlspecialchars($_POST["debug"] ) : false;
                $this->option			= isset($_POST["option"]) ? htmlspecialchars($_POST["option"] ) : "";
                $this->value		    = isset($_POST["value"]) ? htmlspecialchars($_POST["value"] ) : "";
            }else{
                $this->debug			= isset($_GET["debug"]) ? htmlspecialchars($_GET["debug"] ) : false;
                $this->option			= isset($_GET["option"]) ? htmlspecialchars($_GET["option"] ) : "";
                $this->value			= isset($_GET["value"]) ? htmlspecialchars($_GET["value"] ) : "";
            }
            return true;
        }
        if( isset( $_SERVER['argv'] ) ){
            $this->option = $_SERVER['argv'][1];
            $this->value = $_SERVER['argv'][2];
        }

    }
    function run(){

        switch ($this->option) {
            case "tag":
                $this->tag();
                break;
            case "top10":
                $this->top10();
                break;
            default:
        }

    }
    function tag(){

        $args = array(
            'numberposts' => -1,
            'post_type' => '',
            'tag' => $this->value, // Here is where is being filtered by the tag you want
            'orderby' => 'id',
            'order' => 'DESC'
        );        

        $posts = get_posts( $args );

        $json   = $this->render_elements($posts);
        $obj    = json_decode($json);
        $file   = dirname(__FILE__) . "/../../../../Public/MUSIC/FEATURING/in-playlist.m3u";
        $tmp    = "/tmp/in-playlist.m3u";
        $fp = fopen( $tmp , 'w');
        foreach($obj as $o){
            fwrite($fp, $o->mp3 . PHP_EOL );
        }
        fclose($fp);
        wp_reset_postdata();

        if (!copy($tmp, $file)) {
            echo "failed to copy $tmp to $file...\n";
        }
        return;      
    }
    function top10() {

        $query = <<<EOF
SELECT
    json_unquote(data->'$.*.name') as name,
    json_unquote(data->'$.*.time') as time,
    json_unquote(data->'$.*.count') as count    
FROM 
    dmck_audio_log_reports 
WHERE 
    DATE(`updated`) = CURDATE()    
order by 
    updated 
desc
    LIMIT 1        
EOF;

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); } 

        $resp       = $conn->query($query);
        $results    = array();        

        if( $resp instanceof mysqli_result ){ $results = mysqli_fetch_all($resp); }     

        $conn->close();
                       

        error_log(print_r($results[0],1));


        $names      = json_decode($results[0][0]);
        $time       = json_decode($results[0][1]);
        $rank       = json_decode($results[0][2]);

        $file       = dirname(__FILE__) . "/../../../../Public/MUSIC/FEATURING/top10.m3u";
        $input      = dirname(__FILE__) . "/../../../../Public/MUSIC/FEATURING/play.m3u";
        $tmparry    = array();
        
        $fn = fopen($input,"r");
        $count = 0;
        
        while(! feof($fn))  {
            $line = fgets($fn);            
            foreach( $names as $n ){
                if (strpos($line, $n) !== false) { 
                    $tmparry[ $rank[$count] ] = $line;
                    $count++; 
                }
            }         
        }      
        fclose($fn);    
        
        error_log(print_r($tmparry,1));

        // if($tmp){
        //     $fp = fopen( $file , 'w');
        //     fwrite($fp, $tmp);
        //     fclose($fp); 
        // }
    }    

    function query($sql){
    
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 

        $resp       = $conn->query($sql);
        $results    = array();        

        if( $resp instanceof mysqli_result )
        {
            $results = mysqli_fetch_all($resp);  
        }     
        
        $conn->close();

        return ($results);
    
    }    

}

new dmck_create_playlist();

