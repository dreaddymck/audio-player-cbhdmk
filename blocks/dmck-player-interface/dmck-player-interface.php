<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package audio-player-cbhdmk
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function dmck_player_interface_block_init() {
	// Skip block registration if Gutenberg is not enabled/merged.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$dir = dirname( __FILE__ );

	$index_js = 'index.js';
	wp_register_script(
		'dmck-player-interface-block-editor',
		plugins_url( $index_js, __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-components', 
			'wp-editor'
		),
		filemtime( "$dir/$index_js" )
	);
	$local = array(
		'plugin_slug' => dmck_audioplayer::PLUGIN_SLUG,
		'default_album_cover' => get_option('default_album_cover'),
		'site_url' => get_site_url()
	);
	wp_localize_script( 'dmck-player-interface-block-editor', dmck_audioplayer::PLUGIN_SLUG, $local);

	$jquery_ui_js = "js/jquery-ui-1.12.1/jquery-ui.js";
	wp_register_script( 'jquery-ui.min.js.block', plugins_url( $jquery_ui_js, __FILE__ ), array('jquery') );
	$jquery_ui_css = "js/jquery-ui-1.12.1/jquery-ui.min.css";
	wp_register_style( 'jquery-ui.min.css.block', plugins_url( $jquery_ui_css, __FILE__ ), array());	

	$editor_css = 'editor.css';
	wp_register_style( 'dmck-player-interface-block-editor', plugins_url( $editor_css, __FILE__ ), array(), filemtime( "$dir/$editor_css" ) );

	$style_css = 'style.css';
	wp_register_style( 'dmck-player-interface-block', plugins_url( $style_css, __FILE__ ), array(), filemtime( "$dir/$style_css" ) );

	register_block_type( 'audio-player-cbhdmk/dmck-player-interface', array(
		'attributes'  => array(
			'mb_title' => array(
				'type' => 'string'
			), 
			'mb_text' => array(
				'type' => 'string'
			),
			'mb_url' => array(
				'type' => 'string'
			)
		),		
		'editor_script' => 'dmck-player-interface-block-editor',
		'editor_style'  => 'dmck-player-interface-block-editor',
		'style'         => 'dmck-player-interface-block',
		'render_callback' => 'player_interface',
	) );
}
add_action( 'init', 'dmck_player_interface_block_init' );


function mb_block_render( $attributes )
{
 
  $is_in_edit_mode = strrpos($_SSERVER ['REQUEST_URI'], 'context=edit');
 
  $UID = rand(0, 10000);
 
  if ($is_in_edit_mode) {
 
    if(!empty($attributes['mb_text'])){
		$content = '<div class="mb-editor-content" id="mb-editor-content_' . $UID . '">';
		$content .= '<h2 class="mb-editor-title"> ' . $attributes['mb_title'] . '</h2>';
		$content .= '<p class="mb-editor-text"> ' . $attributes['mb_text'] . '</p>';
		$content .= '<a class="mb-editor-url" href="' . $attributes['mb_url'] . '"> ' . $attributes['mb_url'] . '</a>';
		$content .= '</div>';
    } else {
		$content = '<div class="mb-editor-content" id="mb-editor-content_' . $UID . '">';
		$content .= '<h2 class="mb-editor-title"> ' . $attributes['mb_title'] . '</h2>';
		$content .= '</div>';
    }
 
  } else {

		$content = '<div class="mb-editor-content" id="mb-editor-content_' . $UID . '" style="background:#f3f3f3; padding:20px">';
		$content .= '<h2 class="mb-editor-title"> ' . $attributes['mb_title'] . '</h2>';
		$content .= '<p class="mb-editor-text"> ' . $attributes['mb_text'] . '</p>';
		$content .= '<a class="mb-editor-url" href="' . $attributes['mb_url'] . '"> ' . $attributes['mb_url'] . '</a>';
		$content .= '</div>';
  }
  return $content;
}

function player_interface($attributes){

	$id=dmck_audioplayer::PLUGIN_SLUG . "-" . rand(0, 10000);
	$default_album_cover = get_option('default_album_cover');

	return <<<EOF

	<div id="$id-player">
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-box box-background">
				<div class="panel-heading">
					<h1 class="title" title="click for more information"></h1>
				</div>
				<div class="panel-body">				
					<div class="cover" style="background-image: url('$default_album_cover'); background-size: 100%; opacity: 0.8; cursor: pointer;"">
						<div class="h-100">
							<div class="volume" style="display:none"></div>						
							<div class="duration h-100">							
								<h2 class="artist"></h2>	
								<div class="tracktime"> 0 / 0</div>					
							</div>						
						</div>
					</div>
				</div>
				<div class="panel-heading options">
					<div class="controls row ">
						<div class="play fa fa-play-circle fa-3x col-xs-3"  aria-hidden="true" title="Play"></div>
						<div class="pause fa fa-pause fa-3x col-xs-3 hidden"  aria-hidden="true" title="Pause"></div>	
						<div class="rew fa fa-step-backward fa-3x col-xs-3" aria-hidden="true" title="Back"></div>
						<div class="fwd fa fa-step-forward fa-3x col-xs-3"  aria-hidden="true" title="Forward"></div>
					</div> 
				</div>			
			</div>
		</div>
	</div>
	</div>
EOF;


}