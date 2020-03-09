<?php

trait _accesslog {

    public $filepath;		
    
    function __construct(){} 
    
    function accesslog_activity_purge(){        
        $query = <<<EOF
DELETE FROM 
    dmck_audio_log_reports 
WHERE 
    updated <  DATE_SUB(NOW(), INTERVAL 1 YEAR)
EOF;

        $results = $this->query( $query );        
        return;        
    }
    function accesslog_activity_get() {

        $query = <<<EOF
SELECT 
    data FROM dmck_audio_log_reports 
WHERE 
    DATE(`updated`) = CURDATE() 
ORDER BY 
    updated DESC 
LIMIT 1
EOF;
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        $resp = $conn->query($query);
        if( $resp instanceof mysqli_result ) {
            $results = mysqli_fetch_assoc($resp);  
        } 
        $conn->close();
        return $results['data'];
    }
    function accesslog_activity_get_week() {

        $query = <<<EOF
SELECT 
    data FROM dmck_audio_log_reports  
WHERE 
    DATE(`updated`) > DATE_SUB(NOW(), INTERVAL 1 WEEK)    
order by 
    updated DESC
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
    function accesslog_activity_get_month() {

        $query = <<<EOF
SELECT 
    data FROM dmck_audio_log_reports 
WHERE 
    DATE(`updated`) > DATE_SUB(NOW(), INTERVAL 1 MONTH)    
order by 
    updated DESC
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
    function accesslog_activity_put()
    {			
        if(!$this->filepath){ die("Missing access log location"); }	
        $media_root_url = get_option('media_root_url') ? get_option('media_root_url') : die("Missing media_root_url option");

        if ( file_exists( $this->filepath ) ) {			
            try{   
                $handle         = fopen($this->filepath,'r');
                if ( !$handle ) { 
                    throw new Exception('File open failed: ' . $this->filepath);
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
                        //TODO: if match filter vs published post.
                        if ( preg_match('/(('. preg_quote($media_root_url, '/').'.*mp3))/i', $str)){	
                            echo($str);
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
                if(!empty($arr)){
                    $json = json_encode($arr,JSON_FORCE_OBJECT);			
                    $query = "insert into dmck_audio_log_reports (data) values ( '" . $json . "' )";			
                    $results = $this->query( $query );
                }	
                return;
        
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            } 			
        }
        else{
            echo 'File does not exist: ' . $this->filepath ."\n";
        }	
        return;	
    }
}