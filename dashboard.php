<?php
    $ref_url = "http://wordpress.org/plugins/atcontent/";
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    require_once( "include/atcontent_userinit.php" );
    $currentuser = wp_get_current_user();
    $userid = intval( $currentuser->ID );
    if ( strlen( $_GET['connectas'] ) > 0 )
    {
        $userid = intval( $_GET['connectas'] );
    }
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
    if ( strlen($ac_api_key) != 0 && strlen($ac_syncid) != 0 ) {   
        $ac_blogid = get_user_meta( $userid, "ac_blogid", true ); 
    
        $currentuser = wp_get_current_user();
        $userinfo = get_userdata($currentuser -> ID);
        $stats = atcontent_api_get_sync_stat($ac_syncid, $ac_blogid);
?>

<div style="width: 100%; height: 40px;"></div>
<div id="popup-bg" class="popup-bg" style="display: none"></div>

<script>

    jQuery( function($){
        $('#footer-thankyou').before('<a href="https://atcontent.zendesk.com/anonymous_requests/new" target="_blank">AtContent Support Center</a><br>');
        $('#footer-upgrade').prepend('<br>');
    });

    jQuery("#contextual-help-link").hide();

    function beforechangeaccount() {
        if (confirm("Are you sure you want to change AtContent profile?")) {
            jQuery.ajax({url: '<?php echo $ajax_form_action; ?>',
			    type: 'post',
			    data: {
					    action: 'atcontent_disconnect'
					}, 
                success: function(d)
                {  
                    if (d.IsOK) {
                        window.location = 'admin.php?page=atcontent/dashboard.php&noauto=1';
                    } 
                },                   
			    dataType: "json"
		    });
        }
    }
    
    function Resync() {
        jQuery("#resync_button").removeClass('b_orange').addClass('b_enable');
        jQuery("#sync-status").html('In process'); 
        jQuery.ajax({
            url: '<?php echo $ajax_form_action; ?>', 
            type: 'post', 
            data: {
                action: 'atcontent_syncqueue'
            },
            dataType: "json",
            success: function(d) {
                jQuery("#resync_button").removeClass('b_enable').addClass('b_orange');   
            },
            error: function(d, s, e) {
            }
        });    
    }
        
        

</script>
<div style="position: absolute;right: 20px;top: 10px;">
    You are connected to AtContent as 
    <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank">
        <img style="margin-right: 2px; vertical-align: middle" src="<?php echo $ac_avatar_20; ?>" onerror="this.parentNode.removeChild(this)" alt="" width="18" height="18"><?php echo $ac_show_name; ?>
    </a></br>
    <a href="#" style="float: right;font-size: 0.7em;" onclick="beforechangeaccount()">Not you?</a>
</div>
<div class="atcontent_wrap">
    <div class="b-dashboard-table b-dashboard-table-status" id="sync-process" style="width: 420px;">
        <table>
            <tr>
                <th>Sync status
                    <a data-title="Synchronization shows how many posts are available to repost for other AtContent user." class="hint">
                        <img alt="?" src="<?php echo(plugins_url( 'assets/help.png', __FILE__ )); ?>" />
                    </a>
                </th>
                <td id="sync-status"><?php 
                    if ( $stats["IsSyncNow"] ) {
                        echo ('In process'); 
                    } 
                    elseif ( $stats["IsActive"] ) {
                        echo ('Completed'); 
                    }
                    else {
                        echo ('Error'); 
                    }?>
                </td>
            </tr>
            <tr>
                <th>Amount of synced posts</th>
                <td id="post-counter"> <?php echo $stats["PostCount"]; ?></td>
            </tr>
            <?php if($stats["ErrorsCount"]!=0){ ?>  
                <tr>
                    <th>Errors count</th><td id="error-counter"> <?php echo $stats["ErrorsCount"]; ?></td>
                </tr>
            <?php }?>
        </table>
        <a href="#" id="resync_button" style="margin-left: 10px" class="likebutton b_orange" onclick="Resync()">Resync</a>
    </div>
    
    
    <?php
        // PingBack
        if ( ! atcontent_pingback_inline() ) {
            echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
        }
        //End PingBack
        include("stat_block.php"); ?>
      <?php  if ($_GET["step"] == "1"){ ?>
            <script>
                jQuery("#tip_two_step").show();
                jQuery('.one_page_link').show();  
            </script>
        <?php } 
    }
    else
    {
        include( "invite.php" );
    }
?>

    
</div>