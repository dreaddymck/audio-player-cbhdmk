"use strict"

window.playlist_control = {	
	globals: {
		cfg: {
			duration: "." + dmck_audioplayer.plugin_slug + ' .duration',
			volume: "." + dmck_audioplayer.plugin_slug + ' .volume',
			play: "." + dmck_audioplayer.plugin_slug + ' .play',
			title: "." + dmck_audioplayer.plugin_slug + ' .title',
			pause: "." + dmck_audioplayer.plugin_slug + ' .pause',
			fwd: "." + dmck_audioplayer.plugin_slug + ' .fwd',
			rew: "." + dmck_audioplayer.plugin_slug + ' .rew',
			cover: "." + dmck_audioplayer.plugin_slug + ' .cover',
			playing: false,
			song: null,
		},
		container: "",
		target: "",
		playlists : [],
		bpm:"",		
	},
	init: function () {
		//TODO: FIX this: disabled as it doesn't work properly. But do I really need popup controls?
		// if(dmck_audioplayer.audio_control_enabled){ playlist_control.popupcontrol(); }		
		playlist_control.globals.cfg.playing = false;
		playlist_control.globals.cfg.song = null;
		// initialize the volume slider
		jQuery(playlist_control.globals.cfg.volume).slider({
			range: 'min',
			min: 1,
			max: 100,
			value: 100,
			start: function (event, ui) {},
			slide: function (event, ui) { playlist_control.globals.cfg.song.volume = ui.value / 100 },
			stop: function (event, ui) {}
		});

		// empty duration slider
		jQuery(playlist_control.globals.cfg.duration).slider({
			range: 'min',
			min: 0,
			max: 10,
			start: function (event, ui) {},
			slide: function (event, ui) { playlist_control.globals.cfg.song.currentTime = ui.value },
			stop: function (event, ui) {}
		});
		/**
		 * events
		 */
		jQuery(playlist_control.globals.cfg.play).click(function (e) {
			e.preventDefault();
			if(playlist_control.globals.container){
				playlist_control.stopAudio();
				let active = playlist_control.globals.container.children().filter(function(){ return( jQuery(this).hasClass("active") ); })
				playlist_control.initAudio(active);
			}else{
				playlist_control.play_on_click(jQuery("." + dmck_audioplayer.plugin_slug + " .tab-pane.active .dmck-audio-playlist-track").first());
			}

		});
		jQuery(playlist_control.globals.cfg.title).click(function (e) {
			e.preventDefault()
			let permalink = jQuery(this).attr('permalink')
			window.open(permalink, '_blank', '')
		});
		// pause click
		jQuery(playlist_control.globals.cfg.pause).click(function (e) {
			e.preventDefault()
			playlist_control.stopAudio()
		});
		// forward click
		jQuery(playlist_control.globals.cfg.fwd).click(function (e) {
			e.preventDefault();
			playlist_control.stopAudio();
			if(playlist_control.globals.container){
				let next =playlist_control.globals.container.children().filter(function(){
					return( jQuery(this).hasClass("active") );
				}).next();			
				if (next.length == 0) {
					next = playlist_control.globals.container.children().filter(function(){
						return( jQuery(this).hasClass(playlist_control.globals.target) );
					}).first();
				}
				playlist_control.initAudio(next)
				let id = (jQuery(playlist_control.globals.container).attr("id") || jQuery(playlist_control.globals.container).parents(".tab-pane").attr("id"));
				jQuery("." + dmck_audioplayer.plugin_slug + ' a[href="#' + id + '"]').trigger('click');
			}
		});
		// rewind click
		jQuery(playlist_control.globals.cfg.rew).click(function (e) {
			e.preventDefault();
			playlist_control.stopAudio();
			if(playlist_control.globals.container){
				let next =playlist_control.globals.container.children().filter(function(){
					return( jQuery(this).hasClass("active") );
				}).prev();			
				if (next.length == 0) {
					next = playlist_control.globals.container.children().filter(function(){
						return( jQuery(this).hasClass(playlist_control.globals.target) );
					}).last();
				}			
				playlist_control.initAudio(next);
				let id = (jQuery(playlist_control.globals.container).attr("id") || jQuery(playlist_control.globals.container).parents(".tab-pane").attr("id"));
				jQuery("." + dmck_audioplayer.plugin_slug + ' a[href="#' + id + '"]').trigger('click');
			}

		});
	},
	initAudio: function (elem) {

		if (!elem.attr('audiourl')) { return; }

		let url = elem.attr('audiourl');
		let title = playlist_control.DecodeEntities(elem.attr('title'));
		let cover = elem.attr('cover')
		let wavformpng = elem.attr('wavformpng')
		let permalink = elem.attr('permalink')
		let id = elem.attr('id')

		playlist_control.globals.bpm = playlist_control.bpm(elem);
		playlist_control.globals.container = jQuery(elem).parent();
		playlist_control.globals.target = elem[0].className;

		jQuery(playlist_control.globals.cfg.title).html(title).attr('permalink', permalink).attr('ID', id)
	
		playlist_control.set_cover_background(cover)
		if(wavformpng){playlist_control.set_duration_background(wavformpng)}
		playlist_control.globals.cfg.song = new Audio(url);			
		playlist_control.globals.cfg.song.addEventListener('timeupdate', function _listener() {
			if (!isNaN(playlist_control.globals.cfg.song.duration)) {
				jQuery(playlist_control.globals.cfg.duration)
					.slider({
						value: parseFloat(playlist_control.globals.cfg.song.currentTime)
					})

				jQuery("." + dmck_audioplayer.plugin_slug + ' .tracktime')
					.html(
						playlist_control.formatSecondsAsTime(playlist_control.globals.cfg.song.currentTime) +
						' / ' +
						playlist_control.formatSecondsAsTime(playlist_control.globals.cfg.song.duration))
			}
		});		
		playlist_control.globals.cfg.song.addEventListener('ended', function _listener(e) {
			playlist_control.stopAudio();
			let next =playlist_control.globals.container.children().filter(function(){
				return( jQuery(this).hasClass("active") );
			}).next();			
			if (next.length == 0) {
				next = playlist_control.globals.container.children().filter(function(){
					return( jQuery(this).hasClass(playlist_control.globals.target) );
				}).first();
			}
			playlist_control.initAudio(next)
		});
		playlist_control.globals.cfg.song.addEventListener('canplay', function _listener(e) {
			jQuery(playlist_control.globals.cfg.duration).slider('value', parseInt(playlist_control.globals.cfg.song.currentTime, 10))
			if (playlist_control.globals.cfg.playing) {
				playlist_control.playAudio(e)
			}
		});
		jQuery(".dmck-audio-playlist-track").removeClass('active').removeClass('active-highlight');	
		playlist_control.globals.container.children().filter(function(){
			return( this.id == elem[0].id )
		}).addClass('active').addClass("active-highlight")

		jQuery("#now-playing").detach().appendTo( elem.children('td:eq(0)') ).show();
		jQuery("#canvas_visualizer").detach().appendTo( elem.children('td:eq(0)') ).show("slow");	

		playlist_control.globals.cfg.playing = true;

		jQuery("." + dmck_audioplayer.plugin_slug + ' a').removeClass("active-highlight");
		jQuery("." + dmck_audioplayer.plugin_slug + ' a[href="#' + (jQuery(playlist_control.globals.container).attr("id") || jQuery(playlist_control.globals.container)
			.parents(".tab-pane").attr("id")) + '"]')
			.addClass("active-highlight");

	},
	playAudio: function (e) {		
		dmck_visualizer.init(playlist_control.globals.cfg.song, "canvas_visualizer");
		playlist_control.globals.cfg.song.play();
		jQuery(playlist_control.globals.cfg.duration).slider('option', 'max', playlist_control.globals.cfg.song.duration);

		//TODO: Need a check for bars 4/4 or 3/4
		let animation=1
		if(jQuery.isNumeric(playlist_control.globals.bpm)){ 
			animation = ((60 / playlist_control.globals.bpm) * 4).toFixed(2) 
		}
		jQuery("#now-playing").addClass("bounce").css({
			"animation": "bounce " + animation + "s infinite alternate",
			"-webkit-animation": "bounce " + animation + "s infinite alternate"
		});
	},
	stopAudio: function () {
		if(playlist_control.globals.cfg.song){ playlist_control.globals.cfg.song.pause(); }	
		playlist_control.globals.cfg.playing = false;
		jQuery("#now-playing").removeClass("bounce").css({
			"animation": "",
			"-webkit-animation": ""
		});	
	},
	set_cover_background: function (img) {
		jQuery(playlist_control.globals.cfg.cover).css({
			'background-image': 'url(' + img + ')',
		})
	},
	set_duration_background: function (img) {
		jQuery(playlist_control.globals.cfg.duration).css({
			'background-image': 'url("' + img + '")',
			'background-size': '100% 100%',
		})
	},
	play_on_click: function(elem){
		if (jQuery('.dmck-row-cover:hover').length != 0) {
			window.open(jQuery(elem).attr("permalink"), '_blank');
		}else{			
			if(playlist_control.globals.cfg.playing && jQuery(".dmck-audio-playlist-track.active").attr("id") == elem.id){
				playlist_control.stopAudio();
			}else{
				playlist_control.stopAudio();
				playlist_control.initAudio( jQuery(elem) );
			}
		}
	},
	popupcontrol: function(){		
		jQuery("." + dmck_audioplayer.plugin_slug).prepend(
			jQuery('<div id="fixed-controls" class="hidden"></div>').append( jQuery('#dmck_audioplayer .controls').clone() )
		);
		// jQuery('#fixed-controls .controls.row').children().removeClass('fa-3x').addClass('fa-2x'); 
		jQuery('#fixed-controls .controls.row').children(); 
		jQuery('#fixed-controls').width( jQuery('#dmck_audioplayer .panel').width() ) 
		jQuery( window ).resize(function() {
			jQuery('#fixed-controls').width( jQuery('#dmck_audioplayer .panel').width() )
		});	
		jQuery.fn.isAboveScreen = function()
		{
			let win = jQuery(window);		
			let viewport = { top : win.scrollTop(), left : win.scrollLeft() };
			viewport.right = viewport.left + win.width();
			viewport.bottom = viewport.top + win.height();	
			let bounds = this.offset();
			if(bounds){
				bounds.right = bounds.left + this.outerWidth();
				bounds.bottom = bounds.top + this.outerHeight();		
				return (!(viewport.top > bounds.bottom));
			}
		};	
		jQuery(document).scroll(function () {
			if( jQuery('#dmck_audioplayer .panel').isAboveScreen() ){ 
				setTimeout(function(){ 
					jQuery('#fixed-controls').addClass("hidden");  
				}, 200);
			} 
			else { 
				setTimeout(function(){ 
					jQuery('#fixed-controls').removeClass("hidden"); 
				}, 200);				
			}		
		});	
	},	
	formatSecondsAsTime: function (secs, format) {
		let hr = Math.floor(secs / 3600);
		let min = Math.floor((secs - (hr * 3600)) / 60);
		let sec = Math.floor(secs - (hr * 3600) - (min * 60));

		if (min < 10) { min = '0' + min; }
		if (sec < 10) { sec = '0' + sec; }
		return min + ':' + sec;
	},
	bpm: function(elem){
		let tagsarr = elem.attr('tags').match(/(\d.+bpm)/i);
		let bpm=0;
		if(tagsarr && tagsarr.length){ bpm = tagsarr[0].replace(/\D/g,''); }
		return bpm;				
	},
	// Encode/decode htmlentities
	EncodeEntities: function (s) {
		return jQuery('<div/>').text(s).html();
	},
	DecodeEntities: function (s) {
		return jQuery('<div/>').html(s).text();
	},
	

}

// playlist_control.init();