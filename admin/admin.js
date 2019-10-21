jQuery(document).ready(function(){
	
	jQuery('.loading').hide();
	jQuery('.container').show();
	
	jQuery('ul.tabs li').click(function(){
		var tab_id = jQuery(this).attr('data-tab');

		jQuery('ul.tabs li').removeClass('current');
		jQuery('.tab-content').removeClass('current');

		jQuery(this).addClass('current');
		jQuery("#"+tab_id).addClass('current');
	})
	
	jQuery.get( dmck_audioplayer.plugin_url + 'README.md',function(data){
		// let content = data.replace(/(?:\r\n|\r|\n)/g, '<br />');
		let content = marked(data);
		jQuery('#tab-2').html( content );
	});

})