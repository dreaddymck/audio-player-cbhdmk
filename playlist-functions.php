<?php
// error_reporting(E_ALL);
// ini_set('display_errors', true);

require_once( $_SERVER['DOCUMENT_ROOT'] . "/wp-load.php");

if (!class_exists("PlayListFromPostCls")) {

	class PlayListFromPostCls {

		public $debug;
		public $orderby;
		public $order;
		
		function __construct()
		{
			$this->get_request();
			$this->fetch_playList_from_posts();
		}
		function get_request()
		{
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$this->debug			= isset($_POST["debug"]) ? htmlspecialchars($_POST["debug"] ) : false;
				$this->orderby			= isset($_POST["orderby"]) ? htmlspecialchars($_POST["orderby"] ) : "rand";
				$this->order			= isset($_POST["order"]) ? htmlspecialchars($_POST["order"] ) : "DESC";
			}else{
				$this->debug			= isset($_GET["debug"]) ? htmlspecialchars($_GET["debug"] ) : false;
				$this->orderby			= isset($_GET["orderby"]) ? htmlspecialchars($_GET["orderby"] ) : "rand";
				$this->order			= isset($_GET["order"]) ? htmlspecialchars($_GET["order"] ) : "DESC";
			}
		}
		function fetch_playList_from_posts() {
			
						
			$tag_in 	= get_option( 'tag_in') ? array( get_option( 'tag_in') ) : null;
			$tag_not_in = get_option( 'tag_not_in') ? array( get_option( 'tag_not_in')): null;
			
			//$this->_log($tag_in);
			
			$args = array(
					'numberposts' 		=> -1,
					'orderby'          	=> $this->orderby,
					'order'            	=> $this->order,
					'post_status'      	=> 'publish',
					'no_found_rows' 	=> true,
					'tag__in' 			=> $tag_in,
					'tag__not_in'		=> $tag_not_in,
			);
			
			$lastposts 	= get_posts( $args );
			$response 	= [];
		
 			
			
			foreach ( $lastposts as $post ) : setup_postdata( $post );
			
				
				$object 	= new stdClass();
				$audio 		= $this->fetch_audio_from_string( $post->post_content );
			
				if(! isset($audio[0])) { continue; }
				
				$object->ID		        = $post->ID;
				$object->mp3		    = $audio[0];
				
				$object->wavformpng	    = $this->waveformpng($audio[0]);
				$object->wavformjson	= $this->waveformjson($audio[0]);
				
				$object->title		= esc_attr($post->post_title);
				$object->artist		= "dreaddymck";
				$object->rating		= 0;
		// 	 	$object->buy		= '#';
		// 	 	$object->price		= '0.00';
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
		
			wp_reset_postdata();
		
			exit( json_encode($response) ) ;
		
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
		
			// 	$regex   = '~
			//     (([^"\'])|^)            # start of string or attribute delimiter -> match 1
			//     (https?                 # http or https
			//         ://                 # separator
			//         .+/                 # domain plus /
			//         .+                  # file name at least one character
			//         \.                  # a dot
			//         (' . $formats . ')  # file suffixes
			//     )                       # complete URL -> match 3
			//     (([^"\'])|$)?           # end of string or attribute delimiter -> match 5
			//     ~imUx';                 # case insensitive, multi-line, ungreedy, commented
		
			$formats = "mp3";
		
			$regex   = '~(https?://.+/.+\.(' . $formats . '))(([^"\'])|$)?~imUx';
		
			preg_match_all( $regex, $str, $matches, PREG_PATTERN_ORDER );
		
			return $matches[0];
		
		}
		function _log($obj = '') {
		
			error_log( print_r($obj,1));
		}
	}
	new PlayListFromPostCls();
	
}