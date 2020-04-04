<?php
trait _requests {
  
	function __construct(){}
	function handle_requests($data){ return $this->requests($data); }	
    function requests($data){		
		$response = "{}";
		$opt = $data;
		if( is_object($opt) ){ $opt = $data['option']; }		
        switch ($opt) {
			case "search":
				$response = $this->param_request($data);
				break;				
			case "put":
                $response = $this->accesslog_activity_put();
                break;
            case "get":
                $response = $this->accesslog_activity_get();
                break;
            case "get-week":
                $response = $this->accesslog_activity_get_week();
                break;    
            case "get-month":
                $response = $this->accesslog_activity_get_month();
                break;                                
            case "purge":
                $response = $this->accesslog_activity_purge();
                break;
            case "wavform":
                $response = _wavform::wavform();        
                break;                                
            default:
        }
        return $response;
    }	
	function obj_request($obj) {
		global $wpdb;		
		$args = array(
			's'					=> !empty($obj->s) ? urldecode($obj->s)  : "",
			'posts_per_page' 	=> !empty($obj->posts_per_page) ? $obj->posts_per_page : -1,
			'orderby'          	=> !empty($obj->orderby) ? $obj->orderby  : "",
			'order'            	=> !empty($obj->order) ? $obj->order  : "",
			'post_status'      	=> !empty($obj->publish) ? $obj->publish  : "publish",
			'tag'				=> !empty($obj->tag) ? $obj->tag  : "", 
			'tag__in' 			=> !empty($obj->tag_in) ? array( $obj->tag_in ) : "", //array (id)
			'tag__not_in'		=> !empty($obj->tag_not_in) ? array( $obj->tag_not_in ) : "", //array (id)
			'tag_slug__and'		=> !empty($obj->tag_slug__and) ? array( $obj->tag_slug__and ) : "",	
		);
		return $this->_requests_get_posts($args);
		// $posts 	= get_posts( $args );
		// $response   = $this->render_elements($posts);			
		// wp_reset_postdata();
		// return $response;					
	}
	function param_request($data){
		global $wpdb;
		$params 				= $data->get_params();
		$args = array(
			's'					=> !empty($params["s"]) ? urldecode($params["s"] ) : "",
			'posts_per_page' 	=> !empty($params["posts_per_page"]) ? $params["posts_per_page"]  : -1,
			'post_status'   	=> !empty($params["post_status"]) ? $params["post_status"] : "published",
			'orderby'          	=> !empty($params["orderby"]) ? $params["orderby"] : "",
			'order'            	=> !empty($params["order"]) ? $params["order"] : "",
			'tag'				=> !empty($params["tag"]) ? $params["tag"] : "",
			'tag__in' 			=> !empty($params["tag_in"]) ? $params["tag_in"] : "",
			'tag__not_in'		=> !empty($params["tag_not_in"]) ? $params["tag_not_in"] : "",
			'tag_slug__and'		=> !empty($params["tag_slug__and"]) ? $params["tag_slug__and"] : "",						
		);
		return $this->_requests_get_posts($args);			
		// $posts 	    = get_posts( $args );
		// $response   = $this->render_elements($posts);
		// wp_reset_postdata();
		// return($response);
	}
	function _requests_get_posts($args){
		$posts 	    = get_posts( $args );
		$response   = $this->render_elements($posts);
		wp_reset_postdata();
		return($response);
	}	
	function query($sql){
		if($this->debug){
			$this->_log($sql);
		}
		$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
		if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
		$resp       = $conn->query($sql);
		$results    = array();
		if( $resp instanceof mysqli_result ){ $results = mysqli_fetch_all($resp); }
		$conn->close();
		return $results;	
	}
	function top_count_get(){
		/**
		 * playlist generated from access log data
		 */		
		$json = json_decode($this->accesslog_activity_get(), true);
		if(!$json){return array();}
		usort($json, function($a, $b) {
			if( $b["count"] > $a["count"] ) return 1;
			if( $b["count"] < $a["count"] ) return -1;
			return ($b["time"] < $a["time"]) ? -1 : 1;
		});
		$json = array_slice($json,0,10);
		foreach($json as $key=>$value) { 
			
			$param = (object) array('s' => $value["name"]);
			$p = json_decode($this->obj_request( $param ));

			$title = !empty($p[0]->title) ? $p[0]->title : urldecode($value["name"]);
			$date = date('m/d/Y h:i:s a', $json["time"]);
			
			$json[$key]["title"] = $title;
			$json[$key]["date"] = $date;
			$json[$key]["ID"] = $p[0]->ID;						
			$json[$key]["mp3"] = $p[0]->mp3;
			$json[$key]["cover"] = $p[0]->cover;
			$json[$key]["artist"] = $p[0]->artist;
			$json[$key]["permalink"] = $p[0]->permalink;
			$json[$key]["wavformpng"] = $p[0]->wavformpng;
			$json[$key]["tags"] = $p[0]->tags;
			$json[$key]["moreinfo"] = $p[0]->moreinfo;
			
		}
		return $json;
	}
	function playlist_data_get(){
		$arr = array();
		/**
		 * playlist generated from json configuration data
		 */
		if( empty( get_option("playlist_config") ) ){ return; }
		$arr["playlist_json"] = json_decode(get_option("playlist_config"));
		$arr["top_10_json"] = $this->top_count_get();
		return $arr;		
	}
	function render_elements($posts) {		
		$response 	= [];		
		foreach ( $posts as $post ) : setup_postdata( $post );			
			$object 	= new stdClass();
			$audio 		= $this->fetch_audio_from_string( $post->post_content );		
			if(! !empty($audio[0])) { continue; }
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
}