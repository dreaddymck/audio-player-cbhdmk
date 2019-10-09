"use strict";

jQuery(function() {    

    let p1 = Promise.resolve(playlist.init());
    let p2 = Promise.resolve(access_log.init());
    Promise.all([p1, p2]).then(function(resp) {
        console.log(resp);
        if(dmck_audioplayer.autoplay){
     
            if( jQuery('audio')[0] ){
                jQuery('audio')[0].load();
                jQuery('audio')[0].play();
            }
        }        
    }); 

});



