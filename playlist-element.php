<?php
// error_reporting(E_ALL);
// ini_set('display_errors', true);

require_once( "../../../wp-load.php");
require_once( "playlist_utilities_class.php");

if (!class_exists("PlayListElement")) {

	class PlayListElement extends playlist_utilities_class  {

		public $debug;
		public $path;
		
		function __construct()
		{
			$this->get_request();
			$this->fetch();
		}
		function get_request()
		{
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$this->debug			= isset($_POST["debug"]) ? htmlspecialchars($_POST["debug"] ) : false;
				$this->path			    = isset($_POST["path"]) ? htmlspecialchars($_POST["path"] ) : "";
				
			}else{
				$this->debug			= isset($_GET["debug"]) ? htmlspecialchars($_GET["debug"] ) : false;
				$this->path			    = isset($_GET["path"]) ? htmlspecialchars($_GET["path"] ) : "";
			}
		}
		function fetch() {
			
			global $wpdb;
			
			$args = array(
					'numberposts' 		=> -1,
					'post_status'      	=> 'publish'
			);
			
			$posts 	    = get_posts( $args );
			
			// error_log(print_r($posts,1));
			// error_log("*******************************");
			// error_log(var_export($wpdb->last_query, TRUE));			

			$response   = $this->render_elements($posts);
			
			//error_log(print_r($response,1));

			wp_reset_postdata();

			exit($response);
		}
	}
	new PlayListElement();
	
}