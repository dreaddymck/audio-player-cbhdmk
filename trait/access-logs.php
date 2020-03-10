<?php

trait _accesslog {

    public $filepath;		
        
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
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); } 
        $resp = $conn->query($query);
        if( $resp instanceof mysqli_result ) { $results = mysqli_fetch_assoc($resp); } 
        $conn->close();
        return $results['data'];
    }
    function accesslog_activity_get_week($name="") {

        $filter = "";
        if($name){ $filter = " AND json_unquote(data->'$.*.name') REGEXP'$name'"; }
        $query = <<<EOF
SELECT 
    data FROM dmck_audio_log_reports  
WHERE 
    DATE(`updated`) > DATE_SUB(NOW(), INTERVAL 1 WEEK)
    {$filter}    
order by 
    updated DESC
EOF;

        $results = $this->query($query);
        return json_encode($results);	
    } 
    function accesslog_activity_get_month($name="") {
        $filter = "";
        if($name){ $filter = " AND json_unquote(data->'$.*.name') REGEXP'$name'"; }
        $query = <<<EOF
SELECT 
    data FROM dmck_audio_log_reports 
WHERE 
    DATE(`updated`) > DATE_SUB(NOW(), INTERVAL 1 MONTH)
    {$filter}    
order by 
    updated DESC
EOF;

        $results = $this->query($query);	
        return json_encode($results);	
    }       
    function accesslog_activity_put()
    {			
        if(!$this->filepath){ die("Missing access log location"); }	
        $media_root_url = get_option('media_root_url') ? get_option('media_root_url') : die("Missing media_root_url option");

        if ( file_exists( $this->filepath ) ) {			
            try{   
                $handle = fopen($this->filepath,'r');
                if ( !$handle ) { throw new Exception('File open failed: ' . $this->filepath); } 
            }
            catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
                return;
            }
            $requestsCount  = 0;
            $data   = "";
            $cnt    = 0;
            $arr    = array();
            $results = "";
            $regex = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) "([^"]*)" "([^"]*)"$/';

            try {
                while (!feof($handle)) {        
                    
                    $dd = fgets($handle);
                    
                    if ( preg_match('/(('. preg_quote($media_root_url, '/').'.*mp3))/i', $dd)){
                        
                        preg_match($regex , urldecode($dd), $matches);
                        // echo( urldecode($dd) ."\n\r");
                        // echo( print_r($matches,1) );
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
                    $results = $this->query( "select id from dmck_audio_log_reports where DATE(`updated`) = CURDATE()" );
                    if(empty($results)){
                        $results = $this->query( "insert into dmck_audio_log_reports (data) values ( '" . $json . "' )" );
                    }else{
                        $results = $this->query( "UPDATE dmck_audio_log_reports SET data = '" . $json . "' WHERE id=".$results[0][0]  );
                    }
                }	
                return json_encode($results);
        
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