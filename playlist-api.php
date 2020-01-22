<?php
/*
	Extract custom information from posts
*/
error_reporting(E_ALL);
ini_set("display_errors","On");
if (!class_exists("dmck_playlist_api")) {
	class dmck_playlist_api extends dmck_audioplayer {
		public $debug;
		public $posts_per_page;
		public $tag;		
		function __construct()
		{            
			$this->set_plugin_version();
			add_action( 'rest_api_init', function () {
				register_rest_route( $this->plugin_slug.'/v'.$this->plugin_version,'activity/(?P<option>[\w]+)' ,array(
					'methods'   =>  WP_REST_Server::READABLE,
					'callback'  =>  array($this, 'activity'),
					'args' => [ 'option' ],										
				));	
				register_rest_route( $this->plugin_slug.'/v'.$this->plugin_version,'playlist' ,array(
					'methods'   =>  WP_REST_Server::READABLE,
					'callback'  =>  array($this, 'playlist'),					
				));							
				register_rest_route( $this->plugin_slug.'/v'.$this->plugin_version,'upload' ,array(
					'methods'   =>  WP_REST_Server::CREATABLE,
					'callback'  =>  array($this, 'upload'),
					'permission_callback' => function() { return current_user_can('edit_posts'); }						
				));						
			});
	
		}
		function activity($data){		
			$response = "";
			switch ($data['option']) {
				case "get":
					$response = $this->accesslog_activity_get();
					break;
				case "get-today":
					$response = $this->accesslog_activity_get_today();
					break;
				case "wavform":
					$response = $this->wavform($data);
					break; 					                
				default:
			}
			return $response;
		}
		function playlist($data){
			global $wpdb;
			$params 				= $data->get_params();
			$this->s				= isset($params["s"]) ? htmlspecialchars($params["s"] ) : "";
			$this->posts_per_page	= isset($params["posts_per_page"]) ? htmlspecialchars($params["posts_per_page"] ) : 1;
			$this->post_status		= isset($params["post_status"]) ? htmlspecialchars($params["post_status"] ) : "published";
			$this->tag				= isset($params["tag"]) ? htmlspecialchars($params["tag"] ) : "";	
			$this->orderby			= isset($params["orderby"]) ? htmlspecialchars($params["orderby"] ) : ""; 		
			$this->order			= isset($params["order"]) ? htmlspecialchars($params["order"] ) : ""; 
			$this->tag				= isset($params["tag"]) ? htmlspecialchars($params["tag"] ) : "";	
			$this->tag_in			= isset($params["tag_in"]) ? htmlspecialchars($params["tag_in"] ) : "";	
			$this->tag_not_in		= isset($params["tag_not_in"]) ? htmlspecialchars($params["tag_not_in"] ) : "";
			$args = array(
				's'					=> $this->s,
                'posts_per_page' 	=> $this->posts_per_page,
				'post_status'   	=> $this->post_status,
				'tag'           	=> $this->tag,
				'orderby'          	=> $this->orderby,
				'order'            	=> $this->order,
				'tag'				=> $this->tag,
				'tag__in' 			=> $this->tag_in,
				'tag__not_in'		=> $this->tag_not_in,					
			);			
			$posts 	    = get_posts( $args );
			$response   = $this->render_elements($posts);
			wp_reset_postdata();
			return($response);
		}
		function upload(){
			// var_dump($_FILES);
			$response = array();
			foreach($_FILES as $file){
				$path = esc_attr( get_option('media_root_path') );
				$path = preg_replace('{/$}', '', $path);
				array_push($response, move_uploaded_file($file['tmp_name'], $path."/".basename($file['name'])));
			}
			return $response;
		}		
	}
	new dmck_playlist_api();	
}