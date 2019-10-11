<?php
/*
	Extract custom information from posts
*/
if (!class_exists("playlistObject")) {
	class playlistObject extends WPAudioPlayerCBHDMK  {
		public $debug;
		public $posts_per_page;
		public $tag;		
		function __construct()
		{            
			add_action( 'rest_api_init', function () {
				register_rest_route( $this->plugin_slug.'/v1','playlist' ,array(
					'methods'   =>  'POST',
					'callback'  =>  array($this, 'init'),
					// 'permission_callback' => function() { return current_user_can('edit_posts'); }					
				));
			});
			add_action( 'rest_api_init', function () {
				register_rest_route( $this->plugin_slug.'/v'.$this->set_plugin_version(),'playlist' ,array(
					'methods'   =>  'POST',
					'callback'  =>  array($this, 'init'),
					// 'permission_callback' => function() { return current_user_can('edit_posts'); }					
				));
			});			
        }
        function init(){
            $this->args();
			return $this->query();
        }
		function args()
		{	
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$this->debug		= isset($_POST["debug"]) ? htmlspecialchars($_POST["debug"] ) : false;
				$this->posts_per_page	= isset($_POST["posts_per_page"]) ? htmlspecialchars($_POST["posts_per_page"] ) : 1;
				$this->post_status	= isset($_POST["post_status"]) ? htmlspecialchars($_POST["post_status"] ) : "published";
				$this->tag			= isset($_POST["tag"]) ? htmlspecialchars($_POST["tag"] ) : "";	
				$this->orderby		= isset($_POST["orderby"]) ? htmlspecialchars($_POST["orderby"] ) : ""; 		
				$this->order		= isset($_POST["order"]) ? htmlspecialchars($_POST["order"] ) : ""; 
				$this->tag		= isset($_POST["tag"]) ? htmlspecialchars($_POST["tag"] ) : "";	
				$this->tag_in		= isset($_POST["tag_in"]) ? htmlspecialchars($_POST["tag_in"] ) : "";	
				$this->tag_not_in		= isset($_POST["tag_not_in"]) ? htmlspecialchars($_POST["tag_not_in"] ) : "";		
			}				
		}
		function query() {			
			global $wpdb;			
			$args = array(
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
	}
	new playlistObject();	
}