"use strict"

const playlist_control = {
	
	duration: jQuery('.duration'),	
	volume: jQuery('.volume'),	
	container:jQuery('#playlist'),
	target:".featured-track",
	init: function(){

		dmck_audioplayer.playing = false;
		dmck_audioplayer.song = null;

		// initialize the volume slider
		playlist_control.volume.slider({
			range: 'min',
			min: 1,
			max: 100,
			value: 100,
			start: function (event, ui) {},
			slide: function (event, ui) {
				dmck_audioplayer.song.volume = ui.value / 100
			},
			stop: function (event, ui) {}
		});

		// empty duration slider
		playlist_control.duration.slider({
			range: 'min',
			min: 0,
			max: 10,
			start: function (event, ui) {},
			slide: function (event, ui) {
				dmck_audioplayer.song.currentTime = ui.value
			},
			stop: function (event, ui) {}
		});		
	},
	fetch_playlist: function (orderby, order) {

		jQuery('body').css('cursor', 'progress')
		jQuery('.controls button').prop('disabled', 'disabled')
		jQuery('.sort button').prop('disabled', 'disabled')		
		jQuery('.title').html('loading...')
	
		jQuery.get(dmck_audioplayer.plugin_url + 'playlist-functions.php', {
			debug: 'false',
			orderby: orderby ? orderby : 'date',
			order: order ? order : 'DESC'
		}).done(function (data) {
			playlist_render.init(data)
	
			// initialization - first element in playlist
			playlist_control.initAudio(playlist_control.container.find( playlist_control.target + ':first-child'));

			access_log.active( playlist_control.container.find( playlist_control.target + ':first-child').attr("audiourl") );
	
			if (!orderby) {
				playlist_control.player_events()
			}
	
			jQuery('body').css('cursor', 'default')
			jQuery('.controls button').prop('disabled', '')
			jQuery('.sort button').prop('disabled', '')
		})
	},
	

	player_events: function  () {

		// set volume
		dmck_audioplayer.song.volume = 1.0;
	
		// play click
		jQuery('.play').click(function (e) {
			e.preventDefault()
			playlist_control.playAudio()
			// play = true
			dmck_audioplayer.playing = true
		});
	
		jQuery('.player .title').click(function (e) {
			e.preventDefault()
			var permalink = jQuery(this).attr('permalink')
			window.open(permalink, '_top', '')
		});
	
		// pause click
		jQuery('.pause').click(function (e) {
			e.preventDefault()
			playlist_control.stopAudio()
			// play = true
			dmck_audioplayer.playing = false
		});
	
		// forward click
		jQuery('.fwd').click(function (e) {
			e.preventDefault()
			e.stopPropagation()
	
			playlist_control.stopAudio()
	
			var next = playlist_control.container.find( playlist_control.target + '.active').nextAll().filter(function(){
				return jQuery(this).attr('audiourl').length > 0
			})
			if (next.length == 0) {
				next = playlist_control.container.find( playlist_control.target + ':first-child')
			}
	
			playlist_control.initAudio(next)
			// play = true
			//dmck_audioplayer.playing = true
		});
	
		// rewind click
		jQuery('.rew').click(function (e) {
			e.preventDefault()
			e.stopPropagation()
	
			playlist_control.stopAudio()
	
			var prev = playlist_control.container.find( playlist_control.target + '.active').prevAll().filter(function(){
				return jQuery(this).attr('audiourl').length > 0
			})
			if (prev.length == 0) {
				prev = playlist_control.container.find( playlist_control.target + ':last-child')
			}
			playlist_control.initAudio(prev)
			// play = true
			//dmck_audioplayer.playing = true
		});	
		// show playlist
		jQuery('.showlist').click(function (e) {
			e.preventDefault()
			e.stopPropagation()
			playlist_control.show_playlist();
		});	
		jQuery('.sortdef').on('click', function (e) {
			e.preventDefault()
			playlist_control.stopAudio()
			playlist_control.fetch_playlist('rand', 'DESC')
		});
		jQuery('.sortnew').on('click', function (e) {
			e.preventDefault()
			playlist_control.stopAudio()
			playlist_control.fetch_playlist('date', 'DESC')
		});
		jQuery('.sortold').on('click', function (e) {
			e.preventDefault()
			playlist_control.stopAudio()
			playlist_control.fetch_playlist('date', 'ASC')
		});
	},
	show_playlist: function(){
		if (jQuery('.playlist').hasClass('hidden')) {
			jQuery('.playlist').fadeIn(300).removeClass('hidden')
			jQuery('.sort').fadeIn(300).removeClass('hidden')

			jQuery('.showlistIcon').removeClass('ui-icon-plusthick')
			jQuery('.showlistIcon').addClass('ui-icon-minusthick')
		} else {
			jQuery('.playlist').fadeOut(300).addClass('hidden')
			jQuery('.sort').fadeOut(300).removeClass('hidden')

			jQuery('.showlistIcon').removeClass('ui-icon-minusthick')
			jQuery('.showlistIcon').addClass('ui-icon-plusthick')
		}
	},
	set_cover_background: function (img) {
		jQuery('.player .cover').css({
			'background-image': 'url(' + img + ')',
			'background-size': '100% auto',
			'opacity': 0.8
		})
	},
	set_cover_click: function (str) {
		jQuery('.player .cover').css('cursor', 'pointer').unbind('click').bind(
			'click', function () {
				window.open(str, '_top')
			})
	},
	set_duration_background: function  (img) {
		jQuery('.duration').css({
			'background-image': 'url("' + img + '")',
			'background-size': '100% 100%'
		})
	},
	initAudio: function (elem) {

		if( !elem.attr('audiourl') ){return;}

		var url = elem.attr('audiourl')
		
		var title = playlist_control.DecodeEntities(elem.attr('artist')) + ' - ' + 
					playlist_control.DecodeEntities(elem.attr('title')) + '<br><small>' + 
					playlist_control.DecodeEntities(elem.find('.ui-li-excerpt').text()) + '</small>'
	
		var cover = elem.attr('cover')
		var wavformpng = elem.attr('wavformpng')
		var artist = elem.attr('artist')	
		var permalink = elem.attr('permalink')
		var id = elem.attr('ID')
	
		jQuery('.player .title').html(title).attr('permalink', permalink).attr(
			'ID', id)
		jQuery('.player .artist').text(artist)
		// jQuery('.player .cover').css('background-image','url(' + cover + ')' )
		// jQuery('.this_excerpt').text(elem.find('.ui-li-excerpt')[0].innerHTML)
	
		playlist_control.set_cover_background(cover)
		playlist_control.set_cover_click(permalink)
		playlist_control.set_duration_background(wavformpng)

		dmck_audioplayer.song = new Audio(url)
	
		// timeupdate event listener
		dmck_audioplayer.song
			.addEventListener(
				'timeupdate',
				function () {
					if (!isNaN(dmck_audioplayer.song.duration)) {
						playlist_control.duration
							.slider({
								value: parseFloat(dmck_audioplayer.song.currentTime)
							})
	
						jQuery('.tracktime')
							.html(
								playlist_control.formatSecondsAsTime(dmck_audioplayer.song.currentTime)
								+ ' / '
								+ playlist_control.formatSecondsAsTime(dmck_audioplayer.song.duration))
					}
				})
	
		dmck_audioplayer.song.addEventListener('ended', function (e) {
			playlist_control.stopAudio()
	
			var next = playlist_control.container.find( playlist_control.target + '.active').nextAll().filter(function(){
				return jQuery(this).attr('audiourl').length > 0
			})
			if (next.length == 0) {
				next = playlist_control.container.find( playlist_control.target + ':first-child')
			}	
			playlist_control.initAudio(next)
		});
	
		dmck_audioplayer.song.addEventListener('canplay', function (e) {
			playlist_control.duration.slider('value',
				parseInt(dmck_audioplayer.song.currentTime, 10))
	
			// if(play) {
			// playlist_control.playAudio()
			// }
			if (dmck_audioplayer.playing) {
				playlist_control.playAudio()
			}
		});
	
		playlist_control.container.find( playlist_control.target ).removeClass('active');
	
		elem.addClass('active');
		
		access_log.active(url);
	},	

	playAudio: function () {
		dmck_audioplayer.song.play()
	
		playlist_control.duration.slider('option', 'max', dmck_audioplayer.song.duration)
	
		jQuery('.play').addClass('hidden')
		jQuery('.pause').removeClass('hidden')
	
	},

	stopAudio: function () {
		dmck_audioplayer.song.pause()
	
		jQuery('.play').removeClass('hidden')
		jQuery('.pause').addClass('hidden')
	},
	
	formatSecondsAsTime: function  (secs, format) {
		var hr = Math.floor(secs / 3600)
		var min = Math.floor((secs - (hr * 3600)) / 60)
		var sec = Math.floor(secs - (hr * 3600) - (min * 60))
	
		if (min < 10) {
			min = '0' + min
		}
		if (sec < 10) {
			sec = '0' + sec
		}
	
		return min + ':' + sec
	},
	
	// Encode/decode htmlentities
	EncodeEntities: function  (s) {
		return jQuery('<div/>').text(s).html()
	},
	DecodeEntities: function  (s) {
		return jQuery('<div/>').html(s).text()
	},		

}

playlist_control.init();





