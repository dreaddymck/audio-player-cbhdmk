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
            let colors      = dmck_audioplayer.chart_colors ? JSON.parse(dmck_audioplayer.chart_colors) : [];
            
            container.find( target ).each(function(index){
               jQuery(this).attr("style","color:" + (colors[index] ? colors[index] : "") ); 
                /**
                 * top_10_json is currently embeded in html - playlist-layout.php
                 * overriding date values with javascript created date value for reasons
                 */
                let date = new Date(top_10_json[index].time*1000 ).toLocaleString();
                jQuery(this).find("td").next().attr("title",date);               
               
            }).click(function(e){
                playlist_control.stopAudio();	
                jQuery(playlist_control.globals.cfg.duration).slider('option', 'min', 0);
                playlist_control.initAudio(jQuery(this));
                playlist_control.globals.cfg.playing = true;
                return;                            
            }).promise().done(function(){
                access_log.active( container.find( target + ':first-child').attr("audiourl") );
                /**
                 * top_10_json is currently embeded in html - playlist-layout.php
                 */
                access_log.reports.top_requests_chart(top_10_json);                 
            });
        },
        top_requests_chart: function(arr){
            let labels = [];
            let data = [];
            let colors = dmck_audioplayer.chart_colors ? JSON.parse(dmck_audioplayer.chart_colors) : [];
            for( let x in arr ){
                labels.push( unescape(arr[x].name));
                data.push(arr[x].count)                
            }
            jQuery("#top-10").append(`<canvas id="top-requests-chart" width="auto" height="auto"></canvas>`);            
            let ctx = jQuery("#top-requests-chart");
            let top_requests_chart = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '# of Requests',
                        data: data,
                        borderWidth: 1,
                        backgroundColor: colors,
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        labels: {
                            fontColor: "#ffffff",
                        }
                    },                    
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true,
                                fontColor: "#ffffff",
                            }
                        }],                      
                    }
                }
            }); 
        }
    },
    active: function(url){
        jQuery(".top-requests-data tr").each(function(){
            if(typeof jQuery(this).attr('audiourl') === "undefined" ){return}
            if(typeof url !== "string" ){return}
            if( url.includes(jQuery(this).attr('audiourl')) ) {
                jQuery(this).addClass('active').addClass('pulse');                                
            }else{
                jQuery(this).removeClass('active').removeClass('pulse');
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

