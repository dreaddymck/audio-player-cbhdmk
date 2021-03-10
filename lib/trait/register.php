<?php
namespace DMCK_WP_MEDIA_PLUGIN;
trait _register {
    public $adminpreferences		= array(
        'ignore_ip_json',
        'ignore_ip_enabled',
        'charts_enabled',
        'delete_options_on_inactive',
        'drop_table_on_inactive',
        'chart_color_array',
        'chart_color_static',
        'favicon',
        'default_album_cover',
        'moreinfo',
        'access_log',
        'access_log_pattern',
        'playlist_config',
        'media_filename_regex',
        'visualizer_rgb_init',
        'visualizer_rgb',
        'visualizer_samples',
        'visualizer_rgb_enabled',
        'chart_rgb_init',
        'chart_rgb',
        'chart_rgb_enabled',
        'audio_control_enabled',
        'audio_control_slider_height',
        'playlist_config_opt'
    );
    public $userpreferences 		= array('userpreferences');       
	
    function register_activation($options){ $this->_tables_create(); }
    function register_deactivation($options){
        $this->cronstarter_deactivate();
        if (get_option('drop_table_on_inactive')) { 
            $this->_tables_drop();
            return; 
        }
        if (get_option('delete_options_on_inactive')) { 
            $this->unregister_settings();				
            return; 
        }			
    }
    function register_settings() {
        foreach($this->adminpreferences as $settings ) { register_setting( self::SETTINGS_GROUP, $settings ); }
        foreach($this->userpreferences as $settings ){ register_setting( self::SETTINGS_GROUP, $settings ); }
    }
    function unregister_settings() {
        foreach($this->adminpreferences as $settings ) { 
            delete_option($settings);
            unregister_setting( self::SETTINGS_GROUP, $settings ); 
        }
        foreach($this->userpreferences as $settings ){ 
            delete_option($settings);
            unregister_setting( self::SETTINGS_GROUP, $settings );
        }
    }    
}