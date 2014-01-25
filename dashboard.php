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
    $currentuser = wp_get_current_user();
    $userinfo = get_userdata($currentuser -> ID);
    $email = $userinfo -> user_email;
    $site = $_SERVER['HTTP_HOST'];
?>

<div style="width: 100%; height: 40px;"></div>
<div id="popup-bg" class="popup-bg" style="display: none"></div>


<script src="/wp-content/plugins/atcontent/interface.js" type="text/javascript"></script>
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
</script>
<div style="position: absolute;right: 10px;top: 5px;">
    You are connected to AtContent as <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><img style="margin-right: 2px" src="<?php echo $ac_avatar_20; ?>" alt="" width="16" height="16"><?php echo $ac_show_name; ?></a></br>
    <a href="#" style="float: right;font-size: 0.7em;" onclick="beforechangeaccount()">Not you?</a>
</div>
<div class="atcontent_wrap">
    <div id="settings_step" style="float: left;">        
        <div id="first_time_header" style="display: none">
            <h2 style="font-size: 20px;">Congratulations you are just connected your blog to AtContent! </h2>
        </div>
        <?php include("settings.php"); ?>
    </div>
    
    <?php include("stat_block.php");
    ?>
    <div class="clear">
    <a href="#" id="show_sync_link" onclick="show_sync_stat()">Show latest sync stat</a>    
    <div id="sync_stat_block" style="display: none">
        <div id="sync_message"></div>
        <a href="#" class="likebutton b_orange" onclick="Resync()">Resync</a>
    </div>
    <?php
    }
    else
    {
        include( "invite.php" );
    }
?>

    
</div>
 <script>
        function show_sync_stat()
        {
            jQuery("#sync_stat_block").show();
        }

         function Resync()
         {
            jQuery.ajax({url: '<?php echo $ajax_form_action; ?>', 
                type: 'post', 
                data: {action: 'atcontent_syncqueue'},
                dataType: "json",
                success: function(d){         
                    jQuery("#sync_message").show();                       
                    jQuery("#sync_message").html('Resync started');                    
                    setTimeout('jQuery("#sync_message").hide()', 5000); 
                },
                error: function(d, s, e) {
                }
            });    
         }
        
        var isFirstTime = false;
        <?php if ($_GET["step"] == "1"){ ?>
        isFirstTime = true;
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
            jQuery("#triangle_one").hide(); 
            jQuery("#tip_two_step").show();
            jQuery("#tip_two_step").addClass('ac_welcome_show_visible').addClass('inline-block');
            jQuery("#triangle_two").show();
            jQuery("#settings_step").removeClass('ac_welcome_show_visible');   
            jQuery("#first_step_vis").removeClass('ac_welcome_show_visible');     
            jQuery("#stat_text_step").addClass('ac_welcome_show_visible');
        } 
        <?php } ?>
    </script>
