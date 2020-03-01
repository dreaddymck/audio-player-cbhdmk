<?php
/*
Plugin Name: (DMCK) audio player
Plugin URI: dreaddymck.com
Description: Just another Wordpress audio player. This plugin will add the first mp3 link embedded in each active post content into a playlist. shortcode [dmck-audioplayer]
Version: 1.0.40
Author: dreaddymck
Author URI: dreaddymck.com
License: GPL2

*/
// error_reporting(E_ALL);
// ini_set("display_errors","On");
if (!class_exists("dmck_audioplayer")) {
	require_once(plugin_dir_path(__FILE__)."trait/access-logs.php");
	require_once(plugin_dir_path(__FILE__)."trait/wavform.php");	
	require_once(plugin_dir_path(__FILE__)."trait/utilities.php");
	require_once(plugin_dir_path(__FILE__)."trait/tables.php");
	require_once(plugin_dir_path(__FILE__)."trait/cron.php");
	require_once(plugin_dir_path(__FILE__)."trait/api.php");
	require_once(plugin_dir_path(__FILE__)."trait/rss.php");
	require_once(plugin_dir_path(__FILE__)."trait/requests.php");
	class dmck_audioplayer {
		use _accesslog;
		use _wavform;
		use _utilities;
		use _tables;
		use _cron;
		use _api;
		use _rss;
		use _requests;

		public $plugin_title;
		public $plugin_slug				= 'dmck_audioplayer';
		public $plugin_settings_group 	= 'dmck-audioplayer-settings-group';
		public $shortcode				= "dmck-audioplayer";
		public $adminpreferences 		= array('chart_colors','favicon','default_album_cover', 'moreinfo', 'facebook_app_id','access_log','media_root_path','media_root_url','playlist_config');
		public $userpreferences 		= array('userpreferences');	
		public $plugin_version;
		public $plugin_url;
		public $theme_url;
		public $github_url				= "https://github.com/dreaddy/audio-player-cbhdmk";

		public $cron_name;
		public $cron_jobs;
		public $site_url;
		
		function __construct() {		
			$this->setTimezone();
			$this->set_plugin_version();
			$this->plugin_title = '(DMCK)Audio-ver:' . $this->plugin_version;
			$this->plugin_url 	= plugins_url("/",__FILE__);
			$this->theme_url	= dirname( get_bloginfo('stylesheet_url') );			
			$this->site_url     = get_site_url();
			$this->cron_name 	= $this->plugin_slug . "_cronjob";

			register_activation_hook( __FILE__, array($this, 'register_activation' ) );
			register_deactivation_hook (__FILE__, array($this, 'register_deactivation'));

			add_action( 'init', array( $this, '_init_actions'));
			add_action( 'admin_init', array( $this, 'register_settings') );
			add_action( 'admin_menu', array( $this, 'admin_menu' ));			
			add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_setup'), 999);
			add_action( 'wp_enqueue_scripts', array($this, 'user_scripts') );			
			add_action( 'wp_head', array($this, 'head_hook') );
			add_action( 'login_head', array($this, 'head_hook') );
			add_action( 'admin_head', array($this, 'head_hook') );
			// add_action( 'wp', array($this, 'cronstarter_activation'));
			// add_action( $this->cron_name, array($this, 'wp_cron_functions')); 
			add_filter( 'get_the_excerpt', array($this,'the_exerpt_filter'));
			add_filter( 'the_content', array($this,'content_toggle_https'));			
			add_filter( 'cron_schedules', array($this, 'cron_add_minute'));			
			// require_once(plugin_dir_path(__FILE__).'playlist-api.php' );
		}
		function _init_actions(){
			add_shortcode( $this->shortcode, array( $this, 'include_file') );
			$this->_rss_create_feed();
		}
		function set_plugin_version(){
			if(preg_match('/version:[\s\t]+?([0-9.]+)/i',file_get_contents( __FILE__ ), $v)){
				$this->plugin_version = $v[1];
			}								
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
		function excerpt($text){
			$text = strip_shortcodes( $text );
			$text = apply_filters( 'the_content', $text );
			$text = str_replace(']]>', ']]&gt;', $text);
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
			return $text;
		}
		function the_exerpt_filter($param) {			
			$param = preg_replace('/(\[.*\])/', "", $param);
			$param = preg_replace('/(Sorry, your browser doesn\'t support HTML5 audio\.|Sorry, your browser doesn&#8217;t support HTML5 audio.)/i', "", $param);
			return $param;
		}
		function register_activation($options){
			$this->_tables_create();
		}
		function register_deactivation($options){
			$this->cronstarter_deactivate();
			$this->_tables_drop();
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
		function admin_scripts($hook_suffix) {			
			if ( $this->settings_page == $hook_suffix ) {
				$this->shared_scripts();
				
				wp_enqueue_style( 'admin.css',  $this->plugin_url . "admin/admin.css", array(), $this->plugin_version);
				wp_enqueue_script( 'marked.min.js', $this->plugin_url . 'js/marked.min.js', array('jquery'), $this->plugin_version, true );
				
				wp_enqueue_script( 'admin-functions.js', $this->plugin_url . 'admin/admin-functions.js', array('jquery'), $this->plugin_version, true );
				wp_enqueue_script( 'admin.js', $this->plugin_url . 'admin/admin.js', array('jquery'), $this->plugin_version, true );				
				$this->localize_vars();
			}
		}
		function user_scripts() {			
			if( $this->has_shortcode( $this->shortcode ) ) {}			
			$this->shared_scripts();			
			wp_enqueue_script( 'jquery-ui.min.js', $this->plugin_url . 'js/jquery-ui-1.12.1/jquery-ui.js', array('jquery'), $this->plugin_version, true );
			wp_enqueue_style( 'jquery-ui.min.css',  $this->plugin_url . "js/jquery-ui-1.12.1/jquery-ui.min.css", array(), $this->plugin_version);
			wp_enqueue_style( 'playlist.css',  $this->plugin_url . "playlist.css", array(), $this->plugin_version);
			wp_enqueue_script( 'playlist-control.js', $this->plugin_url . 'js/playlist-control.js', array('jquery'), $this->plugin_version, true );
			wp_enqueue_script( 'playlist.js', $this->plugin_url . 'js/playlist.js', array('jquery'), $this->plugin_version, true );
			wp_enqueue_script( 'Chart.bundle.js', $this->plugin_url . 'js/Chart.bundle.js', array('jquery'), $this->plugin_version, true );
			wp_enqueue_script( 'index.js', $this->plugin_url . 'js/index.js', array('jquery'), $this->plugin_version, true );			
			$this->localize_vars();
		}
		function shared_scripts(){
			wp_enqueue_script( 'jquery.cookie.js', $this->plugin_url . 'node_modules/jquery.cookie/jquery.cookie.js', array('jquery'), $this->plugin_version, true );
			wp_enqueue_style( 'font-awesome.min.css',  $this->plugin_url . "/node_modules/font-awesome/css/font-awesome.min.css", array(), $this->plugin_version);
			wp_enqueue_script( 'functions.js', $this->plugin_url . 'js/functions.js', array('jquery'), $this->plugin_version, true );	
			wp_enqueue_style( 'bootstrap.css',  $this->plugin_url . "node_modules/bootstrap/dist/css/bootstrap.min.css", array(), $this->plugin_version);		
			wp_enqueue_script( 'bootstrap.js', $this->plugin_url . 'node_modules/bootstrap/dist/js/bootstrap.min.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'access_log.js', $this->plugin_url . 'js/access_log.js', array('jquery'), $this->plugin_version, true );			
		}
		function localize_vars(){
			global $post,$wp_query;
			$tags = "";
			$category = "";
			if($post){
				$tags = wp_get_post_terms( $post->ID,'post_tag',array( 'fields' => 'names') );
				if($tags){
					$tags = implode("|", $tags);
				}
				$category = wp_get_post_terms( $post->ID,'category',array( 'fields' => 'names') );
				if($category){
					$category = implode("|", $category);
				}				
			}			
			$page 	= get_query_var ( 'paged' ) ? get_query_var ( 'paged' ) : 1;
			$limit	= $wp_query->post_count ? $wp_query->post_count : 1;
			$offset = ($page - 1) * $limit;	
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$this->autoplay			= isset($_POST["autoplay"]) ? htmlspecialchars($_POST["autoplay"] ) : "";
				$this->auto_play		= isset($_POST["auto_play"]) ? htmlspecialchars($_POST["auto_play"] ) : "";
				$this->relatedposts		= isset($_POST["relatedposts"]) ? htmlspecialchars($_POST["relatedposts"] ) : "";
			}else{
				$this->autoplay			= isset($_GET["autoplay"]) ? htmlspecialchars($_GET["autoplay"] ) : "";
				$this->auto_play		= isset($_GET["auto_play"]) ? htmlspecialchars($_GET["auto_play"] ) : "";
				$this->relatedposts		= isset($_GET["relatedposts"]) ? htmlspecialchars($_GET["relatedposts"] ) : "";					
			}
			$local = array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'is_home' => is_home(),
				'is_front_page' => is_front_page(),
				'is_single' => is_single(),
				'is_attachment' => is_attachment(),
				'is_search' => is_search(),  
				'is_page' => is_page(),
				'is_paged' => is_paged(), 
				'is_archive' => is_archive(),
				'is_tag' => is_tag(),
				'is_tax' => is_tax(),
				'is_author' => is_author(),					
				'page'	=> $page,
				'limit' => $limit,
				'offset' => $offset,
				'post_name' => $post ? $post->post_name : "",
				'tags' =>  $tags,
				'category' => $category,
				'plugin_version' => $this->plugin_version,
				'plugin_url' => $this->plugin_url,
				'plugin_slug' => $this->plugin_slug,
				'plugin_title' => $this->plugin_title,
				'playlist_config'=> get_option("playlist_config"),
				'github_url' => $this->github_url,
				'blog_url' => get_bloginfo('url'),
				'site_url' => $this->site_url,
				'has_shortcode' => $this->has_shortcode($this->shortcode),
				'stylesheet_url' => dirname( get_bloginfo('stylesheet_url') )."/",
				'autoplay'	=> ($this->autoplay || $this->auto_play),
				'chart_colors' => esc_attr( get_option('chart_colors') ),
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
			ob_start();
			include (plugin_dir_path(__FILE__).'playlist-layout.php');	
			return ob_get_clean();		
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
	}
	new dmck_audioplayer;
}

?>
