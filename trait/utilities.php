<?php
trait _utilities {

	function upload(){
		$response = array();
		foreach($_FILES as $file){
			$path = esc_attr( get_option('media_root_path') );
			$path = preg_replace('{/$}', '', $path);
			array_push($response, move_uploaded_file($file['tmp_name'], $path."/".basename($file['name'])));
		}
		return $response;
	}			
	function isSecure() {
		return
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| $_SERVER['SERVER_PORT'] == 443;
	}
	function content_handler($content){

		if ( is_singular() && in_the_loop() ) {			
			$paths = $this->fetch_audio_from_string($content);
			foreach($paths as $value){				
				$value = parse_url($value, PHP_URL_PATH);
				$name = basename($value);
				// $this->_dump($this->accesslog_activity_get_week($name));
				$this->accesslog_activity_get_week($name);
			}

			$content = $this->content_toggle_https($content);

			
		}
		return $content;
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
