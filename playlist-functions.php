<?php
// error_reporting(E_ALL);
// ini_set('display_errors', true);

require_once( "../../../wp-load.php");
require_once( "playlist_utilities_class.php");

if (!class_exists("PlayListFromPostCls")) {

	class PlayListFromPostCls extends playlist_utilities_class {

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
			
			$posts 	= get_posts( $args );

			$response   = $this->render_elements($posts);
			
			wp_reset_postdata();
		
			exit( $response ) ;		
		}
	}
	new PlayListFromPostCls();
	
}