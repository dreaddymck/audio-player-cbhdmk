<?php ?>

<div class="loading" style="text-align: center; width: 100%;">
	<img src="<?php echo plugins_url( 'assets/images/loading-nerd.gif', dirname(__FILE__) )?>" />
</div>
<form name="admin-settings-form" name="admin-settings-form" method="post" action="options.php">
	<?php settings_fields( self::SETTINGS_GROUP ); ?>
	<?php do_settings_sections( self::SETTINGS_GROUP ); ?>	
<div class="admin-container" style="display:none;">
	<ul class="tabs">
		<li class="tab-link current" data-tab="tab-1">Settings</li>
		<li class="tab-link" data-tab="tab-4">Playlists</li>
		<?php if( get_option('charts_enabled') ){ ?>
		<li class="tab-link" data-tab="tab-5">Charts</li>
		<?php } ?>
		<li class="tab-link" data-tab="tab-6">Git Commits</li>
		<li class="tab-link" data-tab="tab-3">About</li>
	</ul>
	<?php submit_button(); ?>
	<div id="tab-1" class="tab-content current">
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="default_album_cover"><?php _e('Default Album Cover'); ?></label>
				<input type="text" name="default_album_cover"  title="Image url"  class="form-control" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>"  required placeholder="Required">
				<hr />
				<label for="favicon" ><?php _e('Favicon'); ?></label>
				<textarea  name="favicon" class="form-control" title="ico url or base64"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
				<br />
				<label><?php _e('More info (HTML or TEXT)'); ?></label>
				<input type="text" name="moreinfo"  class="form-control" value="<?php echo esc_attr( get_option('moreinfo') ); ?>" title="This is useless atm">
			</div>
			<div class="col-sm-6 form-group">	
				<input type="checkbox" name="drop_table_on_inactive"  class="form-control" value="1" <?php if (1 == get_option('drop_table_on_inactive')) echo 'checked="checked"'; ?> >
				<label>Drop <?php echo $this->plugin_title ?> tables when deactivated</label>
				<hr>
				<input type="checkbox" name="charts_enabled"  class="form-control" value="1" <?php if (1 == get_option('charts_enabled')) echo 'checked="checked"'; ?> >
				<label>Charts</label>
				<hr>
			</div>			
		</div>
	</div>
	<div id="tab-2" class="tab-content tab-files"></div>
	<div id="tab-3" class="tab-content tab-about"></div>
	<div id="tab-4" class="tab-content tab-playlists">
		<label>playlist json</label>
		<textarea name="playlist_config" class="form-control" rows="18"><?php echo playlist_config_default(); ?></textarea>
		<hr>
		<label>Visualizer</label>
		<table class="table-responsive">
		<thead>
			<tr>
			<th scope="col">R</th>
			<th scope="col">G</th>
			<th scope="col">B</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			<td><input type="text" name="visualizer_r"  class="form-control" value="<?php echo esc_attr( get_option('visualizer_r') ); ?>" title="visualizer r"></td>
			<td><input type="text" name="visualizer_g"  class="form-control" value="<?php echo esc_attr( get_option('visualizer_g') ); ?>" title="visualizer g"></td>
			<td><input type="text" name="visualizer_b"  class="form-control" value="<?php echo esc_attr( get_option('visualizer_b') ); ?>" title="visualizer b"></td>
			</tr>
		</tbody>
		</table>		
	</div>
	<div id="tab-5" class="tab-content tab-charts form-group">
		<div class="row">
			<div class="col-sm-12 form-group">	
				<label><?php _e('Access log filter '); ?></label>
				<input type="text" name="access_log_pattern"  class="form-control" value="<?php echo esc_attr( get_option('access_log_pattern') ); ?>"  placeholder="/.mp3/i">
				<small>Simple regex. The default is <code>/.mp3/i</code>.</small>
				<hr>
				<label><?php _e('Access Log location')?></label>
				<input type="text" name="access_log"  class="form-control" value="<?php echo esc_attr( get_option('access_log') ); ?>">
				<small>Add the following to cron: <code>* * * * * $(which php) <?php echo plugin_dir_path(__DIR__)?>lib/reports.php put > /dev/null 2>&1</code></small>
				<hr>	
				<label><?php _e('Chart background colors array ( <small>Example <code>["#ffffff","#F0F0F0","#E0E0E0","#D0D0D0","#C0C0C0","#B0B0B0","#A0A0A0","#909090","#808080","#707070"]</code></small> )'); ?></label>
				<input type="text" name="chart_color_array"  class="form-control" value="<?php echo esc_attr( get_option('chart_color_array') ); ?>">
				<hr>
				<label><?php _e('Ignore site admin ip addresses'); ?></label> <input type="checkbox" name="ignore_ip_enabled"  class="form-control" value="1" <?php if (1 == get_option('ignore_ip_enabled')) echo 'checked="checked"'; ?> >
				<textarea  name="ignore_ip_json" class="form-control" title="ignore ip json"  <?php if (1 != get_option('ignore_ip_enabled')) echo 'disabled'; ?> ><?php echo esc_attr( get_option('ignore_ip_json') ); ?></textarea>
			</div>
		</div>	
	</div>
	<div id="tab-6" class="tab-content tab-gitlog">
		<?php if (current_user_can('activate_plugins')) : ?>
		<div class="row">
		<div class="col-sm-12 form-group">
			<label>git: <a href="https://github.com/dreaddymck/audio-player-cbhdmk" target="_blank">https://github.com/dreaddymck/audio-player-cbhdmk</a></label>	
			<textarea class="form-control rounded-0" id="git-log" rows="18">
				<?php echo shell_exec('cd ' .__DIR__.  '; git log -n 50 --graph --abbrev-commit --decorate --date=relative --all'); ?>
			</textarea>
		</div>
		</div>
		<?php endif; ?>	
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
		"id" : "featured",
		"title" : "Title text",
		"tag" : "featured",
		"tag_slug__and" : ""
	},
	{
		"topten" : "false"
	}		
]
EOF;

		update_option("playlist_config", $json);				
	} 
	return $json;
}  
?>
