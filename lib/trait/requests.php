<?php
namespace DMCK_WP_MEDIA_PLUGIN;
trait _requests {
	public $path;
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
			case "upload":
				$response = $this->upload();
				break;
			case "export_tables":
				$response = $this->export_tables();
				break;
			case "stats_data":
				$response = $this->stats_data();
				break;	
			case "todays_top_data":
				$response = $this->todays_top_data();
				break;							
            default:
        }
        return $response;
    }
	function stats_data(){

		$type = isset($_POST["type"]) ? $_POST["type"] : "";
		$to = isset($_POST["to"]) ? $_POST["to"] : date("m/d/Y");
		$from = isset($_POST["from"]) ? $_POST["from"] : date("m/d/Y");
		$ids =  isset($_POST["value"]) ? $_POST["value"] : "";
		$response = (object) array(
			"labels"=>array(),
			"data"=>array(),
		);
		if(is_array($ids)){
			foreach ( $ids as $key => $value ) {
				$json = (object) array(
					"type" => $type,
					"value" => $value,
					"to" => $to,
					"from" => $from
				);				
				$resp = $this->get_chart_json($json);
				if(!$resp){continue;}
				array_push($response->data, $resp);
				$response->labels = array_unique(array_merge($response->labels, $resp->labels));							
			}
			if(!empty($response->labels)){
				usort($response->labels, function ($a, $b) {
					return strtotime($a) - strtotime($b);
				});
			}
		}		
		if($this->debug){ echo "array Length: ". count($response) ." | ".__FUNCTION__." | ".$this->memory_usage()."\n\r"; }
		return json_encode($response);
	}
	function todays_top_data(){
		$chart_array = array();
		$chart_title_array = array();
		$ids = array();
		if( get_option('charts_enabled') ){
			$access_log_activity = $this->media_activity_today();
			if(is_array($access_log_activity)){
				foreach($access_log_activity as $a){
					$response = $this->get_chart_json_mths($a["ID"],12);
					array_push($chart_array, $response);
					array_push($ids,$a["ID"]);
					$chart_title_array = array_unique(array_merge($chart_title_array, $response->labels));							
				}
				usort($chart_title_array, function ($a, $b) {
					return strtotime($a) - strtotime($b);
				});								
			}
		}
		return array(
			"labels" => $chart_title_array,
			"datasets" => $chart_array,
			"ids" => '["' . implode('","', $ids) . '"]' //display purpose only
		);	
	}
	function upload(){
		$response  = (object) array();
		$response->status = false;
		foreach($_FILES as $file){
			$response->location = "/tmp/".basename($file['name']);
			$response->status = move_uploaded_file($file['tmp_name'], $response->location);
		}
		return json_encode($response);
	}
	function obj_request_args($obj){
		return array(
			's'					=> isset($obj->s) && !empty($obj->s) ? "/".urldecode($obj->s)  : null,
			'posts_per_page' 	=> isset($obj->posts_per_page) && !empty($obj->posts_per_page) ? $obj->posts_per_page : -1,
			'post_status'      	=> isset($obj->publish) && !empty($obj->publish) ? $obj->publish  : "publish",
			'orderby'          	=> isset($obj->orderby) && !empty($obj->orderby) ? $obj->orderby  : null,
			'order'            	=> isset($obj->order) && !empty($obj->order) ? $obj->order  : null,
			'tag'				=> isset($obj->tag) && !empty($obj->tag) ? $obj->tag  : null,
			'post__in'			=> isset($obj->post__in) && !empty($obj->post__in) ? $obj->post__in  : null,
			'tag_id'			=> isset($obj->tag_id) && !empty($obj->tag_id) ? $obj->tag_id  : null,
			'tag__and'			=> isset($obj->tag__and) && !empty($obj->tag__and) ? $obj->tag__and  : null,
			'tag__in' 			=> isset($obj->tag_in) && !empty($obj->tag_in) ? $obj->tag_in : null,
			'tag__not_in'		=> isset($obj->tag_not_in) && !empty($obj->tag_not_in) ? $obj->tag_not_in : null,
			'tag_slug__and'		=> isset($obj->tag_slug__and) && !empty($obj->tag_slug__and) ? $obj->tag_slug__and : null,
			'tag_slug__in'		=> isset($obj->tag_slug__in) && !empty($obj->tag_slug__in) ? $obj->tag_slug__in : null,
			'cat'				=> isset($obj->cat) && !empty($obj->cat) ? $obj->cat : null,
			'category_name'		=> isset($obj->category_name) && !empty($obj->category_name) ? $obj->category_name : null,
			'category__and'		=> isset($obj->category__and) && !empty($obj->category__and) ? $obj->category__and : null,
			'category__in'		=> isset($obj->category__in) && !empty($obj->category__in) ? $obj->category__in : null,
			'category__not_in'	=> isset($obj->category__not_in) && !empty($obj->category__not_in) ? $obj->category__not_in : null,
		);
	}
	function obj_request($obj) {
		$args = $this->obj_request_args($obj);
		return $this->_requests_get_posts($args);
	}
	function param_request($data){
		$params 				= $data->get_params();
		$response				= $this->obj_request((object)$params);
		return $response;
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
			$p = json_decode( $this->obj_request( $param ));

			foreach($p as $e){
				if(strcasecmp(basename($e->mp3), $value["name"]) == 0){
					$title = pathinfo($e->mp3, PATHINFO_FILENAME);
					$media_filename_regex = esc_attr( get_option('media_filename_regex') );
					if($media_filename_regex){
						$title = preg_replace($media_filename_regex, '', $title);
					}
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
					$title = preg_replace($media_filename_regex, '', $title);
				}
				$object->title		= esc_attr($title);
				$object->rating		= 0;
				$object->cover		= $this->fetch_the_post_thumbnail_src( $post );
				$object->permalink	= get_permalink( $post->ID );
				$object->moreinfo	= get_option('moreinfo') ? get_option('moreinfo') : "";
				$object->playlist_thumb = $object->cover;
				$object->tags 		=  implode( ', ', wp_get_post_tags( $post->ID, array( 'fields' => 'names' )) );
				$object->post_date  = $post->post_date;
				$object->post_date_gmt  = $post->post_date_gmt;

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
		return $img ? $img : esc_attr( get_option('default_album_cover'));
	}
	function extract_embedded_media($str) { //renamed from fetch_audio_from_string
		$matches = array();
		if(!$str){return $matches;}
		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($str);
		$xpath = new \DOMXpath($dom);
		// $src = $xpath->query("//iframe/attribute::src | //source/attribute::src | //audio/attribute::src");
		$src = $xpath->query("//source/attribute::src | //audio/attribute::src");

		if($src->length){
			foreach( $src as $s ) {
				array_push($matches,trim($s->nodeValue));
			}
		}
		return $matches;
	}
	function playlist_data_get(){
		$playlist_data = array();
		if( empty( get_option("playlist_config") ) ){ return; }
		$playlist_data["playlist_json"] = json_decode(get_option("playlist_config"));
		$playlist_data["top_10_json"] = array();
		foreach($playlist_data["playlist_json"] as $p) { 
			if(isset($p->top_request) && filter_var($p->top_request, FILTER_VALIDATE_BOOLEAN)){
				$playlist_data["top_10_json"] = $this->media_activity_today($p->top_count);
			}
		}
		return $playlist_data;
	}
}