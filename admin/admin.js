"use strict";

jQuery(document).ready(function(){
	
	jQuery('.loading').hide();
	jQuery('.container').show();
	jQuery('ul.tabs li').click(function(){
		var tab_id = jQuery(this).attr('data-tab');

		jQuery('ul.tabs li').removeClass('current');
		jQuery('.tab-content').removeClass('current');

		jQuery(this).addClass('current');
		jQuery("#"+tab_id).addClass('current');

		admin_functions.cookie.set({"tab": tab_id});		
	});	
	jQuery('#admin-upload-action').click(function (e) {
		e.preventDefault();
		if (!confirm('Please confirm')) { return false; }
		let callback = function(resp){
			resp = JSON.parse(resp);
			console.log(resp);
			return;	
		}		
		admin_functions.upload(callback);
	});
	jQuery('<iframe id="simple-php-filemanager" style="width:100%;height:300px;" />')
		.attr("src", dmck_audioplayer.plugin_url + "/lib/simple_php_filemanager.php")
		.appendTo('.tab-files')
		.load(function () {
			var $contents = jQuery(this).contents();
			$contents.scrollTop($contents.height());
	});		
	admin_functions.onload();
});