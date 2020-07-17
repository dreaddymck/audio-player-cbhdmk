"use strict";

const access_log = {

	init: function(){ 
        this.defer( this.reports.top_requests ) 
    },		
	defer: function (method) {
        if (window.jQuery) { method(); } 
        else { setTimeout(function() { this.defer(method) }, 500); }
    },   
    reports: 
    {
        global: {},
        top_requests: function(){
            if(!jQuery("#top-10").length){return;}
            let container   = jQuery('#top-10'); 
            let target      = ".top-10-track";
            let colors      = _functions.is_json_string(dmck_audioplayer.chart_color_array)  ? JSON.parse(dmck_audioplayer.chart_color_array) : [];
            container.find( target ).each(function(index){
               jQuery(this).attr("style","color:" + (colors[index] ? colors[index] : "") ); 
                /**
                 * top_10_json is currently embeded in html - playlist-layout.php
                 * overriding date values with javascript created date value for reasons
                 */
                if(typeof top_10_json === 'undefined'){return;}
                let date = new Date(top_10_json[index].time*1000 ).toLocaleString();
                let cover = jQuery(this).attr('cover');
                jQuery(this).find("td.dmck-row-cover div").css({   
                    'background-image': 'url(' + cover + ')',                   
                })
                jQuery(this).find("td.dmck-row-cover h1").attr("title",date);               
                
            }).click(function(e){

                if (jQuery('.dmck-row-cover:hover').length != 0) {
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
                let elem = container.find( target + ':first-child').attr("audiourl");
                if(!elem){ return; }
                if(!elem.length){ return; }
                // jQuery("#top-10").removeClass("hidden");
                // jQuery("#tab-top-10").removeClass("hidden");
                access_log.active( elem );
                /**
                 * add rss link for this list
                 */
                let param = { type: "top-count" }
                let title = "Todays top 10 RSS";
                
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
                .appendTo( jQuery('#top-10') );                 
            });            
        },
    },
    active: function(url){
        jQuery(".top-requests-data tr").each(function(){
            if(typeof jQuery(this).attr('audiourl') === "undefined" ){return}
            if(typeof url !== "string" ){return}
            if( url.includes(jQuery(this).attr('audiourl')) ) {
                jQuery(this).addClass('active');                                
            }else{
                jQuery(this).removeClass('active');
            }
        })
    },
    player_control: function(){
		if( jQuery('.playlist').length ){
            let player = jQuery('body.home .panel .controls').html();			
			return player
		}        
    }
};

