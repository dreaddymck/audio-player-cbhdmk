<?php

namespace DMCK_WP_MEDIA_PLUGIN;

trait _accesslog {

    public $filepath;		
        
    function accesslog_activity_purge(){        
        $query = <<<EOF
DELETE FROM 
    dmck_audio_log_reports 
WHERE 
    updated <  DATE_SUB(NOW(), INTERVAL 2 YEAR)
EOF;

        $results = $this->query( $query ); 
        return $results;        
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
        
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); } 
        $resp = $conn->query($query);
        if( $resp instanceof \mysqli_result ) { $results = mysqli_fetch_assoc($resp); } 
        $conn->close();
        return $results ? $results["data"] : "";
    }
    function accesslog_activity_get_week($name="",$num=1) {

        $filter = "";
        if($name){ $filter = " AND JSON_EXTRACT(data, '$.*.name')  REGEXP'$name'"; }
        $query = <<<EOF
SELECT 
    data FROM dmck_audio_log_reports  
WHERE 
    DATE(`updated`) >= DATE_SUB(NOW(), INTERVAL $num WEEK)
    $filter    
order by 
    updated ASC
EOF;

        $results = $this->query($query);
        return $results ? $results : "";	
    } 
    function accesslog_activity_get_month($name="", $num=1) {
        $filter = "";
        if($name){ $filter = " AND JSON_EXTRACT(data, '$.*.name') REGEXP'$name' "; }
        
        $query = <<<EOF
SELECT 
    data FROM dmck_audio_log_reports 
WHERE 
    DATE(`updated`) >= DATE_SUB(NOW(), INTERVAL $num MONTH)
    $filter    
order by 
    updated ASC
EOF;

        $query = str_replace(array("\r", "\n"), '', $query);
        $results = $this->query($query);
        return $results ? $results : "";	
    }       
    function accesslog_activity_put()
    {			
        if(!$this->filepath){ die("Missing access log location"); }	
        
        $access_log_pattern = get_option('access_log_pattern') ? get_option('access_log_pattern') : "";
        
        $pattern = $access_log_pattern ? $access_log_pattern : "/.mp3/i";
        if($this->debug){
            $pattern = $this->filename  ? $this->filename : $pattern;
            $this->_log("PATTERN: " . $pattern);
        }        
        if ( file_exists( $this->filepath ) ) {			
            try{   
                $handle = fopen($this->filepath,'r');
                if ( !$handle ) { throw new \Exception('File open failed: ' . $this->filepath); } 
            }
            catch (\Exception $e) {
                echo 'Caught \Exception: ', $e->getMessage(), "\n";
                return;
            }

            $ignore_ip_json = get_option('ignore_ip_json') ? get_option('ignore_ip_json') : "";
            $ignore_ip_enabled = get_option('ignore_ip_enabled') ? esc_attr( get_option('ignore_ip_enabled') ) : "";            

            $arr    = array();
            $results = "";
            $regex = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) "([^"]*)" "([^"]*)"$/';

            try {
                while (!feof($handle)) { 
                    $dd = fgets($handle);                    
                    preg_match($regex , urldecode($dd), $matches);
                    // echo( urldecode($dd) ."\n\r");
                    // echo( print_r($matches,1) );
                    if(preg_match( $pattern , $matches[8] ) ){                        
                        if( $ignore_ip_enabled && $ignore_ip_json ){                            
                            // if($ignore_ip_json && preg_match('/'. $matches[1] .'/', $ignore_ip_json)){ continue; }
                            $found_ip = false;
                            foreach(json_decode($ignore_ip_json) as $key=>$value) {                                
                                if( $value->ip == $matches[1] ){
                                    $found_ip = true;   
                                    break;
                                }
                            }
                            if($found_ip){ 
                                continue; 
                            }                            
                        }
                        if($this->debug){
                            $this->_log($matches);
                        }                       
                        $name = basename($matches[8]);
                        $time = $matches[4] .":".$matches[5]." ".$matches[6];
                        $time = strtotime( $time );
                        $referer = $matches[1]." ".$matches[2]." ".$matches[3];

                        if( isset( $arr[$name] ) )
                        {                            
                            $arr[$name]["count"] += 1;		
                            $arr[$name]["time"]  =  $time ? $time : $arr[$name]["time"];
                            $arr[$name]["referer"]  =  $referer;
                        }
                        else
                        {
                            $arr[$name] = array( 
                                "count" => 1, 
                                "time" => $time, 
                                "name" => $name, 
                                "referer" => $referer );
                        }
                    }
                } 

                fclose($handle);
                if(!empty($arr)){
                    
                    $json = json_encode($arr,JSON_FORCE_OBJECT);                                        
                    $results = $this->query( "SELECT id FROM dmck_audio_log_reports WHERE DATE(`updated`) = CURDATE()" );
                    if(empty($results)){
                        $results = $this->query( "INSERT INTO dmck_audio_log_reports (data) VALUES ( '" . $json . "' )" );
                    }else{
                        $results = $this->query( "UPDATE dmck_audio_log_reports SET data = '" . $json . "' WHERE id=".$results[0][0]  );
                    }
                }	
                return json_encode($results);
        
            } catch (\Exception $e) {
                echo 'Caught \Exception: ', $e->getMessage(), "\n";
            } 			
        }
        else{
            echo 'File does not exist: ' . $this->filepath ."\n";
        }	
        return;	
    }
}