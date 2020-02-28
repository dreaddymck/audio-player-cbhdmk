"use strict";

jQuery(document).ready(function () {  

    /**
     * 1st initialize playlist control
     * then initialize dependencies
     */
    dmck_audioplayer.has_shortcode = true;
    playlist_control.init();

	let dmck_audioplayer_end = function() {
		let cookie = playlist.cookie.get();
		if (cookie) {
			cookie = JSON.parse(cookie);
			if (typeof cookie.tab !== 'undefined') {
                jQuery('#info-tabs a[href="' + cookie["tab"] + '"]').tab('show');
                jQuery(".dmck-audio-playlist-track").removeClass('active');	
				playlist_control.initAudio( jQuery("#dmck_audioplayer .tab-pane.active .dmck-audio-playlist-track").first() );
				return;
			}
		}
		let playlist_config = dmck_audioplayer.playlist_config ? JSON.parse(dmck_audioplayer.playlist_config) : "";
		if(playlist_config){
            jQuery('#info-tabs a[href="#'+ playlist_config[0].id  +'"]').tab('show');
            jQuery(".dmck-audio-playlist-track").removeClass('active');	
			playlist_control.initAudio( jQuery("#dmck_audioplayer .tab-pane.active .dmck-audio-playlist-track").first() );
		}
		
	}
    let p1 = Promise.resolve(playlist.init());
    let p2 = Promise.resolve(access_log.init());
    Promise.all(
        [
            p1, 
            p2
        ]
    ).then(function() {

        dmck_audioplayer_end();
        jQuery(".site-info").append( playlist.powered_by );
        if(dmck_audioplayer.autoplay){
            if( jQuery('audio')[0] ){ jQuery('audio')[0].load().play(); }
        }        
    }); 

});



