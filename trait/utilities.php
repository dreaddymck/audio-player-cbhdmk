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
		$html = "";
		if ( is_singular() && in_the_loop() ) {
			if (get_option('charts_enabled')) {
				$paths = $this->fetch_audio_from_string($content);
				$post_chart_json = array();
				foreach($paths as $p){
					// $value = parse_url($p, PHP_URL_PATH);
					$basename = basename($p);
					$filename = urldecode($basename);
					$pattern = "(".preg_quote($basename).")|(".preg_quote($filename).")";
					$target = pathinfo($filename, PATHINFO_FILENAME);
					$target = preg_replace("/(\W)+/", '_', $target);					
					$resp = $this->accesslog_activity_get_month($pattern,12);
					foreach($resp as $key=>$value){					
						$json = json_decode($value[0]);
						foreach($json as $jkey=>$jvalue){
							// if($jvalue->name == $filename){
							// $this->_echo($jvalue->name . " == " . $filename);
							// $this->_echo($jvalue->name . " == " . $pattern);
							if( preg_match("/".$pattern."/", $jvalue->name) ){
								array_push($post_chart_json, array(
									"time"=> $jvalue->time,
									"count" => $jvalue->count,
									"target" => $target,
									"filename" => $filename
								));								
							}						
						}
					}
					$html = "<div class='post_chart_section ". $target ."_chart'></div><script>let post_chart_json = ".json_encode($post_chart_json)."</script>";				
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
