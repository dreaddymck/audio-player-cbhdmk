"use strict";

const playlist = {	
	init: function(){ this.defer( this.setup ) },		
	defer: function (method) {
		if (window.jQuery) {
			method();
		} else {
			setTimeout(function() { this.defer(method) }, 500);
		}
	},
	offscreen: function(){

		//Filter Expression
		jQuery.expr.filters.offscreen = function(el) {
			
			var rect = el.getBoundingClientRect();
			return (
					(rect.x + rect.width) < 0 
					|| (rect.y + rect.height) < 0
					|| (rect.x > window.innerWidth || rect.y > window.innerHeight)
			);
		};		

		jQuery(window).scroll( function(){

			if( jQuery(window).width() < 768 ){

				if( jQuery('.site-branding').is(':offscreen') ){
					jQuery('.navigation-top').addClass('site-navigation-fixed');//    padding-top: 24px;
				}else
				if( jQuery('.site-header').not(':offscreen') ){
					jQuery('.navigation-top').addClass('site-navigation-fixed').css({"padding-top":"25px"})
				}							
				else
				{
					jQuery('.navigation-top').removeClass('site-navigation-fixed').css({"padding-top":"auto"})						
				}
			}
		}) 

	},	
	setup: function(){

		playlist.offscreen();

		jQuery('.entry-header').hide();
		jQuery( "button" ).button();

		if( jQuery('.playlist').length ){
			fetch_playlist();
			let player_secondary = `<div id="menu-item-controls" class="menu-item controls hidden">
										<div class="col-lg-4 col-lg-offset-4">` + 
										jQuery('body.home .controls').html() + 
									`	</div>
									</div>`;
			
			jQuery(playlist.target.nav).append(player_secondary)

			dmck_audioplayer.has_shortcode = true;
		}

		playlist.observe({ 
			targetNodes : 	playlist.target.nav,
			callback	: 	playlist.callback.nav,
			config		: 	{ 
								childList: false, 
								characterData: false, 
								attributes: true, 
								subtree: false 
							} 
		});
		playlist.observe({ 
			targetNodes : playlist.target.list,
			callback	: playlist.callback.list,
			config		: 	{ 
								childList: false, 
								characterData: false, 
								attributes: true, 
								subtree: false 
							} 
		});					
	},
	target: {
		nav: jQuery(".navigation-top, .navigation-top .wrap, .navigation-wrapper"),
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

				}, 200);
			}
		}
	},
	
	observe: function(obj){
		let MutationObserver    = window.MutationObserver || window.WebKitMutationObserver;
		let myObserver          = new MutationObserver (mutationHandler);
		let config           	= obj.config ? obj.config : { 
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



