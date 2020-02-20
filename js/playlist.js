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
		
		if(!dmck_audioplayer){return;}

		let playlist_config = dmck_audioplayer.playlist_config ? JSON.parse(dmck_audioplayer.playlist_config) : "";

		let targets = function(elem){
			let container 	= "#" + dmck_audioplayer.plugin_slug + " #" + elem.id;
			let target 		= "." + elem.id + "-track";
			if( jQuery( target ).length ){
				jQuery( container ).find( target ).click(function () {
					playlist_control.stopAudio();	
					jQuery(playlist_control.globals.cfg.duration).slider('option', 'min', 0);
					playlist_control.initAudio(jQuery(this));
					playlist_control.globals.cfg.playing = true;
				})
				// initialization - first element in playlist
				jQuery( container ).find( target + ':first-child').addClass("active");
				playlist_control.initAudio( jQuery( container ).find( target + ':first-child') );		
			}
		}

		playlist_config.forEach(elem => {
			targets(elem);	
		});				
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

	



