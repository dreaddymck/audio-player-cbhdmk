<?php ?>

<div class="loading" style="text-align: center; width: 100%;">
	<img src="<?php echo plugins_url( 'images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>
	
<div class="admin-container" style="display:none;">
	<ul class="tabs">
		<li class="tab-link current" data-tab="tab-1">Settings</li>
		<li class="tab-link" data-tab="tab-2">Tiny File Manager</li>
		<li class="tab-link" data-tab="tab-3">About</li>		
	</ul>
	<div id="tab-1" class="tab-content current">		
		<form name="admin-settings-form" name="admin-settings-form" method="post" action="options.php">
		<?php settings_fields( $this->plugin_settings_group ); ?>
		<?php do_settings_sections( $this->plugin_settings_group ); ?>
	
		<div>	
			<label for="favicon" ><?php _e('Favicon'); ?></label>
	        <textarea  name="favicon" class="form-control"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
		</div>
		<div>
			<label for="default_album_cover"><?php _e('Default Album Cover'); ?></label>
	        <input type="text" name="default_album_cover"  class="form-control" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>">
        </div>
		<div>
			<label><?php _e('More info HTML/TEXT'); ?></label>
	        <input type="text" name="moreinfo"  class="form-control" value="<?php echo esc_attr( get_option('moreinfo') ); ?>">
        </div>
		<div>	
			<label><?php _e('Access Log for "Top 10 requests"<br /><small>Add the following to cron: <code>$(which php) '. plugin_dir_path(__DIR__) .'lib/reports.php put</code>'); ?></small></label>
	        <input type="text" name="access_log"  class="form-control" value="<?php echo esc_attr( get_option('access_log') ); ?>">
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
			<label><?php _e('Facebook App ID (not functional)'); ?></label>
	        <input type="text" name="facebook_app_id"  class="form-control" value="<?php echo esc_attr( get_option('facebook_app_id') ); ?>">
		</div>
		<div>	
			<!-- <a href="#" id="admin-settings-button" class="btn btn-default">Submit</a>		 -->
			<?php submit_button( __( 'Submit', 'Submit' ), 'default' ); ?>	
		</div>		
		</form>		
	</div>
	<div id="tab-2" class="tab-content tab-files"></div>
	<div id="tab-3" class="tab-content tab-about"></div>

</div><!-- container -->	

