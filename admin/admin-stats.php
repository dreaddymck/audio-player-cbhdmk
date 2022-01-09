<div class="pure-g">
    <div class="pure-u-1 pure-u-md-1-3">
        <div class="pure-padding-box">
<?php if ($playlist_config_selection) : ?>
    <label for="stats_playlist">Playlist:</label>    
<select name="stats_playlist" size="6" title="Playlist selection">    
    <?php if (1 == get_option('playlist_top_media')) { ?>        
        <option value='top-media-requests' draggable=true ><?php echo $playlist_top_media_title ?></option>        
    <?php  } ?> 
    <?php echo $playlist_config_selection ?>
</select>
<?php endif; ?>
        </div>
    </div>
    <div class="pure-u-1 pure-u-md-1-3">
        <div class="pure-padding-box">
            <label for="stats_posts_in">Posts:</label>

            <select name='stats_posts_in' multiple size="6" title="Multiple select ctrl + left click">
                <?php echo playlist_config_options($post__in, array(), "id(array)") ?>
            </select>
        </div>
    </div>
    <div class="pure-u-1 pure-u-md-1-3">
        <div class="pure-padding-box">
        <label for="">Options </label> 
            <div class="pure-g">
                <div class="pure-u-1-2">                
                    <input type="date" name="post_in_date_from" id="post_in_date_from"  class="pure-input-1" title="From date">
                </div>         
                <div class="pure-u-1-2">                
                    <input type="date" name="post_in_date_to" id="post_in_date_to"  class="pure-input-1" title="To date">
                </div>  
                <div class="pure-u-1-1">                
                    <input type="text" name="post_in_stats" id="post_in_stats"  class="pure-input-1" value="" title="post id" readonly/>
                </div>                                           
            </div>   
        </div>
    </div>         
</div>
<div class="pure-g">
    <div class="pure-u-1">
        <div class="pure-padding-box">
            <div id="admin-charts"></div>
        </div>
    </div>         
</div>