<?php
//TODO order by is unfinished, remove or expand.
function playlist_config_default_json(){
	return  '[
	{
		"id" : "",
		"title" : "",
		"order": "",
		"tag":"",
		"tag_id":0,
		"tag__and":[""],
		"tag__in":[""],
		"tag__not_in":[""],
		"tag_slug__and":[""],
		"tag_slug__in": [""],	
		"cat":0,
		"category_name":"",
		"category__and":[""],
		"category__in":[""],
		"category__not_in":[""]

	},
	{
		"top_request" : "false",
		"top_count" : "10",
		"top_title" : "Top"
	}
]';		
}
function playlist_config_default(){
	$json = get_option("playlist_config");
	if(!$json){
		$json = playlist_config_default_json();
		update_option("playlist_config", $json);
	}
	return $json;
}
function playlist_config_options($arry,$selected,$custom){
	
	$options = "";
	$sel = " selected ";
	foreach($arry as $arr){
		switch ($custom) {
			case "string":
				$options .=  "
<option value='$arr->name' ". (( $arr->name == $selected ) ? $sel : "") .">$arr->name</option>";
				break;
			case "slug":
				$options .=  "
<option value='$arr->slug' ". (( $arr->slug == $selected ) ? $sel : "") .">$arr->slug</option>";
				break;				
			case "int":
				$options .=  "
<option value='$arr->term_id' ". (( $arr->term_id == $selected ) ? $sel : "") .">$arr->name - ( ID: $arr->term_id )</option>";   
				break;
			case "int(array)":
                $options .=  "
<option value='$arr->term_id' ". ( in_array($arr->term_id, $selected) ? $sel : "" ) .">$arr->name - ( ID: $arr->term_id )</option>";  
				break;
			case "slug(array)":
				$options .=  "
<option value='$arr->slug' ". ( in_array($arr->slug, $selected) ? $sel : "" ) .">$arr->slug</option>";  
				break;				
		}
	}
	if(strpos($options, $sel)){ $sel = ""; }
	$options = "<option value='' $sel >empty</option>". $options;
	return $options;
}

$playlist_config_default = playlist_config_default();
$playlist_config = json_decode($playlist_config_default);

if($playlist_config){
	$playlist_top_media = "";
	$playlist_top_media_count = "";
	$playlist_top_media_title = "";
	$playlist_config_selection = "";
	$playlist_config_tabs_content = "";
	$playlist_config_tabs_content_inputs = "";
	$playlist_config_x = 0;
	$default_current = "current";
	$default_selected = "selected";	
	$tags = get_tags( array( 'hide_empty' => 0 ) );
	$cats = get_categories( array(
		'orderby' => 'name',
		'parent'  => 0
	));
	foreach($playlist_config as $config){
		if(isset($config->id)){
            // content inputs defaults
			$config->order = isset($config->order) && $config->order ? $config->order : "ASC";
			$config->tag = isset($config->tag) && $config->tag ? $config->tag : null;
			$config->tag_id = isset($config->tag_id) && $config->tag_id ? $config->tag_id : 0;
			$config->tag__and = isset($config->tag__and) && $config->tag__and ? json_encode($config->tag__and) : "[]";
			$config->tag__in = isset($config->tag__in) && $config->tag__in ? json_encode($config->tag__in) : "[]";
			$config->tag__not_in = isset($config->tag__not_in) && $config->tag__not_in ? json_encode($config->tag__not_in) : "[]";
			$config->tag_slug__and = isset($config->tag_slug__and) && $config->tag_slug__and ? json_encode($config->tag_slug__and) : "[]";
			$config->tag_slug__in = isset($config->tag_slug__in) && $config->tag_slug__in ? json_encode($config->tag_slug__in) : "[]";

			$config->cat = isset($config->cat) && $config->cat ? $config->cat : 0;
			$config->category_name = isset($config->category_name) && $config->category_name ? $config->category_name : "";
			$config->category__and = isset($config->category__and) && $config->category__and ? json_encode($config->category__and) : "[]";
			$config->category__in = isset($config->category__in) && $config->category__in ? json_encode($config->category__in) : "[]";
			$config->category__not_in = isset($config->category__not_in) && $config->category__not_in ? json_encode($config->category__not_in) : "[]";

			$config_order_selection = "";
			$config_order_selection .= "<option value='ASC' ". (( "ASC" == $config->order ) ? "selected" : "") .">ASC</option>";
			$config_order_selection .= "<option value='DESC' ". (( "DESC" == $config->order ) ? "selected" : "") .">DESC</option>";

            $config_cat_selection=playlist_config_options($cats, $config->cat,1);

            $playlist_config_selection = $playlist_config_selection."
<option value='$config->id' draggable=true $default_selected>$config->id</option>
            ";

			$playlist_config_tabs_content_inputs . $playlist_config_tabs_content_inputs = "
<input type='hidden' name='id' value='{$config->id}' />
<label>Title: 
<input type='text' name='title' value='{$config->title}' class='pure-input-1' />
</label>

<label>Order: 
<select name='order' onchange='admin_functions.config_update(this,\"{$config->id}\")'> $config_order_selection</select>
</label>

<label>Select meta tag: 
<select name='select_config_meta_tags'>
	<option value='sel_tag'>tag</option>
	<option value='sel_tag_id'>tag_id</option> 
	<option value='sel_tag__and'>tag__and</option> 
	<option value='sel_tag__in'>tag__in</option> 
	<option value='sel_tag__not_in'>tag__not_in</option> 
	<option value='sel_tag_slug__and'>tag_slug__and</option> 
	<option value='sel_tag_slug__in'>tag_slug__in</option>
	<option value='sel_cat'>cat</option>  
	<option value='sel_category_name'>category_name</option> 
	<option value='sel_category__and'>category__and</option> 
	<option value='sel_category__in'>category__in</option> 
	<option value='sel_category__not_in'>category__not_in</option> 
</select>
</label>

<div id='sel_tag' class='config_post_meta_tags current'>
<input type='hidden' name='tag' value='{$config->tag}' class='pure-input-1' readonly />
<select name='select_config_tag' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
	".playlist_config_options($tags, $config->tag, "slug")."
</select>
</div>

<div id='sel_tag_id' class='config_post_meta_tags'>
<input type='hidden' name='tag_id' value='{$config->tag_id}' class='pure-input-1' readonly/>
<select name='select_config_tag_id' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
	".playlist_config_options($tags, $config->tag_id, "int")."
</select>
</div>

<div id='sel_tag__and' class='config_post_meta_tags'>
<input type='hidden' name='tag__and' value='{$config->tag__and}' class='pure-input-1' readonly/>
<select name='select_config_tag__and' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
	".playlist_config_options($tags, json_decode($config->tag__and), "int(array)")."
</select>
</div>

<div id='sel_tag__in' class='config_post_meta_tags'>
<input type='hidden' name='tag__in' value='{$config->tag__in}' class='pure-input-1' readonly/>
<select name='select_config_tag__in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag__in), "int(array)")."
</select>
</div>

<div id='sel_tag__not_in' class='config_post_meta_tags'>
<input type='hidden' name='tag__not_in' value='{$config->tag__not_in}' class='pure-input-1' readonly/>
<select name='select_config_tag__not_in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag__not_in), "int(array)")."
</select>
</div>

<div id='sel_tag_slug__and' class='config_post_meta_tags'>
<input type='hidden' name='tag_slug__and' value='{$config->tag_slug__and}' class='pure-input-1' readonly/>
<select name='select_config_tag_slug__and' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag_slug__and), "slug(array)")."
</select>
</div>

<div id='sel_tag_slug__in' class='config_post_meta_tags'>
<input type='hidden' name='tag_slug__in' value='{$config->tag_slug__in}' class='pure-input-1' readonly/>
<select name='select_config_tag_slug__in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag_slug__in), "slug(array)")."
</select>
</div>

<div id='sel_cat' class='config_post_meta_tags'>
<input type='hidden' name='cat' value='{$config->cat}' class='pure-input-1' readonly/>
<select name='select_config_cat' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->cat), "int")."
</select>
</div>

<div id='sel_category_name' class='config_post_meta_tags'>
<input type='hidden' name='category_name' value='{$config->category_name}' class='pure-input-1' readonly/>
<select name='select_config_category_name' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category_name), "slug")."
</select>
</div>

<div id='sel_category__and' class='config_post_meta_tags'>
<input type='hidden' name='category__and' value='{$config->category__and}' class='pure-input-1' readonly/>
<select name='select_config_category__and' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category__and), "int(array)")."
</select>
</div>

<div id='sel_category__in' class='config_post_meta_tags'>
<input type='hidden' name='category__in' value='{$config->category__in}' class='pure-input-1' readonly/>
<select name='select_config_category__in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category__in), "int(array)")."
</select>
</div>

<div id='sel_category__not_in' class='config_post_meta_tags'>
<input type='hidden' name='category__not_in' value='{$config->category__not_in}' class='pure-input-1' readonly/>
<select name='select_config_category__not_in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category__not_in), "int(array)")."
</select>
</div>

";

			$playlist_config_tabs_content = $playlist_config_tabs_content. "
<div id='playlist-config-tab-$playlist_config_x' class='playlist-config-tab-content $default_current'>
    $playlist_config_tabs_content_inputs
</div>
			";
			$playlist_config_x ++;
			if($default_current){$default_current="";}
			if($default_selected){$default_selected="";}
		}
		if(isset($config->top_request)){$playlist_top_media = filter_var($config->top_request, FILTER_VALIDATE_BOOLEAN); }
		if(isset($config->top_count)){$playlist_top_media_count = $config->top_count; }
		if(isset($config->top_title)){$playlist_top_media_title = $config->top_title; }
	}

	?>
	<h2>Playlist Configuration</h2>

	<?php if ($playlist_config_selection) : ?>
	<select name="playlist_config_selection" size="4">
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
	<div>
		<label  class="pure-checkbox">Enable Top requests:
			<input type="checkbox" name="playlist_top_media"  value="1" <?php if (1 == $playlist_top_media) echo 'checked="checked"'; ?> class="">
		</label>
		<input type='text' name='playlist_top_media_title' value="<?php echo $playlist_top_media_title ?>" class='pure-input-1-4' style="display:<?php echo (1 == $playlist_top_media) ? 'inline' : 'none'; ?>" placeholder="The Title"/>
		<input type='text' name='playlist_top_media_count' value="<?php echo $playlist_top_media_count ?>" class='pure-input-1-4' style="display:<?php echo (1 == $playlist_top_media) ? 'inline' : 'none'; ?>" placeholder="Count"/>
		
	</div>
	
	<hr>
	
	<label>playlist json</label> (<i><small>Double-click input to expand</small></i>)
	<textarea name="playlist_config" class="pure-input-1 rounded-0" rows="3" ondblclick="this.style.height = '';this.style.height = (this.scrollHeight + 12) + 'px'"><?php echo $playlist_config_default; ?></textarea>
	<script>let playlist_config_default_json = <?php echo playlist_config_default_json(); ?></script>
	<hr>
	<?php	

}


