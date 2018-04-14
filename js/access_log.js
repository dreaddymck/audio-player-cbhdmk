"use strict";

const access_log = {

	init: function(){ 
        this.defer( this.setup ) 
    },		
	defer: function (method) {
		if (window.jQuery) {
			method();
		} else {
			setTimeout(function() { this.defer(method) }, 500);
		}
    },
    setup: function(){

        if( ! dmck_audioplayer.is_front_page){
            return false
        }

        access_log.reports.top_requests_now();
        //access_log.reports.top_requests_today();        

    },    
    reports: 
    {
        top_requests_now: function(){

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
    
                            jQuery(".info-tabs").append(`
<li><a data-toggle="tab" href="#tab-top-request" id="#tab-top-request">Today's Top 10</a></li>
                            `);
                            
                            jQuery(".tab-content").append(`
<div id="tab-top-request" class="tab-pane fade top-requests"></div>                        
                            `); 

                            response = JSON.parse(response)
    
                            let sorted = [];
                            for(var x in response ){
                                sorted.push([ decodeURIComponent(x), response[x]]);
                            }
                            sorted.sort(function(a, b) {
                                return b[1].count - a[1].count;
                            });
    
                            jQuery('.top-requests').append( access_log.widget( sorted.slice(0,10) ) ).find(".top-requests-data i").each(function(e){
                                return jQuery(this).addClass("btn-xs");
                            });
    
                            jQuery('.top-played-track').click(function(e){
                                
                                let url = jQuery(this).attr("audiourl");
    
                                let track = "";
    
                                playlist_control.playlist.find('li').each(function(){
                                    
                                    if( jQuery(this).attr('audiourl').includes(url) )
                                    {
                                        jQuery(this).trigger("click");
                                        
                                        track =  jQuery(this).attr('audiourl');
    
                                        return;
                                    }
                                })
                              
                                if(! track ){
                                    playlist_element.get( { path : jQuery(this).attr("audiourl")} );
                                }
    
                                playlist_control.stopAudio()
    
                                playlist_control.duration.slider('option', 'min', 0)                        
    
                                playlist_control.initAudio( track );
                    
                                dmck_audioplayer.playing = true  
                                
                                access_log.active( track );    
                               
                                return;
                            
                            })                        
             
                        }
    
                    });

        },
        top_requests_today: function(){
            
            let param = jQuery.param({
                "options":"get-today"
            });
        
            jQuery
            .get( dmck_audioplayer.plugin_url + '/lib/access_log_reports.php?' + param   )
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
                        jQuery(".info-tabs").append(`
<li><a data-toggle="tab" href="#tab-chart" id="#tab-chart">Chart</a></li>
                        `);

                        jQuery(".tab-content").append(`
<div id="tab-chart" class="tab-pane fade">
    <canvas id="top-requests-chart" width="100%" height="auto"></canvas>
</div>                        
                        `);                        
                        
                        response = JSON.parse(response); 

                        let ctx = jQuery("#top-requests-chart");

                        let top_requests_chart = new Chart(ctx, {
                            type: 'horizontalBar',
                            data: {
                                labels: JSON.parse(  decodeURIComponent(response[0][0]) ),
                                datasets: [{
                                    label: '# of Requests',
                                    data: JSON.parse(response[0][2]),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero:true
                                        }
                                    }],
                                  
                                }
                            }
                        });                        

                    }

                    
                });
        }
    },

    active: function(url){

        jQuery(".top-requests-data tr").each(function(){
                                
            if( url.includes(jQuery(this).attr('audiourl')) )
            {
                jQuery(this).addClass('active');
            }else{
                jQuery(this).removeClass('active');
            }
        })

    },
    widget: function(obj){

        let str     = `
<table class="top-requests-data">
<thead>
<tr>
  <th class="col-sm-9">Track</th>
  <th class="col-sm-1">Requests</th>
  <th class="col-sm-2">Time</th>
</tr>
</thead>
<tbody>
 
        `;
        

        for(var x in obj ){
            
            let date = new Date(obj[x][1].time*1000 ).toLocaleString();

            str += `
<tr class="top-played-track" audiourl="Public/MUSIC/FEATURING/`+ obj[x][0] +`">
    <td>
        <small>
            `+ obj[x][0] +`
        </small>    
    </td>
    <td>
        <small>
            `+ obj[x][1].count +`
        </small>
    </td>
    <td>
        <small>
            `+ date +`
        </small> 
    </td> 
</tr>
            `;
           ;     
        }

        
        str += `
</tbody>
</table>        
        `;
        return str;
    },
    player_control: function(){

		if( jQuery('.playlist').length ){

            let player = jQuery('body.home .panel .controls').html();
			
			return player

		}        
    }

};

access_log.init();