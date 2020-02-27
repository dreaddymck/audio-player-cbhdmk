<?php ?>

<div class="loading" style="text-align: center; width: 100%;">
	<img src="<?php echo plugins_url( 'images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>

<form name="admin-settings-form" name="admin-settings-form" method="post" action="options.php">
	<?php settings_fields( $this->plugin_settings_group ); ?>
	<?php do_settings_sections( $this->plugin_settings_group ); ?>	
<div class="admin-container" style="display:none;">
	<ul class="tabs">
		<li class="tab-link"><?php submit_button( __( 'Submit', 'Submit' ), 'default' ); ?></li>
		<li class="tab-link current" data-tab="tab-1">Settings</li>
		<li class="tab-link" data-tab="tab-4">Playlists</li>
		<li class="tab-link" data-tab="tab-2">Tiny File Manager</li>
		<li class="tab-link" data-tab="tab-3">About</li>
	</ul>
	<div id="tab-1" class="tab-content current">	

		<div>
			<label for="default_album_cover"><?php _e('Default Album Cover'); ?></label>
	        <input type="text" name="default_album_cover"  class="form-control" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>">
        </div>
		<div>	
			<label><?php _e('Media folder root path'); ?></label>
	        <input type="text" name="media_root_path"  class="form-control" value="<?php echo esc_attr( get_option('media_root_path') ); ?>">
		</div>
		<div>	
			<label><?php _e('Media folder url path ( <small>Path after <code>'.get_site_url().'</code> that leads to media folder</small> )'); ?></label>
	        <input type="text" name="media_root_url"  class="form-control" value="<?php echo esc_attr( get_option('media_root_url') ); ?>">
		</div>
		<div>	
			<label><?php _e('Top 10: Access Log location<br /><small>Add the following to cron: <code>$(which php) '. plugin_dir_path(__DIR__) .'lib/reports.php put</code>'); ?></small></label>
	        <input type="text" name="access_log"  class="form-control" value="<?php echo esc_attr( get_option('access_log') ); ?>">
		</div>
		<div>	
			<label><?php _e('Top 10: Chart colors array ( <small>Example <code>["#ffffff","#F0F0F0","#E0E0E0","#D0D0D0","#C0C0C0","#B0B0B0","#A0A0A0","#909090","#808080","#707070"]</code></small> )'); ?></label>
	        <input type="text" name="chart_colors"  class="form-control" value="<?php echo esc_attr( get_option('chart_colors') ); ?>">
		</div>					
		<div>	
			<label for="favicon" ><?php _e('Favicon'); ?></label>
	        <textarea  name="favicon" class="form-control"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
		</div>
		<div>
			<label><?php _e('More info (HTML or TEXT)'); ?></label>
	        <input type="text" name="moreinfo"  class="form-control" value="<?php echo esc_attr( get_option('moreinfo') ); ?>">
        </div>								
		<div class="hidden">	
			<label><?php _e('Facebook App ID (not functional)'); ?></label>
	        <input type="text" name="facebook_app_id"  class="form-control" value="<?php echo esc_attr( get_option('facebook_app_id') ); ?>">
		</div>
	</div>
	<div id="tab-2" class="tab-content tab-files"></div>
	<div id="tab-3" class="tab-content tab-about"></div>
	<div id="tab-4" class="tab-content tab-playlists">
		<h3>Playlists</h3>

		<textarea name="playlist_config" class="form-control" rows="18"><?php echo playlist_config_default() ?></textarea>
		
	</div>
	
</div><!-- container -->	
</form>

<?php
function playlist_config_default(){
	$json = get_option("playlist_config");
	if( !$json ){
		$json = <<<EOF
[
	{
		"id" : "demo",
		"title" : "Demo",
		"tag" : "featured",
		"tag_slug__and" : ""
	}	
]
EOF;

		update_option("playlist_config", $json);				
	} 
	return $json;
}  
?>
