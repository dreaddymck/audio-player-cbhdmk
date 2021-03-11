"use strict";

const admin_functions = {
    onload: function () {

        admin_events.init();
        
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
    notice: function(ident,text,timeout){
        if(!ident && !text){return false};
        timeout = timeout ? timeout : 2000;
        jQuery(ident).text(text).show("slow");
        setTimeout(function() {  jQuery(".notice").hide("slow").text(""); }, timeout);                  
        return false;
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