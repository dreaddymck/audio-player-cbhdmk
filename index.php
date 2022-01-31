<?php
/*
Plugin Name: (DMCK) audio player
Plugin URI: https://github.com/dreaddymck/audio-player-cbhdmk
Description: Generates a media playlists and simple charts. Shortcode [dmck-audioplayer]
Version: 1.1.1-b486735
Author: dreaddymck
Author URI: https://github.com/dreaddymck
License: GPL2

TODO: Gutenberg block support.
TODO: Add support for embedded videos as playlist items


*/
namespace DMCK_WP_MEDIA_PLUGIN;
// error_reporting(E_ALL);
// ini_set("display_errors","On");
if (!class_exists("dmck_audioplayer")) {

	require_once(plugin_dir_path(__FILE__).'admin/admin.php');
	require_once(plugin_dir_path(__FILE__)."lib/trait/data.php");
	require_once(plugin_dir_path(__FILE__)."lib/trait/wavform.php");	
	require_once(plugin_dir_path(__FILE__)."lib/trait/utilities.php");
	require_once(plugin_dir_path(__FILE__)."lib/trait/tables.php");
	// require_once(plugin_dir_path(__FILE__)."lib/trait/cron.php");
	require_once(plugin_dir_path(__FILE__)."lib/trait/rss.php");
	require_once(plugin_dir_path(__FILE__)."lib/trait/requests.php");
	require_once(plugin_dir_path(__FILE__)."lib/trait/meta_box.php");
	require_once(plugin_dir_path(__FILE__)."lib/trait/content.php");
	require_once(plugin_dir_path(__FILE__)."/lib/dmck_menu.php");
	
	
	class dmck_audioplayer {

		use _data;
		use _wavform;
		use _utilities;
		use _tables;
		// use _cron;
		use _rss;
		use _requests;
		use _meta_box;
		use _content;
		use _admin;

		const PLUGIN_SLUG				= 'dmck_audioplayer';
		const SETTINGS_GROUP			= 'dmck-audioplayer-settings-group';
		public $shortcode				= "dmck-audioplayer";
		public $github_url				= "https://github.com/dreaddy/audio-player-cbhdmk";
		public $debug					= false;
		public $plugin_title;
		public $plugin_version;
		public $plugin_url;
		public $theme_url;
		public $plugin_dir_path;		
		public $posts_per_page;
		public $tag;
		public $path;
		public $site_url;
		
		function __construct() {

			$this->setTimezone();
			$this->set_plugin_version();
			$this->plugin_title = '(DMCK) Audio:' . $this->plugin_version;
			$this->plugin_url 	= plugins_url("/",__FILE__);
			$this->plug_dir_path = plugin_dir_path( __FILE__ );
			$this->theme_url	= dirname( get_bloginfo('stylesheet_url') );			
			$this->site_url     = get_site_url();
			// $this->cron_name 	= self::PLUGIN_SLUG . "_cronjob";

			register_activation_hook( __FILE__, array($this, 'register_activation' ) );
			register_deactivation_hook (__FILE__, array($this, 'register_deactivation'));			

			add_action( 'init', array( $this, '_init_actions'));
			add_action( 'admin_init', array( $this, 'register_settings') );
			add_action( 'add_meta_boxes', array($this, 'add_meta_box_hook') );
			add_action( 'save_post',      array( $this, 'save_meta_box_hook') );			
			add_action( 'admin_menu', array( $this, 'admin_menu' ));			
			add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_setup'), 999);
			add_action( 'wp_enqueue_scripts', array($this, 'user_scripts') );			
			add_action( 'wp_head', array($this, 'head_hook') );
			add_action( 'login_head', array($this, 'head_hook') );
			add_action( 'admin_head', array($this, 'head_hook') );

			//do_action( 'updated_option(_fieldname)', string $option, mixed $old_value, mixed $value )
			add_action( 'update_option_access_log', array($this, 'update_option_hook'), 10, 3 );
			add_action( 'update_option_playlist_config', array($this, 'update_option_hook'), 10, 3 );
			
			// add_action( 'wp', array($this, 'cronstarter_activation'));
			// add_action( $this->cron_name."_daily", array($this, 'wp_cron_functions_daily')); 
			add_action( 'rest_api_init', function () {
				$namespace = self::PLUGIN_SLUG.'/'.$this->plugin_version;
				register_rest_route( $namespace,'api/(?P<option>[\w]+)' ,array(
					'methods'   =>  'GET,POST',
					'callback'  =>  array($this, 'requests'),
					'permission_callback' => '__return_true', 
					'args' => [ 'option' => [] ],										
				));	
			});			
			add_filter( 'get_the_excerpt', array($this,'the_exerpt_filter'));
			add_filter( 'the_content', array($this,'content_handler'));			
			// add_filter( 'cron_schedules', array($this, 'cron_add_minute'));
			// require_once(plugin_dir_path(__FILE__)."blocks/example-block/example-block.php");			
		}
		function _init_actions(){
			add_shortcode( $this->shortcode, array( $this, 'include_file') );
			$this->_rss_create_feed();
			$this->_utilities_ignore_ip_auto_set();
		}
		function set_plugin_version(){
			if(preg_match('/version:[\s\t]+?([a-zA-Z0-9-.]+)/i',file_get_contents( __FILE__ ), $v)){
				$this->plugin_version = $v[1];
			}
			return $this->plugin_version;
		}
		function user_scripts() {			
			if( $this->has_shortcode( $this->shortcode ) ) {}			
			wp_enqueue_script( self::PLUGIN_SLUG . '.bundle.js', $this->plugin_url . 'dist/public.bundle.js', array('jquery'), $this->plugin_version, true );			
			$this->localize_vars();
		}
		function localize_vars(){
			global $post,$wp_query;
			$tags = "";
			$category = "";
			if($post){
				$tags = wp_get_post_terms( $post->ID,'post_tag',array( 'fields' => 'names') );
				if($tags){ $tags = implode("|", $tags); }
				$category = wp_get_post_terms( $post->ID,'category',array( 'fields' => 'names') );
				if($category){ $category = implode("|", $category); }				
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
			$date = new \DateTime();
			$local = array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'curr_user_id' => get_current_user_id(),
				'date'=> $date->getTimestamp(),
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
				'plugin_slug' => self::PLUGIN_SLUG,
				'plugin_title' => $this->plugin_title,
				'playlist_config'=> get_option("playlist_config"),
				'github_url' => $this->github_url,
				'blog_url' => get_bloginfo('url'),
				'site_url' => $this->site_url,
				'has_shortcode' => $this->has_shortcode($this->shortcode),
				'stylesheet_url' => dirname( get_bloginfo('stylesheet_url') )."/",
				'autoplay'	=> ($this->autoplay || $this->auto_play),
				'chart_color_array' => esc_attr( get_option('chart_color_array') ),
				'chart_color_static' => esc_attr( get_option('chart_color_static') ),
				'visualizer_rgb_init' => esc_attr( get_option('visualizer_rgb_init')),
				'visualizer_rgb' => get_option("visualizer_rgb"),
				'visualizer_samples' => get_option("visualizer_samples"),
				'visualizer_rgb_enabled' => get_option("visualizer_rgb_enabled"),
				'visualizer_enabled' => get_option("visualizer_enabled"),
				'chart_rgb_init' => get_option("chart_rgb_init"),
				'chart_rgb' => get_option("chart_rgb"),
				'chart_rgb_enabled' => get_option("chart_rgb_enabled"),
				'charts_enabled' => get_option("charts_enabled"),
				'audio_control_enabled' => get_option("audio_control_enabled"),
				'default_album_cover' => esc_attr( get_option('default_album_cover'))				
			);
			// wp_localize_script( 'bundle.js', self::PLUGIN_SLUG, $local);
?>
<script type="text/javascript">
var <?php echo self::PLUGIN_SLUG ?> = <?php echo json_encode($local); ?>;
var dmck_chart_object = [];
</script>
<?php			
		}
		function include_file($options) {		
			ob_start();
			include (plugin_dir_path(__FILE__).'playlist-layout.php');	
			return ob_get_clean();		
		}
		function head_hook() {
			$favicon = esc_attr( get_option('favicon') );
			if( $favicon ){
				echo  '<link href="'.$favicon.'" rel="icon" type="image/x-icon"></link>';
			}
			
		}
		function update_option_hook( $old, $new){
			if($old != $new){
				$this->dmck_playlist_content();
			}				
		}				
	}
	new dmck_audioplayer;
}
?>
