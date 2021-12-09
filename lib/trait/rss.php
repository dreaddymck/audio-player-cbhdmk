<?php

namespace DMCK_WP_MEDIA_PLUGIN;
//TODO change _rss_render_feed to query new rss caching table by uuid.

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
        global $wp_query;
        $idarray        = array();
        $id             = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
        $type           = isset($_REQUEST["type"]) ? $_REQUEST["type"] : "";

        if($id){ array_push($idarray, $id); }
        if($type == "top-count"){
            $data = $this->media_activity_today($limit=10);
            foreach($data as $value) {                
                array_push($idarray, $value["ID"]);     
            }
        }
        $args = array_merge(
            $wp_query->query,
            array(
                "post__in"          => $idarray, 
                "tag"               => isset($_REQUEST["tag"]) ? $_REQUEST["tag"] : null, 
                "tag_slug__and"     => isset($_REQUEST["tag_slug__and"]) ? $_REQUEST["tag_slug__and"] : null,
                'orderby'          	=> isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]  : null,
                'order'            	=> isset($_REQUEST["order"]) ? $_REQUEST["order"]  : null,
                'tag'				=> isset($_REQUEST["tag"]) ? $_REQUEST["tag"]  : null,
                'tag_id'			=> isset($_REQUEST["tag_id"]) ? $_REQUEST["tag_id"]  : null,
                'tag__and'			=> isset($_REQUEST["tag__and"]) ? $_REQUEST["tag__and"]  : null,
                'tag__in' 			=> isset($_REQUEST["tag_in"]) ? $_REQUEST["tag_in"] : null,
                'tag__not_in'		=> isset($_REQUEST["tag_not_in"]) ? $_REQUEST["tag_not_in"] : null,
                'tag_slug__and'		=> isset($_REQUEST["tag_slug__and"]) ? $_REQUEST["tag_slug__and"] : null,
                'tag_slug__in'		=> isset($_REQUEST["tag_slug__in"]) ? $_REQUEST["tag_slug__in"] : null,
                'cat'				=> isset($_REQUEST["cat"]) ? $_REQUEST["cat"] : null,
                'category_name'		=> isset($_REQUEST["category_name"]) ? $_REQUEST["category_name"] : null,
                'category__and'		=> isset($_REQUEST["category__and"]) ? $_REQUEST["category__and"] : null,
                'category__in'		=> isset($_REQUEST["category__in"]) ? $_REQUEST["category__in"] : null,
                'category__not_in'	=> isset($_REQUEST["category__not_in"]) ? $_REQUEST["category__not_in"] : null,                     
            )
        );
        query_posts($args);
        include('wp-includes/feed-rss2.php');
    }
    public function _rss_feed_cache()
    {
        $playlist_data = $this->playlist_data_get();
        if(!$playlist_data){
            return;
        }else{
            foreach($playlist_data["playlist_json"] as $p) { 
                if(isset($p->id) && isset($p->title)) {
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
                if($this->debug){ echo __FUNCTION__. " | ". $this->memory_usage()."\n\r"; } 
            }
        }
    }     
}