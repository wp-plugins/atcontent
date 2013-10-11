<?php
$form_action = admin_url( 'admin-ajax.php' );
// PingBack

         if ( ! atcontent_pingback_inline() ) {
             echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
         }

         //End PingBack
$userid = wp_get_current_user()->ID;
$ac_api_key = get_user_meta($userid, "ac_api_key", true );
?>
<div class="atcontent_wrap">
<?php
    if ( strlen($ac_api_key) == 0 ) {
        $form_action = admin_url( 'admin-ajax.php' );
        include("invite.php");
        ?> 
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) window.location = '<?php echo admin_url( 'admin.php?page=atcontent/settings.php' ); ?>';
            else $("#ac_connect_result").html(
                    'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
    })(jQuery);
</script>
<?php
    } else {
?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div><h2>AtContent Credits</h2>
<?php 
    $quotas_result = atcontent_api_get_quotas ( $ac_api_key );
    if ( $quotas_result["IsOK"] == true ) {
        $subscription_list = array();
        $subscription_caption = "";
        foreach ( $quotas_result["Subscriptions"] as $subscription ) {
            $enddate = strtotime( $subscription["EndTime"] );
            $period_end_date = strtotime ( $subscription["PeriodEndTime"] );
            $subscription_list[] = 'Subscription plan: ' . $subscription["Title"] . 
                "<br>Current period ends " . date( "M j, Y", $period_end_date ) . 
                ($subscription["Title"] == "Free" ? "" : "<br>Paid until " . date( "M j, Y", $enddate )  );
        }
        if ( count( $subscription_list ) == 0 ) {
            $subscription_caption = 'Subscription plan: none';
        } else {
            $subscription_caption = join( $subscription_list, ",");
        }
?>
    <h3><?php echo $subscription_caption; ?></h3>
    <?php
        if ( count( $subscription_list ) == 0 ) {
            echo <<<END
<p><a class="likebutton b_big b_orange" href="https://atcontent.com/Subscribe" target="_blank">Choose the suitable plan</a></p>
END;
        }
        foreach ( $quotas_result["Quotas"] as $quota ) {
            echo <<<END
<h3>Available {$quota["Title"]}: {$quota["Count"]}</h3>
END;
        }
        if ( count ( $subscription_list ) > 0 ) {
            echo <<<END
<p><a class="likebutton b_orange" href="https://atcontent.com/Subscribe" target="_blank">Upgrade to a bigger plan</a></p>
END;
        }        
        
    } else {
        echo "<div class=\"error\">" . 'Couldn\'t get credits and subscription info from AtContent' . "</div>";
    }
    
?>
</div>
</div>
<div class="clear"></div>
<?php         
    }
?>