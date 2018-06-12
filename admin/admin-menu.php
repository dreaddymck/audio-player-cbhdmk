<?php ?>

<div class="loading" style="text-align: center; width: 100%;">
	<img src="<?php echo plugins_url( 'images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>

<form method="post" action="options.php">
<?php settings_fields( $this->plugin_settings_group ); ?>
<?php do_settings_sections( $this->plugin_settings_group ); ?>
	
<div class="container table" style="display:none;">

	<ul class="tabs">
		<li class="tab-link current" data-tab="tab-1">settings</li>
		<li class="tab-link" data-tab="tab-2">about</li>
	</ul>

	<div id="tab-1" class="tab-content current">

		<?php submit_button( __( 'Submit', 'Submit' ), 'primary' ); ?>
		
		
<!--         currencySymbol:'$', -->
<!--         buyText:'BUY', -->
<!--         tracksToShow:5, -->
<!--         autoplay:false,		 -->
	
		<div>	
			<?php _e('favicon href'); ?>: 
	        <br>
	        <textarea  name="favicon" class="form-control"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
		</div>
		<div>
			<?php _e('default album cover'); ?>: 
	        <br>
	        <input type="text" name="default_album_cover"  class="form-control" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>">
        </div>
		<div>
			<?php _e('more info text'); ?>: 
	        <br>
	        <input type="text" name="moreinfo"  class="form-control" value="<?php echo esc_attr( get_option('moreinfo') ); ?>">
        </div>
		
		<hr>                
		
		<div>	
			<?php _e('facebook_app_id'); ?>: 
	        <br>
	        <input type="text" name="facebook_app_id"  class="form-control" value="<?php echo esc_attr( get_option('facebook_app_id') ); ?>">
		</div>	

		<?php submit_button( __( 'Submit', 'Submit' ), 'primary' ); ?>
		
	</div>
	<div id="tab-2" class="tab-content"></div>

</div><!-- container -->	

</form>