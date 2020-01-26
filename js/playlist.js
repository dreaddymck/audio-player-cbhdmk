"use strict";

const playlist = {	
	init: function(){ this.defer( this.setup ) },		
	defer: function (method) {
		if (window.jQuery) {
			method();
		} else {
			setTimeout(function() { this.defer(method) }, 500);
		}
	},
	setup: function(){
		// jQuery(playlist_control.globals.cfg.title).html('loading...')
		let container = "#" + dmck_audioplayer.plugin_slug + " #playlist";
		let target = ".featured-track";		

		if( jQuery( target ).length ){
			jQuery( container ).find( target ).click(function () {
				playlist_control.stopAudio();	
				jQuery(playlist_control.globals.cfg.duration).slider('option', 'min', 0);
				playlist_control.initAudio(jQuery(this));
				playlist_control.globals.cfg.playing = true;
			})
			// initialization - first element in playlist
			playlist_control.initAudio( jQuery( container ).find( target + ':first-child') );		
		}				
	},
	cookie : {
		name: dmck_audioplayer.plugin_slug + "-cookie",
		set : function(obj){
			let cookie = playlist.cookie.get() 
			if(cookie){
                cookie = JSON.parse(cookie);
                let keys = Object.keys(obj);                
                keys.forEach(key => { cookie[key] = obj[key]; });				
			}else{ cookie = obj; }
            jQuery.cookie( playlist.cookie.name, JSON.stringify(cookie), { expires: 30 })
		},
		get : function(){ return jQuery.cookie(playlist.cookie.name); }
    }, 	
	
}

	



