"use strict";

const access_log = {

	init: function(){ 
        this.defer( this.run ) 
    },		
	defer: function (method) {
		if (window.jQuery) {
			method();
		} else {
			setTimeout(function() { this.defer(method) }, 500);
		}
    },
    run: function(){

        if( ! dmck_audioplayer.is_front_page){
            return false
        }
        access_log.reports.top_requests();
    },    
    reports: 
    {
        top_requests: function(){

            let param = jQuery.param( {"options":"get"} );
        
            jQuery
            .get( dmck_audioplayer.plugin_url + '/lib/reports.php?' + param   )
            .done(
                    function(response) {
                        /*
                         * response is a php urlencode string
                         */
                                               
                        if(!response){
                            return;
                        }
                        if (response.errors) { 
                            console.log(response.errors); 
                            return;
                        } 
                        else 
                        {                        
//<li><a data-toggle="tab" href="#tab-top-request" id="#tab-top-request">Today's Top 10</a></li>    
                            jQuery("#info-tabs").append(`
<li class="nav-item">
    <a class="nav-link" id="top-10-tab" data-toggle="tab" href="#top-10" role="tab" aria-controls="top-10" aria-selected="false">
        <h3>Today's Top 10</h3>
    </a>
</li>
                            `);
// <i class="fa fa-download todays-top-10-m3u"  title="download m3u playlist" aria-hidden="true"></i                            
//<div id="tab-top-request" class="tab-pane fade top-requests"></div>                              
                            jQuery(".tab-content").append(`
<div class="tab-pane" id="top-10" role="tabpanel" aria-labelledby="top-10-tab"></div>                      
                            `); 

                            response = JSON.parse(response)
    
                            let sorted = [];
                            for(var x in response ){
                                sorted.push([ decodeURIComponent(x), response[x]]);
                            }
                            sorted.sort(function(a, b) {
                                return b[1].count - a[1].count;
                            });
                            
                            let container = jQuery('#top-10'); 
                            let target = ".top-10-track"; 
                            let widget = function(obj){

                                let str     = `
<table class="table table-responsive-lg top-requests-data"><thead><tr><th>Track</th><th>Requests</th><th>Time</th></tr></thead>
<tbody>
                                `;
                                for(var x in obj ){
                                    
                                    let date = new Date(obj[x][1].time*1000 ).toLocaleString();
                        
                                    str += `
<tr class="top-10-track" audiourl="Public/MUSIC/FEATURING/`+ obj[x][0] +`">
    <td><span><h5>`+ obj[x][0] +`</h5></span></td>
    <td><span> `+ obj[x][1].count + `</span></td>
    <td><span> `+ date + `</span></td> 
</tr>
                                    `;
                                   ;     
                                }
                                str += `
</tbody></table>        
                                `;
                                return str;
                            }                           

                            let s10 = sorted.slice(0,10);
                            let html = widget( s10 );
                            container.html( html );
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
                                playlist_element.get_obj({ 
                                    path : jQuery(this).attr("audiourl"),
                                    callback: callback,
                                    target:jQuery(this)
                                });
                            })                            
    
                            access_log.active( container.find( target + ':first-child').attr("audiourl") ); 
                            access_log.reports.top_requests_chart(s10);
                            
                                  
                        }
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
                        backgroundColor: ["#ffffff","#F0F0F0","#E0E0E0","#D0D0D0","#C0C0C0","#B0B0B0","#A0A0A0","#909090","#808080","#707070"],
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

            if( url.includes(jQuery(this).attr('audiourl')) )
            {
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

