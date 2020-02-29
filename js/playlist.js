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
			let postids		= [];
			if( jQuery( target ).length ){
				
				jQuery( container ).find( target ).each(function(index){
					jQuery(this).attr("style","color:" + (colors[index] ? colors[index] : "") );
					postids.push(jQuery(this).attr("post-id"));  
				 }).click(function () {
					playlist_control.stopAudio();	
					jQuery(playlist_control.globals.cfg.duration).slider('option', 'min', 0);
					playlist_control.initAudio(jQuery(this));
					playlist_control.globals.cfg.playing = true;
				}).promise().done(function(){
					// initialization - first element in playlist
					jQuery( container ).find( target + ':first-child').addClass("active");
					
					/**
					 * add rss link for this list
					 */
					let param = { id: postids }
                	jQuery("<div />",{ class: "text-center" })
					.append(
						jQuery("<a />", {
							"href": dmck_audioplayer.site_url + "/feed/playlist/?" + jQuery.param(param) ,
							"title": elem.title + " rss feed",
							"target": "_blank"
						})
						.append(
							jQuery("<img />", {
								"src": dmck_audioplayer.site_url + "/wp-includes/images/rss-2x.png",
								"vspace":"12"
							})                
						)
					)
					.appendTo( jQuery('#' + elem.id) );
           		});
			}
			// console.log(postids);
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

	



