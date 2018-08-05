"use strict";

playlist.init();
access_log.init();

jQuery(function() {    

    if(dmck_audioplayer.autoplay){
     
        if( jQuery('audio')[0] ){
            jQuery('audio')[0].load();
            jQuery('audio')[0].play();
        }
    }

});



