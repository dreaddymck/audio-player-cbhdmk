"use strict";

const playlist = {
	powered_by: `| Some <a href="https://github.com/dreaddymck/audio-player-cbhdmk" target="_blank">Dreaddymck audio plugin</a>`,	
	init: function(){ this.defer( this.setup ) },		
	defer: function (method) {
		if (window.jQuery) {
			method();
		} else {
			setTimeout(function() { this.defer(method) }, 500);
		}
	},
	setup: function(){
		if(!dmck_audioplayer){return;}

		let playlist_config = dmck_audioplayer.playlist_config ? JSON.parse(dmck_audioplayer.playlist_config) : "";

		let targets = function(elem){
			let container 	= "#" + dmck_audioplayer.plugin_slug + " #" + elem.id;
			let target 		= "." + elem.id + "-track";
			let colors      = dmck_audioplayer.chart_colors ? JSON.parse(dmck_audioplayer.chart_colors) : [];

			if( jQuery( target ).length ){
				jQuery( container ).find( target ).each(function(index){
					jQuery(this).attr("style","color:" + (colors[index] ? colors[index] : "") ); 
				 }).click(function () {
					playlist_control.stopAudio();	
					jQuery(playlist_control.globals.cfg.duration).slider('option', 'min', 0);
					playlist_control.initAudio(jQuery(this));
					playlist_control.globals.cfg.playing = true;
				}).promise().done(function(){
					// initialization - first element in playlist
					jQuery( container ).find( target + ':first-child').addClass("active");	                 
           		});

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
	set_tab: function () {
		let cookie = playlist.cookie.get();
		if (cookie) {
			cookie = JSON.parse(cookie);
			if (typeof cookie.tab !== 'undefined') {
				jQuery('#info-tabs a[href="' + cookie["tab"] + '"]').tab('show');
				playlist_control.initAudio( jQuery("#dmck_audioplayer .tab-pane.active .active") );
				return;
			}
		}
		let playlist_config = dmck_audioplayer.playlist_config ? JSON.parse(dmck_audioplayer.playlist_config) : "";
		if(playlist_config){
			jQuery('#info-tabs a[href="#'+ playlist_config[0].id  +'"]').tab('show');
			playlist_control.initAudio( jQuery("#dmck_audioplayer .tab-pane.active .active") );
		}
		
	},		
	
}

	



