<?php

try{
    require_once dirname(__FILE__) . "/../../../../wp-config.php";
}
catch (Exception $e) { exit($e); }


class dreaddymck_com_accesslog {

    public $debug;
    public $options;
    public $accesslog;

    function __construct() {
        $this::parameters();
        exit($this::run());
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
                $this->accesslog			= isset($_GET["accesslog"]) ? htmlspecialchars($_GET["accesslog"] ) : "";
            }
            return true;
        }
        if( isset( $_SERVER['argv'] ) ){
            $this->options = $_SERVER['argv'][1];
            $this->accesslog = $_SERVER['argv'][2];
        }

    }
    function run(){

        $response = "";

        switch ($this->options) {
            case "put":
                $this->purge();
                $response = $this->put();
                break;
            case "get":
                $response = $this->get();
                break;
            case "get-today":
                $response = $this->get_reports_today();
                break;                
            case "purge":
                $response = $this->purge();
                break;                
            default:
        }
        return $response;        

    }
    function purge(){
        
        $query = "DELETE FROM dmck_audio_log_reports where UNIX_TIMESTAMP( updated ) <  UNIX_TIMESTAMP( DATE_SUB(NOW(), INTERVAL 30 DAY) )";

        $results = $this->query( $query );
        
        return;        
    }
    function get() {

        $query = <<<EOF
SELECT 
    data 
FROM 
    dmck_audio_log_reports 
WHERE 
    DATE(`updated`) = CURDATE() 
ORDER BY 
    updated 
DESC 
    LIMIT 1
EOF;
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 

        $resp       = $conn->query($query);

        if( $resp instanceof mysqli_result ) {
            $results = mysqli_fetch_assoc($resp);  
        }     

        $conn->close();

        return $results['data'];

    }
    function get_reports_today() {

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

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 

        $resp       = $conn->query($query);
        $results    = array();        

        if( $resp instanceof mysqli_result )
        {
            $results = mysqli_fetch_all($resp);  
        }     

        $conn->close();

        return json_encode($results);

    }    
    function put()
    {           
        if ( file_exists( $this->accesslog ) ) {
        
            try{
                
                // $handle         = fopen('/var/log/apache2/access.log','r') or die ('File opening failed');                   
                $handle         = fopen($this->accesslog,'r'); 

                // echo "got here: " . $this->accesslog ."\n";
                // echo "got here: " . $handle ."\n";
                
                if ( !$handle ) {                   

                    throw new Exception('File open failed: ' . $this->accesslog);
                }                     
                
            }
            catch (Exception $e) {

                echo 'Caught exception: ', $e->getMessage(), "\n";
                return;
            }               

            $requestsCount  = 0;
            
            $data   = "";
            $cnt    = 0;
            $arr    = array();        
        
            try {
        
                while (!feof($handle)) {
        
                    $dd = fgets($handle);
            
                    $parts = explode('"', $dd);            
            
                    if( isset($parts[1]) ) {
        
                        $str = $parts[1];
        
                        if ( preg_match('/((\/Public\/MUSIC\/FEATURING.*mp3))/i', $str)){   
        
                            preg_match('/\[(.*)\]/', $parts[0], $date_array);
        
                            $date       = $date_array[1]; 
                            $new_date   = strtotime( $date );                    
        
                            $str = preg_replace('/GET/', "", $str);
                            $str = preg_replace('/HTTP.*/', "", $str);                   
                            $str = trim($str);
        
                            $tmparray = explode("/", $str );
        
                            $str = $tmparray[ count($tmparray) - 1 ];
                            
                            if( isset( $arr[$str] ) )
                            {                            
                                $arr[$str]["count"] += 1;
    
                                $old_date           = $arr[$str]["time"];
                                $arr[$str]["time"]  = $old_date > $new_date ? $old_date : $new_date;
                            }
                            else
                            {
                                $arr[$str] = array( "count" => 1, "time" => $new_date, "name" => $str );
                            }
                        }
                    } 
                }        
        
                fclose($handle);
        
                $json = json_encode($arr,JSON_FORCE_OBJECT);
        
                $query = "insert into dmck_audio_log_reports (data) values ( '" . $json . "' )";
        
                $results = $this->query( $query );

                return;
        
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            } 
        
        }
        else{
            echo 'File does not exist: ' . $this->accesslog ."\n";
        }

        return;

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

new dreaddymck_com_accesslog;

