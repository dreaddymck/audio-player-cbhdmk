<?php

trait _tables {
	
    function __construct(){}
    function _tables_create(){        
        $sql = <<<EOF
create table IF NOT EXISTS dmck_audio_log_reports (
    id serial primary key,
    data json,
    updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

EOF;
        $res = _requests::query($sql);

        // $res = _requests::query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'dmck_audio_log_reports' AND column_name = 'name'");
        // if(empty($res)){            
        //     $res = _requests::query("ALTER TABLE dmck_audio_log_reports ADD COLUMN name varchar(250) AFTER id");
        //     $res = _requests::query("ALTER TABLE dmck_audio_log_reports ADD COLUMN count INT AFTER name");
        //     $res = _requests::query("ALTER TABLE dmck_audio_log_reports ADD COLUMN time TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER count");            
        // }        
    } 
    function _tables_drop(){

        if (!get_option('drop_table_on_inactive')) { return; }

        $sql = <<<EOF
DROP TABLE IF EXISTS dmck_audio_log_reports;
EOF;

        _requests::query($sql);
    } 
}