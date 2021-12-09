<?php
namespace DMCK_WP_MEDIA_PLUGIN;
trait _tables {
    function _tables_create(){ $this->_tables_dmck_media(); }
    function _tables_dmck_media(){
        $query = "
create table IF NOT EXISTS dmck_media_activity_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id INT,
    media text,
    count int,
    time TIMESTAMP,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;
";
        $results = $this->query($query);

        $query = "
create table IF NOT EXISTS dmck_media_activity_referer_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id INT,
    referer text,
    time TIMESTAMP,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci
";
        $results = $this->query($query);

        $query = "
CREATE TABLE IF NOT EXISTS dmck_media_activity_rss(
    uuid VARCHAR(255) PRIMARY KEY NOT NULL,
    xml TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci
";
        $results = $this->query($query);


    }
    function _tables_drop(){
        $results = $this->query("DROP TABLE IF EXISTS dmck_media_activity_log;");
        $results = $this->query("DROP TABLE IF EXISTS dmck_media_activity_referer_log;");      
        $results = $this->query("DROP TABLE IF EXISTS dmck_media_activity_rss;");      
    }      
    function export_tables()
    {        
        if ( !is_super_admin() ) return false;
        $tables = array('dmck_media_activity_log','dmck_media_activity_referer_log');
        $mysqli = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        if ($mysqli->connect_error) { die("Connection failed: " . $mysqli->connect_error); }
        $mysqli->select_db(DB_NAME); 
        $mysqli->query("SET NAMES 'utf8'");
        $queryTables    = $mysqli->query('SHOW TABLES'); 
        while($row = $queryTables->fetch_row()) 
        { 
            $target_tables[] = $row[0];
        }   
        if($tables !== false) 
        { 
            $target_tables = array_intersect( $target_tables, $tables); 
        }
        foreach($target_tables as $table)
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table);  
            $fields_amount  =   $result->field_count;  
            $rows_num=$mysqli->affected_rows;     
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";            

            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
            {                
                while($row = $result->fetch_row())  
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)  
                    { 
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ; 
                        }
                        else 
                        {   
                            $content .= '""';
                        }     
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }      
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                    {   
                        $content .= ";";
                    } 
                    else 
                    {
                        $content .= ",";
                    } 
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";            
        }
        $content = "DROP TABLE IF EXISTS dmck_media_activity_log;\n\rDROP TABLE IF EXISTS dmck_media_activity_referer_log;".$content;
        return $content;
    }    
}