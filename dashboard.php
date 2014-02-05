<?php
    $ref_url = "http://wordpress.org/plugins/atcontent/";
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    require( "include/atcontent_userinit.php" );
    $currentuser = wp_get_current_user();
    $userid = intval( $currentuser->ID );
    if (strlen($_GET['connectas']) > 0)
    {
        $userid = intval($_GET['connectas']);
    }
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
    if ( strlen($ac_api_key) != 0 && strlen($ac_syncid) != 0 ) {   
        $ac_blogid = get_user_meta( $userid, "ac_blogid", true ); 
    
        $currentuser = wp_get_current_user();
        $userinfo = get_userdata($currentuser -> ID);
        $email = $userinfo -> user_email;
        $site = $_SERVER['HTTP_HOST'];
        $stats = atcontent_api_get_sync_stat($ac_syncid, $ac_blogid);
?>

<div style="width: 100%; height: 40px;"></div>
<div id="popup-bg" class="popup-bg" style="display: none"></div>
<div class="b-dashboard-info">
    <a class="one_page_link" onclick="settings()">Show settings</a>
</div>

<script src="/wp-content/plugins/atcontent/assets/interface.js" type="text/javascript"></script>
<script>
    var isFirstTime = false;
    

    var email = '<?php echo $email?>';    
    var site = '<?php echo $site?>';
    gaSend('dashboard', 'opened');

    function gaSend(category, action)
    {
        window.CPlase_ga = window.CPlase_ga || [];
                        CPlase_ga.push({
                            category: category + ' <?php echo AC_VERSION?>',
                            action: action,
                            label: site + '      ' + email
                        });
    }

    jQuery("#contextual-help-link").hide();

    function beforechangeaccount() {
        if (confirm("Are you sure you want to change account?"))
            jQuery.ajax({url: '<?php echo $ajax_form_action; ?>',
			    type: 'post',
			    data: {
					    action: 'atcontent_disconnect'
					}, 
                success: function(d)
                {  
                    if (d.IsOK)
                    {
                        location.reload();
                    }
                    else
                    {
                    }
                },                   
			    dataType: "json"
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

    function hideSettings()
    {
        jQuery('.one_page_link').html('Show settings');  
        jQuery('.one_page_link').attr('onclick','settings()');
        jQuery("#settings_step").hide();    
    } 

    function settings()
    {
        jQuery('.one_page_link').html('Hide settings');        
        jQuery('.one_page_link').attr('onclick','hideSettings()');
        jQuery("#settings_step").show();
    } 

    
        

        function Resync()
        {
            jQuery("#resync_button").removeClass('b_orange').addClass('b_enable');
            jQuery("#sync-status").html('In process'); 
            jQuery.ajax({url: '<?php echo $ajax_form_action; ?>', 
                type: 'post', 
                data: {action: 'atcontent_syncqueue'},
                dataType: "json",
                success: function(d){
                    jQuery("#resync_button").removeClass('b_enable').addClass('b_orange');   
                },
                error: function(d, s, e) {
                }
            });    
        }
        
        

</script>
<div style="position: absolute;right: 20px;top: 10px;">
    You are connected to AtContent as <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><img style="margin-right: 2px; vertical-align: middle" src="<?php echo $ac_avatar_20; ?>" alt="" width="18" height="18"><?php echo $ac_show_name; ?></a></br>
    <a href="#" style="float: right;font-size: 0.7em;" onclick="beforechangeaccount()">Not you?</a>
</div>
<div class="atcontent_wrap">
    <div id="settings_step">    
        <?php include("settings.php"); ?>
        
        <?php if ($_GET["step"] == "1"){ ?>
            <script>    
                isFirstTime = true;
                settings();
                jQuery("#b-settings-block__site_settings").hide();
                jQuery("#settings_step").css('top','95px');
                jQuery("#settings_step").css('left', '50%');
                jQuery("#settings_step").css('margin-left', '-220px');
    
                jQuery("#dashboard-table").css('visibility', 'hidden');   
                jQuery('.one_page_link').hide(); 
                jQuery('#tip_one_step').show();   
            </script>
        <?php } ?>  
    </div>   
    <div class="b-dashboard-table b-dashboard-table-status" id="sync-process" style="width: 420px;">
        <table>
            <tr><th>Sync status
    <a data-title="Synchronization shows how many posts are available to repost for other AtContent user." class="hint"><img src="/wp-content/plugins/atcontent/assets/help.png"></img></a></th><td id="sync-status"><?php 
                    if ($stats["IsSyncNow"]) {
                        echo ('In process'); 
                    } 
                    elseif ($stats["IsActive"]) {
                        echo ('Completed'); 
                    }
                    else {
                        echo ('Error'); 
                    }?></td></tr>
            <tr><th>Amount of synced posts</th><td id="post-counter"> <?php echo $stats["PostCount"]; ?></td></tr>
            <?php if($stats["ErrorsCount"]!=0){ ?>  
                <tr><th>Errors count</th><td id="error-counter"> <?php echo $stats["ErrorsCount"]; ?></td></tr>
            <?php }?>
        </table>
        <a href="#" id="resync_button" style="margin-left: 10px" class="likebutton b_orange" onclick="Resync()">Resync</a>
    </div>
    
    
    <?php
        include("stat_block.php");
    }
    else
    {
        include( "invite.php" );
    }
?>

    
</div>