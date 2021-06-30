"use strict";

jQuery(document).ready(function () {
    /**
     * 1st initialize playlist control
     * then initialize dependencies
     */

    dmck_audioplayer.has_shortcode = true;
    playlist_control.init();

    // jQuery(".tab-pane").hide().first().show();
    // jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    //     let target = jQuery(e.target).attr("href") // activated tab
    //     _functions.cookie.set({ "tab": target });
    // });
        
    jQuery(".tab-pane").hide();
    jQuery("ul.tabs a").click(function (event) {
        event.preventDefault();
        let target = jQuery(this).attr("href");
        jQuery(".tab-pane").removeClass('active').hide();
        jQuery(target).addClass("active").show();
        _functions.cookie.set({ "tab": target });
    });
	let dmck_audioplayer_end = function() {
		let cookie = _functions.cookie.get();
        let playlist_config = dmck_audioplayer.playlist_config ? JSON.parse(dmck_audioplayer.playlist_config) : "";
        if (cookie) {
			cookie = JSON.parse(cookie);
			if (typeof cookie.tab !== 'undefined') {
                jQuery('.' + dmck_audioplayer.plugin_slug + ' a[href="' + cookie["tab"] + '"]').trigger('click');
                jQuery('.' + dmck_audioplayer.plugin_slug + " .dmck-audio-playlist-track").removeClass('active');	
                // playlist_control.initAudio( jQuery("." + dmck_audioplayer.plugin_slug + " .tab-pane.active .dmck-audio-playlist-track").first() );
			}
		}else		
		if(playlist_config){
            jQuery('.' + dmck_audioplayer.plugin_slug + ' a[href="#'+ playlist_config[0].id  +'"]').trigger('click');
            jQuery('.' + dmck_audioplayer.plugin_slug + " .dmck-audio-playlist-track").removeClass('active');	
            // playlist_control.initAudio( jQuery("." + dmck_audioplayer.plugin_slug + " .tab-pane.active .dmck-audio-playlist-track").first() );
        }
        playlist_control.set_cover_background(dmck_audioplayer.default_album_cover);
        playlist_control.globals.cfg.playing = false;
		
	}
    let p1 = Promise.resolve(playlist.init());
    let p2 = Promise.resolve(access_log.init());
    Promise.all(
        [
            p1, 
            p2
        ]
    ).then(function() {
        charts_pkg.top_requests_chart();
        charts_pkg.post_chart();
        dmck_audioplayer_end();
        jQuery(".site-info, #gutenbiz-copyright").append( playlist.powered_by );
        if(dmck_audioplayer.autoplay){
            if( jQuery('audio')[0] ){ jQuery('audio')[0].load().play(); }
        }        
    });
});



