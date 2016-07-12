jQuery(document).ready(function(){
	fetch_playlist();
});	

//var waveform = new Waveform({
//	container: jQuery("#playlist-wrapper"),
//	width: "100px",
//	height: "50",
//	data: [1, 0.2, 0.5]
//});	

function fetch_playlist(orderby, order){
	
	jQuery.get( dmck_audioplayer.plugin_url + "playlist-functions.php", {
		debug: "false", 
		orderby: orderby ? orderby : "modified",
		order: order ? order : "DESC",
	} )			
	.done(function(data){
		render_playlist( data );
	});	
	
}

function render_playlist(response) {

	//console.log("draw the list");
	var myPlaylist 	= jQuery.parseJSON(response);
	
	var description = 'originals and remixes. Listen, enjoy';

	jQuery('#playlist-wrapper').html('');
	
	jQuery('#playlist-wrapper').ttwMusicPlayer(myPlaylist, {
		autoPlay:false, 
		description:description,
		jPlayer:{
			swfPath: dmck_audioplayer.plugin_url + 'plugin/jquery-jplayer' //You need to override the default swf path any time the directory structure changes
		}
	});
	
}
