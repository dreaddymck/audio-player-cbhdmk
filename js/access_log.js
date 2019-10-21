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
        global: {
            colors: ["#ffffff","#F0F0F0","#E0E0E0","#D0D0D0","#C0C0C0","#B0B0B0","#A0A0A0","#909090","#808080","#707070"],
            base_url: dmck_audioplayer.blog_url + "/wp-json/" + dmck_audioplayer.plugin_slug + '/v' + dmck_audioplayer.plugin_version,
            sorted:[],
        },
        get_obj: function(obj){
            jQuery.get(access_log.reports.global.base_url + "/playlist/", {
                s: obj.path 
            }).done(function (data) {
                var json    = jQuery.parseJSON(data);
                obj.callback(json);
                return json           
            })
        },
        top_requests: function(){
            jQuery
            .get( access_log.reports.global.base_url + '/activity/get/'  )
            .done(
                    function(response) {
                        /*
                         * response is a php urlencode string
                         */
                                               
                        if(!response){ return; }
                        if (response.errors) { console.log(response.errors); return; }                        
                        response = JSON.parse(response);

                        access_log.reports.global.sorted = [];
                        for(var x in response ){ access_log.reports.global.sorted.push([ decodeURIComponent(x), response[x]]); }
                        access_log.reports.global.sorted.sort(function(a, b) { return b[1].count - a[1].count; }); 
jQuery("#info-tabs").append(`
<li class="nav-item">
<a class="nav-link" id="top-10-tab" data-toggle="tab" href="#top-10" role="tab" aria-controls="top-10" aria-selected="false">
    <h3>Today's Top 10</h3>
</a>
</li>
                        `);
// <i class="fa fa-download todays-top-10-m3u"  title="download m3u playlist" aria-hidden="true"></i                            
                        jQuery(".tab-content").append(`
<div class="tab-pane" id="top-10" role="tabpanel" aria-labelledby="top-10-tab"></div>                      
                        `);
                        
                        let container   = jQuery('#top-10'); 
                        let target      = ".top-10-track";
                        let date;  
                        let content     = function(obj){

                            let html     = `
<table class="table table-responsive-lg top-requests-data"><thead><tr><th>Track</th><th class="text-center">Requests</th></tr></thead><tbody>
                            `;
                            for(let x in obj ){
                                
                                date = new Date(obj[x][1].time*1000 ).toLocaleString();
                    
                                html += `
<tr class="top-10-track" audiourl="Public/MUSIC/FEATURING/`+ obj[x][0] +`" style="color:`+ access_log.reports.global.colors[x] +`">
<td><h5>`+ obj[x][0] +`</h5></td>
<td class="text-center" title=" `+ date + `">
    <h1> `+ obj[x][1].count + `</h1>
</td> 
</tr>
                                `;
                            }
                            html += `
</tbody></table>        
                            `;
                            return html;
                        }
                        
                        container.html( content( access_log.reports.global.sorted.slice(0,10) ) );
                        container.find( target ).each( function(){                            
                            let callback = function(track){
                                this.target.attr({
                                    'cover':track[0].cover,
                                    'artist': track[0].artist,
                                    // 'title': track[0].title,
                                    'permalink': track[0].permalink,
                                    'wavformpng': track[0].wavformpng,
                                    'id': track[0].ID,
                                })
                                this.target.find("h5").text( playlist_control.DecodeEntities(track[0].title) )
                                    .after(`
                                        <span class="ui-li-tags">` + playlist_control.DecodeEntities(track[0].tags.toLowerCase()) + `
                                            <a class="ui-li-moreinfo" title="more info" href="` + track[0].permalink + `" target="_top"> ... </a></span>`
                                    )
                                this.target.click(function(e){                                    
                                    playlist_control.container = container;
                                    playlist_control.target = target;                                    

                                    playlist_control.stopAudio()    
                                    playlist_control.duration.slider('option', 'min', 0)
                                    playlist_control.initAudio(jQuery(this))
                                    dmck_audioplayer.playing = true    
                                    return;                            
                                })
                                                                    
                            }
                            access_log.reports.get_obj({ 
                                path : jQuery(this).attr("audiourl"),
                                callback: callback,
                                target:jQuery(this)
                            });
                        });
                        access_log.active( container.find( target + ':first-child').attr("audiourl") ); 
                        access_log.reports.top_requests_chart(access_log.reports.global.sorted.slice(0,10));
                    });

        },
        top_requests_chart: function(arr){
            let labels = [];
            let data = [];
            for( let x in arr ){
                labels.push(arr[x][0]);
                data.push(arr[x][1].count)
            }
            jQuery("#top-10").append(`
<canvas id="top-requests-chart" width="auto" height="auto"></canvas>
            `);
            
            let ctx = jQuery("#top-requests-chart");

            let top_requests_chart = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '# of Requests',
                        data: data,
                        borderWidth: 1,
                        backgroundColor: access_log.reports.global.colors,
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

