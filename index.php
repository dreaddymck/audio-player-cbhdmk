<?php
/*
Plugin Name: (DMCK) audio player
Plugin URI: dreaddymck.com
Description: Just another Wordpress audio player. This plugin will add the first mp3 link embedded in each active post content into a playlist. shortcode [dmck-audioplayer]
Version: 1.0
Author: dreaddymck
Author URI: dreaddymck.com
License: MIT
*/

// error_reporting(E_ALL);
// ini_set("display_errors","On");

require_once( "playlist_utilities_class.php");

if (!class_exists("WPAudioPlayerCBHDMK")) {

	class WPAudioPlayerCBHDMK  extends playlist_utilities_class {

		public $plugin_title 			= '(DMCK) audio player';
		public $plugin_slug				= 'dmck_audioplayer';
		public $plugin_settings_group 	= 'dmck-audioplayer-settings-group';
		public $shortcode				= "dmck-audioplayer";
		public $adminpreferences 		= array('adminpreferences','favicon','default_album_cover', 'moreinfo', 'facebook_app_id	');
		public $userpreferences 		= array('userpreferences');
		
		public $plugin_url;
		public $theme_url;
		public $github_url				= "https://github.com/dreaddy/audio-player-cbhdmk";
		public $wpdb;
		
		public $tag_in	= null;
		public $tag_not_in	= null;
		
		function __construct() {
			
			global $wpdb;
			$this->wpdb = $wpdb;
			
			$this->plugin_url 	= plugins_url("/",__FILE__);
			$this->theme_url	= dirname( get_bloginfo('stylesheet_url') );

			register_activation_hook( __FILE__, array($this, 'register_activation' ) );

			add_action( 'init', array( $this, 'autoplay'));
			add_action( 'init', array( $this, 'register_shortcodes'));
			add_action( 'admin_init', array( $this, 'register_settings') );
			add_action( 'admin_menu', array( $this, 'admin_menu' ));
			
			add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_setup'), 999);

			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
			add_action( 'wp_enqueue_scripts', array($this, 'user_scripts') );
			
			add_action( 'wp_head', array($this, 'head_hook') );
			add_action( 'wp_head', array($this,'insert_meta_to_head'), 5 );
			add_action( 'login_head', array($this, 'head_hook') );
			add_action( 'admin_head', array($this, 'head_hook') );
			
			add_filter('get_the_excerpt', array($this,'the_exerpt_filter'));
			add_filter('the_content', array($this,'content_toggle_https'));
			add_filter('language_attributes', array($this,'set_doctype'));

			

		}
		function autoplay()
		{
			
			if( isset( $_SERVER['REQUEST_METHOD'] ) ){
	
				if ($_SERVER['REQUEST_METHOD'] === 'POST') {
					$this->autoplay			= isset($_POST["autoplay"]) ? htmlspecialchars($_POST["autoplay"] ) : "";
					$this->auto_play		= isset($_POST["auto_play"]) ? htmlspecialchars($_POST["auto_play"] ) : "";
					$this->relatedposts		= isset($_POST["relatedposts"]) ? htmlspecialchars($_POST["relatedposts"] ) : "";
				}else{
					$this->autoplay			= isset($_GET["autoplay"]) ? htmlspecialchars($_GET["autoplay"] ) : "";
					$this->auto_play		= isset($_GET["auto_play"]) ? htmlspecialchars($_GET["auto_play"] ) : "";
					$this->relatedposts		= isset($_GET["relatedposts"]) ? htmlspecialchars($_GET["relatedposts"] ) : "";					
				}

				if($this->autoplay || $this->auto_play){

					list($path, $qs) = explode("?", $_SERVER["REQUEST_URI"], 2);

					$path		= trim($path, "/");
					$the_array 	= explode('/', $path);
					$slug 		= $the_array[ count($the_array) - 1 ];					

					$post 		= $this->get_post_by_slug($slug);
					$img 		= $this->fetch_the_post_thumbnail_src( get_the_post_thumbnail($post->ID, "large") );
					$desc 		= $this->excerpt($post->post_content);
					
					$matches 	= $this->fetch_audio_from_string( $post->post_content );
					if($this->isSecure()){
						$matches[0] = preg_replace("/^http:/i", "https:", $matches[0]);
					}

					$html 		= <<<EOF

<!DOCTYPE html><html>
<head>
	<title>{$post->post_title}</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
	.container {
		width		: 400px; 
		height		: 640px;
		text-align	: center;
	}
	</style>
</head>
<body>
<div class="container">

	<h2>DreaddyMck</h2>
	
	<h1>{$post->post_title}</h1>
	
	<audio preload="auto" controls="controls" autoplay="autoplay">
		<source src="{$matches[0]}" type="audio/mpeg" />
	</audio>
	
	<p><img src="{$img}" width="100%" height="auto"></p>
	
</div>
</body></html>

EOF;
					exit($html);
				}
			}
			return;	
		}		

		function get_post_by_slug($slug){

			$posts = get_posts(array(
					'name' => $slug,
					'post_type'   => 'post',
					'post_status' => 'publish',
					'numberposts' => 1
			));
			
	
			return $posts[0];
		}

		//Adding the Open Graph in the Language Attributes
		function set_doctype( $output ) {

			$output .= ' xmlns:og="http://opengraphprotocol.org/schema/" ';

			$facebook_app_id = esc_attr( get_option('facebook_app_id') );	
			if($facebook_app_id){
				$output .= '  xmlns:fb="http://www.facebook.com/2008/fbml"';
			}

			return $output;
		}		

		function insert_meta_to_head() {

			global $post, $wp;			

			$title 			= $post->post_title;
			$desc 			= get_bloginfo('description');			
			$ogtype			= "article";
			$twitter_type	= "summary";
			$url			= home_url( $wp->request );

			if ( is_singular() ) //if it is not a post or a page
			{
				$matches 	= $this->fetch_audio_from_string( $post->post_content );
				$url		= get_permalink();
				$desc 		= $this->excerpt($post->post_content);

				if($matches[0]){

					$matches[0] = esc_url( $matches[0] );

					if($this->isSecure()){
						$matches[0] = preg_replace("/^http:/i", "https:", $matches[0]);
						echo '<meta property="og:audio:secure_url" content="'. $matches[0] .'" />'."\r\n";
					}				
					echo '<meta property="og:audio" 		content="'.$matches[0].'" />'."\r\n";
					echo '<meta property="og:audio:type" 	content="audio/mpeg" />'."\r\n";
					echo '<meta property="og:audio:title" 	content="'.$title.'" />'."\r\n";
					echo '<meta property="og:audio:artist" 	content="DreaddyMck" />'."\r\n";
					echo '<meta property="og:audio:album" 	content="Projects" />'."\r\n";

					$ogtype			= "music.song";
					$twitter_type	= "player";

					echo '<meta name="twitter:player" content="'.$url.'" />'."\r\n";
					echo '<meta name="twitter:player:stream" content="'.$matches[0].'" />'."\r\n";
					echo '<meta name="twitter:player:stream:content_type" content="audio/mpeg"/>'."\r\n";;
					echo '<meta name="twitter:player:width" content="100%" />'."\r\n";
					echo '<meta name="twitter:player:height" content="auto" />'."\r\n";
				}
			}
			if( is_archive()  ){
				$tmp		= get_the_archive_description();
				$desc		= $tmp ? $tmp : $desc;
				$title		= get_the_archive_title();
				$ogtype		= "blog.archive";
			}

			echo '<meta property="og:title" 		content="' . $title . '"/>'."\r\n";
			echo '<meta property="og:type" 			content="'.$ogtype.'"/>'."\r\n";
			echo '<meta property="og:url" 			content="' . $url . '"/>'."\r\n";
			echo '<meta property="og:site_name" 	content="' . get_bloginfo( 'name' ) . '"/>'."\r\n";
			echo '<meta property="og:description" 	content="'.$desc.'" />'."\r\n";

			$img = $this->fetch_the_post_thumbnail_src( get_the_post_thumbnail($post->ID, "medium") );
			if($img){
				$img = esc_url( $img );
				echo '<meta property="og:image" 	content="' . $img . '"/>'."\r\n";
				echo '<meta name="twitter:image" 	content="'. $img .'" />'."\r\n";	
			}

			echo '<meta name="twitter:title" 		content="' . $title . '"/>'."\r\n";
			echo '<meta name="twitter:card" 		content="' .$twitter_type.'" />'."\r\n";
			echo '<meta name="twitter:site" 		content="@dreaddymck" />'."\r\n";
			echo '<meta name="twitter:creator" 		content="@dreaddymck" />'."\r\n";
			echo '<meta name="twitter:description" 	content="'.$desc.'" />'."\r\n";

			$facebook_app_id = esc_attr( get_option('facebook_app_id') );	
			if($facebook_app_id){

				echo '<meta property="fb:app_id" content="'.$facebook_app_id.'" />';

			}
			
		}

		function excerpt($text){

			$text = strip_shortcodes( $text );
			$text = apply_filters( 'the_content', $text );
			$text = str_replace(']]>', ']]&gt;', $text);
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );	

			return $text;
		}
				
	
		function content_toggle_https($content){
			
			$site_url = get_site_url();
			
			if( $this->isSecure() ){			
				
				$secure_url		= preg_replace( "/^http:/i", "https:", $site_url );
				$insecure_url	= preg_replace( "/^https:/i", "http:", $site_url );
	
				$pattern 		= "/" .preg_quote($insecure_url, '/') . "/i";
	
				$content 	= preg_replace( $pattern, $secure_url, $content );

			}

			return $content;
		}
		function the_exerpt_filter($param) {
			
			$param = preg_replace('/(\[.*\])/', "", $param);
			$param = preg_replace('/(Sorry, your browser doesn\'t support HTML5 audio\.|Sorry, your browser doesn&#8217;t support HTML5 audio.)/i', "", $param);
			
			return $param;
		}
		function register_activation($options){}
		function register_shortcodes(){
			add_shortcode( $this->shortcode, array( $this, 'include_file') );
		}
		function register_settings() {
			foreach($this->adminpreferences as $settings ) {
				register_setting( $this->plugin_settings_group, $settings );
			}
			foreach($this->userpreferences as $settings ){
				register_setting( $this->plugin_settings_group, $settings );
			}
		}

		function admin_menu()
		{
			$this->settings_page = add_options_page(
					$this->plugin_title,
					$this->plugin_title,
					'read',
					$this->plugin_slug,
					array( $this, 'admin_menu_include')
			);
		}
		public function register_plugin_styles() {
			wp_register_style( 'bootstrap.min.css',  $this->plugin_url . "css/bootstrap.min.css");
			wp_enqueue_style( 'bootstrap.min.css' );
			wp_register_style( 'jquery-ui.min.css',  $this->plugin_url . "plugins/jquery-ui-1.12.1/jquery-ui.min.css");
			wp_enqueue_style( 'jquery-ui.min.css' );
		}			
		function admin_scripts($hook_suffix) {
			
			if ( $this->settings_page == $hook_suffix ) {
				
				$this->register_plugin_styles();
				$this->shared_scripts();
				$this->localize_vars();

				wp_enqueue_style( 'admin.css',  $this->plugin_url . "admin/admin.css");
				wp_enqueue_script( 'admin.js', $this->plugin_url . 'admin/admin.js', array( 'jquery' ), '1.0.0', true );
			}
		}
		function user_scripts() {
			
			if( $this->has_shortcode( $this->shortcode ) ) {}
		
			$this->shared_scripts();	
			$this->localize_vars();
			
			wp_enqueue_script( 'jquery-ui.min.js', $this->plugin_url . 'plugins/jquery-ui-1.12.1/jquery-ui.js', array( 'jquery' ), '1.12.1', true );
			wp_enqueue_style( 'playlist.css',  $this->plugin_url . "playlist.css");
			wp_enqueue_script( 'playlist-render.js', $this->plugin_url . 'js/playlist-render.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'playlist-control.js', $this->plugin_url . 'js/playlist-control.js', array( 'jquery' ), '1.0.3', true );
			wp_enqueue_script( 'playlist-element.js', $this->plugin_url . 'js/playlist-element.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'playlist.js', $this->plugin_url . 'js/playlist.js', array( 'jquery' ), '1.0.1', true );
			wp_enqueue_script( 'Chart.bundle.js', $this->plugin_url . 'js/Chart.bundle.js', array( 'jquery' ), '1.0.1', true );
			wp_enqueue_script( 'index.js', $this->plugin_url . 'js/index.js', array( 'jquery' ), '1.0.0', true );

		}
		function shared_scripts(){

			wp_enqueue_script( 'functions.js', $this->plugin_url . 'js/functions.js', array( 'jquery' ), '1.0.1', true );			
			wp_enqueue_script( 'bootstrap.js', $this->plugin_url . 'js/bootstrap.min.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'access_log.js', $this->plugin_url . 'js/access_log.js', array( 'jquery' ), '1.0.0', true );
			
		}
		function localize_vars(){
			
			$local = array(
					'plugin_url' => $this->plugin_url,
					'is_front_page' => is_front_page(),
					'is_single' => is_single(),
					'is_page' => is_page(),
					'plugin_slug' => $this->plugin_slug,
					'plugin_title' => $this->plugin_title,
					'github_url' => $this->github_url,
					'has_shortcode' => $this->has_shortcode($this->shortcode),
			        'stylesheet_url' => dirname( get_bloginfo('stylesheet_url') )."/",
			);
				
			wp_localize_script( 'functions.js', $this->plugin_slug, $local);
		}
		function has_shortcode($shortcode = '') {
		
			$post_to_check = get_post(get_the_ID());
			$found = false;
		
			if ($shortcode && $post_to_check) {
				if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) {
					$found = true;
				}
			}
			return $found;
		}
		function admin_bar_setup(){

			global $wp_admin_bar;

			if ( !is_super_admin() || !is_admin_bar_showing() ) return;

			$url_to = admin_url( 'options-general.php?page='.$this->plugin_slug);
			
			$wp_admin_bar->add_menu(
									array(
											'id' => $this->plugin_slug,
											'title' => __( $this->plugin_title, $this->plugin_slug ),
											'href' => $url_to,
											'meta'  => array(
															'title' => $this->plugin_title,
															'class' => $this->plugin_slug
															)
											
										)
									);
		}
		function include_file($options) {			
		
			update_option( 'tag_in', $options['tag_in']);
			update_option( 'tag_not_in', $options['tag_not_in']);
						
			//include (plugin_dir_path(__FILE__).'playlist-layout.php');
			
			return file_get_contents(plugin_dir_path(__FILE__).'playlist-layout.php');
		}

		function admin_menu_include() {
			if ( !current_user_can( 'read' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			include ( plugin_dir_path(__FILE__).'admin/admin-menu.php' );
		}
		function head_hook() {
			$favicon = esc_attr( get_option('favicon') );
			if( $favicon ){
				echo  '<link href="'.$favicon.'" rel="icon" type="image/x-icon"></link>';
			}
		}
		function var_error_log( $object=null ){
			ob_start();                    // start buffer capture
			var_dump( $object );           // dump the values
			$contents = ob_get_contents(); // put the buffer into a variable
			ob_end_clean();                // end capture
			error_log( $contents );        // log contents of the result of var_dump( $object )
		}
		function _log($obj = '') {
			error_log ( print_r ( $obj, 1 ) );
		}
		function _dump($obj = '') {
			var_dump( print_r( $obj,1 ) );
		}	
		function _echo($obj = '') {
			echo "<pre>" . ( print_r ( $obj, 1 ) ) . "</pre>";
		}		


	}
	new WPAudioPlayerCBHDMK;
}

?>
