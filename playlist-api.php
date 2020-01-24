<?php
/*
	Extract custom information from posts
*/

if (!class_exists("dmck_playlist_api")) {
	class dmck_playlist_api extends dmck_audioplayer {
		public $debug;
		public $posts_per_page;
		public $tag;		
		function __construct()
		{            
			$this->set_plugin_version();
			add_action( 'rest_api_init', function () {
				register_rest_route( $this->plugin_slug.'/v'.$this->plugin_version,'api/(?P<option>[\w]+)' ,array(
					'methods'   =>  WP_REST_Server::READABLE,
					'callback'  =>  array($this, 'activity'),
					'args' => [ 'option' ],										
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
				case "search":
					$response = $this->search($data);
					break;				
				case "get":
					$response = $this->accesslog_activity_get();
					break;
				case "get-today":
					$response = $this->accesslog_activity_get_today();
					break;
				case "wavform":
					$response = $this->wavform();
					break; 					                
				default:
			}
			return $response;
		}
		function upload(){
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