<?php

// tag (string) – use tag slug.
// tag_id (int) – use tag id.
// tag__and (array) – use tag ids.
// tag__in (array) – use tag ids.
// tag__not_in (array) – use tag ids.
// tag_slug__and (array) – use tag slugs.
// tag_slug__in (array) – use tag slugs.

trait _utilities {

	public $debug;
	public $path;		
	
	function __construct(){}	
	function isSecure() {
		return
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| $_SERVER['SERVER_PORT'] == 443;
	}
	function content_toggle_https($content){
			
		$site_url = get_site_url();			
		if( $this->isSecure() ){				
			$secure_url		= preg_replace( "/^http:/i", "https:", $site_url );
			$insecure_url	= preg_replace( "/^https:/i", "http:", $site_url );	
			$pattern 		= "/" .preg_quote($insecure_url, '/') . "/i";	
			$content 	= preg_replace( $pattern, $secure_url, $content );
		}
		return $content;
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
		var_dump( print_r( $obj,1 ) );
	}	
	function _echo($obj = '') {
		echo "<pre>" . ( print_r ( $obj, 1 ) ) . "</pre>";
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
	
			//Windows (Untested) (Thanks @Mugoma J. Okomba!)
			case(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'):
				$timezone = exec('tzutil /g');
				break;
	
			//OSX (Tested on OSX 10.11 - El Capitan)
			// case(file_exists('/usr/sbin/systemsetup')):
			// 	if(!isset($OSXPassword)){
			// 		$OSXPassword = readline('**WARNING your input will appear on screen!**  Password for sudo: ');
			// 	}
			// 	$timezone = exec("echo '" . $OSXPassword ."' | sudo -S systemsetup -gettimezone");
			// 	$timezone = substr($timezone, 11);
			// 	break;
		}		
		if(!empty($timezone)){
			date_default_timezone_set($timezone);
		}		
		return $timezone;
	}		
	function respond_ok($response){
		// check if fastcgi_finish_request is callable
		if (is_callable('fastcgi_finish_request')) {
			echo $response;
			/*
			* http://stackoverflow.com/a/38918192
			* This works in Nginx but the next approach not
			*/
			session_write_close();
			fastcgi_finish_request();
	
			return;
		}			

		ignore_user_abort(true);
		ob_start();		
		echo $response;
		
		$serverProtocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
		header($serverProtocol . ' 200 OK');
		// Disable compression (in case content length is compressed).
		header('Content-Encoding: none');
		header('Content-Length: ' . ob_get_length());
		
		// Close the connection.
		header('Connection: close');
		
		ob_end_flush();
		ob_flush();
		flush();		
	}
	function remove_session_lock(){
		//Closing the PHP session lock: PHP 5.x and PHP 7
		session_start();
		session_write_close();	
	}		
	function playlist_create(){
		error_log("success");
	}  
}
