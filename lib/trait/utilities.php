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
	function content_handler($content){
		global $post;
		$html = "";
		if ( is_singular() && in_the_loop() ) {
			if (get_option('charts_enabled')) {
				$paths = $this->fetch_audio_from_string($content);
				if(sizeof($paths)){
					$post_chart_json = array();
					foreach($paths as $p){
						$basename = basename($p);
						$filename = urldecode($basename);
						$pattern = "(".preg_quote($basename)."|".preg_quote($filename).")";
						$target = pathinfo($filename, PATHINFO_FILENAME); 
						$target = preg_replace("/(\W)+/", '_', $target);					
						$resp = $this->dmck_media_activity_month($post->ID,12);						
						if($resp){
							foreach($resp as $key=>$value){					
								$json = (object)($value);
								array_push($post_chart_json, array(
									"time"=> $json->time,
									"count" => $json->count,
									"target" => $target,
									"filename" => $filename
								));	
							}
						}
						$html = "<div class='post_chart_section ". $target ."_chart'></div><script>let post_chart_json = ".json_encode($post_chart_json)."</script>";				
					}
				}
			}
		}
		$content = $this->content_toggle_https($content);
		return $content.$html;
	}
	function content_toggle_https($content){			
		$site_url = get_site_url();			
		if( $this->isSecure() ){				
			$secure_url		= preg_replace( "/^http:/i", "https:", $site_url );
			$insecure_url	= preg_replace( "/^https:/i", "http:", $site_url );	
			$pattern 		= "/" .preg_quote($insecure_url, '/') . "/i";	
			$content 		= preg_replace( $pattern, $secure_url, $content );
		}
		return $content;
	}
	function get_post_by_slug($slug){
		$posts = get_posts(array(
				'name' => $slug,
				'post_type'   => 'post',
				'post_status' => 'publish',
				'numberposts' => 1
		));	
		return $posts[0];
	}
	function excerpt($text){
		$text = strip_shortcodes( $text );
		$text = apply_filters( 'the_content', $text );
		$text = str_replace(']]>', ']]&gt;', $text);
		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
		return $text;
	}
	function the_exerpt_filter($param) {			
		$param = preg_replace('/(\[.*\])/', "", $param);
		$param = preg_replace('/(Sorry, your browser doesn\'t support HTML5 audio\.|Sorry, your browser doesn&#8217;t support HTML5 audio.)/i', "", $param);
		return $param;
	}	
	function setTimezone($OSXPassword = null){		
		$timezone = null;
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
		if(!empty($timezone)){
			date_default_timezone_set($timezone);
		}		
		return $timezone;
	}	
	function var_error_log( $object=null ){
		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	}
	function _log($obj = '') {
		error_log ( print_r ( $obj, 1 ) );
	}
	function _dump($obj = '') {
		var_dump( $obj );
	}	
	function _echo($obj = '') {
		echo "<pre>" . ( print_r ( $obj, 1 ) ) . "</pre>";
	}		
}
