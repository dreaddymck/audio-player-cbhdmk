<?php
namespace DMCK_WP_MEDIA_PLUGIN;
trait _requests {
	public $path;
	function handle_requests($data){ return $this->requests($data); }
    function requests($data){
		$response = "{}";
		$opt = $data;
		if( is_object($opt) ){ $opt = $data['option']; }
        switch ($opt) {
			case "search":
				$response = $this->param_request($data);
				break;
			case "get_my_ip":
				$response = $this->get_my_ip();
				break;
            default:
        }
        return $response;
    }
	function obj_request($obj) {
		global $wpdb;
		$args = array(
			's'					=> !empty($obj->s) ? "/".urldecode($obj->s)  : "",
			'posts_per_page' 	=> !empty($obj->posts_per_page) ? $obj->posts_per_page : -1,
			'post_status'      	=> !empty($obj->publish) ? $obj->publish  : "publish",
			'orderby'          	=> !empty($obj->orderby) ? $obj->orderby  : "",
			'order'            	=> !empty($obj->order) ? $obj->order  : "",
			'tag'				=> !empty($obj->tag) ? $obj->tag  : "",
			'tag__in' 			=> !empty($obj->tag_in) ? array( $obj->tag_in ) : "", //array (id)
			'tag__not_in'		=> !empty($obj->tag_not_in) ? array( $obj->tag_not_in ) : "", //array (id)
			'tag_slug__and'		=> !empty($obj->tag_slug__and) ? array( $obj->tag_slug__and ) : "",
		);
		return $this->_requests_get_posts($args);
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
	}
	function _requests_get_posts($args){
		$posts 	    = get_posts( $args );
		$response   = $this->render_elements($posts);
		wp_reset_postdata();
		return($response);
	}
    function mysqli_query($query){
        $results = array();
        if(!$query){return $results;}
        $conn = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
        $resp = $conn->query($query);
        if( $resp instanceof \mysqli_result ) { while ($row = $resp->fetch_assoc()) { array_push($results, $row); } }
        $conn->close();
        return $results;        
    }	
	function query($sql){
		$conn = new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
		if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
		$resp       = $conn->query($sql);
		$results    = array();
		if( $resp instanceof \mysqli_result ){ $results = mysqli_fetch_all($resp); }
		$conn->close();
		return $results;
	}
	function media_activity_today($limit=0){
		$response = array();
		$json = $this->dmck_media_activity_today();
		if(!$json){return array();}
		usort($json, function($a, $b) {
			if( $b["count"] > $a["count"] ) return 1;
			if( $b["count"] < $a["count"] ) return -1;
			return ($b["time"] < $a["time"]) ? -1 : 1;
		});
		if($limit){ $json = array_slice($json,0,$limit); }		
		foreach($json as $key=>$value) {

			$param = (object) array('s' => $value["name"]);
			$p = json_decode($this->obj_request( $param ));

			foreach($p as $e){

				if(basename($e->mp3) == $value["name"]){

					$title = pathinfo($e->mp3, PATHINFO_FILENAME); 
					$title = preg_replace('/^\w*\s?.\-/', '', $title);					
					$date = date('m/d/Y h:i:s a', $value["time"]);		
					$json[$key]["title"] = $title;
					$json[$key]["date"] = $date;
					$json[$key]["ID"] = $e->ID;
					$json[$key]["mp3"] = $e->mp3;
					$json[$key]["cover"] = $e->cover;
					$json[$key]["permalink"] = $e->permalink;
					$json[$key]["wavformpng"] = $e->wavformpng;
					$json[$key]["tags"] = $e->tags;
					$json[$key]["moreinfo"] = $e->moreinfo;
				}
			}
		}
		return $json;
	}

	function render_elements($posts) {
		$response = array();
		$is_secure = $this->isSecure();
		foreach ( $posts as $post ) { 
			setup_postdata( $post );
			if(get_post_status($post->ID) != "publish" ){ continue; }
			$audio 		= $this->extract_embedded_media( $post->post_content );
			if(empty($audio[0])) { continue; }			
			foreach($audio as $a){			
				$a = urldecode($a);
				$object 				= new \stdClass();
				$object->ID		        = $post->ID;
				$object->mp3		    = $a;
				$object->wavformpng		= get_post_meta( $post->ID, 'dmck_wavformpng', true );
				$object->wavformpng	    = $object->wavformpng ? $object->wavformpng : $this->waveformpng($a);
				$object->wavformjson	= $this->wavformjson($a);
				if($is_secure){
					$object->mp3		    = preg_replace("/^http:/i", "https:", $object->mp3);
					$object->wavformpng	    = preg_replace("/^http:/i", "https:", $object->wavformpng);
					$object->wavformjson	= preg_replace("/^http:/i", "https:", $object->wavformjson);
				}
				$title = pathinfo($a, PATHINFO_FILENAME);
				$media_filename_regex = esc_attr( get_option('media_filename_regex') ); 
				if($media_filename_regex){ 
					$title = preg_replace($media_filename_regex, '', $title); // /^\w*\s?.\-/i /dreaddymck.?[.*\-]\s?/i
				}				
				$object->title		= esc_attr($title);
				$object->rating		= 0;
				$object->cover		= $this->fetch_the_post_thumbnail_src( $post );
				$object->permalink	= get_permalink( $post->ID );
				$object->moreinfo	= get_option('moreinfo') ? get_option('moreinfo') : "";
				$object->playlist_thumb = $object->cover;
				$object->tags 		=  implode( ', ', wp_get_post_tags( $post->ID, array( 'fields' => 'names' )) );
				array_push( $response, $object );
			}
		}
		return json_encode($response);
    }
	function waveformpng($str) {
		return preg_replace('/\.mp3$/', '.wavform.png', $str);
	}
	function wavformjson($str) {
		return preg_replace('/\.mp3$/', '.wavform.json', $str);
	}
	function fetch_the_post_thumbnail_src($post)
	{
		$img = get_the_post_thumbnail_url($post->ID, "thumbnail");
		if(!$img){
			$dom = new \DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML($post->post_content);
			$xpath = new \DOMXpath($dom);
			$src = $xpath->query("//img/attribute::src");			
			if($src->length){
				foreach( $src as $s ) {
					$img = $s->nodeValue;
					break;
				}
			}
		}
		// return (preg_match('~\bsrc="([^"]++)"~', $img, $matches)) ? $matches[1] : esc_attr( get_option('default_album_cover') );
		return $img ? $img : esc_attr( get_option('default_album_cover'));
	}
	function extract_embedded_media($str) { //renamed from fetch_audio_from_string
		$matches = array();

		// This REGEX solution fails on classic html pages/posts imported from blogspot
		//
		# See http://en.wikipedia.org/wiki/Audio_file_format
		# Adjust the list to your needs
		// 	$suffixes = array (
		// 		'3gp', 'aa3', 'aac', 'aiff', 'ape', 'at3', 'au',  'flac', 'm4a', 'm4b',
		// 		'm4p', 'm4r', 'm4v', 'mpc',  'mp3', 'mp4', 'mpp', 'oga',  'ogg', 'oma',
		// 		'pcm', 'tta', 'wav', 'wma',  'wv',
		// 	);
		//	$formats = join( '|', $suffixes );
		//  $formats = "mp3";
		//  $regex   = '~
		//  (([^"\'])|^)            # start of string or attribute delimiter -> match 1
		//  (https?                 # http or https
		//     ://                 # separator
		//     .+/                 # domain plus /
		//     .+                  # file name at least one character
		//     \.                  # a dot
		//     (' . $formats . ')  # file suffixes
		//  )                       # complete URL -> match 3
		//  (([^"\'])|$)?           # end of string or attribute delimiter -> match 5
		//  ~imUx';                 # case insensitive, multi-line, ungreedy, commented
		//
		//
		// $regex   = '~(https?://.+/.+\.(' . $formats . '))(([^"\'])|$)?~imUx';
		// $regex = '~((?:\'|")https?://\w.+\.(mp3)(?:\'|"))~';
		// preg_match_all( $regex, $str, $matches, PREG_PATTERN_ORDER );

		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($str);
		$xpath = new \DOMXpath($dom);
		$src = $xpath->query("//iframe/attribute::src | //source/attribute::src | //audio/attribute::src");
		if($src->length){
			foreach( $src as $s ) {
				$value = $s->nodeValue;
				array_push($matches,$value);
			}
		}
		return $matches;
	}
	function playlist_data_get(){
		$arr = array();
		if( empty( get_option("playlist_config") ) ){ return; }
		$arr["playlist_json"] = json_decode(get_option("playlist_config"));
		$arr["top_10_json"] = $this->media_activity_today($limit=10);
		return $arr;
	}	
}