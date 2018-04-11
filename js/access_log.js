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

        let param = jQuery.param( {"options":"get"} );
        
        jQuery
        .get( dmck_audioplayer.plugin_url + '/lib/dreaddymck.com.accesslog.php?' + param   )
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
                    } 
                    else 
                    {                        
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

                        let play = function(obj){


                        }


                        jQuery('.top-played-track').click(function(e){
                            
                            let url = jQuery(this).attr("audiourl");

                            let obj = "";

                            playlist_control.playlist.find('li').each(function(){
                                
                                if( jQuery(this).attr('audiourl').includes(url) )
                                {
                                    jQuery(this).trigger("click");
                                    
                                    obj =  jQuery(this).attr('audiourl');

                                    return;
                                }
                            })
                          
                            if(!obj){
                                playlist_element.get( { path : jQuery(this).attr("audiourl")} );
                            }

                            playlist_control.stopAudio()

                            playlist_control.duration.slider('option', 'min', 0)                        

                            playlist_control.initAudio(obj);
                
                            dmck_audioplayer.playing = true                               
                           
                            return;
                        
                        })                        
         
                    }

                });        

    },
    widget: function(obj){

        let control = ""; //this.player_control();

        let str     = `
<table class="top-requests-data">
<thead>
<tr>
  <th scope="col-sm-8">
    <div class="col-xs-2">Track</div>
    <div class="col-xs-4">`+ control +`</div>
    <div class="col-xs-6">&nbsp;</div>
  </th>
  <th scope="col-sm-1">Requests</th>
  <th scope="col-sm-3">Time</th>
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