"use strict";
window.admin_functions = {
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
    playlist_status_data: function(value){
        let playlist_config = jQuery("textarea[name='playlist_config']").val();
        if(playlist_config){
            playlist_config = JSON.parse(playlist_config);
            playlist_config.find(function(obj, index){
                if(typeof(obj) !== 'undefined' && typeof(obj.id) !== 'undefined' && obj.id == value){
                    let base_url_path = dmck_audioplayer.site_url + "/wp-json/" + dmck_audioplayer.plugin_slug + "/" + dmck_audioplayer.plugin_version + "/api/";
                    let url = base_url_path + "search";
                    let callback = function(results){
                        results = JSON.parse(results); 
                        let ids = []
                        results.find(function(obj, index){
                            ids.push(JSON.stringify(obj.ID)); 
                        });
                        jQuery('input[name="post_in_stats"]').val(JSON.stringify(ids));            
                        let json = {
                            type: "",
                            value: ids,
                            to: jQuery('input[name="post_in_date_to"]').val(),
                            from: jQuery('input[name="post_in_date_from"]').val()
                        }
                        admin_functions.status_data(json);                        
                    }
                    if(value == "top-media-requests"){
                        url = base_url_path + "todays_top_data";
                        callback = function(results){
                            jQuery('input[name="post_in_stats"]').val(results.ids); 
                            dmck_chart_object['admin-charts'] = results;
                            let json = {
                                type: "",
                                value: JSON.parse(results.ids),
                                to: jQuery('input[name="post_in_date_to"]').val(),
                                from: jQuery('input[name="post_in_date_from"]').val()
                            }
                            admin_functions.status_data(json);                            
                        }                                                
                    }    
                    new Promise(function (resolve, reject) {
                        jQuery.ajax({
                            type: "POST",
                            url: url,
                            data: obj,
                            beforeSend: function (xhr) { xhr.setRequestHeader('X-WP-Nonce', dmck_audioplayer.nonce); },
                        })
                        .done(function (data) { resolve(data); })
                        .fail(function (xhr, textStatus, errorThrown) { reject(false); });
                    })
                    .then(
                        function (results) {
                            admin_functions.overlay.off()
                            callback(results)
                        },
                        function (error) {
                            admin_functions.overlay.off()
                            admin_functions.notice(".notice-error", error);
                        }
                    );
                }    
            });                                
        }
    },    
    status_data: function(json){
        admin_functions.overlay.on()
        let url = dmck_audioplayer.site_url + "/wp-json/" + dmck_audioplayer.plugin_slug + "/" + dmck_audioplayer.plugin_version + "/api/stats_data";
        function callback(results){
            results = JSON.parse(results);
            dmck_chart_object['admin-charts'] = {
                labels: results.labels,
                datasets: results.data
            };

            jQuery(".chart-container").remove();            
            _dmck_charts_pkg.time_scale("admin-charts");
            // console.log(results);            
        }
        new Promise(function (resolve, reject) {
            jQuery.ajax({
                type: "POST",
                url: url,
                data: json,
                beforeSend: function (xhr) { xhr.setRequestHeader('X-WP-Nonce', dmck_audioplayer.nonce); },
            })
            .done(function (data) { resolve(data); })
            .fail(function (xhr, textStatus, errorThrown) { reject(false); });
        })
        .then(
            function (results) {
                admin_functions.overlay.off()
                callback(results)
             },
            function (error) {
                admin_functions.overlay.off()
                admin_functions.notice(".notice-error", error);
             }
        );        
    },
    submit_form(){
        admin_functions.overlay.on()
        if(!_dmck_functions.json_validate( jQuery("textarea[name='playlist_config']").val()) ){
            admin_functions.notice(".notice-error", "Invalid json configuration");
            admin_functions.overlay.off()
            return;
        }        
        admin_functions.config_clean(); //remove empty elements
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
                admin_functions.overlay.off()
                document.location.reload();
             },
            function (error) {
                admin_functions.overlay.off()
                admin_functions.notice(".notice-error", error);
             }
        );
    },
    config_clean: function(obj){
        let config = jQuery("textarea[name='playlist_config']").val();
        config = JSON.parse(config);
        config.find(function (obj, index) {
            obj = _dmck_functions.clean(obj);
        });
        jQuery("textarea[name='playlist_config']").val(JSON.stringify(config, "", 8));     
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
                download(results)
             },
            function (error) {
                jQuery(document.body).css({'cursor' : 'default'});
                admin_functions.notice(".notice-error", error);
             }
        );        
    },
    playlist_top_media_activity: function(){
        let playlist_config = jQuery("textarea[name='playlist_config']").val();
        if(playlist_config){
            playlist_config = JSON.parse(playlist_config);
            playlist_config.find(function(obj, index){
                if(obj.id == 'top-media-requests'){
                    obj.top_request = jQuery("input[type='checkbox'][name='playlist_top_media']").prop("checked");
                    obj.top_count = jQuery("input[type='text'][name='playlist_top_media_count']").val();
                    obj.top_title = jQuery("input[type='text'][name='playlist_top_media_title']").val(); 
                    if(obj.top_request){
                        jQuery("input[type='text'][name='playlist_top_media_count']").css("display","inline");
                        jQuery("input[type='text'][name='playlist_top_media_title']").css("display","inline");
                    }else{
                        jQuery("input[type='text'][name='playlist_top_media_count']").css("display","none");
                        jQuery("input[type='text'][name='playlist_top_media_title']").css("display","none");
                    }
                }    
            })                
        }            
        jQuery("textarea[name='playlist_config']").val(JSON.stringify(playlist_config,"",8));
    },
    notice: function(ident,text,timeout){
        if(!ident && !text){return false};
        timeout = timeout ? timeout : 2000;
        jQuery(ident).text(text).show("slow");
        setTimeout(function() {  jQuery(".notice").hide("slow").text(""); }, timeout);
        return false;
    },
    overlay: {
        on: function(){
            jQuery(document.body).css({'cursor' : 'wait'});
            jQuery('#loading').show();
        },
        off: function(){
            jQuery(document.body).css({'cursor' : 'default'});
            jQuery('#loading').hide();
        }        
    }
}