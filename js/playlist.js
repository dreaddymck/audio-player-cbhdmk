jQuery(document).ready(function(){
	
	jQuery( "button" ).button();
	
	if( jQuery('.playlist').length ){
		
		fetch_playlist();
		dmck_audioplayer.has_shortcode = true;
		
		
	}	
});	
