"use strict";

const _functions = {

	cookie : {
		name: dmck_audioplayer.plugin_slug + "-cookie",
		set : function(obj){
			let cookie = _functions.cookie.get() 
			if(cookie){
                cookie = JSON.parse(cookie);
                let keys = Object.keys(obj);                
                keys.forEach(key => { cookie[key] = obj[key]; });				
			}else{ cookie = obj; }
            jQuery.cookie( _functions.cookie.name, JSON.stringify(cookie), { expires: 30 })
		},
		get : function(){ return jQuery.cookie(_functions.cookie.name); }
	},
	is_json_string: function(str) {
		try {
			JSON.parse(str);
		} catch (e) {
			return false;
		}
		return true;
	},
	hex_to_rgb: function(h) {
		let r = 0, g = 0, b = 0;
		// 3 digits
		if (h.length == 4) {
		  r = "0x" + h[1] + h[1];
		  g = "0x" + h[2] + h[2];
		  b = "0x" + h[3] + h[3];
	  
		// 6 digits
		} else if (h.length == 7) {
		  r = "0x" + h[1] + h[2];
		  g = "0x" + h[3] + h[4];
		  b = "0x" + h[5] + h[6];
		}
		
		return "rgb("+ +r + "," + +g + "," + +b + ")";
	  }		
}

