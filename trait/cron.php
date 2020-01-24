<?php

trait _cron {
	
    function __construct(){}  
    // here's the function we'd like to call with our cron job
    function wp_cron_functions() {
        //cron activities here
        try{
            $cmd = '$(which php) '. plugin_dir_path(__DIR__) .'lib/reports.php put'; 
            error_log($cmd);
            $resp = shell_exec($cmd);
            error_log($resp);
        }
        catch (Exception $e) { error_log($e); }   
    }
    function cronstarter_deactivate() {	
        // find out when the last event was scheduled
        $timestamp = wp_next_scheduled ($this->cron_name);			
        // unschedule previous event if any
        wp_unschedule_event ($timestamp, $this->cron_name);
    } 				
    function cronstarter_activation() {
        if( !wp_next_scheduled( $this->cron_name ) ) {  
            wp_schedule_event( time(), 'everyminute', $this->cron_name );  
        }
    }
    function cron_add_minute( $schedules ) { // Adds once every minute to the existing schedules. 
        $schedules['everyminute'] = array( 'interval' => 60, 'display' => __( 'Once Every Minute' ) ); 
        return $schedules; 
    }    

}
