<?php

namespace DMCK_WP_MEDIA_PLUGIN;
trait _admin {
    public $adminpreferences		= array(
        'ignore_ip_json',
        'ignore_ip_enabled',
        'delete_options_on_inactive',
        'drop_table_on_inactive',
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
        'visualizer_enabled',
        'charts_enabled',
        'chart_rgb_enabled',
        'chart_rgb_init',
        'chart_rgb',
        'chart_color_array',
        'chart_color_static',
        'audio_control_enabled',
        'audio_control_slider_height',
        'playlist_config_opt',
        'playlist_top_media'
    );
    public $userpreferences 		= array('userpreferences');       
	
    function register_activation($options){ $this->_tables_create(); }
    function register_deactivation($options){
        // $this->cronstarter_deactivate();
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
            delete_option('playlist_html_pane');
            delete_option('playlist_html_tabs');
            unregister_setting( self::SETTINGS_GROUP, $settings ); 
        }
        foreach($this->userpreferences as $settings ){ 
            delete_option($settings);
            unregister_setting( self::SETTINGS_GROUP, $settings );
        }
    } 
    function admin_menu(){
        $this->settings_page = add_options_page(
            $this->plugin_title,
            $this->plugin_title,
            'read',
            self::PLUGIN_SLUG,
            array( $this, 'admin_menu_include')
        );
    }    
    function admin_bar_setup(){
        global $wp_admin_bar;
        if ( !is_super_admin() || !is_admin_bar_showing() ) return;
        $dmck   = new DMCK();
        $parent = $dmck->menu_create();        
        $url_to = admin_url( 'options-general.php?page='.self::PLUGIN_SLUG);			
        $wp_admin_bar->add_menu(
            array(
                'parent' => $parent,
                'id' => self::PLUGIN_SLUG,
                'title' => __( $this->plugin_title, self::PLUGIN_SLUG ),
                'href' => $url_to,
                'meta' => array( 
                    'title' => $this->plugin_title, 
                    'class' => self::PLUGIN_SLUG 
                )						
            )
        );
    }    
    function admin_menu_include() {
        if ( !current_user_can( 'read' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        include ( 'admin-menu.php' );
    }
    function admin_scripts($hook_suffix) {			
        if ( $this->settings_page == $hook_suffix ) {
            wp_enqueue_script( self::PLUGIN_SLUG . '.marked.min.js', $this->plugin_url . 'assets/js/marked.min.js', array('jquery'), $this->plugin_version, false );	
            wp_enqueue_script( self::PLUGIN_SLUG . '.fonts.js', $this->plugin_url . 'dist/fonts.bundle.js', array('jquery'), $this->plugin_version, true );  
            wp_enqueue_script( self::PLUGIN_SLUG . '.solid.js', $this->plugin_url . 'dist/solid.bundle.js', array('jquery'), $this->plugin_version, true ); 
            wp_enqueue_script( self::PLUGIN_SLUG . '.pure.js', $this->plugin_url . 'dist/pure.bundle.js', array('jquery'), $this->plugin_version, true ); 
            wp_enqueue_script( self::PLUGIN_SLUG . '.chart.js', $this->plugin_url . 'dist/chart.bundle.js', array('jquery'), $this->plugin_version, true ); 
            wp_enqueue_script( self::PLUGIN_SLUG . '.bundle.js', $this->plugin_url . 'dist/private.bundle.js', array('jquery'), $this->plugin_version, true );
            			
            $this->localize_vars();
        }
    }    
       
}