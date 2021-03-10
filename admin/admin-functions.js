"use strict";

const admin_functions = {
    onload: function () {
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
        jQuery('form[name*="admin-settings-form"]').submit(function (e) {

            //TODO refractor playlist_config keys to the new naming convention: playlist_config_[key] : value
            //TEST retrieving JSON from HTML
            let array = []
            let json;
            let test = jQuery(".playlist-config-tab-content").each(
                function()  {
                    json = jQuery(this).find("input").serializeObject();                    
                    array.push(json); 
                }
            );

            jQuery("#playlist_config_test").text(JSON.stringify(array,null, 8));
            //TEST retrieving JSON from HTML
            
            
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }

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
                        jQuery(".notice-success").text("Settings saved").show("slow");
                        setTimeout(function() {  jQuery(".notice").hide("slow").text(""); }, 5000);                        
                     },
                    function (error) { 
                        jQuery(".notice-error").text(error).show("slow");
                        setTimeout(function() {  jQuery(".notice").hide("slow").text(""); }, 5000);                                      
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

        /**
         * Tabs
         */
        let cookie = admin_functions.cookie.get();
        if (cookie) {
            cookie = JSON.parse(cookie);
            if (typeof cookie.tab !== 'undefined') {
                jQuery("ul.parent-tabs > li").removeClass('current');
                jQuery(".parent-tab-content").removeClass('current');
                jQuery("ul.parent-tabs > li[data-tab*='" + cookie.tab + "']").addClass('current');
                jQuery("#" + cookie.tab).addClass('current');
            }
            if (typeof cookie.playlist_config_tab !== 'undefined') {
                jQuery("ul.playlist-config-tabs > li").removeClass('current');
                jQuery(".playlist-config-tab-content").removeClass('current');
                jQuery("ul.playlist-config-tabs > li[data-tab*='" + cookie.playlist_config_tab + "']").addClass('current');
                jQuery("#" + cookie.playlist_config_tab).addClass('current');
            }            
        }
        /**
         * ReadME
         */
        jQuery.get(dmck_audioplayer.plugin_url + 'README.md', function (data) {
            // let content = data.replace(/(?:\r\n|\r|\n)/g, '<br />');
            let content = marked(data);
            jQuery('.tab-about').html(content);
        });
    },
    cookie: {
        set: function (obj) {
            let cookie = admin_functions.cookie.get()
            if (cookie) {
                cookie = JSON.parse(cookie);
                let keys = Object.keys(obj);
                keys.forEach(key => {
                    cookie[key] = obj[key];
                });
            } else {
                cookie = obj;
            }
            jQuery.cookie(dmck_audioplayer.plugin_slug, JSON.stringify(cookie), {
                expires: 30
            })
        },
        get: function () {
            return jQuery.cookie(dmck_audioplayer.plugin_slug);
        }
    },
    upload: function (callback) {

        jQuery("body").css("cursor", "progress");
        jQuery(".progress .progress-bar").width("0%");

        function progress(e) {
            if (e.lengthComputable) {
                var max = e.total;
                var current = e.loaded;
                var Percentage = (current * 100) / max;
                Percentage = parseInt(Percentage);
                jQuery(".progress .progress-bar")
                    .width(Percentage + "%")
                    .attr("aria-valuenow", Percentage)
                    .text(Percentage + "%");
                // jQuery( ".progressbar" ).progressbar( "option", "value", parseInt(Percentage) );
                if (Percentage >= 100) {
                    // jQuery( ".progress .progress-bar" ).width("0%");
                }
            }
        }

        let url = dmck_audioplayer.site_url + "/wp-json/" + dmck_audioplayer.plugin_slug + "/v" + dmck_audioplayer.plugin_version + "/upload";

        new Promise(function (resolve, reject) {

            let elem = jQuery('input[name*="admin-upload"]');
            let form = new FormData();
            form.append(elem[0].files[0]["name"], elem[0].files[0]);

            let xhr = (window.XMLHttpRequest) ? new XMLHttpRequest() : new activeXObject("Microsoft.XMLHTTP");
            xhr.upload.addEventListener('progress', progress, false);
            xhr.upload.addEventListener("load", function (evt) {
                let msg = "Transfer complete.";
                jQuery(".notice").html(msg);
                console.log(evt);
            });
            xhr.upload.addEventListener("error", function (evt) {
                let msg = "Transfer error";
                jQuery(".notice").html(msg);
                console.log(evt);
            });
            xhr.upload.addEventListener("abort", function (evt) {
                let msg = "Transfer aborted";
                jQuery(".notice").html(msg);
                console.log(evt);
            });
            xhr.open('POST', url, true);
            xhr.setRequestHeader("X-WP-Nonce", dmck_audioplayer.nonce);
            xhr.onload = function () {
                if (this.status >= 200 && this.status < 300) {
                    resolve(xhr.response);
                } else {
                    reject({
                        status: this.status,
                        statusText: xhr.statusText
                    });
                }
            };
            xhr.onerror = function () {
                reject({
                    status: this.status,
                    statusText: xhr.statusText
                });
            };
            xhr.send(form);
        }).then(
            function (resp) {
                jQuery("body").css("cursor", "default");
                callback(resp);
            },
            function (e) {
                jQuery("body").css("cursor", "default");
                jQuery(".notice").html(e);
            }
        );
    },
}