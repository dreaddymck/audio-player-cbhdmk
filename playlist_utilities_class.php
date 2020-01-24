<?php
// error_reporting(E_ALL);
// ini_set('display_errors', true);

if (!class_exists("playlist_utilities_class")) {

	class playlist_utilities_class {

		public $debug;
		public $orderby;
		public $order;
		public $path;
		public $filepath;		
		
		function __construct(){}  
		          
		function render_elements($posts) {
			
            $response 	= [];
			
			foreach ( $posts as $post ) : setup_postdata( $post );
				
				$object 	= new stdClass();
				$audio 		= $this->fetch_audio_from_string( $post->post_content );
			
                if(! isset($audio[0])) { continue; }

				/* If $this->path exist
				* playlist_elements call
				* we should be extracting a single element that matches
				*/
				if($this->path) {					
					if (strpos(  urldecode($audio[0]), $this->path) === false) {
						continue;
					}				
				}				
				$object->ID		        = $post->ID;
				$object->mp3		    = $audio[0];				
				$object->wavformpng	    = $this->waveformpng($audio[0]);
				$object->wavformjson	= $this->waveformjson($audio[0]);
				
				if($this->isSecure()){
					$object->mp3		    = preg_replace("/^http:/i", "https:", $object->mp3);
					$object->wavformpng	    = preg_replace("/^http:/i", "https:", $object->wavformpng);
					$object->wavformjson	= preg_replace("/^http:/i", "https:", $object->wavformjson);	
				}				
				$object->title		= esc_attr($post->post_title);
				$object->artist		= "dreaddymck";
				$object->rating		= 0;
				$object->cover		= $this->fetch_the_post_thumbnail_src( get_the_post_thumbnail($post->ID, "thumbnail") );
				$object->permalink	= get_permalink( $post->ID );
				$object->moreinfo	= get_option('moreinfo') ? get_option('moreinfo') : "";
				$object->playlist_thumb = $object->cover;
				$object->tags 		=  implode( ', ', wp_get_post_tags( $post->ID, array( 'fields' => 'names' )) );
			
				$excerpt_tmp 	= $post->post_content;
				$excerpt_tmp 	= htmlspecialchars_decode($excerpt_tmp);
				$excerpt_tmp 	= preg_replace('#<[^>]+>#', ' ', $excerpt_tmp);
				$excerpt_tmp 	= preg_replace("#(\r|\n){2,}#", " ", $excerpt_tmp);
				$excerpt_tmp 	= str_replace( chr( 194 ) . chr( 160 ), ' ', $excerpt_tmp );
				$excerpt_tmp 	= preg_replace( '#(Sorry, your browser doesn\'t support HTML5 audio\.)#', ' ', $excerpt_tmp );
				$excerpt_tmp 	= preg_replace( '#(download)#', ' ', $excerpt_tmp );
		
				$object->excerpt = wp_trim_words( esc_attr( $excerpt_tmp ), 12, "...");
			
				array_push( $response, $object );
		
			endforeach;			
		
            return json_encode($response);
		
		}
		function isSecure() {
			return
			  (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			  || $_SERVER['SERVER_PORT'] == 443;
		}		
		function waveformpng($str) {
		    return preg_replace('/\.mp3$/', '.wavform.png', $str);
		}
		function waveformjson($str) {
		    return preg_replace('/\.mp3$/', '.wavform.json', $str);
		}
		function fetch_the_post_thumbnail_src($img)
		{
			return (preg_match('~\bsrc="([^"]++)"~', $img, $matches)) ? $matches[1] : esc_attr( get_option('default_album_cover') ); //'https://dl.dropboxusercontent.com/u/1273929/MUSIC/FEATURING/photo.jpg';
		}
		function fetch_audio_from_string($str) {		
			# See http://en.wikipedia.org/wiki/Audio_file_format
			# Adjust the list to your needs
			// 	$suffixes = array (
			// 		'3gp', 'aa3', 'aac', 'aiff', 'ape', 'at3', 'au',  'flac', 'm4a', 'm4b',
			// 		'm4p', 'm4r', 'm4v', 'mpc',  'mp3', 'mp4', 'mpp', 'oga',  'ogg', 'oma',
			// 		'pcm', 'tta', 'wav', 'wma',  'wv',
			// 	);
			//	$formats = join( '|', $suffixes );
			$formats = "mp3";		
			// $regex   = '~
			// (([^"\'])|^)            # start of string or attribute delimiter -> match 1
			// (https?                 # http or https
			//     ://                 # separator
			//     .+/                 # domain plus /
			//     .+                  # file name at least one character
			//     \.                  # a dot
			//     (' . $formats . ')  # file suffixes
			// )                       # complete URL -> match 3
			// (([^"\'])|$)?           # end of string or attribute delimiter -> match 5
			// ~imUx';                 # case insensitive, multi-line, ungreedy, commented
			$regex   = '~(https?://.+/.+\.(' . $formats . '))(([^"\'])|$)?~imUx';
			preg_match_all( $regex, $str, $matches, PREG_PATTERN_ORDER );
			// error_log("*******************************");
			// error_log(var_export($matches, TRUE));			
			return $matches[0];
		}
		function shortcode_playlist() {
			global $wpdb;						
			$tag = get_option( 'tag') ? array( get_option( 'tag') ) : null;
			$tag_in = get_option( 'tag_in') ? array( get_option( 'tag_in') ) : null;
			$tag_not_in = get_option( 'tag_not_in') ? array( get_option( 'tag_not_in')): null;			
			//$this->_log($tag_in);			
			$args = array(
				'numberposts' 		=> -1,
				'orderby'          	=> $this->orderby,
				'order'            	=> $this->order,
				'post_status'      	=> 'publish',
				'no_found_rows' 	=> true,
				'tag'				=> $tag,
				'tag__in' 			=> $tag_in,
				'tag__not_in'		=> $tag_not_in,	
			);
			$posts 	= get_posts( $args );
			$response   = $this->render_elements($posts);			
			wp_reset_postdata();
			return $response;					
		}
		function search($data){
			global $wpdb;
			$params 				= $data->get_params();
			$args = array(
				's'					=> isset($params["s"]) ? htmlspecialchars($params["s"] ) : "",
                'posts_per_page' 	=> isset($params["posts_per_page"]) ? htmlspecialchars($params["posts_per_page"] ) : 1,
				'post_status'   	=> isset($params["post_status"]) ? htmlspecialchars($params["post_status"] ) : "published",
				'tag'           	=> isset($params["tag"]) ? htmlspecialchars($params["tag"] ) : "",
				'orderby'          	=> isset($params["orderby"]) ? htmlspecialchars($params["orderby"] ) : "",
				'order'            	=> isset($params["order"]) ? htmlspecialchars($params["order"] ) : "",
				'tag'				=> isset($params["tag"]) ? htmlspecialchars($params["tag"] ) : "",
				'tag__in' 			=> isset($params["tag_in"]) ? htmlspecialchars($params["tag_in"] ) : "",
				'tag__not_in'		=> isset($params["tag_not_in"]) ? htmlspecialchars($params["tag_not_in"] ) : "",					
			);			
			$posts 	    = get_posts( $args );
			$response   = $this->render_elements($posts);
			wp_reset_postdata();
			return($response);
		}		

		function query($sql){
		
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			} 
	
			$resp       = $conn->query($sql);
			$results    = array();        
	
			if( $resp instanceof mysqli_result )
			{
				$results = mysqli_fetch_all($resp);  
			}     
			
			$conn->close();
	
			return ($results);
		
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
	new playlist_utilities_class();
	
}