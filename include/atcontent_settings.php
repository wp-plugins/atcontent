<?php
    require_once( "atcontent_userinit.php" );
    $ac_oneclick_repost = atcontent_get_user_settings_oneclick_repost( $userid );
    $ac_mainpage_repost = atcontent_get_user_settings_mainpage_repost( $userid );
    $ac_settings_tab_settings = get_user_meta( $userid, "ac_settings_tab_settings", true );
    $ac_use_vglink = get_user_meta( $userid, "ac_use_vglink", true );
    $ac_vglink_apikey = get_user_meta( $userid, "ac_vglink_apikey", true );
    $ac_is_envato_user = get_user_meta( $userid, "ac_is_envato_version", true );
?>

<div class="b-ac-page b-ac-page_incomplete" id="ac-page">
    <?php if ( isset( $_GET["step"] ) &&  $_GET["step"] == "1" ) { ?>    
   
    <iframe src="http://atcontent.com/service/codechecker.ashx" style="display:none;"></iframe>
    
    <div class="b-ac-acc">
        <div class="b-ac-acc__pane b-ac-acc__pane_open">
            
            <div class="b-ac-acc__pane-content">
                <div class="b-ac-settings-section">
                     <div class="update-nag">
        Congratulations, <?php echo $ac_show_name; ?>! Now you are connected to AtContent network of tens of thousands bloggers!
    </div>
                    <div class="b-ac-following">
                        <p>
                            <a href="http://atcontent.com/following-wp/" class="button button-nav button-hero" target="_blank" id="follow_bloggers_button">Discover and Follow Bloggers</a>
                        </p>
                        <p>
                            Discover and follow bloggers by relevant tags to create your AtContent Feed for reposting.
                        </p>
                        <p>
                            Bloggers will follow you back and repost your content, growing your audience and traffic!
                        </p>
                    </div>
                </div>
                <div style="text-align:right;"><a style="color:#767676;" href="admin.php?page=atcontent&step=2">Skip it</a></div>
            </div>
        </div>
        
    </div>
    
    <?php } else if ( isset( $_GET["step"] ) &&  $_GET["step"] == "2" ) { ?>
    
    <div class="b-ac-acc">
        <div class="b-ac-acc__pane b-ac-acc__pane_open">
            <div class="b-ac-acc__pane-content">
                <div class="b-ac-settings-section">
                    <br><br>
                    <div class="b-ac-following">
                        <p>
                            <a href="http://atcontent.com/profile/" class="button button-nav button-hero" target="_blank" id="follow_bloggers_button">Complete Your AtContent Profile</a>
                        </p>
                        <p>
                            Complete your AtContent profile, so other bloggers can find and repost your content, </p><p>growing your audience and traffic.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php } else if ( $ac_is_envato_user == "1"  && isset( $_GET["step"] ) &&  $_GET["step"] == "envatoUser" ) { ?>
    
    <div class="b-ac-acc">
        <div class="b-ac-acc__pane b-ac-acc__pane_open">
            <div class="b-ac-acc__pane-content">
                <div class="b-ac-settings-section">
                    <br><br>
                    <div class="b-ac-following">                        
                        <p>
                            Dear Envato Market user,<br> Please enter purchase code to get premium bonuses!
                        </p>
                        <form id="f-envato_purchase">                            
                            <input type="hidden" name="action" value="atcontent_set_envato_purchase" />
                            <input type="hidden" name="ac_envato_purchase_id" id="ac_envato_purchase_id" value="" />
                            <input type="text" placeholder="Purchase Code" id="ac_envato_purchase_id_show" />
                        </form>
                        <p>
                            <button onclick="sendPuchaseId()" class="button button-nav button-hero" id="b-send-purchase_id" >Activate Envato Purchase</button>
                        </p>
                        <div id="b-ac-user__signuperror" class="f-settings_envato_error error" style="display:none" ></div>
                        <span class="update-nag" id="envato-settings-success" style="display: none;">
                            Congrats, from now you are a premium user!
                        </span>
                        <div id="envato-settings-next" style="margin-top: 10px; display: none;">
                            <a href="admin.php?page=atcontent&step=1" class="button button-nav button-hero" id="b-send-purchase_id" >Next Step</a>
                        </div>
                        <p><small>If you don't know your purchase code, you can find it in the mailbox linked with Envato Market.</small></p>
                    </div>
                </div>
                <div id="envato-settings-skipit_link" style="text-align:right;"><a style="color:#767676;" href="admin.php?page=atcontent&step=1">Skip it</a></div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var sending = false;
        function sendPuchaseId()
        {      
            if (sending)
            {
                return;
            } else{
                sending = true;
                jQuery("#ac_envato_purchase_id_show").attr("disabled", "");
                jQuery("#b-ac-user__signuperror").hide();
                jQuery("#ac_envato_purchase_id").val(jQuery("#ac_envato_purchase_id_show").val());
                jQuery('#b-send-purchase_id').after('<span id="save-settings-loader" class="spinner fixed_spinner" ></span>');
                jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', jQuery("#f-envato_purchase").serialize() , function(r) {
                    jQuery("#save-settings-loader").remove();
                    r = JSON.parse(r);
                    if (r.IsOK){
                        jQuery("#envato-settings-success").show();
                        jQuery("#envato-settings-next").show();
                        jQuery("#b-send-purchase_id").hide();
                        jQuery("#envato-settings-skipit_link").hide();
                    }
                    else {        
                        sending = false;
                        jQuery("#ac_envato_purchase_id_show").attr("disabled", null);
                        jQuery('#b-ac-user__signuperror').html('<p>' + r.Error + '</p>');
                        jQuery("#b-ac-user__signuperror").show().delay(5000).fadeOut(300);
                    }
                });
            }
        }
    </script>
    
    <?php } else { ?>
    <h1>AtContent Settings</h1>
    <div class="b-ac-acc">

        <div class="b-ac-acc__pane b-ac-acc__pane_open" data-id="settings">

            <div class="b-ac-acc__pane-content">
                <br><br>
                <form id="f-settings">
                <input type="hidden" name="action" value="atcontent_save_settings">
                <div class="b-ac-settings-section">

                    <table class="b-ac-settings">
                        <tr>
                            <td class="b-ac-settings__controls-col">
                                <h4 class="b-ac-settings__hdl">Show repost button on the home page</h4>
                                <label>
                                    <input type="checkbox" name="ac_mainpage_repost" value="1" <?php echo $ac_mainpage_repost == "1" ? "checked=\"\"" : ""; ?> />
                                    Yes please
                                </label>
                            </td>
                            <td class="b-ac-settings__note-col">
                                <div class="b-ac-settings__note b-ac-settings__note_aside">
                                    <p> Show 
<span class="ac-rpst-b">
    <span class="ac-rpst-b__b">
        <span class="ac-rpst-b__l"></span>
        <span class="ac-rpst-b__t">Repost</span>
    </span>
    <span class="ac-rpst-b__c">27</span>
</span> button under each post on the home page. It increases your chances to be reposted by bloggers who come directly to your blog. The number of reposts shows how popular your posts are.</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="b-ac-settings__controls-col">
                                <h4 class="b-ac-settings__hdl">One-click repost function</h4>
                                <label>
                                    <input type="radio" name="ac_oneclick_repost" value="1" <?php echo $ac_oneclick_repost == "1" ? "checked=\"\"" : ""; ?> />
                                    Publish immediately
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="ac_oneclick_repost" value="0" <?php echo $ac_oneclick_repost == "0" ? "checked=\"\"" : ""; ?> />
                                    Save as a draft
                                </label>
                            </td>
                            <td class="b-ac-settings__note-col">
                                <div class="b-ac-settings__note b-ac-settings__note_aside">
                                    <p>One-click repost function allows you repost any post from “Content&nbsp;Feed” and “Monetize&nbsp;Blog” tabs or from any site where you see 
<span class="ac-rpst-b">
    <span class="ac-rpst-b__b">
        <span class="ac-rpst-b__l"></span>
        <span class="ac-rpst-b__t">Repost</span>
    </span>
    <span class="ac-rpst-b__c">27</span>
</span> at the bottom of the post, including AtContent.com.</p>
                                    <p>
                                        <b>Publish immediately</b> means repost will appear on your blog at once.
                                    </p>
                                    <p>
                                        <b>Save as a draft</b> means repost will appear as a draft on your blog. Thus, you can adjust excerpt, featured image, title, tags and other settings before publishing it on your blog.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <h4 class="b-ac-settings__hdl">VigLink settings</h4>
                            <label>
                                <input id="f-settings_vglink_checkbox" type="checkbox" name="ac_use_vglink" <?php echo $ac_use_vglink == "1" ? "checked=\"\"" : ""; ?> />
                                Use VigLink in reposts
                            </label>
                            <br /><br />
                            <label>
                                <input <?php echo $ac_use_vglink != "1"?"style=\"display: none\"":""; ?> id="f-settings_vglink_textbox" placeholder="Type VigLink API key here" type="text" name="ac_vglink_apikey" value="<?php echo $ac_vglink_apikey ?>" />
                            </label>
                    <p>
                        <button id="b-save-settings" type="button" class="button button-primary">Save Settings</button>
                        <span class="b-ac-settings__note b-ac-settings__note_aside" id="save-settings-success" style="display: none;">
                            Congtats, settings saved!
                        </span>
                    </p>
					
                </div>
                </form>
                <?php if( $ac_is_envato_user == "1" ){
                    ?> 
						<div class="d-settings_envato_block"><h4 class="b-ac-settings__hdl">Envato Premium Bonus</h4> 
					<?php
                    $last_purchase_id = get_user_meta($userid, "ac_envato_purchase_id", TRUE);
                    $ac_envato_activate_date = get_user_meta($userid, "ac_envato_activate_date", TRUE);
                    $is_ok = get_user_meta($userid, "ac_envato_is_ok", TRUE);
                    if ($is_ok["IsOK"]){ ?>
                        <p style="margin: 0px 0px 30px;">
                             Activated with purchase code <?php echo($last_purchase_id); ?> on <?php echo($ac_envato_activate_date -> format('m.d.Y')); ?>.
                        </p>
                    <?php } else{ ?>
                        <p>
                             Enter your purchase code here and get premium bonuses!
                        </p>
                        <form id="f-envato_purchase">                            
                            <input type="hidden" name="action" value="atcontent_set_envato_purchase" />
                            <input type="hidden" name="ac_envato_purchase_id" id="ac_envato_purchase_id" value="" />
                            <input type="text" placeholder="Purchase Code" id="ac_envato_purchase_id_show" value="<?php echo($last_purchase_id); ?>" />
                        </form>
                        <p>
                            <button onclick="sendPuchaseId()" class="button button-nav button-hero" id="b-send-purchase_id" >Activate Envato Purchase</button>
                        </p>
                        <div id="b-ac-user__signuperror" class="d-settings_envato_error error" style="display:none" ></div>
                        <span class="update-nag" id="save-envato-success" style="display: none;">
                            Congrats, from now you are a premium user.
                        </span>
                        <p><small>If you don't know your purchase code, you can find it in the mailbox linked with Envato Market.</small></p>
                        <script type="text/javascript">
                            var sending = false;
                            function sendPuchaseId()
                            {
                                if (sending)
                                {
                                    return;
                                } else{
                                    sending = true;
                                    jQuery("#ac_envato_purchase_id_show").attr("disabled", "");
                                    jQuery("#b-ac-user__signuperror").hide();
                                    jQuery("#ac_envato_purchase_id").val(jQuery("#ac_envato_purchase_id_show").val());
                                    jQuery('#b-send-purchase_id').after('<span id="save-settings-loader" class="spinner fixed_spinner"></span>');
                                    jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', jQuery("#f-envato_purchase").serialize() , function(r) {
                                        jQuery("#save-settings-loader").remove();
                                        r = JSON.parse(r);
                                        if (r.IsOK){
                                            jQuery("#save-envato-success").show();
                                            jQuery("#b-send-purchase_id").hide();
                                            location.reload();
                                        }
                                        else {
                                            sending = false;
                                            jQuery("#ac_envato_purchase_id_show").attr("disabled", null);
                                            jQuery('#b-ac-user__signuperror').html('<p>' + r.Error + '</p>');
                                            jQuery("#b-ac-user__signuperror").show().delay(5000).fadeOut(300);
                                        }
                                    });
                                }
                            }
                        </script>
                    <?php } ?>
                </div>  
                <?php } ?>
                <div class="b-ac-settings-section">
				
                    <h3>You are connected to AtContent as</h3>
                    <div class="b-ac-user">
                        <div class="b-ac-user__info">
                            <div class="b-ac-user__photo">
                                <a href="http://atcontent.com/profile/<?php echo $ac_pen_name; ?>/" target="_blank"><img src="<?php echo $ac_avatar_80; ?>" width="80" height="80" alt=""/></a>
                            </div>
                            <div class="b-ac-user__about">
                                <span class="b-ac-user__name"><?php echo $ac_show_name; ?></span>
                            </div>
                        </div>
                    </div>                    
					
                    <p>
                        <button id="b-ac__renewinfo" type="button" class="button ">Update</button>
                        <span class="b-ac-settings__note b-ac-settings__note_aside" id="b-ac__renewsuccess" style="display: none;">
                            Congrats, AtContent profile was updated on your blog.
                        </span>
                    </p>
                    
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<script type="text/javascript">
    ac_ga_s( 'settingsTab', 'view' );
    (function ($) {
    'use strict';
    
    $(function () {
        
        $('#follow_bloggers_button').on('click', function(e){
            window.location = 'admin.php?page=atcontent';
        });
        
        $('#b-save-settings').on('click', function(e){
            e.preventDefault();
            if ($("#save-settings-loader").length == 0) {
                ac_ga_s( 'settingsTab', 'save' );
                $('#b-save-settings').after('<span id="save-settings-loader" class="spinner"></span>');
                $("#save-settings-success").hide();
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $("#f-settings").serialize() , function(r) {
                    $("#save-settings-loader").remove();
                    $("#save-settings-success").show().delay(3000).fadeOut(300);
                });
            }
        });        
    
        $('#f-settings_vglink_checkbox').on('click', function(e)
        {
            if ($('#f-settings_vglink_checkbox').is(':checked'))
            {
                $('#f-settings_vglink_textbox').show();
            }
            else 
            {        
                $('#f-settings_vglink_textbox').hide();
            }
        });
        
        $('#b-ac__renewinfo').on('click', function(e){
            ac_ga_s( 'settingsTab', 'renewinfo' );
            $('#b-ac__renewinfo').after('<span id="renewinfo-loader" class="spinner"></span>');
            $.post('admin-ajax.php', {'action' : 'atcontent_renewinfo'}, function(ee){
                $("#renewinfo-loader").remove();
                $("#b-ac__renewsuccess").show().delay(3000).fadeOut(300);
            }, 'json');
        });
        
    });
})(jQuery);
</script>
