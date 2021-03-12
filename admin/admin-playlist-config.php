		
<?php
$playlist_config_default = $this->playlist_config_default();
$playlist_config = json_decode($playlist_config_default);
$playlist_config_tabs="";
$playlist_config_tabs_content = "";
$playlist_config_tabs_content_inputs = "";
$playlist_config_x = 1;
$playlist_top_media = false;
$default_current = "current";
if($playlist_config){
	foreach($playlist_config as $pconfig){
		if(isset($pconfig->id)){
			$playlist_config_tabs = $playlist_config_tabs."
<li class='tab-link $default_current' data-tab='playlist-config-tab-$playlist_config_x'>
    {$pconfig->id} <a href='#' class='playlist_config_del'><i class='fa fa-minus-circle' aria-hidden='true' title='Click to remove'></i></a>
</li>
			";
			$playlist_config_tabs_content_inputs . $playlist_config_tabs_content_inputs = "
<input type='hidden' name='id' value='{$pconfig->id}' />
<label>title</label>: <input type='text' name='title' value='{$pconfig->title}' class='form-control form-control-sm' />
<label>tag</label>: <input type='text' name='tag' value='{$pconfig->tag}' class='form-control form-control-sm' />
<label>tag_slug__and</label>: <input type='text' name='tag_slug__and' value='{$pconfig->tag_slug__and}' class='form-control form-control-sm' />
			";
			$playlist_config_tabs_content = $playlist_config_tabs_content. "
<div id='playlist-config-tab-$playlist_config_x' class='playlist-config-tab-content tab-content  $default_current'>
    $playlist_config_tabs_content_inputs
</div>
			";
			$playlist_config_x ++;
			if($default_current){$default_current="";}
		}
		if(isset($pconfig->topten)){$playlist_top_media = filter_var($pconfig->topten, FILTER_VALIDATE_BOOLEAN); }
	}
}
?>
<label>Playlist Configuration</label>
<a class="button playlist_config_add">Add playlist item</a>
<div>
    <label>Enable Top requests:</label>
    <input type="checkbox" name="playlist_top_media"  class="form-control form-control-sm" value="1" <?php if (1 == $playlist_top_media) echo 'checked="checked"'; ?> >
</div>
<?php $this->notices() ?>
<ul class="playlist-config-tabs tabs">
    <?php echo $playlist_config_tabs ?>
</ul>
<div class="playlist-config-content-container">
    <?php echo $playlist_config_tabs_content ?>
</div>

<hr>
<label>playlist json</label> (<i><small>Double-click input to expand</small></i>)
<textarea name="playlist_config" class="form-control form-control-sm" rows="3" ondblclick="this.style.height = '';this.style.height = (this.scrollHeight + 12) + 'px'"><?php echo $playlist_config_default; ?></textarea>
<script>let playlist_config_default_json = <?php echo $this->playlist_config_default_json(); ?></script>
<hr>