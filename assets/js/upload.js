"use strict";

const upload = {

    init: function (callback) {

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