"use strict";
jQuery(document).ready(function(){	
	jQuery.fn.serializeObject = function () {
		var o = {};
		var a = this.serializeArray();
		jQuery.each(a, function () {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) { o[this.name] = [o[this.name]]; }
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};	
	jQuery('.loading').hide();
	jQuery('.admin-container').show();
	admin_functions.onload();
});