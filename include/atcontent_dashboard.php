<?php
    
function atcontent_dashboard_widget_function() {
    $userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta($userid, "ac_api_key", true );
    $ac_syncid = get_user_meta($userid, "ac_syncid", true );
    echo '<div id="atcontent_dashboard_inside">';
    if ( strlen( $ac_api_key ) == 0 || strlen( $ac_syncid ) == 0  ) {
        $connect_url = admin_url( "admin.php?page=atcontent" );
        $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
        echo '<a class="button button-primary ac-connect-button" href="' . $connect_url . '">Connect your account to AtContent</a> <br><br>and get advanced analytics of your blog<div class="clear"></div></div>';
        return;
    }

    include( 'atcontent_analytics.php' );
    
    if ( current_user_can( 'edit_posts' ) ) {
        ?>
<script>
    ac_ga_s('dashboard', 'view');
    (function( $ ) {
        $(function() {
            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                action: 'atcontent_readership'
            }, function(r) {
                if (r && r.IsOK) {
                    var html = '';
                    html += '<div class="ac-dashboard-brief"><table><tr><th class="ac-dashboard-brief__tab-inactive"><a id="ac-dashboard-monthly-stats" href="#">Last 30 Days</a></th>' + 
                            '<th><a id="ac-dashboard-cumulative-stats" href="#">All Time</a></th></tr>';
                    html += '<tr id="ac-dashboard-monthly-row">' +                             
                            '<td><div class="b-dashboard-brief__left"><div class="b-dashboard-brief__value b-dashboard-brief__value_orange">' + r.monthlyBlogReposts + '</div>' + 
                            '<div class="b-dashboard-brief__description">repost' + (r.monthlyBlogReposts == 1 ? '' : 's' ) + ' of your content' + 
                            '<br>by other bloggers</div>' + 
                            '<div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">' + r.monthlyBlogRepostViews + '</div>' + 
                            '<div class="b-dashboard-brief__description b-dashboard-brief__description_small">views across AtContent network</div>' + 
                            '</div></td>' + 
                            '<td><div class="b-dashboard-brief__left"><div class="b-dashboard-brief__value b-dashboard-brief__value_orange">' + r.monthlyReposts + '</div>' + 
                            '<div class="b-dashboard-brief__description">repost' + (r.monthlyReposts == 1 ? '' : 's' ) + ' you\'ve made' + 
                            '<br>&nbsp;</div>' + 
                            '<div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">' + r.monthlyRepostViews + '</div>' +
                            '<div class="b-dashboard-brief__description b-dashboard-brief__description_small">views of reposts on your blog</div>' +
                            '</div></td>' + 
                            '</tr>';
                    html += '<tr id="ac-dashboard-cumulative-row">' + 
                            '<td><div class="b-dashboard-brief__left"><div class="b-dashboard-brief__value b-dashboard-brief__value_orange">' + r.totalBlogReposts + '</div>' + 
                            '<div class="b-dashboard-brief__description">repost' + (r.totalBlogReposts == 1 ? '' : 's' ) + ' of your content' + 
                            '<br>by other bloggers</div>' + 
                            '<div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">' + r.totalBlogRepostViews + '</div>' + 
                            '<div class="b-dashboard-brief__description b-dashboard-brief__description_small">views across AtContent network</div>' + 
                            '</div></td>' + 
                            '<td><div class="b-dashboard-brief__left"><div class="b-dashboard-brief__value b-dashboard-brief__value_orange">' + r.totalReposts + '</div>' + 
                            '<div class="b-dashboard-brief__description">repost' + (r.totalReposts == 1 ? '' : 's' ) + ' you\'ve made' + 
                            '<br>&nbsp;</div>' + 
                            '<div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">' + r.totalRepostViews + '</div>' +
                            '<div class="b-dashboard-brief__description b-dashboard-brief__description_small">views of reposts on your blog</div>' +
                            '</div></td>' + 
                            '</tr>';
                    
                    if (r.subscription == 'Free' && parseInt(r.newPostQuota) > -1) {
                        html += '<tr><td><div class="b-dashboard-brief__left"><div class="b-dashboard-brief__value b-dashboard-brief__value_small b-dashboard-brief__value_blue">' + r.newPostQuota + '</div>' + 
                                '<div class="b-dashboard-brief__description b-dashboard-brief__description_small">posts to be published<br> on AtContent this month<br><br></div></div></td><td></td></tr>';
                    }
                    
                    html += '</table></div>';
                    
                    html += '<div class="clear"></div></div>';
                    $('#atcontent_dashboard_inside').html(html);
                    $('#ac-dashboard-cumulative-stats').on('click', function(e){
                        e.preventDefault();
                        $(this).parent().removeClass('ac-dashboard-brief__tab-inactive');
                        $('#ac-dashboard-monthly-stats').parent().addClass('ac-dashboard-brief__tab-inactive');
                        $('#ac-dashboard-monthly-row').hide();
                        $('#ac-dashboard-cumulative-row').show();
                    });
                    $('#ac-dashboard-monthly-stats').on('click', function(e){
                        e.preventDefault();
                        $(this).parent().removeClass('ac-dashboard-brief__tab-inactive');
                        $('#ac-dashboard-cumulative-stats').parent().addClass('ac-dashboard-brief__tab-inactive');
                        $('#ac-dashboard-monthly-row').show();
                        $('#ac-dashboard-cumulative-row').hide();
                    });
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
    wp_add_dashboard_widget('atcontent_dashboard_widget', '<img style="vertical-align:middle;" src="' . $img_url . '" alt=""> AtContent Stats', 'atcontent_dashboard_widget_function');
    global $wp_meta_boxes;
    $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
    $atcontent_dashboard_widget = array('atcontent_dashboard_widget' => $normal_dashboard['atcontent_dashboard_widget']);
    unset($normal_dashboard['atcontent_dashboard_widget']);
    $sorted_dashboard = array_merge($atcontent_dashboard_widget, $normal_dashboard);
    $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}

?>