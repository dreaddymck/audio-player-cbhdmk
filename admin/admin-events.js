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
            id = admin_functions.string_to_slug(id);
            let dupecheck = jQuery(".playlist-config-tab-content").children('input[name="id"]').filter(function(){
                return this.value.toUpperCase() == id.toUpperCase();
            });
            if(dupecheck.length){
                admin_functions.notice(".notice-error", "Duplicate identifier");
                return false;
            }
            let playlist_config = jQuery("textarea[name='playlist_config']").val();
            if(playlist_config){
                playlist_config = JSON.parse(playlist_config);
                playlist_config.find(function(key, value){
                    console.log(key)
                    if(key == "topten"){
                        value = jQuery("input[type='checkbox'][name='playlist_top_media']").prop("checked"); 
                    }    
                })                
            }
        })        
        jQuery('.playlist_config_del').click(function(e){
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
            let id = jQuery(this).closest("li").text().trim();
            console.log(id);
        })  
        jQuery("input[name='playlist_top_media']").click(function(e){            
            let playlist_config = jQuery("textarea[name='playlist_config']").val();
            if(playlist_config){
                playlist_config = JSON.parse(playlist_config);
                playlist_config.find(function(obj, index){
                    if(typeof(obj.topten) !== 'undefined'){
                        obj.topten = jQuery("input[type='checkbox'][name='playlist_top_media']").prop("checked"); 
                    }    
                })                
            }            
            jQuery("textarea[name='playlist_config']").val(JSON.stringify(playlist_config,"",8));
        })      
        jQuery('form[name*="admin-settings-form"]').submit(function (e) {

            //TEST retrieving JSON from HTML
            // Not sure I need this anymore
            /*let array = [];
            let json;
            jQuery(".playlist-config-tab-content").each(
                function()  {
                    json = jQuery(this).find("input").serializeObject();                    
                    array.push(json); 
                }
            );
            array.push({ "topten" : jQuery("input[type='checkbox'][name='playlist_top_media']").prop("checked") });

            jQuery("#playlist_config_test").text(JSON.stringify(array,null, 8));
             */
            //TEST retrieving JSON from HTML
            
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
            admin_functions.submit_form();
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
            upload.init(callback);
        });
    }
}