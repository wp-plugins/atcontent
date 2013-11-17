<div class="atcontent_wrap">
<?php
    
    $atcontent_menu_section = "subscription";
    require( "atcontent_userinit.php" );

    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/connect.php" );
        ?>
<script>window.location = '<?php echo $connect_url; ?>';</script>
        <?php
    }

    include( "settings_menu.php" );
?>
    <div class="b-dashboard">
        <div class="b-dashboard-col">
            <h2>Subscription Info</h2>
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
<?php if ( $quotas_result["IsOK"] == true && $subscription["Title"] != "Professional" ) { ?>
                <p style="text-align: right;padding-right:10px;"><a href="https://atcontent.com/Subscribe/" target="_blank">Upgrade to a bigger plan here</a></p>
<?php } ?>
            </div>
<?php if ( $quotas_result["IsOK"] == true && count( $quotas_result["Quotas"] ) > 0 ) {  ?>
            </div><div class="b-dashboard-col">
            <h2>Credits</h2>
            <div class="b-dashboard-table">
                <table>
<?php
    if ( $quotas_result[ "IsOK" ] == true ) {
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
    </div>

</div>