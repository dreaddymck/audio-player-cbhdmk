"use strict";

const jquery = require("jquery");

window.admin_events = {
    init: function(){
        jQuery('ul.parent-tabs li').on("click",function () {
            var tab_id = jQuery(this).attr('data-tab');
            jQuery('ul.parent-tabs li, .parent-tab-content').removeClass('current');
            jQuery(this).addClass('current');
            jQuery("#" + tab_id).addClass('current');
            admin_functions.cookie.set({ "tab": tab_id });
        });
        jQuery('select[name="playlist_config_selection"]').on("change",function () {
            let index = jQuery(this)[0].selectedIndex;
            jQuery('.playlist-config-tab-content').removeClass('current');
            jQuery("#playlist-config-tab-" + index).addClass('current');
            admin_functions.cookie.set({ "playlist_config_selected": index });
        });
        jQuery("input[name='title']").on("change",function(e){
            let title = this.value;
            let index =  jQuery('select[name="playlist_config_selection"]')[0].selectedIndex;
            let id = jQuery('select[name="playlist_config_selection"] option:eq('+index+')').val();
            let playlist_config = jQuery("textarea[name='playlist_config']").val();
            if(playlist_config){
                playlist_config = JSON.parse(playlist_config);
                playlist_config.find(function(obj, index){
                    if(typeof(obj.id) !== 'undefined' && (obj.id.localeCompare(id) == 0)){
                        return obj.title = title;
                    }
                })
            }
            jQuery("textarea[name='playlist_config']").val(JSON.stringify(playlist_config,"",8));
        })
        jQuery('select[name="select_config_meta_tags"]').on("change",function () {
            let id = jQuery(this).val();
            jQuery('div.config_post_meta_tags').removeClass('current');
            if(id){
                jQuery("div#" + id).addClass('current');
                // admin_functions.cookie.set({ "playlist_config_selected": index });
            }
        });
        jQuery('.playlist_config_up').on("click",function(){
            let config = JSON.parse(jQuery("textarea[name='playlist_config']").val());
            let index =  jQuery('select[name="playlist_config_selection"]')[0].selectedIndex;
            if((index) < 1){return;}
            [config[index], config[index-1]] = [config[index-1], config[index]];
            jQuery('select[name="playlist_config_selection"] option:eq('+index+')').insertBefore(
                jQuery('select[name="playlist_config_selection"] option:eq('+ (index - 1) +')')
            );
            jQuery("textarea[name='playlist_config']").val(JSON.stringify(config,"",8));
        })
        jQuery('.playlist_config_down').on("click",function(e){
            let config = JSON.parse(jQuery("textarea[name='playlist_config']").val());
            let index =  jQuery('select[name="playlist_config_selection"]')[0].selectedIndex;
            if((index + 1) > config.length){return;}
            [config[index], config[index+1]] = [config[index+1], config[index]];
            jQuery('select[name="playlist_config_selection"] option:eq('+index+')').insertAfter(
                jQuery('select[name="playlist_config_selection"] option:eq('+ (index + 1) +')')
            );
            jQuery("textarea[name='playlist_config']").val(JSON.stringify(config,"",8));
        })
        jQuery('.playlist_config_add').on("click",function(e){
            e.preventDefault();
            let id = _dmck_functions.uuidv4();
            let dupecheck = jQuery(".playlist-config-tab-content").children('input[name="id"]').filter(function(){
                return (this.value.localeCompare(id) == 0);
            });
            if(dupecheck.length){
                admin_functions.notice(".notice-error", "Duplicate identifier");
                return false;
            }
            let playlist_config = jQuery("textarea[name='playlist_config']").val();
            if(playlist_config){
                playlist_config = JSON.parse(playlist_config);
                playlist_config.find(function(key, value){
                    if(key == "top_request"){ value = jQuery("input[type='checkbox'][name='playlist_top_media']").prop("checked"); }
                    if(key == "top_count"){ value = jQuery("input[type='text'][name='playlist_top_media_count']").val(); }
                })
                if(typeof playlist_config_default_json !== "undefined"){
                    let default_json = playlist_config_default_json[0];
                    default_json.id = id;
                    let position = playlist_config.length - 1;
                    playlist_config.splice(position, 0, default_json);
                    admin_functions.cookie.set({ "playlist_config_selected": position });
                    jQuery("textarea[name='playlist_config']").val(JSON.stringify(playlist_config,"",8));
                    admin_functions.submit_form();
                }

            }
        })
        jQuery('.playlist_config_del').on("click",function(e){
            e.preventDefault();
            if (!confirm('Please confirm delete')) { return false; }
            let id = jQuery('select[name="playlist_config_selection"]').val();
            let playlist_config = jQuery("textarea[name='playlist_config']").val();
            if(playlist_config){
                playlist_config = JSON.parse(playlist_config);
                playlist_config.find(function(obj, index){
                    if(typeof(obj) !== 'undefined' && typeof(obj.id) !== 'undefined' && obj.id == id){
                        playlist_config.splice(index,1);
                        index = ((index - 1) >= 0) ? (index - 1) : 0;
                        admin_functions.cookie.set({ "playlist_config_selected": index });
                        jQuery("textarea[name='playlist_config']").val(JSON.stringify(playlist_config,"",8));
                        admin_functions.submit_form();
                    }
                });
            }
        })
        jQuery("input[name='playlist_top_media_count']").on("change",function(e){
            let count = this.value;
            let playlist_config = jQuery("textarea[name='playlist_config']").val();
            if(playlist_config){
                playlist_config = JSON.parse(playlist_config);
                playlist_config.find(function(obj, index){
                    if(typeof(obj.top_count) !== 'undefined'){
                        if( ! Number(count) > 0 ){
                            jQuery("input[type='text'][name='playlist_top_media_count']").val(obj.top_count);
                        }else{
                            obj.top_count = jQuery("input[type='text'][name='playlist_top_media_count']").val();
                        }
                    }
                })
            }
            jQuery("textarea[name='playlist_config']").val(JSON.stringify(playlist_config,"",8));
        })
        jQuery("input[name='playlist_top_media_title']").on("change",function(e){
            let title = this.value;
            let playlist_config = jQuery("textarea[name='playlist_config']").val();
            if(playlist_config){
                playlist_config = JSON.parse(playlist_config);
                playlist_config.find(function(obj, index){
                    if(typeof(obj.top_title) !== 'undefined'){
                        obj.top_title = jQuery('<div>').text(title).html();
                       return;
                    }
                })
            }
            jQuery("textarea[name='playlist_config']").val(JSON.stringify(playlist_config,"",8));
        })
        jQuery('form[name*="admin-settings-form"]').submit(function (e) {

            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
            admin_functions.submit_form();
            return;
        });
        jQuery("input[type='checkbox'][name='ignore_ip_enabled']").on("click",function (e) {
            jQuery("textarea[name='ignore_ip_json']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery("input[type='checkbox'][name='audio_control_enabled']").on("click",function (e) {
            jQuery("input[name='audio_control_slider_height']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery("input[type='checkbox'][name='visualizer_enabled']").on("click",function (e) {
            jQuery("input[name='visualizer_rgb_enabled']").prop("checked", jQuery(this).prop("checked"));
            jQuery("input[name='visualizer_rgb_enabled']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("input[name='visualizer_rgb_init']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("input[name='visualizer_rgb']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("select[name='visualizer_samples']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery("input[type='checkbox'][name='visualizer_rgb_enabled']").on("click",function (e) {
            jQuery("input[name='visualizer_rgb_init']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("input[name='visualizer_rgb']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery('#admin-upload-action').on("click",function (e) {
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
            let callback = function (resp) {
                resp = JSON.parse(resp);
                console.log(resp);
                return;
            }
            upload.init({
                "callback": callback,
                "input": jQuery('input[name*="admin-upload"]')
            });
        });
        jQuery("input[type='checkbox'][name='playlist_top_media']").on("click",function (e) {
            if( jQuery(this).prop("checked")){
                jQuery("li[data-tab='parent-tabs-2']").removeClass("hidden");
            }else{
                jQuery("li[data-tab='parent-tabs-2']").addClass("hidden");
            }
            admin_functions.opt_requirements();
        });
        jQuery("input[type='checkbox'][name='charts_enabled']").on("click",function (e) {
            if( jQuery(this).prop("checked")){                
                jQuery("li[data-tab='parent-tabs-5']").removeClass("hidden");
            }else{
                jQuery("li[data-tab='parent-tabs-5']").addClass("hidden");
            }
            admin_functions.opt_requirements();
        });        
        jQuery("input[type='checkbox'][name='playlist_top_media']").on("click",function(e){
            admin_functions.playlist_top_media_activity();
        });
        jQuery("input[type='checkbox'][name='chart_rgb_enabled']").on("click",function (e) {
            jQuery("input[name='chart_rgb_init']").prop("disabled", !jQuery(this).prop("checked"));
            jQuery("input[name='chart_rgb']").prop("disabled", !jQuery(this).prop("checked"));
        });
        jQuery('select[name="stats_posts_in"]').on("click",function () {
            let value = jQuery(this).val();
            jQuery("input[name='post_in_stats']").val(JSON.stringify(value));
            let json = {
                type: this.name,
                value: value,
                to: jQuery('input[name="post_in_date_to"]').val(),
                from: jQuery('input[name="post_in_date_from"]').val()
            }
            jQuery(".chart-container").fadeOut('slow');
            admin_functions.status_data(json);
            dmck_chart_object.admin_chart_last_object = this;

        });
        jQuery('select[name="stats_playlist"]').on("click",function () {
            let value = jQuery(this).val();
            jQuery(".chart-container").fadeOut('slow');
            admin_functions.playlist_status_data(value)
            dmck_chart_object.admin_chart_last_object = this;
        });
        jQuery('input[name="post_in_date_from"], input[name="post_in_date_to"]').on("change",function () {
            clearTimeout(dmck_audioplayer.timeout_handler);
            dmck_audioplayer.timeout_handler = setTimeout(function(){
                jquery(dmck_chart_object.admin_chart_last_object)
                .trigger("focus")
                .trigger("click")
            }, 1000)
        });
        jQuery('select[name="stats_playlist"], select[name="stats_posts_in"]').on("keyup",function (e) {
            e.preventDefault();
            clearTimeout(dmck_audioplayer.timeout_handler);
            dmck_audioplayer.timeout_handler = setTimeout(function(){
                jquery(dmck_chart_object.admin_chart_last_object).trigger("click") 
            }, 1000)            
                       
        });
        jQuery('#stats-all').on("click",function (e) {
            e.preventDefault();
            clearTimeout(dmck_audioplayer.timeout_handler);
            jQuery('select[name="stats_posts_in"] option').prop('selected', true);
            jQuery('select[name="stats_posts_in"]').trigger("click");
        });            
    }
}