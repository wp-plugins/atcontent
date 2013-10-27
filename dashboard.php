<?php
    $userid = intval( wp_get_current_user()->ID );
    require( "atcontent_userinit.php" );
    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/connect.php" );
        ?>
<script>window.location = '<?php echo $connect_url; ?>';</script>
        <?php
    }
?>
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

    <div class="b-dashboard">
        <div class="b-dashboard-col">
            <h2>Credits</h2>
            <div class="b-dashboard-table">
                <table>
<?php 
    $quotas_result = atcontent_api_get_quotas ( $ac_api_key );
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
                        Different plans provide different amounts of guest posts,<br>advanced tracking and plagiarism protection.<br>
                        <a href="https://atcontent.com/Subscribe/" target="_blank">Choose the suitable plan here</a></th><td></td></tr>
<?php
        }
    } else {
        echo '<tr><th>Error getting data</th></tr>';
    }
?>                    
                </table>
<?php if ( $quotas_result["IsOK"] == true && count( $quotas_result["Subscriptions"] ) > 0 ) { ?>
                <p style="text-align: right;padding-right:10px;"><a href="https://atcontent.com/Subscribe/" target="_blank">Upgrade to a bigger plan here</a></p>
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
                <table>
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
            if ( $response["IncreaseRate"] > 0 ) {
                $num = number_format_i18n( $response["IncreaseRate"] );
                echo '<tr><th>Increase in readership</th>' .
                    '<td>' .$num . '%</td></tr>';
            }
            if ( $response["OriginalViews"] > 0 ) {
                $num = number_format_i18n( $response["OriginalViews"] );
                echo '<tr><th>Views on my blog</th>' .
                    '<td>' .$num . '</td></tr>';
            }
            if ( $response["RepostViews"] > 0 ) {
                $num = number_format_i18n( $response["RepostViews"] );
                echo '<tr><th>Views outside of my blog</th>' .
                    '<td>' .$num . '</td></tr>';
            }
            if ( $response["Days"] > 0 ) {
                $num = number_format_i18n( $response["Days"] );
                echo '<tr><th>Period of using AtContent</th>' .
                     '<td>' . $num . ' day' . ($num != 1 ? 's' : '') . '</td></tr>';
            }
        } else {
            echo '<tr><th>Error getting data</th></tr>';
        }
    }
?>
                </table>
            </div>
    
            <h2>What can I do?</h2>
            <ul class="b-dashboard-list">
                <li><a href="<?php echo admin_url("admin.php?page=atcontent/settings.php"); ?>">Adjust plugin settings</a></li>
                <li><a href="<?php echo admin_url("admin.php?page=atcontent/sync.php"); ?>">Sync my blog posts</a></li>
                <li><a href="<?php echo admin_url("admin.php?page=atcontent/subscription.php"); ?>">Choose a subscription plan</a></li>
            </ul>
    
            <h2>Invite your friends to AtContent</h2>
            <!--<p>For every friend who installs AtContent plugin on their blog,<br> we'll give you <b>free</b> check for plagiarism for up to 100 of your posts!</p>
            <h3>Invite friends</h3>-->
        
            <textarea id="inviteText" style="width: 100%;height: 45px;display: none;">Jump up in search, reach new readership, brand and control your content with #AtContent. Free WP plugin for your blog</textarea>
        
            <!--<p style="font-size: 1.2em;">&nbsp;&nbsp; <b>↓</b> Send by email or share anywhere!</p>-->
            <div id="addthis_share">
            <!-- AddThis Button BEGIN -->
                <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
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