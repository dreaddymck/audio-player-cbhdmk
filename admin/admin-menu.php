<?php ?>
<div class="loading" style="text-align: center; width: 100%;"><img src="<?php echo plugins_url( 'assets/images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>
<?php $this->notices() ?>
<form name="admin-settings-form" name="admin-settings-form" method="post" action="options.php">
<?php settings_fields( self::SETTINGS_GROUP ); ?>
<?php do_settings_sections( self::SETTINGS_GROUP ); ?>
<div class="admin-container" style="display:none;">
	<ul class="parent-tabs tabs">
		
		<li class="tab-link current" data-tab="parent-tabs-1">Settings</li>
		<li class="tab-link" data-tab="parent-tabs-4">Playlists</li>
		<?php if( get_option('charts_enabled') ){ ?>
		<li class="tab-link" data-tab="parent-tabs-5">Charts</li>
		<li class="tab-link" data-tab="parent-tabs-7">Data</li>
		<?php } ?>
		<li class="tab-link" data-tab="parent-tabs-6">Git Commits</li>
		<li class="tab-link" data-tab="parent-tabs-3">About</li>
	</ul>
	<?php submit_button(null,"primary small"); ?>
	<div id="parent-tabs-1" class="parent-tab-content tab-content current">
		<div class="row">
			<div class="col-lg-6 form-group">
				<label for="default_album_cover"><?php _e('Default Album Cover'); ?></label>
				<input type="text" name="default_album_cover"  title="Image url"  class="form-control form-control-sm" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>"  required placeholder="Required">
				<hr />
				<label for="favicon" ><?php _e('Favicon'); ?></label>
				<textarea  name="favicon" class="form-control form-control-sm" title="ico url or base64"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
				<br />
				<label><?php _e('More info (HTML or TEXT)'); ?></label>
				<input type="text" name="moreinfo"  class="form-control form-control-sm" value="<?php echo esc_attr( get_option('moreinfo') ); ?>" title="This is useless atm">
			</div>
			<div class="col-lg-6      form-group">
				<label>When plugin deactivated</label>
				<br>
				<input type="checkbox" name="drop_table_on_inactive"  class="form-control form-control-sm" value="1" <?php if (1 == get_option('drop_table_on_inactive')) echo 'checked="checked"'; ?> >
				<label>Drop tables</label>
				<br>
				<input type="checkbox" name="delete_options_on_inactive"  class="form-control form-control-sm" value="1" <?php if (1 == get_option('delete_options_on_inactive')) echo 'checked="checked"'; ?> >
				<label>Delete saved options</label>
				<hr>
				<input type="checkbox" name="audio_control_enabled"  class="form-control form-control-sm" value="1" <?php if (1 == get_option('audio_control_enabled')) echo 'checked="checked"'; ?> >
				<label>Audio Control Display</label>
				<br>
				<label>Audio Control slider height:</label>
				<input type="text" name="audio_control_slider_height" class="form_control" value="<?php if(get_option('audio_control_slider_height')){ echo esc_attr( get_option('audio_control_slider_height') ); }else{ echo "200"; } ?>" <?php if (0 == get_option('audio_control_enabled')) echo 'disabled'; ?> > px
				<hr>
				<input type="checkbox" name="charts_enabled"  class="form-control form-control-sm" value="1" <?php if (1 == get_option('charts_enabled')) echo 'checked="checked"'; ?> >
				<label>Charts</label>
				<hr>
			</div>
		</div>
	</div>
	<div id="parent-tabs-2" class="parent-tab-content tab-content tab-files"></div>
	<div id="parent-tabs-3" class="parent-tab-content tab-content tab-about"></div>
	<div id="parent-tabs-4" class="parent-tab-content tab-content tab-playlists">
		<div class="row">
			<div class="col-lg-8 form-group admin-playlist-config-container">
				<?php include_once(plugin_dir_path(__FILE__)."admin-playlist-config.php"); ?>
			</div>
			<div class="col-lg-4 form-group">
				<label>Media Filename REGEX</label>:
				<input name="media_filename_regex"  class="form-control form-control-sm" value="<?php if(get_option('media_filename_regex')){ echo esc_attr( get_option('media_filename_regex') ); } ?>" title="Regex replace media filename" />
				<hr>
				<label>Visualizer Enable: <input type="checkbox" name="visualizer_rgb_enabled"  class="form-control form-control-sm" value="1" <?php if (1 == get_option('visualizer_rgb_enabled')) echo 'checked="checked"'; ?> ></label>
				<div>
					<label>Colors: </label><br>
					<input name="visualizer_rgb_init" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('visualizer_rgb_init')){ echo esc_attr( get_option('visualizer_rgb_init') ); }else{ echo "rgba(0,0,0,1.0)"; } ?>" title="Initial visualizer fill color"  <?php if (1 != get_option('visualizer_rgb_enabled')) echo 'disabled'; ?>>
					<input name="visualizer_rgb" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('visualizer_rgb')){ echo esc_attr( get_option('visualizer_rgb') ); }else{ echo "rgba(255,255,255,1.0)"; } ?>" title="Visualizer fill color" <?php if (1 != get_option('visualizer_rgb_enabled')) echo 'disabled'; ?>>
				</div>
				<label>Samples: </label>
				<select name='visualizer_samples' <?php if (1 != get_option('visualizer_rgb_enabled')) echo 'disabled'; ?>>
					<option value='' <?php selected( get_option('visualizer_samples'), "" ); ?>>Select</option>
					<option value='32' <?php selected( get_option('visualizer_samples'), "32" ); ?>>32</option>
					<option value='64' <?php selected( get_option('visualizer_samples'), "64" ); ?>>64</option>
					<option value='128' <?php selected( get_option('visualizer_samples'), "128" ); ?>>128</option>
					<option value='256' <?php selected( get_option('visualizer_samples'), "256" ); ?>>256</option>
					<option value='512' <?php selected( get_option('visualizer_samples'), "512" ); ?>>512</option>
					<option value='1024' <?php selected( get_option('visualizer_samples'), "1024" ); ?>>1024</option>
					<option value='2048' <?php selected( get_option('visualizer_samples'), "2048" ); ?>>2048</option>
					<option value='4096' <?php selected( get_option('visualizer_samples'), "4096" ); ?>>4096</option>
					<option value='8192' <?php selected( get_option('visualizer_samples'), "8192" ); ?>>8192</option>
					<option value='16384' <?php selected( get_option('visualizer_samples'), "16384" ); ?>>16384</option>
					<option value='32768' <?php selected( get_option('visualizer_samples'), "32768" ); ?>>32768</option>
				</select>
			</div>
		</div>
	</div>
	<div id="parent-tabs-5" class="parent-tab-content tab-content tab-charts form-group">
		<div class="row">
			<div class="col-lg-8   form-group">
				<label><?php _e('Access log filter '); ?></label>
				<input type="text" name="access_log_pattern"  class="form-control form-control-sm" value="<?php echo esc_attr( get_option('access_log_pattern') ); ?>"  placeholder="/.mp3/i">
				<small>Simple regex. The default is <code>/.mp3/i</code>.</small>
				<hr>
				<label><?php _e('Access Log location')?></label>
				<input type="text" name="access_log"  class="form-control form-control-sm" value="<?php echo esc_attr( get_option('access_log') ); ?>">
				<small>Add the following to cron: <code>* * * * * $(which php) <?php echo plugin_dir_path(__DIR__)?>lib/reports.php put > /dev/null 2>&1</code></small>
				<hr>
				<label><?php _e('Chart fill colors array ( <small>Example <code>["#ffffff","#F0F0F0","#E0E0E0","#D0D0D0","#C0C0C0","#B0B0B0","#A0A0A0","#909090","#808080","#707070"]</code></small> )'); ?></label>
				<input type="text" name="chart_color_array"  class="form-control form-control-sm" value="<?php echo esc_attr( get_option('chart_color_array') ); ?>">
				<hr>
			</div>
			<div class="col-lg-4   form-group">
				<label>Enable charts on posts: </label> <input type="checkbox" name="chart_rgb_enabled"  class="form-control form-control-sm" value="1" <?php if (1 == get_option('chart_rgb_enabled')) echo 'checked="checked"'; ?> >:
				<br>
				<label>Colors: </label>
				<br>
				<input name="chart_rgb_init" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('chart_rgb_init')){ echo esc_attr( get_option('chart_rgb_init') ); }else{ echo "rgba(0,0,0,1.0)"; } ?>" title="Initial chart fill color" <?php if (1 != get_option('chart_rgb_enabled')) echo 'disabled'; ?>>
				<input name="chart_rgb" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('chart_rgb')){ echo esc_attr( get_option('chart_rgb') ); }else{ echo "rgba(255,255,255,1.0)"; } ?>" title="chart fill color" <?php if (1 != get_option('chart_rgb_enabled')) echo 'disabled'; ?>>
				<hr>
				<label><?php _e('Ignore admin ip addresses'); ?>: </label> <input type="checkbox" name="ignore_ip_enabled"  class="form-control form-control-sm" value="1" <?php if (1 == get_option('ignore_ip_enabled')) echo 'checked="checked"'; ?> >
				<textarea  name="ignore_ip_json" class="form-control form-control-sm" title="ignore ip json"  <?php if (1 != get_option('ignore_ip_enabled')) echo 'disabled'; ?> ><?php echo esc_attr( get_option('ignore_ip_json') ); ?></textarea>
				<hr>
			</div>
		</div>
	</div>
	<div id="parent-tabs-7" class="parent-tab-content tab-content tab-charts-data form-group">
		<div class="row">
			<div class="col-lg-12">
				<label><?php _e("Media Requests Today"); ?></label>
				<textarea  class="form-control form-control-sm" rows="12"><?php
					$access_log_activity = array();
					if( get_option('charts_enabled') ){
						$access_log_activity = $this->media_activity_today();
						if(is_array($access_log_activity)){
							echo json_encode($access_log_activity, JSON_PRETTY_PRINT);
						}
					}
				?></textarea>
				<label><?php echo "Total: ".sizeof($access_log_activity) ?><label>
			</div>
		</div>
	</div>
	<div id="parent-tabs-6" class="parent-tab-content tab-content tab-gitlog">
		<?php if (current_user_can('activate_plugins')) : ?>
		<div class="row">
			<div class="col-sm-12 form-group">
				<label>git: <a href="https://github.com/dreaddymck/audio-player-cbhdmk" target="_blank">https://github.com/dreaddymck/audio-player-cbhdmk</a></label>
				<textarea
					class="form-control form-control-sm rounded-0"
					id="git-log"
					rows="18"
					><?php echo shell_exec('cd ' .__DIR__.  '; git log -n 50 --graph --abbrev-commit --decorate --date=relative --all'); ?></textarea>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div><!-- container -->
</form>
<?php

?>
