<?php
    $userid = wp_get_current_user()->ID;
    $posts = $wpdb->get_results( 
	            "
	            SELECT ID, post_title, post_author
	            FROM {$wpdb->posts}
	            WHERE post_status = 'publish' 
		            AND post_author = {$userid} AND post_type = 'post'
	            "
            );
    $posts_count = 0;
    $imported_count = 0;
    foreach ( $posts as $post ) 
    {
         $ac_postid = get_post_meta($post->ID, "ac_postid", true);
         if ( strlen( $ac_postid ) > 0 ) $imported_count++;
         $posts_count++;
    }

?>
<style>
    
.b-big-text {
    font-size: 15px;
}
    button.button-size-big, .likebutton.b_big {
    font-size: 25px;
}
    button.button-color-orange, .likebutton.b_orange {
    background: none repeat scroll 0 0 #EE8900;
    color: #FFFFFF !important;
}
    .likebutton {
    -moz-box-sizing: border-box;
    cursor: pointer;
    display: inline-block;
    font: 16px/2em 'Trebuchet MS',Arial,Helvetica,sans-serif;
    padding: 0 1em;
    position: relative;
    text-decoration: none;
    text-shadow: -1px -1px rgba(0, 0, 0, 0.15);
    vertical-align: middle;
}
    .likebutton:hover {
    text-decoration: none;
}
    button.button-color-orange:hover, .likebutton.b_orange:hover, .qq-upload-button-hover .likebutton.b_orange {
    background: none repeat scroll 0 0 #F5A200;
}
    button.button-color-orange:active, .likebutton.b_orange:active {
    background: none repeat scroll 0 0 #E76D00;
}
</style>
<div class="wrap">
<div class="icon32" id="icon-tools"><br></div><h2>AtContent CopyLocator</h2>
<div class="tool-box">
    <p class="b-big-text">Find all illegal copies of your content across the Internet</p>
    <?php if ( $imported_count == 0 ) { ?>
        <?php if ( $posts_count == 0 ) {  ?>
            <p>You don't have publications yet. Write something first!</p>
        <?php } else { ?>
            <p>You have <?php echo $posts_count ?> publications, but you should import it first. 
                Follow <a href="<?php echo admin_url("admin.php?page=atcontent/settings.php"); ?>">AtContent Dashboard page</a> and click Import</p>
        <?php } ?>
    <?php } else { ?>
        <?php if ( $imported_count < $posts_count ) { ?>
            <p>You have <?php echo $imported_count ?> publications. 
                And <?php echo $posts_count - $imported_count ?> more available for <a href="<?php echo admin_url("admin.php?page=atcontent/settings.php"); ?>">import</a>.</p>
        <?php } else { ?>
            <p>You have <?php echo $imported_count ?> publications.</p>
        <?php } ?>
    <?php }?>
            <p><a href="http://atcontent.com/CopyLocator/Create/" class="likebutton b_big b_orange">Find illegal copies</a></p>
</div>
</div>