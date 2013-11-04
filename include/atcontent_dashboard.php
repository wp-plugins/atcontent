<?php
    
function atcontent_dashboard_widget_function() {
	$userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta($userid, "ac_api_key", true );
    
    echo '<div class="atcontent_wrap">';

    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/connect.php" );
        $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
        echo '<img style="vertical-align:bottom;" src="' . $img_url . '" alt=""> To activate AtContent features, please, <a href="' . $connect_url . '">connect</a> your blog to AtContent<div class="clear"></div></div>';
        return;
    }

    if ( current_user_can( 'edit_posts' ) ) {
        global $wpdb;
        $posts = $wpdb->get_results( 
	        "
	        SELECT ID, post_title, post_author
	        FROM {$wpdb->posts}
	        WHERE post_status = 'publish' 
		        AND post_author = {$userid} AND post_type = 'post'
	        "
        );

        $posts_id = array();

        foreach ( $posts as $post ) 
        {
            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            if ( strlen( $ac_postid ) > 0 ) { 
                array_push( $posts_id, $ac_postid );
            }
        }

        $gp_result = atcontent_api_guestposts_incoming( site_url(), $ac_api_key );
        if ( $gp_result["IsOK"] == true ) {
            foreach ( $gp_result["List"] as $gp_item ) {
                if ( $gp_item["Status"] != "Accepted") continue;
                array_push( $posts_id, $gp_item["Post4gId"] );
            }
        }

        $response = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );
        if ( $response["OriginalViews"] > 0 ) {
            $num = number_format_i18n( $response["OriginalViews"] );
            ?>
            <div class="ac_readership">
                <table class="ac_table_readership">
                    <tr><th>Original Views</th></tr>
                    <tr><td><?php echo $num; ?></td></tr>
                </table>
            </div>
            <?php
        }
        if ( $response["RepostViews"] > 0 ) {
            $num = number_format_i18n( $response["RepostViews"] );
            ?>
            <div class="ac_readership">
                <table class="ac_table_readership">
                    <tr><th>Repost Views</th></tr>
                    <tr><td><?php echo $num; ?></td></tr>
                </table>
            </div>
            <?php
        }
        if ( $response["IncreaseRate"] > 0 ) {
            $num = number_format_i18n( $response["IncreaseRate"] );
            ?>
            <div class="ac_readership">
                <table class="ac_table_readership">
                    <tr><th>Increase Rate, %</th></tr>
                    <tr><td><?php echo $num; ?></td></tr>
                </table>
            </div>
            <?php
        }
        if ( $response["Days"] > 0 ) {
            $num = number_format_i18n( $response["Days"] );
            ?>
            <div class="ac_readership">
                <table class="ac_table_readership">
                    <tr><th>Days Connected</th></tr>
                    <tr><td><?php echo $num; ?></td></tr>
                </table>
            </div>
            <?php
        }
    }
    $statisticslink = admin_url("admin.php?page=atcontent/statistics.php");
    $ratinglink = admin_url("admin.php?page=atcontent/rating.php");
    echo "<div class=\"clear\"></div><div style=\"text-align:center;margin-top:15px;\">" . 
    "<a href=\"{$statisticslink}\">Get details</a></div></div>";
}

function atcontent_add_dashboard_widgets() {

    $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
	wp_add_dashboard_widget('atcontent_dashboard_widget', '<img style="vertical-align:bottom;" src="' . $img_url . '" alt=""> AtContent', 'atcontent_dashboard_widget_function');

    global $wp_meta_boxes;
	
	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	
	$atcontent_dashboard_widget = array('atcontent_dashboard_widget' => $normal_dashboard['atcontent_dashboard_widget']);
	unset($normal_dashboard['atcontent_dashboard_widget']);

	$sorted_dashboard = array_merge($atcontent_dashboard_widget, $normal_dashboard);

	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}

?>