<?php

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
        // if(empty($_REQUEST["id"])){exit;};
        $idarray        = array();
        $id             = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
        $type           = isset($_REQUEST["type"]) ? $_REQUEST["type"] : "";
        $tag            = $_REQUEST["tag"];
        $tag_slug__and  = $_REQUEST["tag_slug__and"];        
        if($type == "top-count"){
            $data = $this->top_count_get();
            foreach($data as $value) {                
                array_push($idarray, $value["ID"]);     
            }
        }
        $args = array_merge(
            $wp_query->query,
            array(
                "post__in" => $idarray, 
                "tag" => $tag, 
                "tag_slug__and" => $tag_slug__and
            )
        );
        // var_dump($args);
        query_posts($args);
        include('wp-includes/feed-rss2.php');
    }
}