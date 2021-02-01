<?php

namespace DMCK_WP_MEDIA_PLUGIN;

trait _meta_box {


    function add_meta_box_hook($post_type){

        // Limit meta box to certain post types.
        $post_types = array( 'post' );

        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'audio_dmck_meta_box',
                __( 'DMCK Audio Meta Options', 'textdomain' ),
                array( $this, 'meta_box_display' ),
                $post_type,
                'normal',
                'high'
            );
        }			
    }    

    function meta_box_display($post){

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'audio_dmck_inner_custom_box', 'audio_dmck_inner_custom_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        // $value = get_post_meta( $post->ID, 'dmck_sidebar_post_upsell', true );

        // Display the form, using the current value.
        ?>
        <label for="dmck_wavformpng"><?php _e( 'WAVFORM image url. Requires audio controller enabled.)', 'textdomain' ); ?></label>
        <br>
        <input type="text" style="width:100%" id="dmck_wavformpng" name="dmck_wavformpng"  value="<?php echo esc_attr( get_post_meta( $post->ID, 'dmck_wavformpng', true ) ); ?>" >
        <br><br>
        
        <?php
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box_hook( $post_id ) {

        /*
        * We need to verify this came from the our screen and with proper authorization,
        * because save_post can be triggered at other times.
        */

        // Check if our nonce is set.
        if ( ! isset( $_POST['audio_dmck_inner_custom_box_nonce'] ) ) { return $post_id; }

        $nonce = $_POST['audio_dmck_inner_custom_box_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'audio_dmck_inner_custom_box' ) ) { return $post_id; }

        /*
        * If this is an autosave, our form has not been submitted,
        * so we don't want to do anything.
        */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        /* OK, it's safe for us to save the data now. */
        // Sanitize the user input.
        // $mydata = sanitize_text_field( $_POST['dmck_sidebar_post_upsell'] );

        // Update the meta field.
        update_post_meta( $post_id, 'dmck_wavformpng', $_POST['dmck_wavformpng']  );
    }
}