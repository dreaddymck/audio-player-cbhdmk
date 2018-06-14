"use strict";

playlist.init();
access_log.init();

jQuery(function() {

    jQuery(".cc-block").detach().appendTo('.site-content .wrap').css({"width":"100%"});

    if(dmck_audioplayer.autoplay){
     
        if( jQuery('audio')[0] ){
            jQuery('audio')[0].load();
            jQuery('audio')[0].play();
        }
    }

});



