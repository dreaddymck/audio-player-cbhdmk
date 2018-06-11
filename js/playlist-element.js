"use strict";

const playlist_element = {
    
    get: function(obj){

        jQuery.get(dmck_audioplayer.plugin_url + 'playlist-element.php', {
            debug: 'false',
            path: obj.path 
        }).done(function (data) {

            var json    = jQuery.parseJSON(data);
            var li      = {};

            jQuery.each(json, function (i) {

                var title = playlist_control.DecodeEntities(json[i].title); // replace(/&nbsp;|&#039;|&amp;|&#039;|  /g, " ")
                var excerpt = playlist_control.DecodeEntities(json[i].excerpt); // .replace(/&nbsp;|&#039;|&amp;|&#039|  ;/g,	" ")
                var tags = playlist_control.DecodeEntities(json[i].tags.toLowerCase())
        
                var permalink = json[i].permalink
                var wavformpng = json[i].wavformpng
                var wavformjson = json[i].wavformjson
                var id = json[i].ID
                var moreinfo = json[i].moreinfo
        
                li = jQuery('<li/>').addClass('ui-li-item')
                    .attr('audiourl', decodeURIComponent(json[i].mp3))
                    .attr('cover', json[i].cover)
                    .attr('artist', json[i].artist)
                    .attr('title', json[i].title)
                    .attr('permalink', permalink)
                    .attr('wavformpng', wavformpng)
                    .attr('id', id)
                    // .css({
                    //     'background-image': 'url("' + wavformpng + '")',
                    //     'background-size': '100% 100%'
                    // })
                    //.appendTo(jQuery('.playlist'))
        
                jQuery('<img>').addClass('ui-li-img').attr('src', json[i].cover).attr(
                    'height', '50px').attr('width', '50px').appendTo(li)
        
                jQuery('<div>').addClass('ui-li-title').text(title).appendTo(li)
        
                jQuery('<div>').addClass('ui-li-excerpt').text(excerpt).appendTo(li)
        
                jQuery('<div>').addClass('ui-li-tags').text(tags).appendTo(li)
        
                jQuery('<span>').addClass('ui-li-moreinfo')
                    .attr('permalink', permalink).click(function (e) {
                    e.preventDefault()
                    e.stopPropagation()
                    var permalink = jQuery(this).attr('permalink')
                    window.open(permalink, '_top', '')
                }).text(moreinfo).appendTo(li)

                if(typeof obj.callback === 'function'){
                    obj.callback(li);
                }
        
                return li;

            })            
        })
    }   
}