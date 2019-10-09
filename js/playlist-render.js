"use strict";

const playlist_render = {

	init: function  (response) {
	
		let json = jQuery.parseJSON(response)
		let container = jQuery('#playlist'); 
		let target = ".featured-track";

		container.html('')	
		jQuery.each(json, function (i) {
			playlist_render.element(json[i]);
		})
	
		container.find( target ).click(function () {
			playlist_control.container = container;
			playlist_control.target = target;
			playlist_control.stopAudio()	
			playlist_control.duration.slider('option', 'min', 0)
			playlist_control.initAudio(jQuery(this))
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

		var li = jQuery('<li/>')
			.addClass('ui-li-item featured-track')
			.attr('audiourl', decodeURIComponent(item.mp3))
			.attr('cover', item.cover)
			.attr('artist', item.artist)
			.attr('title', item.title)
			.attr('permalink', permalink)
			.attr('wavformpng', wavformpng)
			.attr('id', id)
			.appendTo(jQuery('#playlist'))

		let div 	= jQuery('<div>').addClass('track-content row').appendTo(li);
		let divleft = jQuery('<div>').addClass('col-lg-10').appendTo(div);
		let divright = jQuery('<div>').addClass('col-lg-2 text-center').appendTo(div);

		jQuery('<img>').addClass('ui-li-img').attr('src', item.cover).attr({'height':'100','width':'100'}).appendTo(divright)

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