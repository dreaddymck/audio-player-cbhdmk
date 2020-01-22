"use strict";

const admin_functions = {
    onload : function(){
        /**
         * events
         */
        jQuery('body').bind('beforeunload',function(){
            jQuery("#tiny-file-manager").detach();
        });        
        jQuery('ul.tabs li').click(function(){
            var tab_id = jQuery(this).attr('data-tab');
    
            jQuery('ul.tabs li').removeClass('current');
            jQuery('.tab-content').removeClass('current');
    
            jQuery(this).addClass('current');
            jQuery("#"+tab_id).addClass('current');
    
            admin_functions.cookie.set({"tab": tab_id});		
        });	
        jQuery('#admin-settings-button').click(function (e) {

            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }

            let url     = "options.php"
            let data    = jQuery('form[name*="admin-settings-form"]').serializeArray();
            
            let render = function (res) {
                // res = JSON.parse(res);
                jQuery("#message").text(res);
            }
            new Promise(function (resolve, reject) {
                jQuery.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', dmck_audioplayer.nonce);
                        },
                })
                .done(function (data) {
                    resolve(data);
                })
                .fail(function (xhr, textStatus, errorThrown) {
                    console.log(errorThrown);
                    reject(false);
                });
            })
            .then(
                function (results) {
                    render(results);
                },
                function (error) {
                    console.log(error);
                }
            );
            return;
        });        
        jQuery('#admin-upload-action').click(function (e) {
            e.preventDefault();
            if (!confirm('Please confirm')) { return false; }
            let callback = function(resp){
                resp = JSON.parse(resp);
                console.log(resp);
                return;	
            }		
            admin_functions.upload(callback);
        });
        jQuery('<iframe id="tiny-file-manager" style="width:100%;height:auto;" />')
            .attr("src", dmck_audioplayer.plugin_url + "/lib/tiny-file-manager.php")
            .appendTo('.tab-files')
            .load(function () {
                var $contents = jQuery(this).contents();
                // $contents.scrollTop($contents.height());
                jQuery("#tiny-file-manager").height( $contents.height() + "px")
            });
        /**
         * Tabs
         */
        let cookie = admin_functions.cookie.get();
        if(cookie){
            cookie = JSON.parse(cookie);
            if(typeof cookie.tab !== 'undefined'){
                jQuery(".tabs > li").removeClass('current');
                jQuery(".tab-content").removeClass('current');
                jQuery(".tabs > li[data-tab*='"+cookie.tab+"']").addClass('current');
                jQuery("#"+cookie.tab).addClass('current');
            }
        } 
        /**
         * ReadME
         */        
        jQuery.get( dmck_audioplayer.plugin_url + 'README.md',function(data){
            // let content = data.replace(/(?:\r\n|\r|\n)/g, '<br />');
            let content = marked(data);
            jQuery('.tab-about').html( content );
        });               
    },
	cookie : {
		set : function(obj){
			let cookie = admin_functions.cookie.get()
			if(cookie){
                cookie = JSON.parse(cookie);
                let keys = Object.keys(obj);                
                keys.forEach(key => { cookie[key] = obj[key]; });				
			}else{ cookie = obj; }
            jQuery.cookie(dmck_audioplayer.plugin_slug, JSON.stringify(cookie), { expires: 30 })
		},
		get : function(){ return jQuery.cookie(dmck_audioplayer.plugin_slug); }
    },    
    upload : function(callback){

        jQuery("body").css("cursor", "progress");
        jQuery( ".progress .progress-bar" ).width("0%");
        function progress(e){
            if(e.lengthComputable){
                var max = e.total;
                var current = e.loaded;
                var Percentage = (current * 100)/max;
                Percentage = parseInt(Percentage);
                jQuery( ".progress .progress-bar" )
                    .width( Percentage + "%")
                    .attr( "aria-valuenow", Percentage)
                    .text(Percentage + "%");
                // jQuery( ".progressbar" ).progressbar( "option", "value", parseInt(Percentage) );
                if(Percentage >= 100) {
                    // jQuery( ".progress .progress-bar" ).width("0%");
                }
            }  
        }
        
        let url = dmck_audioplayer.site_url+"/wp-json/"+dmck_audioplayer.plugin_slug+"/v"+dmck_audioplayer.plugin_version+"/upload";
    
        new Promise(function(resolve, reject){
    
            let elem = jQuery('input[name*="admin-upload"]');
            let form = new FormData();
            form.append(elem[0].files[0]["name"], elem[0].files[0]);
    
            let xhr = (window.XMLHttpRequest) ? new XMLHttpRequest() : new activeXObject("Microsoft.XMLHTTP");
            xhr.upload.addEventListener('progress',progress, false);
            xhr.upload.addEventListener("load", function(evt) {
                let msg = "Transfer complete.";
                jQuery(".message").html(msg);                
                console.log(evt);
            });
            xhr.upload.addEventListener("error", function(evt) {
                let msg = "Transfer error";            
                jQuery(".message").html(msg);                    
                console.log(evt);
            });
            xhr.upload.addEventListener("abort", function(evt) {
                let msg = "Transfer aborted";          
                jQuery(".message").html(msg);                                     
                console.log(evt);
            });
            xhr.open( 'POST', url, true );                      
            xhr.setRequestHeader("X-WP-Nonce", dmck_audioplayer.nonce );		
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
            function (resp) { jQuery("body").css("cursor", "default"); callback(resp); }, 
            function (e) { jQuery("body").css("cursor", "default"); jQuery(".message").html(e); }
        );
    },
}
