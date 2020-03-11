<?php

trait _cron {
	
    function __construct(){}  
    function wp_cron_functions_daily() {
        try{
            $this->accesslog_activity_purge();
        }
        catch (Exception $e) { error_log($e); }   
    }
    function cronstarter_deactivate() {	
        $timestamp = wp_next_scheduled ($this->cron_name);			
        // unschedule previous event if any
        wp_unschedule_event ($timestamp, $this->cron_name);
    } 				
    function cronstarter_activation() {
        if( !wp_next_scheduled( $this->cron_name."_daily" ) ) {  
            wp_schedule_event( time(), 'daily', $this->cron_name."_daily" );  
        }
    }
    function cron_add_minute( $schedules ) { // Adds once every minute to the existing schedules. 
        $schedules['everyminute'] = array( 'interval' => 60, 'display' => __( 'Once Every Minute' ) ); 
        return $schedules; 
    }    

}
