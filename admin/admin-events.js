"use strict";

const admin_events = {

    init: function(){
        jQuery('ul.parent-tabs li').click(function () {
            var tab_id = jQuery(this).attr('data-tab');
            jQuery('ul.parent-tabs li, .parent-tab-content').removeClass('current');
            jQuery(this).addClass('current');
            jQuery("#" + tab_id).addClass('current');
            admin_functions.cookie.set({ "tab": tab_id });
        });
        jQuery('ul.playlist-config-tabs li').click(function () {
            var tab_id = jQuery(this).attr('data-tab');
            jQuery('ul.playlist-config-tabs li, .playlist-config-tab-content').removeClass('current');
            jQuery(this).addClass('current');
            jQuery("#" + tab_id).addClass('current');
            admin_functions.cookie.set({ "playlist_config_tab": tab_id });
        });
        jQuery('.playlist_config_add').click(function(e){
            e.preventDefault();
            let id = prompt("Enter unique identifier","");
            if (!id) { return false; }
            let dupecheck = jQuery(".playlist-config-tab-content").children('input[name="id"]').filter(function(){
                return this.value.toUpperCase() == id.toUpperCase();
            });
            if(dupecheck.length){
                admin_functions.notice(".notice-error", "Duplicate identifier");
                return false;
            }
        })        
        jQuery('.playlist_config_del').click(function(e){
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
        })        
        jQuery('form[name*="admin-settings-form"]').submit(function (e) {

            //TEST retrieving JSON from HTML
            let array = []
            let json;
            jQuery(".playlist-config-tab-content").each(
                function()  {
                    json = jQuery(this).find("input").serializeObject();                    
                    array.push(json); 
                }
            );
            array.push({"topten" : jQuery("input[type='checkbox'][name='playlist_top_media']").prop("checked")})

            jQuery("#playlist_config_test").text(JSON.stringify(array,null, 8));
            //TEST retrieving JSON from HTML
            
            
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
            jQuery(document.body).css({'cursor' : 'wait'});

            let url = "options.php"
            let data = jQuery(this).serializeArray();
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
                        document.location.reload(true);                        
                     },
                    function (error) { 
                        jQuery(document.body).css({'cursor' : 'default'});
                        admin_functions.notice(".notice-error", error);
                     }
                );
            return;
        });
        jQuery("input[type='checkbox'][name='ignore_ip_enabled']").click(function (e) {
            jQuery("textarea[name='ignore_ip_json']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery("input[type='checkbox'][name='audio_control_enabled']").click(function (e) {
            jQuery("input[name='audio_control_slider_height']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery("input[type='checkbox'][name='visualizer_rgb_enabled']").click(function (e) {
            jQuery("input[name='visualizer_rgb_init']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("input[name='visualizer_rgb']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("select[name='visualizer_samples']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery("input[type='checkbox'][name='chart_rgb_enabled']").click(function (e) {
            jQuery("input[name='chart_rgb_init']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("input[name='chart_rgb']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery('#admin-upload-action').click(function (e) {
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
            let callback = function (resp) {
                resp = JSON.parse(resp);
                console.log(resp);
                return;
            }
            admin_functions.upload(callback);
        });
    }
}