"user strict"

const playlist = {	
	init: function(){ playlist.defer( playlist.setup ) },		
	defer: function (method) {
		if (window.jQuery) {
			method();
		} else {
			setTimeout(function() { playlist.defer(method) }, 500);
		}
	},	
	setup: function(){

		jQuery( "button" ).button();

		if( jQuery('.playlist').length ){
			fetch_playlist();
			let player_secondary = `<li id="menu-item-controls" class="menu-item controls hidden">` + 
									jQuery('body.home .controls').html() + 
									`</li>`;
			
			jQuery(playlist.target.nav).find("#top-menu").append(player_secondary)

			dmck_audioplayer.has_shortcode = true;
		}
		let callback = function(type){
			if(type == "attributes"){
				if( jQuery(playlist.target).hasClass("site-navigation-fixed") ){
					playlist.toggle(true)
				}
				else
				{
					playlist.toggle(false)
				}
			}
		}
		playlist.observe({ 
			targetNodes : playlist.target.nav,
			callback	: playlist.callback.nav, 
		});
		playlist.observe({ 
			targetNodes : playlist.target.list,
			callback	: playlist.callback.list, 
		});					
	},
	target: {
		nav: jQuery("body.home .navigation-top"),
		list: jQuery("body.home .playlist")
	},

	timerId:"",
	
	callback: {
		nav:function(type){
			if(type == "attributes"){
				if( jQuery(playlist.target.nav).hasClass("site-navigation-fixed") ){
					playlist.toggle(true)
				}
				else
				{
					playlist.toggle(false)
				}
			}
		},
		list:function(type){

			if(type == "attributes"){

				clearTimeout( playlist.timerId );
				
				playlist.timerId = setTimeout(function(){

					let menu_fixed 		= jQuery(playlist.target.nav).hasClass("site-navigation-fixed"); 
					let is_list_hidden 	= jQuery(playlist.target.list).hasClass("hidden"); 

					if( menu_fixed && ! is_list_hidden){
						playlist.toggle(true)
					}
					else
					{
						playlist.toggle(false)
					}					

				}, 500);
			}
		}
	},
	
	observe: function(obj){
		let MutationObserver    = window.MutationObserver || window.WebKitMutationObserver;
		let myObserver          = new MutationObserver (mutationHandler);
		let config           	= { 
				childList: true, 
				characterData: true, 
				attributes: true, 
				subtree: true 
		};
		//--- Add a target node to the observer. Can only add one node at a time.
		obj.targetNodes.each ( function () {
			myObserver.observe (this, config);
		});
		function mutationHandler (mutationRecords) {
			mutationRecords.forEach ( function (mutation) {
				obj.callback(mutation.type);
				// console.log (mutation.type);
				// console.log (mutation.removedNodes);
			});
		}
	},	
	toggle: function(opt){
		
		if(opt){
			if( ! jQuery("body.home .playlist").hasClass("hidden") ){
				jQuery(playlist.target.nav).find("#menu-item-controls").removeClass("hidden")
			}
		}
		else
		{
			jQuery(playlist.target.nav).find("#menu-item-controls").addClass("hidden")			
		}
	}
}

playlist.init();	



