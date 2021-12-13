"use strict";

window.playlist = {
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
			if(!elem.id){return;}
			let container 	= "." + dmck_audioplayer.plugin_slug + " #" + elem.id;
			let target 		= "." + elem.id + "-track";
			let colors      = _dmck_functions.is_json_string(dmck_audioplayer.chart_color_array) ? JSON.parse(dmck_audioplayer.chart_color_array) : [];
			if( jQuery( target ).length ){				
				jQuery( container ).find( target ).each(function(index){
					jQuery(this).attr("style","color:" + (colors[index] ? colors[index] : "") );
				 }).click(function (e) {
					playlist_control.play_on_click(this);
					return;
				}).promise().done(function(){
					// initialization - first element in playlist
					jQuery( container ).find( target + ':first-child').addClass("active");					
					/**
					 * add rss link for this list
					 */
					let param = {id: elem.id }
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

		playlist_config.forEach(elem => { targets(elem); });		
		 
		if(document.getElementsByTagName('audio').length){

			let audio_obj = document.querySelectorAll('audio');
			let canvas;
			
			for(let i=0; i<audio_obj.length;i++){

				canvas = document.createElement("canvas");
				canvas.id = "canvas_visualizer_" + i;
				canvas.style = "display:";
				audio_obj[i].parentNode.insertBefore(canvas, audio_obj[i].nextSibling);
				audio_obj[i].addEventListener("playing", function(event){
					dmck_visualizer.init(event.target, this.nextSibling.id);
				})
				audio_obj[i].addEventListener("pause", function(event){});

			}
		}
	}, 
}

	



