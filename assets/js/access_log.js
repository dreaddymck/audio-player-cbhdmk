"use strict";

window.access_log = {

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
                let date = new Date(top_10_json.data[index].time*1000 ).toLocaleString();
                let cover = jQuery(this).attr('cover');
                jQuery(this).find("td.dmck-row-cover div").css({   
                    'background-image': 'linear-gradient( rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5) ), url(' + cover + ')',                   
                })
                jQuery(this).find("td.dmck-row-cover h1").attr("title",date + "\nClick for details");               
                
            }).click(function(e){
                playlist_control.play_on_click(this);
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
                let param = {id: top_10_json.id }
                let title = (top_10_json.title ? (top_10_json.title) + " " : "") + " RSS";
                
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

