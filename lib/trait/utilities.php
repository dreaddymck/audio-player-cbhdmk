<?php

namespace DMCK_WP_MEDIA_PLUGIN;

trait _utilities {

	function isSecure() {
		return
			( isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443;
	}
	function get_my_ip(){
		$ip = "";
		if ( !empty($_SERVER['HTTP_CLIENT_IP']) ) {			
			$ip = $_SERVER['HTTP_CLIENT_IP']; // Check IP from internet.
		} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // Check IP is passed from proxy.
		} 
		else {
			$ip = $_SERVER['REMOTE_ADDR']; // Get IP address from remote address.
		}
		return $ip;
	}
/*
The idea here is to set the current ip addresses of site admins.
Purge old entries on the fly.
Update upon login.
*/
	function _utilities_ignore_ip_auto_set(){

		$ignore_ip_enabled = get_option('ignore_ip_enabled') ? esc_attr( get_option('ignore_ip_enabled') ) : "";

		if( $ignore_ip_enabled && is_super_admin() ){                            
			
			$ignore_ip_json = get_option('ignore_ip_json') ? get_option('ignore_ip_json') : "";			
			$ip = $this->get_my_ip();
			$curr = (object) [
				'uid' => get_current_user_id(),
				'ip' => $ip,
				'date' => time(),					
			];

			if( $ignore_ip_json ){
				$ignore_ip_json = json_decode($ignore_ip_json);
				$ip_exists = false;
				foreach($ignore_ip_json as $key=>$value) {
					/*
					The access log cron task collects data only for current day. 
					So ignored IP addresses will be purged after that one day.
					*/
					if($value->date < strtotime('-1 day')){ // remove old ip addresses
						unset($ignore_ip_json[$key]);
					}else	
					if( $value->ip == $ip ){
						$ip_exists = true;						
					}			
				}
				if(!$ip_exists){
					array_push($ignore_ip_json, $curr);					
				}
				$ignore_ip_json = array_values($ignore_ip_json);			
			}else{
				$ignore_ip_json = [];
				array_push($ignore_ip_json, $curr);
			}	
			$ignore_ip_json = json_encode($ignore_ip_json);		
			update_option( 'ignore_ip_json', $ignore_ip_json);			
		}
	}
	
	function setTimezone(){		
		$timezone = get_option('timezone_string');
		if(empty($timezone)){
			switch(true){
				//Linux (Tested on Ubuntu 14.04)
				case(file_exists('/etc/timezone')):
					$timezone = file_get_contents('/etc/timezone');
					$timezone = trim($timezone); //Remove an extra newline char.
					break;
				case(date_default_timezone_get()):
					$timezone = date_default_timezone_get();
					break;				
				case(ini_get('date.timezone')):
					$timezone =  ini_get('date.timezone');
					break;
			}
		}
		if(!empty($timezone)){ date_default_timezone_set($timezone); }	
		return $timezone;
	}
    function progressBar($done, $total) {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }		
}
