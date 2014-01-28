<?php
    $userid = intval( wp_get_current_user()->ID );
    $ref_url = "http://wordpress.org/plugins/atcontent/";
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    require( "include/atcontent_userinit.php" );
    $currentuser = wp_get_current_user();
    $userid = intval( $currentuser->ID );
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
    if ( strlen($ac_api_key) != 0 && strlen($ac_syncid) != 0 ) {   
    $ac_blogid = get_user_meta( $userid, "ac_blogid", true ); 
    $currentuser = wp_get_current_user();
    $userinfo = get_userdata($currentuser -> ID);
    $email = $userinfo -> user_email;
    $site = $_SERVER['HTTP_HOST'];
?>

<div style="width: 100%; height: 40px;"></div>
<div id="popup-bg" class="popup-bg" style="display: none"></div>


<script src="/wp-content/plugins/atcontent/assets/interface.js" type="text/javascript"></script>
<script>
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
        hide_welcome();
        gaSend('dashboard', 'get details clicked');
    }
</script>
<div style="position: absolute;right: 10px;top: 5px;">
    You are connected to AtContent as <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><img style="margin-right: 2px; vertical-align: middle" src="<?php echo $ac_avatar_20; ?>" alt="" width="18" height="18"><?php echo $ac_show_name; ?></a></br>
    <a href="#" style="float: right;font-size: 0.7em;" onclick="beforechangeaccount()">Not you?</a>
</div>
<div class="atcontent_wrap">
    <div id="settings_step" style="float: left; width: 450px;">    
        <?php include("settings.php"); ?>
    </div>
    
    <?php include("stat_block.php");
    $stats = atcontent_api_get_sync_stat($ac_syncid, $ac_blogid);
    ?>
    <div class="clear">
    <?php if ($_GET["step"] != "1"){ ?>
    <div class="b-dashboard-table b-dashboard-table-status" id="sync-process" style="width: 420px">
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
        }
    }
    else
    {
        include( "invite.php" );
    }
?>

    
</div>
 <script>
        function UpdateSyncStat()
        {
            jQuery.ajax({url: '<?php echo $ajax_form_action; ?>', 
                type: 'post', 
                data: {action: 'atcontent_get_sync_stat'},
                dataType: "json",
                success: function(d){    
                    jQuery("#post-counter").html(d.stats.PostCount);    
                    if (d.stats.IsSyncNow)   
                     {
                        jQuery("#sync-status").html('In process');    
                     }
                     else
                     {
                        if (d.stats.IsActive)
                        {
                            jQuery("#sync-status").html('Completed'); 
                        }
                        else
                        {
                            jQuery("#sync-status").html('Error');
                        }
                     }                
                },
                error: function(d, s, e) {
                }
            });   
            setTimeout('UpdateSyncStat()', 5000);
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
        
        var isFirstTime = false;
        UpdateSyncStat();
        <?php if ($_GET["step"] == "1"){ ?>
        isFirstTime = true;
        jQuery("#follow_steps_block").hide();
        jQuery("#popup-bg").show();      
        jQuery("#first_time_header").show();      
        jQuery("#settings_step").addClass('ac_welcome_show_visible');  
        jQuery("#tip_one_step").show();
        jQuery("#triangle_one").show();
        jQuery("#tip_one_step").addClass('ac_welcome_show_visible'); 
        function hide_welcome()
        {
            jQuery("#popup-bg").hide();   
            jQuery("#stat_text_step").removeClass('ac_welcome_show_visible');
            jQuery("#triangle_two").hide();
            jQuery("#tip_two_step").hide();
            jQuery("#first_time_header").hide();
        }

        function third_welcome_step()
        {          
            jQuery("#tip_one_step").hide();
            jQuery("#follow_steps_block").show();
            jQuery("#triangle_one").hide(); 
            jQuery("#tip_two_step").show();
            jQuery("#tip_two_step").addClass('ac_welcome_show_visible');
            jQuery("#triangle_two").show();
            jQuery("#settings_step").removeClass('ac_welcome_show_visible');   
            jQuery("#first_step_vis").removeClass('ac_welcome_show_visible');     
            jQuery("#stat_text_step").addClass('ac_welcome_show_visible');
        } 
        <?php } ?>
    </script>
