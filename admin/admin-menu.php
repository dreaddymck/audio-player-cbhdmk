<?php ?>

<div class="loading" style="text-align: center; width: 100%;">
	<img src="<?php echo plugins_url( 'images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>

<form method="post" action="options.php">
<?php settings_fields( $this->plugin_settings_group ); ?>
<?php do_settings_sections( $this->plugin_settings_group ); ?>
	
<div class="container table" style="display:none;">
	<ul class="tabs">
		<li class="tab-link"><?php submit_button( __( 'Submit', 'Submit' ), 'default' ); ?></li>
		<li class="tab-link current" data-tab="tab-1">Settings</li>
		<li class="tab-link" data-tab="tab-2">Upload</li>
		<li class="tab-link" data-tab="tab-3">About</li>		
	</ul>
	<div id="tab-1" class="tab-content current">		
		<div>	
			<label><?php _e('Favicon'); ?></label>
	        <textarea  name="favicon" class="form-control"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
		</div>
		<div>
			<label><?php _e('Default Album Cover'); ?></label>
	        <input type="text" name="default_album_cover"  class="form-control" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>">
        </div>
		<div>
			<label><?php _e('More info text (Not sure if being used)'); ?></label>
	        <input type="text" name="moreinfo"  class="form-control" value="<?php echo esc_attr( get_option('moreinfo') ); ?>">
        </div>
		<div>	
			<label><?php _e('Access Log for "Top 10 requests"<br />Add the following to cron: <code>$(which php) '. plugin_dir_path(__DIR__) .'lib/reports.php put</code>'); ?></label>
	        <input type="text" name="access_log"  class="form-control" value="<?php echo esc_attr( get_option('access_log') ); ?>">
		</div>
		<div>	
			<label><?php _e('Path to media folder'); ?></label>
	        <input type="text" name="path_to_media"  class="form-control" value="<?php echo esc_attr( get_option('path_to_media') ); ?>">
			<a class="btn btn-info">Generate Wavform</a>
		</div>				
		<div>	
			<label><?php _e('Facebook App ID (not functional)'); ?></label>
	        <input type="text" name="facebook_app_id"  class="form-control" value="<?php echo esc_attr( get_option('facebook_app_id') ); ?>">
		</div>		
	</div>
	<div id="tab-2" class="tab-content tab-files">
		<div>	
			<a id="admin-upload-action" class="btn btn-default">Upload</a>
			<input name="admin-upload[]" type="file" multiple="multiple" />
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">25%</div>
			</div>
			<div class="message"></div>
		</div>	
	</div>
	<div id="tab-3" class="tab-content tab-about"></div>

</div><!-- container -->	

</form>