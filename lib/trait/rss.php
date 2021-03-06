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
}