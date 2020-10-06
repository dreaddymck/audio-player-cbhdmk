"use strict"

const playlist_control = {	
	globals: {
		cfg: {
			duration: "#" + dmck_audioplayer.plugin_slug + ' .duration',
			volume: "#" + dmck_audioplayer.plugin_slug + ' .volume',
			play: "#" + dmck_audioplayer.plugin_slug + ' .play',
			title: "#" + dmck_audioplayer.plugin_slug + ' .title',
			pause: "#" + dmck_audioplayer.plugin_slug + ' .pause',
			fwd: "#" + dmck_audioplayer.plugin_slug + ' .fwd',
			rew: "#" + dmck_audioplayer.plugin_slug + ' .rew',
			cover: "#" + dmck_audioplayer.plugin_slug + ' .cover',
			artist: "#" + dmck_audioplayer.plugin_slug + ' .artist',
			playing: false,
			song: null,
		},
		container: "",
		target: "",		
	},
	init: function () {
		playlist_control.popupcontrol();
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
			e.preventDefault()
			playlist_control.playAudio()
			let id = (jQuery(playlist_control.globals.container).attr("id") || jQuery(playlist_control.globals.container).parents(".tab-pane").attr("id"));
			jQuery('#info-tabs a[href="#' + id + '"]').tab('show');
			playlist_control.globals.cfg.playing = true
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
			playlist_control.globals.cfg.playing = false
		});
		// forward click
		jQuery(playlist_control.globals.cfg.fwd).click(function (e) {
			e.preventDefault();
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
			let id = (jQuery(playlist_control.globals.container).attr("id") || jQuery(playlist_control.globals.container).parents(".tab-pane").attr("id"));
			jQuery('#info-tabs a[href="#' + id + '"]').tab('show');
			playlist_control.globals.cfg.playing = true
		});
		// rewind click
		jQuery(playlist_control.globals.cfg.rew).click(function (e) {
			e.preventDefault();
			playlist_control.stopAudio();
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
			jQuery('#info-tabs a[href="#' + id + '"]').tab('show');
			playlist_control.globals.cfg.playing = true
		});
		jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			let target = jQuery(e.target).attr("href") // activated tab
			_functions.cookie.set({ "tab": target });
		});
	},
	initAudio: function (elem) {

		if (!elem.attr('audiourl')) { return; }

		let url = elem.attr('audiourl');
		let title = playlist_control.DecodeEntities(elem.attr('artist')) + ' - ' +playlist_control.DecodeEntities(elem.attr('title'));
		let cover = elem.attr('cover')
		let wavformpng = elem.attr('wavformpng')
		let artist = elem.attr('artist')
		let permalink = elem.attr('permalink')
		let id = elem.attr('id')

		playlist_control.globals.container = jQuery(elem).parent();
		playlist_control.globals.target = elem[0].className;

		jQuery(playlist_control.globals.cfg.title).html(title).attr('permalink', permalink).attr('ID', id)
		jQuery(playlist_control.globals.cfg.artist).text(artist)
	
		playlist_control.set_cover_background(cover)
		// playlist_control.set_cover_click(permalink)
		playlist_control.set_duration_background(wavformpng)

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
				playlist_control.playAudio()
			}
		});
		jQuery(".dmck-audio-playlist-track").removeClass('active');	
		playlist_control.globals.container.children().filter(function(){
			return( this.id == elem[0].id )
		}).addClass('active');
	},
	playAudio: function () {
		playlist_control.globals.cfg.song.play()
		jQuery(playlist_control.globals.cfg.duration).slider('option', 'max', playlist_control.globals.cfg.song.duration)

		jQuery(playlist_control.globals.cfg.play).addClass('hidden')
		jQuery(playlist_control.globals.cfg.pause).removeClass('hidden')
	},
	stopAudio: function () {
		if(playlist_control.globals.cfg.song){ playlist_control.globals.cfg.song.pause(); }		
		jQuery(playlist_control.globals.cfg.play).removeClass('hidden')
		jQuery(playlist_control.globals.cfg.pause).addClass('hidden')
	},
	set_cover_background: function (img) {
		jQuery(playlist_control.globals.cfg.cover).css({
			'background-image': 'url(' + img + ')',
			'background-size': '100% auto',
			'opacity': 0.8
		})
	},
	set_duration_background: function (img) {
		jQuery(playlist_control.globals.cfg.duration).css({
			'background-image': 'url("' + img + '")',
			'background-size': '100% 100%'
		})
	},
	play_on_click: function(elem){
		if (jQuery('.dmck-row-cover:hover').length != 0) {
			window.open(jQuery(elem).attr("permalink"), '_blank')
		}
		else{
			
			playlist_control.stopAudio();
			jQuery(playlist_control.globals.cfg.duration).slider('option', 'min', 0);

			if(playlist_control.globals.cfg.playing && jQuery(".dmck-audio-playlist-track.track-content.active").attr("id") == elem.id){
				playlist_control.globals.cfg.playing = false;
				return;
			}
			
			playlist_control.initAudio( jQuery(elem) );
			playlist_control.globals.cfg.playing = true;
		}
	},
	popupcontrol: function(){		
		jQuery("#" + dmck_audioplayer.plugin_slug).prepend(
			jQuery('<div id="fixed-controls" class="hidden"></div>').append( jQuery('#dmck_audioplayer .panel-heading.options').clone() )
		);
		jQuery('#fixed-controls .controls.row').children().removeClass('fa-3x').addClass('fa-2x'); 
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
			if( jQuery('#dmck_audioplayer .panel').isAboveScreen() ){ //#dmck_audioplayer > div.row > div > div > div.panel-heading.options > div
				setTimeout(function(){ 
					// console.log("hide");
					jQuery('#fixed-controls').addClass("hidden");  
				}, 200);
			} 
			else { 
				setTimeout(function(){ 
					// console.log("show");
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
	// Encode/decode htmlentities
	EncodeEntities: function (s) {
		return jQuery('<div/>').text(s).html();
	},
	DecodeEntities: function (s) {
		return jQuery('<div/>').html(s).text();
	},
	

}

// playlist_control.init();