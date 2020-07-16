"use strict";

const playlist = {
	powered_by: `| A crappy <a href="https://github.com/dreaddymck/audio-player-cbhdmk" target="_blank">Dreaddymck plugin</a>`,	
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
			let colors      = _functions.is_json_string(dmck_audioplayer.chart_color_array) ? JSON.parse(dmck_audioplayer.chart_color_array) : [];
			if( jQuery( target ).length ){				
				jQuery( container ).find( target ).each(function(index){
					jQuery(this).attr("style","color:" + (colors[index] ? colors[index] : "") );
					// postids.push(jQuery(this).attr("post-id"));  
				 }).click(function () {

					if (jQuery('.row-cover:hover').length != 0) {
						window.open(jQuery(this).attr("permalink"), '_blank')
					}
					else
					{					
						playlist_control.stopAudio();	
						jQuery(playlist_control.globals.cfg.duration).slider('option', 'min', 0);
						playlist_control.initAudio(jQuery(this));
						playlist_control.globals.cfg.playing = true;
					}
					return;
				}).promise().done(function(){
					// initialization - first element in playlist
					jQuery( container ).find( target + ':first-child').addClass("active");					
					/**
					 * add rss link for this list
					 */
					let param = {"tag" : elem.tag, "tag_slug__and" : elem.tag_slug__and }
					let title = elem.title + " RSS";
                	jQuery("<div />",{ class: "text-center" })
					.append(
						jQuery("<a />", {
							"href": dmck_audioplayer.site_url + "/feed/playlist/?" + jQuery.param(param) ,
							"title": title,
							"target": "_blank"
						})
						.append(
							jQuery("<img />", {
								"src": dmck_audioplayer.site_url + "/wp-includes/images/rss-2x.png",
								"vspace":"12"
							})                
						)						
					)
					.append(
						jQuery("<div />", {
							class: "text-center",
							"text": title
						})                
					)					
					.appendTo( jQuery('#' + elem.id) );
           		});
			}
		}
		playlist_config.forEach(elem => {
			targets(elem);	
		});				
	}, 
}

	



