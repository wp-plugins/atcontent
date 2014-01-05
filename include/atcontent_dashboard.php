<?php
    
function atcontent_dashboard_widget_function() {
	$userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta($userid, "ac_api_key", true );
    
    echo '<div class="atcontent_wrap">';

    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/connect.php" );
        $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
        echo '<img style="vertical-align:bottom;" src="' . $img_url . '" alt=""> To activate AtContent features, please, <a href="' . $connect_url . '">connect</a> your blog to AtContent<div class="clear"></div></div>';
        atcontent_ga("Dashboard", "WordPress Dashboard");
        return;
    }

    
    $connect_result = atcontent_api_connectgate( $ac_api_key, $userid, get_site_url(), admin_url("admin-ajax.php") );
    if ( $connect_result["IsOK"] == TRUE ) {
        update_user_meta( $userid, "ac_oneclick_repost", "1" );
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

        $response = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );
        ?>
<div style="position: relative">
<div class="b-dashboard-brief">
            <div class="b-dashboard-brief__left b-dashboard-brief__left_front">
                <div class="b-dashboard-brief__value b-dashboard-brief__value_orange">
                    <span class="b-dashboard-brief__plus">+</span>
                    <?php echo $response["repostViews"]; ?>
                </div>
                <div class="b-dashboard-brief__description">
                    view<span data-role="plural">s</span> via AtContent
                    <br>
                    for the last 12 hours
                </div>
                <div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">
                    <?php echo $response["originalViews"]; ?>
                </div>
                <div class="b-dashboard-brief__description b-dashboard-brief__description_small">
                    views on your blog
                </div>
                <p><a class="button" href="https://atcontent.com/Studio/Statistics" target="_blank">Get details</a></p>
            </div>
            <div class="b-dashboard-brief__right b-dashboard-brief__right_front">
                <?php if ( intval( $response["originalViews"] ) + intval( $response["repostViews"] ) == 0 ) { ?>
                <div class="b-dashboard-brief__empty-chart"></div>
                <?php } else { ?>
                <div id="atcontent_chart" class="b-dashboard-brief__chart"></div>
                <?php } ?>
            </div>
        </div>
        <script src="//www.google.com/jsapi"></script>
        <script>
            google.load('visualization', '1.0', {
                'packages': ['corechart', 'table']
            });
            google.setOnLoadCallback(function () {
                var options, data, chart, element, rows;
                
                element = document.getElementById('atcontent_chart');

                options = {
                    colors: ['#13669d', '#ee8900'],
                    chartArea: {
                        width: '90%',
                        height: '90%'
                    },
                    title: '',
                    titleTextStyle: {
                        bold: false
                    },
                    fontName: 'Segoe UI',
                    legend: {
                        position: 'none'
                    },
                    pieSliceTextStyle: {
                        fontSize: 15
                    }
                };

                data = new google.visualization.DataTable ();
                data.addColumn('string', 'Type');
                data.addColumn('number', 'Views');
                
                rows = [
                    ['Views on your blog', <?php echo $response["originalViews"]; ?>],
                    ['Views via AtContent', <?php echo $response["repostViews"]; ?>]
                ];
                
                data.addRows(rows);

                chart = new google.visualization.PieChart (element);
                chart.draw(data, options);
            });
        </script>
<div class="clear"></div>
<?php } ?>
</div>
<a href="https://atcontent.com/Studio/Statistics" target="_blank">
<div style="position: absolute;width: 100%;height: 100%;top: 0px;left: 0px;z-index: 100;">&nbsp;</div>
</a>
</div>
<?php
    atcontent_ga("Dashboard", "WordPress Dashboard");
}

function atcontent_add_dashboard_widgets() {

    $userid = wp_get_current_user()->ID;
    if ( !user_can( $userid, "publish_posts" ) ) return;

    $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
	wp_add_dashboard_widget('atcontent_dashboard_widget', '<img style="vertical-align:middle;" src="' . $img_url . '" alt=""> AtContent', 'atcontent_dashboard_widget_function');

    global $wp_meta_boxes;
	
	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	
	$atcontent_dashboard_widget = array('atcontent_dashboard_widget' => $normal_dashboard['atcontent_dashboard_widget']);
	unset($normal_dashboard['atcontent_dashboard_widget']);

	$sorted_dashboard = array_merge($atcontent_dashboard_widget, $normal_dashboard);

	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}

?>