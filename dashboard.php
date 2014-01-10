<?php
    $userid = intval( wp_get_current_user()->ID );
    $ref_url = "http://wordpress.org/plugins/atcontent/";
    require( "include/atcontent_userinit.php" );
    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/connect.php" );
        ?>
<script>window.location = '<?php echo $connect_url; ?>';</script>
        <?php
    }
    $currentuser = wp_get_current_user();
    $userinfo = get_userdata($currentuser -> ID);
    $email = $userinfo -> user_email;
    $site = $_SERVER['HTTP_HOST'];
?>
<script src="/wp-content/plugins/atcontent/interface.js" type="text/javascript"></script>
<script>
    var email = '<?php echo $email?>';    
    var site = '<?php echo $site?>';
    gaSend('dashboard', 'opened');

    function gaSend(category, action)
    {
        window.CPlase_ga = window.CPlase_ga || [];
                        CPlase_ga.push({
                            category: category,
                            action: action,
                            label: site + '      ' + email
                        });
    }

    function upgradePlanClick()
    {
        gaSend('dashboard', 'upgrade plan clicked');
    }

    function getDetailsClick()
    {
        gaSend('dashboard', 'get details clicked');
    }
</script>
<div class="atcontent_wrap">
    <div class="wrap">
        <div class="icon32" id="icon-index"><br></div><h2>AtContent Dashboard</h2>
    </div>

    <p>
        Hello,
        <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><img style="vertical-align: text-top; margin-right: .3em" 
            src="<?php echo $ac_avatar_20; ?>" alt=""><?php echo $ac_show_name; 
 ?></a>!
        <br>Your blog is connected to AtContent.
    </p>
<?php if ( user_can( $userid, "publish_posts" ) ) { ?>
    <div class="b-dashboard">
        <div class="b-dashboard-col">
            <h2>Credits</h2>
            <div class="b-dashboard-table">
                <table>
<?php
    
    $quotas_result = atcontent_api_get_quotas( $ac_api_key );
    if ( $quotas_result["IsOK"] == true ) {
        foreach ( $quotas_result["Subscriptions"] as $subscription ) {
            $enddate = strtotime( $subscription["EndTime"] );
            $enddate_text = date( "M j, Y", $enddate );
            $period_end_date = strtotime ( $subscription["PeriodEndTime"] );
            $period_end_date_text = date( "M j, Y", $period_end_date );
            echo <<<END
<tr><th>Subscription plan</th><td>{$subscription["Title"]}</td></tr>
<tr><th>Current period ends</th><td>{$enddate_text}</td></tr>
END;
            if ( $subscription["Title"] != "Free" ) {
                echo <<<END
<tr><th>Paid until</th><td>{$period_end_date_text}</td></tr>
END;
            }
        }
        if ( count( $quotas_result["Subscriptions"] ) == 0 ) {
?>
                    <tr><th>
                        To use all features of AtContent you should choose a subscription plan.<br>
                        Different plans provide different amounts of guest posts,<br>advanced tracking and copy protection.<br>
                        <a href="https://atcontent.com/Subscribe/" target="_blank">Choose the suitable plan here</a></th><td></td></tr>
<?php
        }
    } else {
        echo '<tr><th>Error getting data</th></tr>';
    }
?>                    
                </table>
<?php if ( $quotas_result["IsOK"] == true && count( $quotas_result["Subscriptions"] ) > 0 ) { ?>
                <p style="text-align: right;padding-right:10px;"><a href="https://atcontent.com/Subscribe/" target="_blank" onclick="upgradePlanClick()">Upgrade to a bigger plan here</a></p>
<?php } ?>
            </div>
<?php if ( $quotas_result["IsOK"] == true && count( $quotas_result["Quotas"] ) > 0 ) {  ?>
            <h3>I have</h3>
            <div class="b-dashboard-table">
                <table>
<?php
    if ( $quotas_result["IsOK"] == true ) {
        foreach ( $quotas_result["Quotas"] as $quota ) {
            echo <<<END
<tr><th>{$quota["Title"]}</th><td>{$quota["Count"]}</td></tr>
END;
        }
    } else {
        echo '<tr><th>Error getting data</th></tr>';
    }

?>
                </table>
            </div>
<?php } ?>
        </div>
        <div class="b-dashboard-col">
            <h2>Statistics of my blog</h2>
            <div class="b-dashboard-table">
                <div style="width:416px;">
<?php
    if ( strlen( $ac_api_key ) > 0 ) {
        $posts = $wpdb->get_results( 
            "
            SELECT ID, post_title, post_author
            FROM {$wpdb->posts}
            WHERE post_status = 'publish' 
                AND post_author = {$userid} AND post_type = 'post'
            "
        );

        $posts_id = array();

        wp_cache_flush();

        foreach ( $posts as $post ) 
        {
            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            if ( strlen( $ac_postid ) > 0 ) { 
                array_push( $posts_id, $ac_postid );
            }
            wp_cache_flush();
        }

        $response = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );

        if ( $response["IsOK"] == true ) {
?>
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
                <p><a class="button" href="https://atcontent.com/Studio/Statistics" target="_blank" onclick="getDetailsClick()">Get details</a></p>
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
<?php
        } else {
            echo '<tr><th>Error getting data</th></tr>';
        }
    }
?>
                </div>
            </div>
    <?php    } //end if admin-dashboard ?>
            <h2>What can I do?</h2>
            <ul class="b-dashboard-list">
                <li><a href="<?php echo admin_url( "admin.php?page=atcontent/settings.php" ); ?>">Adjust plugin settings</a></li>
                <li><a href="<?php echo admin_url( "admin.php?page=atcontent/sync.php" ); ?>">Sync my blog posts</a></li>
                <?php if ( user_can( $userid, "publish_posts" ) ) { ?>
                <li><a href="https://atcontent.com/Subscribe/" target="_blank" onclick="upgradePlanClick()">Choose a subscription plan</a></li>
                <?php } ?>
            </ul>
    
            <h2>Invite friends to AtContent</h2>
            <!--<p>For every friend who installs AtContent plugin on their blog,<br> we'll give you <b>free</b> check for plagiarism for up to 100 of your posts!</p>
            <h3>Invite friends</h3>-->
        
            <textarea id="inviteText" style="width: 100%;height: 45px;display: none;">Jump up in search, reach new readership, brand and control your content with #AtContent. Free WP plugin for your blog</textarea>
        
            <!--<p style="font-size: 1.2em;">&nbsp;&nbsp; <b>↓</b> Send by email or share anywhere!</p>-->
            <div id="addthis_share">
            <!-- AddThis Button BEGIN -->
                <div addthis:url="<?php echo $ref_url; ?>" 
                     addthis:title="WordPress with AtContent — even better. Check it!"
                     addthis:description="Jump up in search, reach new readership, brand and control your content with #AtContent. Free WP plugin for your blog"

                    class="addthis_toolbox addthis_default_style addthis_32x32_style">
                    <a class="addthis_button_email"></a>
                    <a class="addthis_button_facebook"></a>
                    <a class="addthis_button_twitter"></a>
                    <a class="addthis_button_linkedin"></a>
                    <a class="addthis_button_pinterest_share"></a>
                    <a class="addthis_button_google_plusone_share"></a>
                    <a class="addthis_button_stumbleupon"></a> 
                    <a class="addthis_button_digg"></a>
                    <a class="addthis_button_compact"></a>
                    <a class="addthis_counter addthis_bubble_style"></a>
                
                </div>
                <script type="text/javascript">
                    var addthis_share =
                    {
                        url: '<?php echo $ref_url; ?>',
                        title: 'WordPress with AtContent — even better. Check it!',
                        description: 'Jump up in search, reach new readership, brand and control your content with #AtContent. Free WP plugin for your blog',
                        email_template: 'plugin_invite',
                    };
                    var ac_j = jQuery;
                    ac_j(function(){
                        window.addthis_share.title = ac_j("#inviteText").val();
                        ac_j("#inviteText").bind('input propertychange', function() {
                            if(this.value.length){
                                window.addthis_share.title = this.value;
                                window.addthis_share.description = this.value;            
                                addthis.toolbox(".addthis_toolbox");
                                
                            }
                        });
                    });
                </script>
                <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-514ee41e167a87dc"></script>
                <!-- AddThis Button END -->
                    
            </div>
        </div>
    </div>
</div>
<?php atcontent_ga("DashboardTab", "Dashboard"); ?>