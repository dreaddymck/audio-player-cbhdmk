"use strict";
jQuery(function(){	
	jQuery.fn.serializeObject = function () {
		var o = {};
		var a = this.serializeArray();
		jQuery.each(a, function () {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) { o[this.name] = [o[this.name]]; }
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};	
	admin_functions.overlay.off()
	jQuery('.admin-container').show();
	admin_events.init();
	let cookie = admin_functions.cookie.get();
	if (cookie) {
		cookie = JSON.parse(cookie);
		cookie.tab = (typeof cookie.tab !== 'undefined') ? cookie.tab : "parent-tabs-1";
		jQuery("ul.parent-tabs > li").removeClass('current');
		jQuery(".parent-tab-content").removeClass('current');
		jQuery("ul.parent-tabs > li[data-tab*='" + cookie.tab + "']").addClass('current');
		jQuery("#" + cookie.tab).addClass('current');

		cookie.playlist_config_selected = (typeof cookie.playlist_config_selected !== 'undefined') ? cookie.playlist_config_selected : 0;
		jQuery('select[name="playlist_config_selection"]')[0].selectedIndex = cookie.playlist_config_selected
		jQuery('.playlist-config-tab-content').removeClass('current');
		jQuery("#playlist-config-tab-" + cookie.playlist_config_selected).addClass('current');
		admin_functions.cookie.set({ "playlist_config_selected": cookie.playlist_config_selected });
	}

	Date.prototype.todaysDateValue = (function() {
		var local = new Date(this);
		local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
		return local.toJSON().slice(0,10);
	});
	Date.prototype.oneYearFromTodayValue = (function() {
		var local = new Date(new Date(this).setFullYear(new Date(this).getFullYear() - 1));
		local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
		return local.toJSON().slice(0,10);
	});                
	jQuery('input[name="post_in_date_to"]').val(new Date().todaysDateValue());
	jQuery('input[name="post_in_date_from"]').val(new Date().oneYearFromTodayValue());
	jQuery('input[name="post_in_stats"]').val(JSON.stringify([]));

	if(jQuery("input[type='checkbox'][name='playlist_top_media']").prop("checked")){
		jQuery('select[name="stats_playlist"] option[value="top-media-requests"]').attr('selected','selected').trigger("click");
	}else{
		jQuery('select[name="stats_playlist"]')[0].selectedIndex = 0 // TODO: set cookie value for the selected option
	}	

	jQuery.get(dmck_audioplayer.plugin_url + 'README.md', function (data) {
		let content = marked(data);
		jQuery('.tab-about').html(content);
	});
	jQuery("#wp-admin-bar-" + dmck_audioplayer.plugin_slug).css("font-style","italic"); 
});