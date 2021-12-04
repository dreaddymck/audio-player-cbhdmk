<?php

namespace DMCK_WP_MEDIA_PLUGIN;

trait _attachments {  
    function attachments(){    
		if ( ! function_exists( 'wp_crop_image' ) ) {
			include( ABSPATH . 'wp-admin/includes/image.php' );
		}
		include(plugin_dir_path(__FILE__).'../mime-types.php');		
		$response = array();		    
		$args = $this->obj_request_args((object) array());
		$posts = get_posts( $args );
		$response = array();
		$is_secure = $this->isSecure();
		foreach ( $posts as $post ) {
			setup_postdata( $post );
			if(get_post_status($post->ID) != "publish" ){ continue; }
			$audio 		= $this->extract_embedded_media( $post->post_content );
			if(empty($audio[0])) { continue; }
			foreach($audio as $a){
				$attached = false;
				$abname = basename(urldecode($a));
				$media = get_attached_media( '', $post->ID );
				foreach($media as $m){
					$guid = basename(urldecode($m->guid));
					if(strcasecmp($guid,$abname) == 0 ){
						$attached = true;
						continue;
					}
				}
				if($attached){continue;}				
				$ext = pathinfo($a, PATHINFO_EXTENSION);
				$attachment = array(
					'guid'           => $a, 
					'post_mime_type' => $mime_types[$ext],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', $abname  ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				// error_log(json_encode($a));
				// error_log(json_encode($attachment));
				// error_log(json_encode($mime_types[$ext]));
				// exit();				
				$attach_id = wp_insert_attachment( $attachment, $a, $post->ID );
				array_push( $response, $attachment );				
			}
		} 
		if($this->debug){ 
			echo __FUNCTION__. " | ". $this->memory_usage()."\n\r"; 
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
    }
}
