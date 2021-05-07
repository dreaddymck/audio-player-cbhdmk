<?php ?>
<div class="loading" style="text-align: center; width: 100%;"><img src="<?php echo plugins_url( 'assets/images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>
<?php $this->notices() ?>
<form name="admin-settings-form" method="post" action="options.php" class="pure-form pure-form-stacked">
<?php settings_fields( self::SETTINGS_GROUP ); ?>
<?php do_settings_sections( self::SETTINGS_GROUP ); ?>
<div class="admin-container" style="display:none;">
	<div class="pure-menu pure-menu-horizontal">
		<a class="pure-menu-heading pure-menu-link"><?php echo $this->plugin_title ?></a>
		<ul class="pure-menu-list tabs-settings parent-tabs tabs">
			<li class="pure-menu-item" data-tab="parent-tabs-1"><a href="#" class="pure-menu-link">Settings</a></li>
			<li class="pure-menu-item" data-tab="parent-tabs-4"><a href="#" class="pure-menu-link">Playlists</a></li>
			<?php if( get_option('charts_enabled') ){ ?>
			<li class="pure-menu-item" data-tab="parent-tabs-5"><a href="#" class="pure-menu-link">Charts</a></li>
			<li class="pure-menu-item" data-tab="parent-tabs-7"><a href="#" class="pure-menu-link">Data</a></li>
			<?php } ?>
			<li class="pure-menu-item" data-tab="parent-tabs-6"><a href="#" class="pure-menu-link">Plugin git commits</a></li>
			<li class="pure-menu-item" data-tab="parent-tabs-3"><a href="#" class="pure-menu-link">About</a></li>                
		</ul>
	</div>
	<?php submit_button(null,"primary small"); ?>
	<div id="parent-tabs-1" class="parent-tab-content tab-content current">
		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-5-5">
				
				<label for="default_album_cover"><?php _e('Default Album Cover'); ?>
				<input type="text" name="default_album_cover"  title="Image url"  class="pure-input-1" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>"  required placeholder="Required">
				</label>				
				<hr />
				<label for="favicon" ><?php _e('Favicon'); ?>
				<textarea  name="favicon" class="pure-input-1" title="ico url or base64"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
				</label>				
				<label><?php _e('More info (HTML or TEXT)'); ?>
				<input type="text" name="moreinfo"  class="pure-input-1" value="<?php echo esc_attr( get_option('moreinfo') ); ?>" title="This is useless atm">
				</label>				
				<h2>Plugin data</h2>				
				<label>	 				
				<a class="secondary small" id="export-tables" onclick="admin_functions.export_tables()">Export</a> plugin data
				</label> 
				<label>Drop tables when deactivated
				<input type="checkbox" name="drop_table_on_inactive"  class="" value="1" <?php if (1 == get_option('drop_table_on_inactive')) echo 'checked="checked"'; ?> >
				</label>
				<label>	Delete saved options when deactivated
				<input type="checkbox" name="delete_options_on_inactive"  class="" value="1" <?php if (1 == get_option('delete_options_on_inactive')) echo 'checked="checked"'; ?> >
				</label>
				<hr>
				<h2>Controls</h2>
				<label>Audio Control Display
				<input type="checkbox" name="audio_control_enabled"  class="" value="1" <?php if (1 == get_option('audio_control_enabled')) echo 'checked="checked"'; ?> >
				</label>
				<label>Audio Control slider height (pixels):
				<input type="text" name="audio_control_slider_height" class="pure-input-1" value="<?php if(get_option('audio_control_slider_height')){ echo esc_attr( get_option('audio_control_slider_height') ); }else{ echo "200"; } ?>" <?php if (0 == get_option('audio_control_enabled')) echo 'disabled'; ?> >
				</label>
				<hr>
				<label>Charts activate
				<input type="checkbox" name="charts_enabled"  class="" value="1" <?php if (1 == get_option('charts_enabled')) echo 'checked="checked"'; ?> >
				</label>
				<hr>
			</div>
		</div>
	</div>
	<div id="parent-tabs-2" class="parent-tab-content tab-content tab-files"></div>
	<div id="parent-tabs-3" class="parent-tab-content tab-content tab-about"></div>
	<div id="parent-tabs-4" class="parent-tab-content tab-content tab-playlists">
		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-3-5 admin-playlist-config-container">
				<div class="pure-padding-box">
					<?php include_once(plugin_dir_path(__FILE__)."admin-playlist-config.php"); ?>
				</div>
			</div>
			<div class="pure-u-1 pure-u-md-2-5">
				<div class="pure-padding-box">
				<label>Media Filename REGEX:
				<input name="media_filename_regex"  class="pure-input-1" value="<?php if(get_option('media_filename_regex')){ echo esc_attr( get_option('media_filename_regex') ); } ?>" title="Regex replace media filename" />
				</label>
				<hr>
				<label>Visualizer Enable: <input type="checkbox" name="visualizer_rgb_enabled"  class="" value="1" <?php if (1 == get_option('visualizer_rgb_enabled')) echo 'checked="checked"'; ?> >
				</label>
				<div>
					<label>Colors: 
					<input name="visualizer_rgb_init" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('visualizer_rgb_init')){ echo esc_attr( get_option('visualizer_rgb_init') ); }else{ echo "rgba(0,0,0,1.0)"; } ?>" title="Initial visualizer fill color"  <?php if (1 != get_option('visualizer_rgb_enabled')) echo 'disabled'; ?>>
					<input name="visualizer_rgb" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('visualizer_rgb')){ echo esc_attr( get_option('visualizer_rgb') ); }else{ echo "rgba(255,255,255,1.0)"; } ?>" title="Visualizer fill color" <?php if (1 != get_option('visualizer_rgb_enabled')) echo 'disabled'; ?>>
					</label>
				</div>
				<label>Samples:
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
				</label>
				</div>
			</div>
		</div>
	</div>
	<div id="parent-tabs-5" class="parent-tab-content tab-content tab-charts ">
		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-3-5">
				<div class="pure-padding-box">	
				<label><?php _e('Access log filter '); ?></label>
				<input type="text" name="access_log_pattern"  class="pure-input-1" value="<?php echo esc_attr( get_option('access_log_pattern') ); ?>"  placeholder="/.mp3/i">
				<small>Simple regex. The default is <code>/.mp3/i</code>.</small>
				<hr>
				<label><?php _e('Access Log location')?></label>
				<input type="text" name="access_log"  class="pure-input-1" value="<?php echo esc_attr( get_option('access_log') ); ?>">
				<small>Add the following to cron:<br><code>* * * * * $(which php) <?php echo plugin_dir_path(__DIR__)?>lib/reports.php put > /dev/null 2>&1</code></small>
				<hr>
				<label><?php _e('Chart fill colors array'); ?><br>
				( <small>Example <code>["#ffffff","#F0F0F0","#E0E0E0","#D0D0D0","#C0C0C0","#B0B0B0","#A0A0A0","#909090","#808080","#707070"]</code></small> )
				<input type="text" name="chart_color_array"  class="pure-input-1" value="<?php echo esc_attr( get_option('chart_color_array') ); ?>">
				</label>
				<hr>
				</div>
			</div>
			<div class="pure-u-1 pure-u-md-2-5">
				<div class="pure-padding-box">
				<label>Enable charts on posts: 
				<input type="checkbox" name="chart_rgb_enabled"  class="" value="1" <?php if (1 == get_option('chart_rgb_enabled')) echo 'checked="checked"'; ?> >
				</label> 
				<label>Colors: 
				<input name="chart_rgb_init" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('chart_rgb_init')){ echo esc_attr( get_option('chart_rgb_init') ); }else{ echo "rgba(0,0,0,1.0)"; } ?>" title="Initial chart fill color" <?php if (1 != get_option('chart_rgb_enabled')) echo 'disabled'; ?>>
				<input name="chart_rgb" data-jscolor="{preset:'large dark'}" value="<?php if(get_option('chart_rgb')){ echo esc_attr( get_option('chart_rgb') ); }else{ echo "rgba(255,255,255,1.0)"; } ?>" title="chart fill color" <?php if (1 != get_option('chart_rgb_enabled')) echo 'disabled'; ?>>
				</label>
				<hr>
				<label><?php _e('Ignore admin ip addresses'); ?>:  
				<input type="checkbox" name="ignore_ip_enabled"  class="" value="1" <?php if (1 == get_option('ignore_ip_enabled')) echo 'checked="checked"'; ?> >
				</label>
				<textarea  name="ignore_ip_json" class="pure-input-1" title="ignore ip json"  <?php if (1 != get_option('ignore_ip_enabled')) echo 'disabled'; ?> ><?php echo esc_attr( get_option('ignore_ip_json') ); ?></textarea>
				<hr>
				</div>
			</div>
		</div>
	</div>
	<div id="parent-tabs-7" class="parent-tab-content tab-content tab-charts-data ">
		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-5-5">
				<label><?php _e("Media Requests Today"); ?></label>
				<textarea  class="pure-input-1" rows="12"><?php
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
		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-5-5">
				<label>git: <a href="https://github.com/dreaddymck/audio-player-cbhdmk" target="_blank">https://github.com/dreaddymck/audio-player-cbhdmk</a></label>
				<textarea
					class="pure-input-1 rounded-0"
					id="git-log"
					rows="18"
					><?php echo shell_exec('cd ' .__DIR__.  '; git log -n 25 --graph --abbrev-commit --decorate --date=relative --all'); ?></textarea>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div><!-- container -->
</form>
<?php

?>
