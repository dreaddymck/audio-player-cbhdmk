dmck_audioplayer.playing = false;
dmck_audioplayer.song = null;

//var play = false;
var duration = jQuery('.duration');
var volume = jQuery('.volume');

// initialize the volume slider
volume.slider({
    range: 'min',
    min: 1,
    max: 100,
    value: 100,
    start: function(event,ui) {},
    slide: function(event, ui) {
        dmck_audioplayer.song.volume = ui.value / 100;
    },
    stop: function(event,ui) {},
});

// empty duration slider
duration.slider({
    range: 'min',
    min: 0, 
    max: 10,
    start: function(event,ui) {},
    slide: function(event, ui) {
        dmck_audioplayer.song.currentTime = ui.value;
    },
    stop: function(event,ui) {}
});

function fetch_playlist(orderby, order){
	
	jQuery("body").css("cursor", "progress");	
	jQuery('.controls button').prop("disabled", "disabled");
	jQuery('.sort button').prop("disabled", "disabled");
	
	jQuery('.title').html('loading...');
	
	jQuery.get( dmck_audioplayer.plugin_url + "playlist-functions.php", {
		debug: "false", 
		orderby: orderby ? orderby : "modified",
		order: order ? order : "DESC",
	})			
	.done(function(data){
		
		render_playlist( data );
		
		// initialization - first element in playlist
		initAudio(jQuery('.playlist li:first-child'));
		
		if(! orderby) {
			
			player_events();
		}
		
		jQuery("body").css("cursor", "default");
		jQuery('.controls button').prop("disabled", "");
		jQuery('.sort button').prop("disabled", "");
	});		
}

function render_playlist(response) {

	var json 	= jQuery.parseJSON(response);

	var description = 'originals and rebrixes. Listen, enjoy';

	jQuery('.playlist').html('');
	
	//console.log(json);
	
	jQuery.each(json, function(i)
	{
		var title 		= json[i].title.replace(/&nbsp;|&#039;|&amp;|&#039;|  /g, " ");
		var excerpt		= json[i].excerpt.replace(/&nbsp;|&#039;|&amp;|&#039|  ;/g, " ");
		var permalink	= json[i].permalink;
		var wavformpng	= json[i].wavformpng;
		var wavformjson	= json[i].wavformjson;
		var id			= json[i].ID;
		var moreinfo	= json[i].moreinfo;
		
	    var li = jQuery('<li/>')
	        .addClass('ui-li-item')
	        .attr('audiourl', json[i].mp3 )
	        .attr('cover', json[i].cover )
	        .attr('artist', title )
	        .attr('permalink', permalink )
	        .attr('wavformpng', wavformpng )
	        .attr('id', id )
	        .appendTo( jQuery('.playlist') );

	    jQuery('<img>')
	        .addClass('ui-li-img')
	        .attr('src', json[i].cover )
	        .attr('height', "50px" )
	        .attr('width', "50px" )
	        .appendTo(li);
	    
	    jQuery('<div>')
	        .addClass('ui-li-title')
	        .text( title )
	        .appendTo(li);
	    
	    jQuery('<div>')
	        .addClass('ui-li-excerpt')
	        .text( excerpt )
	        .appendTo(li);
	    
	    jQuery('<div>')
	        .addClass('ui-li-tags')
	        .text( json[i].tags.toLowerCase() )
	        .appendTo(li);
	    
	    jQuery('<span>')
	        .addClass('ui-li-moreinfo')
	        .attr('permalink', permalink )
	        .click(function (e) {
	    	    e.preventDefault();
	    	    e.stopPropagation();
	    	    var permalink = jQuery(this).attr("permalink");
	    	    window.open(permalink,'_blank','');
	    	})
	        .text( moreinfo )
	        .appendTo(li);	    
	    
	    jQuery('<br>')
	        .addClass('ui-li-br')
	        .appendTo(li);	    
	});	
	
	// playlist elements - click
	jQuery('.playlist li').click(function () {
		
	    stopAudio();
	    
	    duration.slider("option", "min", 0);
	    
	    var id_old = jQuery('.player .title').attr("ID");
	    var id_new = jQuery(this).attr("ID");
	    
	    initAudio(jQuery(this));
	    //play = true;
	    dmck_audioplayer.playing = true;


	});		
	
}


function player_events(){	

	// set volume
	dmck_audioplayer.song.volume = 0.8;
	
	// play click
	jQuery('.play').click(function (e) {
	    e.preventDefault();
	    playAudio();
	    //play = true;
	    dmck_audioplayer.playing = true;
	});

	jQuery('.player .title').click(function (e) {
	    e.preventDefault();
	    var permalink = jQuery(this).attr("permalink");
	    window.open(permalink,'_blank','');
	});
	
	// pause click
	jQuery('.pause').click(function (e) {
	    e.preventDefault();
	    stopAudio();
	    //play = true;
	    dmck_audioplayer.playing = false;
	});
	
	
	//forward click
	jQuery('.fwd').click(function (e) {
	    
		e.preventDefault();
	    e.stopPropagation();
	    
	    stopAudio();

	    var next = jQuery('.playlist li.active').next();
	    if (next.length == 0) {
	        next = jQuery('.playlist li:first-child');
	    }
	    
	    initAudio(next);
	    //play = true;
	    dmck_audioplayer.playing = true;	    
	});

	// rewind click
	jQuery('.rew').click(function (e) {
	    e.preventDefault();
	    e.stopPropagation();
	    
	    stopAudio();

	    var prev = jQuery('.playlist li.active').prev();
	    if (prev.length == 0) {
	        prev = jQuery('.playlist li:last-child');
	    }
	    initAudio(prev);
	    //play = true;
	    dmck_audioplayer.playing = true;
	});	

	
	//show playlist
	jQuery('.showlist').click(function (e) {
	    
		e.preventDefault();
	    e.stopPropagation();
	    
	    if( jQuery('.playlist').hasClass('hidden') ) {
		    jQuery('.playlist').fadeIn(300).removeClass('hidden');
		    jQuery('.sort').fadeIn(300).removeClass('hidden');
		    
		    jQuery( ".showlist" ).button({
		        icons: { primary: "ui-icon-folder-open" },
		        text: false
		    });		    
		    
		    
	    }else{
		    jQuery('.playlist').fadeOut(300).addClass('hidden');
		    jQuery('.sort').fadeOut(300).removeClass('hidden');

		    jQuery( ".showlist" ).button({
		        icons: { primary: "ui-icon-folder-collapsed" },
		        text: false
		    });		    
	    
	    }
	    
	});
	
	jQuery('.sortdef').on('click', function(e){
		e.preventDefault();	
		stopAudio();
		fetch_playlist('rand', 'DESC');	
	});
	jQuery('.sortnew').on('click', function(e){
		e.preventDefault();	
		stopAudio();
		fetch_playlist('date', 'DESC');	
	});
	jQuery('.sortold').on('click', function(e){
		e.preventDefault();
		stopAudio();
		fetch_playlist('date', 'ASC')	
	});		
}
function set_cover_background(img){
	 
    jQuery('.player .cover').css( {
		'background-image' :'url(' + img + ')',
		'background-size' : 'cover'
	} );
	
}
function set_cover_click(str){
    jQuery('.player .cover')
    	.css("cursor", "pointer")
    	.unbind( "click" )
    	.bind( "click", function(){
    		window.open(str,'_blank');
    	} );	
}
function set_duration_background(img){
 
    jQuery('.duration').css( {
		'background-image' :'url(' + img + ')',
		'background-size' : 'cover'
	} );
	
}

function initAudio(elem) {
    var url = elem.attr('audiourl');
    var title = elem.attr('artist');
    var cover 	= elem.attr('cover');
    var wavformpng = elem.attr('wavformpng');
    var artist = elem.attr('artist');
    var permalink = elem.attr("permalink");
    var id = elem.attr("ID");
    
    jQuery('.player .title').text(title)
    	.attr("permalink", permalink)
    	.attr("ID", id);
    jQuery('.player .artist').text(artist);
    // jQuery('.player .cover').css('background-image','url(' + cover + ')' );
    set_cover_background(cover);
    set_cover_click(permalink);
    set_duration_background(wavformpng);

    //song = new Audio('data/' + url);
    dmck_audioplayer.song = new Audio(url);
    
    // timeupdate event listener
    dmck_audioplayer.song.addEventListener('timeupdate',function (){
		
    	if( ! isNaN(dmck_audioplayer.song.duration) ){
    		
    		duration.slider( { value: parseFloat(dmck_audioplayer.song.currentTime) } );
    		
            jQuery('.tracktime').html( formatSecondsAsTime(dmck_audioplayer.song.currentTime) + ' / ' + formatSecondsAsTime(dmck_audioplayer.song.duration) );
        }    		
    	
    });
    
    dmck_audioplayer.song.addEventListener('ended',function(e){
	    stopAudio();
	    
	    var next = jQuery('.playlist li.active').next();
	    if (next.length == 0) {
	        next = jQuery('.playlist li:first-child');
	    }
	    
	    initAudio(next);
    });
    
    dmck_audioplayer.song.addEventListener('canplay',function(e){
	    
    	duration.slider('value', parseInt(dmck_audioplayer.song.currentTime, 10) );
	    
//	    if(play) {
//	    	playAudio();
//	    }
	    if(dmck_audioplayer.playing) {
	    	playAudio();
	    }    	
    });
    
    jQuery('.playlist li').removeClass('active');
    
    elem.addClass('active');
}

function playAudio() {
    
	dmck_audioplayer.song.play();

    duration.slider("option", "max", dmck_audioplayer.song.duration);

    jQuery('.play').addClass('hidden');
    jQuery('.pause').addClass('visible');
}

function stopAudio() {
    
	dmck_audioplayer.song.pause();
    
    jQuery('.play').removeClass('hidden');
    jQuery('.pause').removeClass('visible');
}

function formatSecondsAsTime(secs, format) {
	
	var hr  = Math.floor(secs / 3600);
	var min = Math.floor((secs - (hr * 3600))/60);
	var sec = Math.floor(secs - (hr * 3600) -  (min * 60));

	if (min < 10){ 
		min = "0" + min; 
	}
	if (sec < 10){ 
		sec  = "0" + sec;
	}

	return min + ':' + sec;
}



