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

        jQuery
        .get( 'data/dreaddymck.com.log.json' )
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
                        let sorted = [];
                        for(var x in response ){
                            sorted.push([ decodeURIComponent(x), response[x]]);
                        }
                        sorted.sort(function(a, b) {
                            return b[1] - a[1];
                        });

                        jQuery('.entry-content').append( access_log.widget( sorted.slice(0,5) ) )
                        //jQuery('.entry-content').append( access_log.widget( sorted ) )

                        jQuery('.top-played-track').click(function(e){                          

                            playlist_element.get( { path : jQuery(this).attr("audiourl")} )                            
                        
                        })                        
         
                    }

                });        

    },
    widget: function(obj){

        let div     = jQuery('<div class="col-lg-6 col-lg-offset-4">');
        let title   = jQuery('<h3>').text("Today's Top 5 ");
        let list    = jQuery('<ul class="top-played">');
        let str;

        for(var x in obj ){
            str = `
                <li class="top-played-track" audiourl="Public/MUSIC/FEATURING/`+ obj[x][0] +`">
                    `+ obj[x][0] +`
                </li>`;
            list.append(str);     
        }
        div.append(title);
        div.append(list);
       
        return div;
    }

};

access_log.init();