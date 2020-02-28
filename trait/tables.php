<?php

trait _tables {
	
    function __construct(){}
    function _tables_create(){        
        $sql = <<<EOF
DROP TABLE IF EXISTS dmck_audio_log_reports;
create table dmck_audio_log_reports (
    id serial primary key,
    data json,
    updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

EOF;
        _utilities::query($sql);
    } 
    function _tables_drop(){

        $sql = <<<EOF
DROP TABLE IF EXISTS dmck_audio_log_reports;
EOF;

        _utilities::query($sql);
    } 
}