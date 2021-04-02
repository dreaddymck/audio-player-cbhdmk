"use strict";

const admin_functions = {
    cookie: {
        id: function(id){
            return id ? id : (dmck_audioplayer.plugin_slug + "-" + window.location.hostname);
        },
        set: function (obj) {
            let cookie = admin_functions.cookie.get();
            if (cookie) {
                cookie = JSON.parse(cookie);
                let keys = Object.keys(obj);
                keys.forEach(key => {
                    cookie[key] = obj[key];
                });
            } else {
                cookie = obj;
            }
            jQuery.cookie(admin_functions.cookie.id(), JSON.stringify(cookie), {
                expires: 30
            })
        },
        get: function () {
            return jQuery.cookie(admin_functions.cookie.id());
        }
    },
    onload: function () {
        admin_events.init();
        let cookie = admin_functions.cookie.get();
        if (cookie) {
            cookie = JSON.parse(cookie);
            cookie.tab = (typeof cookie.tab !== 'undefined') ? cookie.tab : "parent-tabs-1";
            jQuery("ul.parent-tabs > li").removeClass('current');
            jQuery(".parent-tab-content").removeClass('current');
            jQuery("ul.parent-tabs > li[data-tab*='" + cookie.tab + "']").addClass('current');
            jQuery("#" + cookie.tab).addClass('current');

            cookie.playlist_config_selected = (typeof cookie.playlist_config_selected !== 'undefined') ? cookie.playlist_config_selected : 0;
            jQuery('select[name="playlist_config_selection"]')[0].selectedIndex = cookie.playlist_config_selected
            jQuery('.playlist-config-tab-content').removeClass('current');
            jQuery("#playlist-config-tab-" + cookie.playlist_config_selected).addClass('current');
            admin_functions.cookie.set({ "playlist_config_selected": cookie.playlist_config_selected });
        }
        jQuery.get(dmck_audioplayer.plugin_url + 'README.md', function (data) {
            let content = marked(data);
            jQuery('.tab-about').html(content);
        });
        jQuery("#wp-admin-bar-" + dmck_audioplayer.plugin_slug).css("font-style","italic");
    },
    submit_form(){
        jQuery(document.body).css({'cursor' : 'wait'});
        let form = jQuery('form[name*="admin-settings-form"]');
        let url = "options.php"
        let data = jQuery(form).serializeArray();
        new Promise(function (resolve, reject) {
            jQuery.ajax({
                type: "POST",
                url: url,
                data: data,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', dmck_audioplayer.nonce);
                },
            })
            .done(function (data) { resolve(data); })
            .fail(function (xhr, textStatus, errorThrown) {
                // console.log(errorThrown);
                reject(false);
            });
        })
        .then(
            function (results) {
                jQuery(document.body).css({'cursor' : 'default'});
                document.location.reload();
             },
            function (error) {
                jQuery(document.body).css({'cursor' : 'default'});
                admin_functions.notice(".notice-error", error);
             }
        );
    },
    config_update: function(elem,id){

        let target = elem.name.replace("select_config_", "");
        let config = JSON.parse(jQuery("textarea[name='playlist_config']").val());
        config.find(x => x.id === id)[target] = jQuery(elem).val();
        jQuery(elem).parent("div").children("input[name='" + target + "']").val(JSON.stringify(jQuery(elem).val()));
        jQuery("textarea[name='playlist_config']").val(JSON.stringify(config,"",8));

    },
    export_tables: function(){
        let url = dmck_audioplayer.site_url + "/wp-json/" + dmck_audioplayer.plugin_slug + "/" + dmck_audioplayer.plugin_version + "/api/export_tables";
        function download(str){
            let doc = window.location.hostname + "-" + dmck_audioplayer.plugin_slug + ".export." + Date.now() + ".sql"
            let elem = document.createElement('a');
            elem.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(str));
            elem.setAttribute('download', doc);
            elem.style.display = 'none';
            document.body.appendChild(elem);
            elem.click();
            document.body.removeChild(elem);            
        }
        new Promise(function (resolve, reject) {
            jQuery.ajax({
                type: "GET",
                url: url,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', dmck_audioplayer.nonce);
                },
            })
            .done(function (data) { resolve(data); })
            .fail(function (xhr, textStatus, errorThrown) {
                // console.log(errorThrown);
                reject(false);
            });
        })
        .then(
            function (results) {
                jQuery(document.body).css({'cursor' : 'default'});
                console.log(results);
                download(results)
             },
            function (error) {
                jQuery(document.body).css({'cursor' : 'default'});
                admin_functions.notice(".notice-error", error);
             }
        );        
    },
    string_to_slug: function (str)
    {
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        var from = "àáäâèéëêìíïîòóöôùúüûñçěščřžýúůďťň·/_,:;";
        var to   = "aaaaeeeeiiiioooouuuuncescrzyuudtn------";

        for (var i=0, l=from.length ; i<l ; i++)  {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        str = str.replace('.', '-') // replace a dot by a dash
            .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by a dash
            .replace(/-+/g, '-') // collapse dashes
            .replace( /\//g, '' ); // collapse all forward-slashes

        return str;
    },
    notice: function(ident,text,timeout){
        if(!ident && !text){return false};
        timeout = timeout ? timeout : 2000;
        jQuery(ident).text(text).show("slow");
        setTimeout(function() {  jQuery(".notice").hide("slow").text(""); }, timeout);
        return false;
    },
}