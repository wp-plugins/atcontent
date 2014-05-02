<?php
    
function atcontent_dashboard_widget_function() {
    wp_register_script( 'atcontentAdminGoogleAPI',  '//www.google.com/jsapi', array(), true );
    wp_enqueue_script( 'atcontentAdminGoogleAPI' );
	$userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta($userid, "ac_api_key", true );
    $ac_syncid = get_user_meta($userid, "ac_syncid", true );
    
    echo '<div class="atcontent_wrap" id="atcontent_dashboard_inside">';

    if ( strlen( $ac_api_key ) == 0 || strlen( $ac_syncid ) == 0  ) {
        $connect_url = admin_url( "admin.php?page=atcontent/dashboard.php" );
        $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
        echo '<a class="button button-primary ac-connect-button" href="' . $connect_url . '">Connect your account to AtContent</a> <br><br>and get advanced analytics of your blog<div class="clear"></div></div>';
        return;
    }
    
    if ( current_user_can( 'edit_posts' ) ) {
        ?>
<script>
    (function( $ ) {
        $(function() {
            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                action: 'atcontent_readership'
            }, function(r) {
                if (r && r.IsOK) {
                    var html = '<div style="position: relative"><div class="b-dashboard-brief"><div class="b-dashboard-brief__left b-dashboard-brief__left_front">' + 
                                '<div class="b-dashboard-brief__value b-dashboard-brief__value_orange">';
                    if (r.repostViews > 0) {
                        html += '<span class="b-dashboard-brief__plus">+</span>';
                    }
                    html += r.repostViews + '</div>';
                    html += '<div class="b-dashboard-brief__description">view<span data-role="plural">s</span> via AtContent';
                    html += '<br>for the last ' + r.days + ' ' + (r.days > 1 ? 'days' : 'day') + '</div>';
                    html += '<div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">' + r.originalViews + '</div>';
                    html += '<div class="b-dashboard-brief__description b-dashboard-brief__description_small">views on your blog</div>';
                    html += '<p><a class="button" href="https://atcontent.com/studio/statistics/?wp=1" target="_blank">Get details</a></p></div>';
                    html += '<div class="b-dashboard-brief__right b-dashboard-brief__right_front">';
                    if (r.repostViews + r.originalViews == 0) {
                        html += '<div class="b-dashboard-brief__empty-chart"></div>';
                    } else {
                        html += '<div id="atcontent_chart" class="b-dashboard-brief__chart"></div>';
                        google.load('visualization', '1.0', {
                            'packages': ['corechart', 'table'],
                            'callback': function(){
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
                                    ['Views on your blog', r.originalViews],
                                    ['Views via AtContent', r.repostViews]
                                ];
                                data.addRows(rows);
                                chart = new google.visualization.PieChart (element);
                                chart.draw(data, options);
                            }
                        });
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="clear"></div></div>';
                    $('#atcontent_dashboard_inside').html(html);
                } else {
                    $('#atcontent_dashboard_inside').html('');
                }
            }, 'json');
        });
    })(jQuery);
        
</script>
    <?php } ?>
</div>
<?php
}

function atcontent_add_dashboard_widgets() {
    $userid = wp_get_current_user()->ID;
    if ( !user_can( $userid, "edit_posts" ) ) return;
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