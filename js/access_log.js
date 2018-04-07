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

                        jQuery('.entry-content').append( access_log.widget( sorted.slice(0,5) ) )

                        jQuery('.top-played-track').click(function(e){                          

                            playlist_element.get( { path : jQuery(this).attr("audiourl")} )                            
                        
                        })                        
         
                    }

                });        

    },
    widget: function(obj){

        let div     = jQuery('<div>');
        let title   = jQuery('<h3>').text("Top 5");
        let list    = jQuery('<ul class="top-played">');
        let str;

        

        for(var x in obj ){
            
            let date = new Date(obj[x][1].time*1000 ).toLocaleString();

            str = `
<li class="top-played-track" audiourl="Public/MUSIC/FEATURING/`+ obj[x][0] +`">
    <row>
        <div class="col-sm-8">
            <small>
                `+ obj[x][0] +`
            </small>    
        </div>
        <div class="col-sm-1">
            <small>
                `+ obj[x][1].count +`
            </small>
        </div>
        <div class="col-sm-3">
            <small>
                `+ date +`
            </small> 
        </div>                    
    </row>
</li>
            `;
            list.append(str);     
        }
        div.append(title);
        div.append(list);
       
        return div;
    }

};

access_log.init();