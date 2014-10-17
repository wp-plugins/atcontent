<?php
    require_once( "atcontent_userinit.php" );
    $ac_oneclick_repost = atcontent_get_user_settings_oneclick_repost( $userid );
    $ac_mainpage_repost = atcontent_get_user_settings_mainpage_repost( $userid );
    $ac_settings_tab_settings = get_user_meta( $userid, "ac_settings_tab_settings", true );
    $ac_use_vglink = get_user_meta( $userid, "ac_use_vglink", true );
    $ac_vglink_apikey = get_user_meta( $userid, "ac_vglink_apikey", true );    
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
