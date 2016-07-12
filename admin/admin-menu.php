<?php ?>

<div class="loading" style="text-align: center; width: 100%;">
	<img src="<?php echo plugins_url( 'images/loading-nerd.gif', dirname(__FILE__) )?>" /></div>

<form method="post" action="options.php">
<?php settings_fields( $this->plugin_settings_group ); ?>
<?php do_settings_sections( $this->plugin_settings_group ); ?>
	
<div class="container" style="display:none;">

	<ul class="tabs">
		<li class="tab-link current" data-tab="tab-1">settings</li>
		<li class="tab-link" data-tab="tab-2">about</li>
	</ul>

	<div id="tab-1" class="tab-content current">
		<?php submit_button(); ?>
		
		
<!--         currencySymbol:'$', -->
<!--         buyText:'BUY', -->
<!--         tracksToShow:5, -->
<!--         autoplay:false,		 -->
		
		<div>	
			<?php _e('favicon href'); ?>: 
	        <br>
	        <textarea  name="favicon" style="width: auto; height: auto;"><?php echo esc_attr( get_option('favicon') ); ?></textarea>
		</div>
		<div>
			<?php _e('default album cover'); ?>: 
	        <br>
	        <input type="text" name="default_album_cover" value="<?php echo esc_attr( get_option('default_album_cover') ); ?>">
        </div>
		<div>
			<?php _e('more info text'); ?>: 
	        <br>
	        <input type="text" name="moreinfo" value="<?php echo esc_attr( get_option('moreinfo') ); ?>">
        </div>                
		<?php submit_button(); ?>
		
	</div>
	<div id="tab-2" class="tab-content"></div>

</div><!-- container -->	

</form>