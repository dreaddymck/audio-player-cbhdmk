		
<?php
function playlist_config_default_json(){
//TODO: add link to wordpress documentation.
// tag (string) - use tag slug.
// tag_id (int) - use tag id.
// tag__and (array) - use tag ids.
// tag__in (array) - use tag ids.
// tag__not_in (array) - use tag ids.
// tag_slug__and (array) - use tag slugs.
// tag_slug__in (array) - use tag slugs.
// cat (int) - use category id.
// category_name (string) - use category slug.
// category__and (array) - use category id.
// category__in (array) - use category id.
// category__not_in (array) - use category id.				
	return  '[
	{
		"id" : "",
		"title" : "",
		"orderby":"",
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
		"topten" : "false"
	}
]';		
}
function playlist_config_default(){
	$json = get_option("playlist_config");
	if(!$json){
		error_log("????");
		$json = playlist_config_default_json();
		update_option("playlist_config", $json);
	}
	return $json;
}
function playlist_config_options($arry,$selected,$custom){
	
	$options="<option value=''>empty</option>";
	foreach($arry as $arr){
		switch ($custom) {
			case "string":
				$options .=  "
<option value='$arr->name' ". (( $arr->name == $selected ) ? "selected" : "") .">( ID: $arr->term_id ) - $arr->name</option>";
				break;
			case "slug":
				$options .=  "
<option value='$arr->slug' ". (( $arr->slug == $selected ) ? "selected" : "") .">( ID: $arr->term_id ) - $arr->slug</option>";
				break;				
			case "int":
				$options .=  "
<option value='$arr->term_id' ". (( $arr->term_id == $selected ) ? "selected" : "") .">( ID: $arr->term_id ) - $arr->name</option>";   
				break;
			case "int(array)":
                $options .=  "
<option value='$arr->term_id' ". ( in_array($arr->term_id, $selected) ? "selected" : "" ) .">( ID: $arr->term_id ) - $arr->name</option>";  
				break;
			case "slug(array)":
				$options .=  "
<option value='$arr->slug' ". ( in_array($arr->slug, $selected) ? "selected" : "" ) .">( ID: $arr->term_id ) - $arr->slug</option>";  
				break;				
		}
	}
	return $options;
}

$playlist_config_default = playlist_config_default();
$playlist_config = json_decode($playlist_config_default);

if($playlist_config){

	$playlist_config_selection = "";
	$playlist_config_tabs_content = "";
	$playlist_config_tabs_content_inputs = "";
	$playlist_config_x = 0;
	$playlist_top_media = false;

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
			$config->orderby = isset($config->orderby) && $config->orderby ? $config->orderby : "";
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
<label>title:</label> 
<input type='text' name='title' value='{$config->title}' class='form-control form-control-sm' />

<label>order:</label> 
<select name='order' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
    $config_order_selection
</select>
<br>
<label>orderby:</label> 
<input type='text' name='orderby' value='{$config->orderby}' class='form-control form-control-sm' />

<label>tag:</label> 
<input type='text' name='tag' value='{$config->tag}' class='form-control form-control-sm' readonly />
<select name='select_config_tag' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
	".playlist_config_options($tags, $config->tag, "slug")."
</select>

<br>

<label>tag_id:</label> 
<input type='text' name='tag_id' value='{$config->tag_id}' class='form-control form-control-sm' readonly/>
<select name='select_config_tag_id' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
	".playlist_config_options($tags, $config->tag_id, "int")."
</select>

<br>

<label>tag__and:</label> 
<input type='text' name='tag__and' value='{$config->tag__and}' class='form-control form-control-sm' readonly/>
<select name='select_config_tag__and' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
	".playlist_config_options($tags, json_decode($config->tag__and), "int(array)")."
</select>
<br>
<label>tag__in:</label> 
<input type='text' name='tag__in' value='{$config->tag__in}' class='form-control form-control-sm' readonly/>
<select name='select_config_tag__in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag__in), "int(array)")."
</select>
<br>
<label>tag__not_in:</label> 
<input type='text' name='tag__not_in' value='{$config->tag__not_in}' class='form-control form-control-sm' readonly/>
<select name='select_config_tag__not_in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag__not_in), "int(array)")."
</select>
<br>
<label>tag_slug__and:</label> 
<input type='text' name='tag_slug__and' value='{$config->tag_slug__and}' class='form-control form-control-sm' readonly/>
<select name='select_config_tag_slug__and' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag_slug__and), "slug(array)")."
</select>
<br>
<label>tag_slug__in:</label> 
<input type='text' name='tag_slug__in' value='{$config->tag_slug__in}' class='form-control form-control-sm' readonly/>
<select name='select_config_tag_slug__in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($tags, json_decode($config->tag_slug__in), "slug(array)")."
</select>

<br>
<label>cat:</label> 
<input type='text' name='cat' value='{$config->cat}' class='form-control form-control-sm' readonly/>
<select name='select_config_cat' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->cat), "int")."
</select>
<br>
<label>category_name:</label> 
<input type='text' name='category_name' value='{$config->category_name}' class='form-control form-control-sm' readonly/>
<select name='select_config_category_name' onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category_name), "slug")."
</select>
<br>
<label>category__and:</label> 
<input type='text' name='category__and' value='{$config->category__and}' class='form-control form-control-sm' readonly/>
<select name='select_config_category__and' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category__and), "int(array)")."
</select>
<br>
<label>category__in:</label> 
<input type='text' name='category__in' value='{$config->category__in}' class='form-control form-control-sm' readonly/>
<select name='select_config_category__in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category__in), "int(array)")."
</select>
<br>
<label>category__not_in:</label> 
<input type='text' name='category__not_in' value='{$config->category__not_in}' class='form-control form-control-sm' readonly/>
<select name='select_config_category__not_in' multiple onchange='admin_functions.config_update(this,\"{$config->id}\")'>
".playlist_config_options($cats, json_decode($config->category__not_in), "int(array)")."
</select>
<br>";

			$playlist_config_tabs_content = $playlist_config_tabs_content. "
<div id='playlist-config-tab-$playlist_config_x' class='playlist-config-tab-content tab-content  $default_current'>
    $playlist_config_tabs_content_inputs
</div>
			";
			$playlist_config_x ++;
			if($default_current){$default_current="";}
			if($default_selected){$default_selected="";}
		}
		if(isset($config->topten)){$playlist_top_media = filter_var($config->topten, FILTER_VALIDATE_BOOLEAN); }
	}

	?>
	<label>Playlist Configuration</label>
	
	<div>
		<label>Enable Top requests:</label>
		<input type="checkbox" name="playlist_top_media"  class="form-control form-control-sm" value="1" <?php if (1 == $playlist_top_media) echo 'checked="checked"'; ?> >
	</div>
	<?php if ($playlist_config_selection) : ?>
	<select name="playlist_config_selection">
		<?php echo $playlist_config_selection ?>
	</select>
	<?php endif; ?>
	<a class="button playlist_config_add">Add</a>
	<a class="button playlist_config_del">Remove</a>
	<?php $this->notices() ?>	
	<div class="playlist-config-content-container">
		<?php echo $playlist_config_tabs_content ?>
	</div>
	
	<hr>
	<label>playlist json</label> (<i><small>Double-click input to expand</small></i>)
	<textarea name="playlist_config" class="form-control form-control-sm" rows="3" ondblclick="this.style.height = '';this.style.height = (this.scrollHeight + 12) + 'px'"><?php echo $playlist_config_default; ?></textarea>
	<script>let playlist_config_default_json = <?php echo playlist_config_default_json(); ?></script>
	<hr>
	<?php	

}


