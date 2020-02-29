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
        add_feed( $feedname, function() use ($lang) {$this->_rss_render_feed();} );
    }    
    /**
     * Renders the feed.
     *
     * @param string $lang
     */
    public function _rss_render_feed()
    {
        global $wp_query;
        if(empty($_REQUEST["id"])){exit;};
        $id = $_REQUEST["id"];
        $args = array_merge(
                $wp_query->query,
                array('post__in' => $id)
        );
        query_posts($args);
        include('wp-includes/feed-rss2.php');
    }
}