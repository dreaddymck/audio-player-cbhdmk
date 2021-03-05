<?php
namespace DMCK_WP_MEDIA_PLUGIN;

trait _tables {
	
    function __construct(){}

    function _tables_create(){        
        $this->_tables_dmck_audio();
        $this->_tables_dmck_media();
    }
    function _tables_dmck_audio(){        
        $sql = "
create table IF NOT EXISTS dmck_audio_log_reports (
    id serial primary key,
    data json,
    updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;
";
        $res = $this->query($sql);
    }    
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

    }
    function _tables_media_drop(){
        $results = $this->query("DROP TABLE IF EXISTS dmck_media_activity_log;");
        $results = $this->query("DROP TABLE IF EXISTS dmck_media_activity_referer_log;");          
    } 
    function _tables_drop(){
        if (!get_option('drop_table_on_inactive')) { return; }
        $sql = "DROP TABLE IF EXISTS dmck_audio_log_reports";
        $res = $this->query($sql);
    } 
}