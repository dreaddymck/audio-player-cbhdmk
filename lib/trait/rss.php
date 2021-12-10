<?php

namespace DMCK_WP_MEDIA_PLUGIN;

trait _rss {

    function __construct(){}
    /**
     * Register the feed.
     */
    public function _rss_create_feed()
    {
        global $wp_rewrite;
        $feedname = 'playlist';
        // Making sure that the feed is created
        if ( ! in_array( $feedname, $wp_rewrite->feeds ) ) {
            $wp_rewrite->feeds[] = $feedname;
            flush_rewrite_rules( FALSE );
        }
        add_feed( $feedname, function(){$this->_rss_render_feed();} );
    }    
    /**
     * Renders the feed.
     *
     * @param string $lang
     */
    public function _rss_render_feed()
    {
        $id             = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
        if($id){
            $query = "select xml from dmck_media_activity_rss where uuid = '$id'";    
            $response = $this->mysqli_query($query);
            header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
            if(isset($response[0]["xml"]))
            {
                echo $response[0]["xml"];
            }            
        }
    }
    public function _rss_feed_cache()
    {
        $playlist_data = $this->playlist_data_get();
        $listid = array();
        if(!$playlist_data){
            return;
        }else{            
            foreach($playlist_data["playlist_json"] as $p) {
                if(isset($p->id)) {
                    array_push($listid, $p->id);                
                    if(isset($p->title)){
                        $args = $this->obj_request_args($p);
                    }else
                    if(isset($p->top_request) && filter_var($p->top_request, FILTER_VALIDATE_BOOLEAN)){                    
                        $idarray = array();
                        $data = $this->media_activity_today($limit=10);
                        foreach($data as $value) {                
                            array_push($idarray, $value["ID"]);     
                        }
                        $args = $this->obj_request_args( (object) array("post__in"=> $idarray));                    
                    }
                    query_posts($args);
                    ob_start();
                    // include( ABSPATH .'wp-includes/feed-rss2.php');
                    include( plugin_dir_path(__DIR__) . "feed-rss2-no-header.php");
                    $output = ob_get_clean();
                    wp_reset_postdata();
                    $query = "

INSERT INTO dmck_media_activity_rss (uuid, xml) 
VALUES ('$p->id','$output')
ON DUPLICATE KEY UPDATE xml='$output';                

                    ";
                    $this->mysqli_query($query);
                }
                if($this->debug){ echo __FUNCTION__. " | ". $this->memory_usage()."\n\r"; } 
            }
            if(!empty($listid)){
                $listid = "'".implode("','", $listid)."'";       
                $this->_rss_feed_cache_clean($listid);
            } 
        }
    }
    public function _rss_feed_cache_clean($listid){
        $query = "DELETE FROM dmck_media_activity_rss where uuid NOT IN ($listid)";
        $this->mysqli_query($query);
        if($this->debug){ echo __FUNCTION__. " | ". $this->memory_usage()."\n\r"; } 
    }         
}