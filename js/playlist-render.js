"use strict";

const playlist_render = {

	init: function  (response) {
	
		var json = jQuery.parseJSON(response)
	
		var description = 'originals and rebrixes. Listen, enjoy'
	
		jQuery('.playlist').html('')
	
		// console.log(json)
	
		jQuery.each(json, function (i) {
			playlist_render.element(json[i]);
		})
	
		// playlist elements - click
		playlist_control.playlist.find('li').click(function () {
			playlist_control.stopAudio()
	
			playlist_control.duration.slider('option', 'min', 0)
	
			var id_old = jQuery('.player .title').attr('ID')
			var id_new = jQuery(this).attr('ID')
	
			playlist_control.initAudio(jQuery(this))
			// play = true
			dmck_audioplayer.playing = true
		})
	},
	element: function(item){
		var title = playlist_control.DecodeEntities(item.title); // replace(/&nbsp;|&#039;|&amp;|&#039;|  /g, " ")
		var excerpt = playlist_control.DecodeEntities(item.excerpt); // .replace(/&nbsp;|&#039;|&amp;|&#039|  ;/g,	" ")
		var tags = playlist_control.DecodeEntities(item.tags.toLowerCase())

		var permalink = item.permalink
		var wavformpng = item.wavformpng
		var wavformjson = item.wavformjson
		var id = item.ID
		var moreinfo = item.moreinfo

		var li = jQuery('<li/>').addClass('ui-li-item')
			.attr('audiourl', decodeURIComponent(item.mp3))
			.attr('cover', item.cover)
			.attr('artist', item.artist)
			.attr('title', item.title)
			.attr('permalink', permalink)
			.attr('wavformpng', wavformpng)
			.attr('id', id)
			// .css({
			// 	'background-image': 'url("' + wavformpng + '")',
			// 	'background-size': '100% 100%'
			// })
			.appendTo(jQuery('.playlist'))

		let div 	= jQuery('<div>').addClass('track-content row').appendTo(li);
		let divleft = jQuery('<div>').addClass('col-lg-10').appendTo(div);
		let divright = jQuery('<div>').addClass('col-lg-2').appendTo(div);

		jQuery('<img>').addClass('ui-li-img').attr('src', item.cover).attr({'height':'auto','width':'auto'}).appendTo(divright)

		jQuery('<h5>').addClass('ui-li-title').text(title).appendTo(divleft)
		//jQuery('<small>').addClass('ui-li-excerpt').text(excerpt).appendTo(div)
		//jQuery('<br>').addClass('ui-li-br').appendTo(div)
		jQuery('<span>').addClass('ui-li-tags').text(tags).appendTo(divleft)
		//jQuery('<br>').addClass('ui-li-br').appendTo(div)
        jQuery('<span>').addClass('ui-li-moreinfo')
            .attr("title", "more information")
			.attr('permalink', permalink).click(function (e) {
			e.preventDefault()
			e.stopPropagation()
			var permalink = jQuery(this).attr('permalink')
			window.open(permalink, '_top', '')
		}).text(moreinfo).appendTo(divleft)

		jQuery('<br>').addClass('ui-li-br').appendTo(div)
	},

}