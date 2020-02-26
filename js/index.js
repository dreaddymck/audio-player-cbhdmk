"use strict";

jQuery(function() {    

    /**
     * 1st initialize playlist control
     * then initialize dependencies
     */
    dmck_audioplayer.has_shortcode = true;
    playlist_control.init();
    let p1 = Promise.resolve(playlist.init());
    let p2 = Promise.resolve(access_log.init());
    Promise.all(
        [
            p1, 
            p2
        ]
    ).then(function() {
        playlist.set_tab();
        jQuery(".site-info").append( playlist.powered_by );
        if(dmck_audioplayer.autoplay){
            if( jQuery('audio')[0] ){ jQuery('audio')[0].load().play(); }
        }        
    }); 

});



