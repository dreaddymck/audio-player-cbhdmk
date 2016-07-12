<?php ?>

<div class="ttw-music-player">
	<div class="player jp-interface">
		<div class="album-cover">
			<span class="img"></span>
			<span class="highlight"></span>
		</div>
		<div class="track-info">
			<p class="title"></p>
			<p class="artist-outer">By <span class="artist"></span></p>
<!-- 			<div class="rating">  -->
<!-- 				<span class="rating-level rating-star on"></span>  -->
<!-- 				<span class="rating-level rating-star on"></span>  -->
<!-- 				<span class="rating-level rating-star on"></span>  -->
<!-- 				<span class="rating-level rating-star on"></span>  -->
<!-- 				<span class="rating-level rating-star"></span>  -->
<!-- 			</div>  -->
		</div>
		<div class="player-controls">
			<div class="main">
				<div class="previous jp-previous"></div>
				<div class="play jp-play"></div>
				<div class="pause jp-pause"></div>
				<div class="next jp-next"></div>
<!-- These controls aren\'t used by this plugin, but jPlayer seems to require that they exist -->
				<span class="unused-controls">
					<span class="jp-video-play"></span>
					<span class="jp-stop"></span>
					<span class="jp-mute"></span>
					<span class="jp-unmute"></span>
					<span class="jp-volume-bar"></span>
					<span class="jp-volume-bar-value"></span>
					<span class="jp-volume-max"></span>
					<span class="jp-current-time"></span>
					<span class="jp-duration"></span>
					<span class="jp-full-screen"></span>
					<span class="jp-restore-screen"></span>
					<span class="jp-repeat"></span>
					<span class="jp-repeat-off"></span>
					<span class="jp-gui"></span>
				</span>
			</div>
			<div class="progress-wrapper">
				<div class="progress jp-seek-bar">
					<div class="elapsed jp-play-bar"></div>
				</div>
			</div>
		</div>
	</div>
	<p class="description"></p>
	<div class="playlist-toolbar">
		sort: 	<a href="#" class="sortdef" >random</a>
				<a href="#" class="sortnew" >new</a>
				<a href="#" class="sortold" >old</a>
	</div>
	<div class="tracklist">
		<ol class="tracks"> </ol>
		<div class="more">View More...</div>
	</div>
	<div class="jPlayer-container"></div>
</div>



