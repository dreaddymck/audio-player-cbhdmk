<?php
// error_reporting(E_ALL);
// ini_set('display_errors', true);

require_once __DIR__ . '/vendor/autoload.php';

use maximal\audio\Waveform;

if (!class_exists("playlist_utilities_class")) {

	class playlist_utilities_class {

		public $debug;
		public $orderby;
		public $order;
		public $path;
		public $accesslog;		
		
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
				$object->moreinfo	= get_option('moreinfo') ? esc_attr( get_option('moreinfo') ) : "permalink";
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
		function fetch_playList_from_posts() {
			global $wpdb;
						
			$tag 	= get_option( 'tag') ? array( get_option( 'tag') ) : null;
			$tag_in 	= get_option( 'tag_in') ? array( get_option( 'tag_in') ) : null;
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

			// error_log(print_r($posts,1));
			// error_log("*******************************");
			// error_log(var_export($wpdb->last_query, TRUE));
			
			$response   = $this->render_elements($posts);
			
			wp_reset_postdata();
			// var_dump($_SERVER['REQUEST_METHOD']);			
			return $response;					
		}
		function accesslog_activity_purge(){        
			$query = "DELETE FROM dmck_audio_log_reports where UNIX_TIMESTAMP( updated ) <  UNIX_TIMESTAMP( DATE_SUB(NOW(), INTERVAL 30 DAY) )";
			$results = $this->query( $query );        
			return;        
		}
		function accesslog_activity_get() {
	
			$query = <<<EOF
SELECT 
	data 
FROM 
	dmck_audio_log_reports 
WHERE 
	DATE(`updated`) = CURDATE() 
ORDER BY 
	updated 
DESC 
	LIMIT 1
EOF;
			
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			} 
			$resp       = $conn->query($query);
			if( $resp instanceof mysqli_result ) {
				$results = mysqli_fetch_assoc($resp);  
			} 
			$conn->close();
			return $results['data'];
		}
		function accesslog_activity_get_today() {
	
			$query = <<<EOF
SELECT
	json_unquote(data->'$.*.name') as name,
	json_unquote(data->'$.*.time') as time,
	json_unquote(data->'$.*.count') as count    
FROM 
	dmck_audio_log_reports 
WHERE 
	DATE(`updated`) = CURDATE()    
order by 
	updated 
desc
	LIMIT 1        
EOF;
	
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			} 
	
			$resp       = $conn->query($query);
			$results    = array();    
	
			if( $resp instanceof mysqli_result )
			{
				$results = mysqli_fetch_all($resp);  
			}     
	
			$conn->close();
	
			return json_encode($results);
	
		}    
		function accesslog_activity_put()
		{ 
			
			if(!$this->accesslog){ die("Missing access log location"); }
	
			if ( file_exists( $this->accesslog ) ) {
			
				try{   
					$handle         = fopen($this->accesslog,'r');
					if ( !$handle ) { 
						throw new Exception('File open failed: ' . $this->accesslog);
					} 
				}
				catch (Exception $e) {
					echo 'Caught exception: ', $e->getMessage(), "\n";
					return;
				}               
	
				$requestsCount  = 0;
				
				$data   = "";
				$cnt    = 0;
				$arr    = array();        
			
				try {
			
					while (!feof($handle)) {
			
						$dd = fgets($handle);
				
						$parts = explode('"', $dd);            
				
						if( isset($parts[1]) ) {
			
							$str = $parts[1];
			
							if ( preg_match('/((\/Public\/MUSIC\/FEATURING.*mp3))/i', $str)){   

								// error_log($str);
			
								preg_match('/\[(.*)\]/', $parts[0], $date_array);
			
								$date       = $date_array[1]; 
								$new_date   = strtotime( $date );                    
			
								$str = preg_replace('/GET/', "", $str);
								$str = preg_replace('/HTTP.*/', "", $str);                   
								$str = trim($str);
			
								$tmparray = explode("/", $str );
			
								$str = $tmparray[ count($tmparray) - 1 ];
								
								if( isset( $arr[$str] ) )
								{                            
									$arr[$str]["count"] += 1;
		
									$old_date           = $arr[$str]["time"];
									$arr[$str]["time"]  = $old_date > $new_date ? $old_date : $new_date;
								}
								else
								{
									$arr[$str] = array( "count" => 1, "time" => $new_date, "name" => $str );
								}
							}
						} 
					}        
			
					fclose($handle);
			
					$json = json_encode($arr,JSON_FORCE_OBJECT);
			
					$query = "insert into dmck_audio_log_reports (data) values ( '" . $json . "' )";
			
					$results = $this->query( $query );
	
					return;
			
				} catch (Exception $e) {
					echo 'Caught exception: ', $e->getMessage(), "\n";
				} 
			
			}
			else{
				echo 'File does not exist: ' . $this->accesslog ."\n";
			}
	
			return;
	
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
/*
wavform render
*/
		function wavform(){			
			
			if(!get_option('path_to_media')){
				return;
			}
			foreach (new DirectoryIterator(get_option('path_to_media')) as $fileInfo) {
				
				if($fileInfo->isDir() && !$fileInfo->isDot()) {
					// Do whatever
				}
				if($fileInfo->getExtension() == "mp3"){
					$pathname = $fileInfo->getPathname(); 
					$basename = $fileInfo->getBasename(".mp3");
					$path = $fileInfo->getPath();
					$png = 	$path.'/'.$basename.'.wavform.png';  
					// var_dump( $path.'/'.$basename.'.wavform.png' );
					$waveform = new Waveform($pathname);
					Waveform::$color = [95, 95, 95, 0.5];
					Waveform::$backgroundColor = [0, 0, 0, 0];					
					$success = $waveform->getWaveform( $png, 1200, 600);
					var_dump("Writing: ".$path.'/'.$basename.'.wavform.png');
				}
			}
		}  

		function _log($obj = '') {		
			error_log( print_r($obj,1));
		}
	}
	new playlist_utilities_class();
	
}