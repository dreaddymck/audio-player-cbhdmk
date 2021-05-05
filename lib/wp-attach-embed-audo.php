<?php
/*
    Command line support for cron calls.

    TODO: check if attachment exists before adding.
*/
namespace DMCK_WP_MEDIA_PLUGIN;
try{
    require_once dirname(__FILE__) . "/../../../../wp-load.php";
    require_once(dirname(__FILE__) . "/trait/access-logs.php");
	require_once(dirname(__FILE__) . "/trait/wavform.php");
    require_once(dirname(__FILE__) . "/trait/utilities.php");
    require_once(dirname(__FILE__) . "/trait/requests.php");
}
catch (Exception $e) { exit($e); }
class wp_attach_embede_audio{
    use _accesslog;
    use _wavform;
    use _utilities;
    use _requests;

    function __construct() {
        $this->setTimezone();
        if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] ) { exit( header("Location: ".get_bloginfo('url')) ); }

        echo "Thie application will update meta tags enclosure, dmck_wavformpng for use in rss feeds."; 
        echo "Continue (Y/N)?";

        $input = rtrim(fgets(STDIN));
        if(strtolower($input) != "y"){exit();}        


        $this->attach_audio();
    }
    function attach_audio(){
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'post'
        );
        $p = json_decode($this->_requests_get_posts($args));

        foreach($p as $e){

            if($e->mp3){
                $title = pathinfo($e->mp3, PATHINFO_FILENAME);
                $url=$e->mp3;

                $path_parts = pathinfo($url);
                // echo $path_parts['dirname'], "\n";
                // echo $path_parts['basename'], "\n";
                // echo $path_parts['extension'], "\n";
                // echo $path_parts['filename'], "\n"; // since PHP 5.2.0   
                $wavform = $path_parts['dirname'] . "/" . $path_parts['filename'] . ".wavform.png";
                // $file_type = wp_check_filetype(basename($url), null);
                // // echo "File Type: ". $file_type['type']."\n";
                // $attachment = array(
                //     'guid' => $url,
                //     'post_mime_type' => $file_type['type'],
                //     'post_title' => $title,
                //     'post_name' => $title,
                //     'post_content' => '',
                //     'post_status' => 'inherit',
                //     'post_modified' => $e->post_date,
                //     'post_modified_gmt' => $e->post_date_gmt
                // );
                // echo print_r($attachment,1)."\n";
                // echo $e->ID . "\n\n";
                // wp_insert_attachment($attachment, false, $e->ID);
                update_post_meta( $e->ID, 'enclosure', $url );
                if( $this->file_exists_curl($wavform) ){
                    update_post_meta( $e->ID, 'dmck_wavformpng', $wavform );
                }
            }
        }
    }
    function file_exists_curl($filePath)
    {
        return ($ch = curl_init($filePath)) ? @curl_close($ch) || true : false;
    }    
}

new wp_attach_embede_audio();
