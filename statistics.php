<?php 
    $ac_postid = $_GET["postid"]; 
    if ( strlen( $ac_postid ) == 0 ) {
        $userid = wp_get_current_user()->ID;
        $posts = $wpdb->get_results( 
	        "
	        SELECT ID, post_title, post_author
	        FROM {$wpdb->posts}
	        WHERE post_status = 'publish' 
		        AND post_author = {$userid} AND post_type = 'post'
	        "
        );

        echo "<h2>AtContent Statistics</h2>";

        foreach ( $posts as $post ) 
        {
            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            if ($post->post_author == $userid && strlen( $ac_postid ) > 0 ) {
                echo "<a href=\"" . atcontent_get_statistics_link( $post->ID ) . "\">{$post->post_title}</a>";
            }
        }        
    } else {
?>
<script type="text/javascript">
    function initstatframe() {
        (function ($) {
            $("#statloader").remove();
            $("#statframe").css("visibility", "visible");
            $("#wpbody-content").css("float", "none");
        })(jQuery);
    }
</script>
<div style="text-align: center;padding: 50px;font-size: 40px;" id="statloader">Loading...</div>
<iframe id="statframe" onload="initstatframe()" src="https://atcontent.com/Studio/Publication/StatIframe/<?php echo $ac_postid; ?>/" style="position: absolute; width: 100%; height: 100%; visibility: hidden; ">
</iframe>
<?php    
    }
?>