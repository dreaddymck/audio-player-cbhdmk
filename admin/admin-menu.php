<?php 


?>
<div id="loading"  class="fa-3x"><i id="loading-image" class="fas fa-spinner fa-pulse"></i></div>
<?php include_once(plugin_dir_path(__FILE__)."admin-playlist-config.php"); ?>
<?php $opts_enabled = ( get_option('charts_enabled') ||  get_option('playlist_top_media'));  ?>
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
				<?php $top_media_en = get_option('playlist_top_media') ?>
				<li class="pure-menu-item <?php echo (!$top_media_en ? "hidden_" : "") ?>" data-tab="parent-tabs-2"><a
						href="#" class="pure-menu-link">Top Media</a></li>
				<?php $charts_en = get_option('charts_enabled') ?>
				<li class="pure-menu-item <?php echo (!$charts_en ? "hidden" : "") ?>" data-tab="parent-tabs-5"><a
						href="#" class="pure-menu-link">Charts</a></li>				
				<li class="pure-menu-item <?php echo (!$opts_enabled ? "hidden" : "") ?>" data-tab="parent-tabs-7"><a
						href="#" class="pure-menu-link">Stats</a></li>
				<li class="pure-menu-item" data-tab="parent-tabs-6"><a href="#" class="pure-menu-link">Plugin git
						commits</a></li>
				<li class="pure-menu-item" data-tab="parent-tabs-3"><a href="#" class="pure-menu-link">About</a></li>
			</ul>
		</div>
		<?php submit_button(null,"primary small"); ?>
		<!-- settings -->
		<div id="parent-tabs-1" class="parent-tab-content tab-content current">
			<div class="pure-g">
				<div class="pure-u-1">
					<div class="pure-padding-box">
						<label for="default_album_cover"><?php _e('Default Album Cover'); ?>
							<input type="text" name="default_album_cover" title="Image url" class="pure-input-1"
								value="<?php echo esc_attr( get_option('default_album_cover') ); ?>" required
								placeholder="Required">
						</label>
						<hr />
						<label for="favicon"><?php _e('Favicon (optional)'); ?>
							<textarea name="favicon" class="pure-input-1"
								title="ico url or base64"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
						</label>
						<hr>

						<label>Drop tables when deactivated
							<input type="checkbox" name="drop_table_on_inactive" class="" value="1"
								<?php if (1 == get_option('drop_table_on_inactive')) echo 'checked="checked"'; ?>>
						</label>
						<label> Delete saved options when deactivated
							<input type="checkbox" name="delete_options_on_inactive" class="" value="1"
								<?php if (1 == get_option('delete_options_on_inactive')) echo 'checked="checked"'; ?>>
						</label>
						<hr>
						<label  class="pure-checkbox">Enable Top requests:
							<input type="checkbox" name="playlist_top_media"  value="1" 
								<?php if (1 == get_option('playlist_top_media')) echo 'checked="checked"'; ?> class="">
						</label>
						<label>Enable charts below playlist and post:
							<input type="checkbox" name="charts_enabled" class="" value="1"
								<?php if (1 == get_option('charts_enabled')) echo 'checked="checked"'; ?>>
						</label>						
						<label class="export-tables-label <?php echo (!$opts_enabled ? "hidden" : "") ?>" >
							<a class="secondary small" 
								id="export-tables"
								onclick="admin_functions.export_tables()">Export</a> chart data
						</label>												
						<hr>

						<div class="opt_requirements <?php echo (!$opts_enabled ? "hidden" : "") ?>">
							
							<label><?php _e('Required '); ?></label>

							<label><?php _e('cron '); ?></label>
							<small><code>* * * * * $(which php) <?php echo plugin_dir_path(__DIR__)?>lib/reports.php logs > /dev/null 2>&1</code></small>
							<!-- Do not move above without addressing dependencies -->
							<hr>
							<label><?php _e('Options'); ?></label>
							<label><?php _e('access Log location')?></label>
							<small>accepts /path/to/access_log, ["/path/to/access_log","/path/to/access_log_other"]</small>
							<input type="text" name="access_log" class="pure-input-1"
								value="<?php echo esc_attr( get_option('access_log') ); ?>" <?php if (1 == get_option('charts_enabled')) echo 'required placeholder="Required"'; ?>>
								<hr>
								<label><?php _e('Ignore admin ip addresses'); ?>:
									<input type="checkbox" name="ignore_ip_enabled" class="" value="1"
										<?php if (1 == get_option('ignore_ip_enabled')) echo 'checked="checked"'; ?>>
								</label>
								<textarea name="ignore_ip_json" class="pure-input-1" title="ignore ip json"
									<?php if (1 != get_option('ignore_ip_enabled')) echo 'disabled'; ?>><?php echo esc_attr( get_option('ignore_ip_json') ); ?></textarea>	


						</div>	
		
					</div>
				</div>
			</div>
		</div>
		<!--  -->
		<div id="parent-tabs-2" class="parent-tab-content tab-content tab-top-media">

			<label><?php _e('Custom title and count '); ?></label>
			<input type='text' name='playlist_top_media_title' value="<?php echo $playlist_top_media_title ?>" class='pure-input-1-2' style="display:<?php echo (1 == $playlist_top_media) ? 'inline' : 'none'; ?>" placeholder="The Title"/>
			<input type='text' name='playlist_top_media_count' value="<?php echo $playlist_top_media_count ?>" class='pure-input-1-4' style="display:<?php echo (1 == $playlist_top_media) ? 'inline' : 'none'; ?>" placeholder="Count"/>

			<label><?php _e('Access log filter '); ?></label><small>Simple regex. The default is <code>/.mp3/i</code>.</small>
			<input type="text" name="access_log_pattern" class="pure-input-1"
				value="<?php echo esc_attr( get_option('access_log_pattern') ); ?>" placeholder="/.mp3/i">
			
					
						

		</div>
		<!-- about -->
		<div id="parent-tabs-3" class="parent-tab-content tab-content tab-about"></div>
		<!-- playlist -->
		<div id="parent-tabs-4" class="parent-tab-content tab-content tab-playlists">
			<div class="pure-g">

				<div class="pure-u-1 pure-u-md-3-5">
					<div class="pure-padding-box">
						<?php if ($playlist_config_selection) : ?>
						<select name="playlist_config_selection" size="6">
							<?php echo $playlist_config_selection ?>
						</select>
						<?php endif; ?>
						<a class="button secondary-small playlist_config_add" title="Add item">Add</a>
						<a class="button secondary-small playlist_config_del" title="Remove item">Remove</a>
						<a class="button secondary-small playlist_config_up" title="Move up"><strong>&#8593;</strong></a>
						<a class="button secondary-small playlist_config_down" title="Move down"><strong>&#8595;</strong></a>
						<?php $this->notices() ?>
						<div class="playlist-config-content-container">
							<?php echo $playlist_config_tabs_content ?>
						</div>
						<br>
						<hr>


					</div>
				</div>
				<div class="pure-u-1 pure-u-md-2-5">
					<div class="pure-padding-box">
						 <label>Audio Control Display
							<input type="checkbox" name="audio_control_enabled" class="" value="1"
								<?php if (1 == get_option('audio_control_enabled')) echo 'checked="checked"'; ?>>
						</label>
						<label>Audio Control slider height (pixels):
							<input type="text" name="audio_control_slider_height" class="pure-input-1"
								value="<?php if(get_option('audio_control_slider_height')){ echo esc_attr( get_option('audio_control_slider_height') ); }else{ echo "200"; } ?>"
								<?php if (0 == get_option('audio_control_enabled')) echo 'disabled'; ?>>
						</label>
						<hr>
						<label>Visualizer: <input type="checkbox" name="visualizer_enabled" class="" value="1"
								<?php if (1 == get_option('visualizer_enabled')) echo 'checked="checked"'; ?>>
						</label>
						<label>Colors: <input type="checkbox" name="visualizer_rgb_enabled" class="" value="1"
								<?php if (1 == get_option('visualizer_rgb_enabled')) echo 'checked="checked"'; ?>>
						</label>						
						<div>
							<input name="visualizer_rgb_init" data-jscolor="{preset:'large dark'}"
								value="<?php if(get_option('visualizer_rgb_init')){ echo esc_attr( get_option('visualizer_rgb_init') ); }else{ echo "rgba(0,0,0,1.0)"; } ?>"
								title="Initial visualizer fill color"
								<?php if (1 != get_option('visualizer_rgb_enabled')) echo 'disabled'; ?>>
							<input name="visualizer_rgb" data-jscolor="{preset:'large dark'}"
								value="<?php if(get_option('visualizer_rgb')){ echo esc_attr( get_option('visualizer_rgb') ); }else{ echo "rgba(255,255,255,1.0)"; } ?>"
								title="Visualizer fill color"
								<?php if (1 != get_option('visualizer_rgb_enabled')) echo 'disabled'; ?>>
						</div>
						<label>Samples:
							<select name='visualizer_samples'
								<?php if (1 != get_option('visualizer_enabled')) echo 'disabled'; ?>>
								<option value='32' <?php selected( get_option('visualizer_samples'), "32" ); ?>>32
								</option>
								<option value='64' <?php selected( get_option('visualizer_samples'), "64" ); ?>>64
								</option>
								<option value='128' <?php selected( get_option('visualizer_samples'), "128" ); ?>>128
								</option>
								<option value='256' <?php selected( get_option('visualizer_samples'), "256" ); ?>>256
								</option>
								<option value='512' <?php selected( get_option('visualizer_samples'), "512" ); ?>>512
								</option>
								<option value='1024' <?php selected( get_option('visualizer_samples'), "1024" ); ?>>1024
								</option>
								<option value='2048' <?php selected( get_option('visualizer_samples'), "2048" ); ?>>2048
								</option>
								<option value='4096' <?php selected( get_option('visualizer_samples'), "4096" ); ?>>4096
								</option>
								<option value='8192' <?php selected( get_option('visualizer_samples'), "8192" ); ?>>8192
								</option>
								<option value='16384' <?php selected( get_option('visualizer_samples'), "16384" ); ?>>
									16384</option>
								<option value='32768' <?php selected( get_option('visualizer_samples'), "32768" ); ?>>
									32768</option>
							</select>
						</label>
						<hr>

						<label>Custom Filename REGEX (Filename is used as title):
							<input name="media_filename_regex" class="pure-input-1"
								value="<?php if(get_option('media_filename_regex')){ echo esc_attr( get_option('media_filename_regex') ); } ?>"
								title="Regex replace media filename" />
						</label>
					</div>
				</div>
				<div class="pure-u-1">
					<div class="pure-padding-box">
						<label>playlist json</label> (<i><small>Double-click input to expand</small></i>)
						<textarea name="playlist_config" class="pure-input-1 rounded-0" rows="5" ondblclick="this.style.height = '';this.style.height = (this.scrollHeight + 12) + 'px'"><?php echo $playlist_config_default; ?></textarea>
						<script>let playlist_config_default_json = <?php echo playlist_config_default_json(); ?></script>
						<hr>
					</div>		
				</div>				
			</div>
		</div>
		<!-- charts -->
		<div id="parent-tabs-5" class="parent-tab-content tab-content tab-charts ">
			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-5-5">
					<div class="pure-padding-box">

						<hr>
						<label><?php _e('Colors:'); ?> <input type="checkbox" name="chart_rgb_enabled" class="" value="1"
								<?php if (1 == get_option('chart_rgb_enabled')) echo 'checked="checked"'; ?>>
						</label>
						<input name="chart_rgb_init" data-jscolor="{preset:'large dark'}"
							value="<?php if(get_option('chart_rgb_init')){ echo esc_attr( get_option('chart_rgb_init') ); }else{ echo "rgba(0,0,0,1.0)"; } ?>"
							title="Initial chart fill color"
							<?php if (1 != get_option('chart_rgb_enabled')) echo 'disabled'; ?>>
						<input name="chart_rgb" data-jscolor="{preset:'large dark'}"
							value="<?php if(get_option('chart_rgb')){ echo esc_attr( get_option('chart_rgb') ); }else{ echo "rgba(255,255,255,1.0)"; } ?>"
							title="chart fill color"
							<?php if (1 != get_option('chart_rgb_enabled')) echo 'disabled'; ?>>
						<hr>						
						<label><?php _e('Colors array:'); ?><br>
							<small>Example
								<code>["#ffffff","#F0F0F0","#E0E0E0","#D0D0D0","#C0C0C0","#B0B0B0","#A0A0A0","#909090","#808080","#707070"]</code></small>
								<input type="text" name="chart_color_array" class="pure-input-1"
									value="<?php echo esc_attr( get_option('chart_color_array') ); ?>">
						</label>
						<hr>
					</div>
				</div>
			</div>
		</div>
		<!-- activity reports -->
		<div id="parent-tabs-7" class="parent-tab-content tab-content tab-charts-data ">
			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-5-5">
					<div class="pure-padding-box">

					<?php include_once(plugin_dir_path(__FILE__)."admin-stats.php"); ?>

					</div>
				</div>
			</div>
		</div>
		<!-- git -->
		<div id="parent-tabs-6" class="parent-tab-content tab-content tab-gitlog">
			<?php if (current_user_can('activate_plugins')) : ?>
			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-5-5">
					<label>git: <a href="https://github.com/dreaddymck/audio-player-cbhdmk"
							target="_blank">https://github.com/dreaddymck/audio-player-cbhdmk</a></label>
					<textarea class="pure-input-1 rounded-0" id="git-log"
						rows="18"><?php echo shell_exec('cd ' .__DIR__.  '; git log -n 25 --graph --abbrev-commit --decorate --date=relative --all'); ?></textarea>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div><!-- container -->
</form>
<?php

?>