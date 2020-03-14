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
	}	
}

