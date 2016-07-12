jQuery(document).ready(function(){
	
	if( jQuery('.playlist').length ){
		
		fetch_playlist();
		dmck_audioplayer.has_shortcode = true;
	}	
});	
