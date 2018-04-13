<div class="player row">

	<div class="col-md-4">

		<div class="panel panel-box box-background">
			<div class="panel-body">
				<div class="cover"></div>
			</div>
			<div class="panel-heading options">			

				<div class="controls row ">

					<div class="controls-centered">

						<i class="btn btn-default play fa fa-play-circle"  aria-hidden="true" title="Play"></i>
						<i class="btn btn-default pause fa fa-pause hidden"  aria-hidden="true" title="Pause"></i>							

						<i class="btn btn-default rew fa fa-step-backward" aria-hidden="true" title="Back"></i>							

						<i class="btn btn-default fwd fa fa-step-forward"  aria-hidden="true" title="Forward"></i>							
					<!-- 
						<i class="btn btn-default showlist fa fa-list"  aria-hidden="true"  title="Tone Deaf Playlist"> </i> 	
					-->							
					</div>			 
				</div> 

			</div>

			
		</div>	

	</div>
	<div class="col-md-8">
		
		<h3 class="title" title="click for more information"></h3>

		<div class="volume" style="display:none"></div>
		
		<div class="duration">
			
			<h1 class="artist"></h1>
		
		</div>
		
		<div class="tracktime">0 / 0</div>
	</div>

</div>
<div class="row">

	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#tab-home">Playlist</a></li>
		<li><a data-toggle="tab" href="#tab-top-request" id="#tab-top-request">Today's Top 10</a></li>
	</ul>

	<div class="tab-content">
		<div id="tab-home" class="tab-pane fade in active">
			<ul class="playlist ">
				<li audiourl="*.mp3" cover="cover1.jpg" artist="Artist 1">*.mp3</li>
			</ul>
		</div>
		<div id="tab-top-request" class="tab-pane fade top-requests"></div>
	</div>	

</div>
