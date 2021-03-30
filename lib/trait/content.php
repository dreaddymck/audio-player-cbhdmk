<?php
namespace DMCK_WP_MEDIA_PLUGIN;
trait _content {
	function content_handler($content){
		global $post;
		$html = "";
		if ( is_singular() && in_the_loop() ) {
			if (get_option('charts_enabled')) {
				$paths = $this->extract_embedded_media($content);
				if(sizeof($paths)){
					$post_chart_json = array();
					foreach($paths as $p){
						$basename = basename($p);
						$filename = urldecode($basename);
						$pattern = "(".preg_quote($basename)."|".preg_quote($filename).")";
						$target = pathinfo($filename, PATHINFO_FILENAME);
						$target = preg_replace("/(\W)+/", '_', $target);
						$resp = $this->dmck_media_activity_month($post->ID,12);
						if($resp){
							foreach($resp as $key=>$value){
								$json = (object)($value);
								array_push($post_chart_json, array(
									"time"=> $json->time,
									"count" => $json->count,
									"target" => $target,
									"filename" => $filename
								));
							}
						}
						$html = "<div class='post_chart_section ". $target ."_chart'></div><script>let post_chart_json = ".json_encode($post_chart_json)."</script>";
					}
				}
			}
		}
		$content = $this->content_toggle_https($content);
		return $content.$html;
	}
	function content_toggle_https($content){			
		$site_url = get_site_url();			
		if( $this->isSecure() ){				
			$secure_url		= preg_replace( "/^http:/i", "https:", $site_url );
			$insecure_url	= preg_replace( "/^https:/i", "http:", $site_url );	
			$pattern 		= "/" .preg_quote($insecure_url, '/') . "/i";	
			$content 		= preg_replace( $pattern, $secure_url, $content );
		}
		return $content;
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

	function notices(){
		echo  '
	<div class="notice notice-success" style="display:none;"></div>
	<div class="notice notice-error" style="display:none;"></div>
	<div class="notice notice-warning" style="display:none;"></div>
	<div class="notice notice-info" style="display:none;"></div>	
	';
	}	        
}