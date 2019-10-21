<?php ?>

<div class="loading" style="text-align: center; width: 100%;">
	<img src="<?php echo plugins_url( 'images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>

<form method="post" action="options.php">
<?php settings_fields( $this->plugin_settings_group ); ?>
<?php do_settings_sections( $this->plugin_settings_group ); ?>
	
<div class="container table" style="display:none;">
	<ul class="tabs">
		<li class="tab-link current" data-tab="tab-1">settings</li>
		<li class="tab-link" data-tab="tab-2">about</li>
	</ul>
	<div id="tab-1" class="tab-content current">
		<?php submit_button( __( 'Submit', 'Submit' ), 'primary' ); ?>
		<div>	
			<label><?php _e('Favicon'); ?></label>
	        <textarea  name="favicon" class="form-control"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
		</div>
		<div>
			<label><?php _e('Default album cover'); ?></label>
	        <input type="text" name="default_album_cover"  class="form-control" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>">
        </div>
		<div>
			<label><?php _e('More info text (Not sure if being used)'); ?></label>
	        <input type="text" name="moreinfo"  class="form-control" value="<?php echo esc_attr( get_option('moreinfo') ); ?>">
        </div>
		<div>	
			<label><?php _e('Access Log for "Top 10 requests"<br />Add the following to cron: <small>$(which php) '. plugin_dir_path(__FILE__) .'lib/reports.php put</small>'); ?></label>
	        <input type="text" name="access_log"  class="form-control" value="<?php echo esc_attr( get_option('access_log') ); ?>">
		</div>		
		<div>	
			<label><?php _e('Facebook App ID (not functional)'); ?></label>
	        <input type="text" name="facebook_app_id"  class="form-control" value="<?php echo esc_attr( get_option('facebook_app_id') ); ?>">
		</div>
		<?php submit_button( __( 'Submit', 'Submit' ), 'primary' ); ?>
		
	</div>
	<div id="tab-2" class="tab-content"></div>

</div><!-- container -->	

</form>