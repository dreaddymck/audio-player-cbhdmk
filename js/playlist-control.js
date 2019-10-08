"use strict"

const playlist_control = {
	
	// var play = false
	duration: jQuery('.duration'),
	
	volume: jQuery('.volume'),
	
	playlist:jQuery('.playlist'),
	
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
			playlist_control.initAudio(playlist_control.playlist.find('li:first-child'));

			access_log.active( playlist_control.playlist.find('li:first-child').attr("audiourl") );
	
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
	
			var next = playlist_control.playlist.find('li.active').next()
			if (next.length == 0) {
				next = playlist_control.playlist.find('li:first-child')
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
	
			var prev = playlist_control.playlist.find('li.active').prev()
			if (prev.length == 0) {
				prev = playlist_control.playlist.find('li:last-child')
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

		if(!elem.attr){return}

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
	
		// playlist_control.visu(dmck_audioplayer)
		// var ctx = new AudioContext()
		// dmck_audioplayer.audioSrc = ctx.destination
		// dmck_audioplayer.analyser = ctx.createAnalyser()
		// we have to connect the MediaElementSource with the analyser
		// dmck_audioplayer.audioSrc.connect(dmck_audioplayer.analyser)
		// we could configure the analyser: e.g. analyser.fftSize (for further
		// infos read the spec)
	
		// song = new Audio('data/' + url)
		dmck_audioplayer.song = new Audio(url)
	
		// frequencyBinCount tells you how many values you'll receive from the
		// analyser
		// var frequencyData = new
		// Uint8Array(dmck_audioplayer.analyser.frequencyBinCount)
		//
		// console.log(frequencyData)
	
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
	
			var next = playlist_control.playlist.find('li.active').next()
			if (next.length == 0) {
				next = playlist_control.playlist.find('li:first-child')
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
	
		playlist_control.playlist.find('li').removeClass('active');
	
		elem.addClass('active');
		
		access_log.active(url);
	},	

	playAudio: function () {
		dmck_audioplayer.song.play()
	
		playlist_control.duration.slider('option', 'max', dmck_audioplayer.song.duration)
	
		jQuery('.play').addClass('hidden')
		jQuery('.pause').removeClass('hidden')
	
		// setTimeout(function() {
		// playlist_control.visu(dmck_audioplayer)
		// }, 1000)
	
	},
	
	visu: function (dmck_audioplayer) {
		var ctx = new AudioContext()
	
		// console.log(ctx.state)
		//
		// return
	
		if (ctx.state == 'running') {
	
			// var audioSrc = ctx.destination
			// var analyser = ctx.createAnalyser()
			// we have to connect the MediaElementSource with the analyser
			// audioSrc.connect(analyser)
			// we could configure the analyser: e.g. analyser.fftSize (for further
			// infos read the spec)
	
			// frequencyBinCount tells you how many values you'll receive from the
			// analyser
			// var frequencyData = new
			// Uint8Array(dmck_audioplayer.analyser.frequencyBinCount)
	
			// console.log(frequencyData)
	
			// we're ready to receive some data!
			// loop
			function renderFrame () {
				requestAnimationFrame(renderFrame)
				// update data in frequencyData
				dmck_audioplayer.analyser.getByteFrequencyData(frequencyData)
				// render frame based on values in frequencyData
	
				renderer = renderers['r0']
	
				renderer.init({
					count: dmck_audioplayer.analyser.frequencyBinCount,
					width: 250,
					height: 250
				})
	
				console.log(frequencyData)
	
				renderer.renderFrame(frequencyData)
	
				requestAnimationFrame(renderFrame)
			}
	
		// renderFrame()
		}
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





