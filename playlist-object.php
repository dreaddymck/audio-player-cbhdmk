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
				$this->posts_per_page	= isset($_POST["posts_per_page"]) ? htmlspecialchars($_POST["posts_per_page"] ) : "";
				$this->tag			= isset($_POST["tag"]) ? htmlspecialchars($_POST["tag"] ) : "";			
			}else{
				$this->debug		= isset($_GET["debug"]) ? htmlspecialchars($_GET["debug"] ) : false;
				$this->numberposts	= isset($_GET["posts_per_page"]) ? htmlspecialchars($_GET["posts_per_page"] ) : "";
				$this->tag			= isset($_GET["tag"]) ? htmlspecialchars($_GET["tag"] ) : "";
			}					
		}
		function query() {			
			global $wpdb;			
			$args = array(
                'posts_per_page' 	=> $this->posts_per_page ? $this->posts_per_page : 1,
				'post_status'   	=> 'publish',
				'tag'           	=> $this->tag,
				// 'tax_query' 		=> array(
				// 	array(
				// 		'taxonomy' => 'post_tag',
				// 		'field'    => 'name',
				// 		'terms'    => $this->tag,
				// 	),
				// ),				
			);			
			$posts 	    = get_posts( $args );
			$response   = $this->render_elements($posts);
			wp_reset_postdata();
			return($response);
		}
	}
	new playlistObject();	
}