<?php
    require_once( "atcontent_userinit.php" );
    $posts_id = array();
    $stat_responce = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );

    $ac_oneclick_repost = atcontent_get_user_settings_oneclick_repost( $userid );
    $ac_mainpage_repost = atcontent_get_user_settings_mainpage_repost( $userid );

    $ac_settings_tab_guide = get_user_meta( $userid, "ac_settings_tab_guide", true );
    $ac_settings_tab_fourways = get_user_meta( $userid, "ac_settings_tab_fourways", true );
    $ac_settings_tab_settings = get_user_meta( $userid, "ac_settings_tab_settings", true );

    $guide_unread = " b-ac-acc__pane_unread";
    $fourways_unread = " b-ac-acc__pane_unread";
    $settings_unread = " b-ac-acc__pane_unread";
    $settings_opened = "open";
    $guide_opened = "";
    $fourways_opened = "";
    if ( $ac_settings_tab_settings != "1" ) {
        $guide_opened = "";
        $fourways_opened = "";
        $settings_opened = "open";
    } else {
        $guide_unread = "";
    }
    if ( $ac_settings_tab_fourways != "1" ) {
        $guide_opened = "";
        $fourways_opened = "open";
        $settings_opened = "";
    } else {
        $fourways_unread = "";
    }
    if ($ac_settings_tab_settings == "1") {
        $settings_unread = "";
    }
    if ( ( isset( $_GET["step"] ) && $_GET["step"] == "1" ) || $ac_settings_tab_guide != "1" ) {
        $guide_opened = "open";
        $fourways_opened = "";
        $settings_opened = "";
    }
?>

<div class="b-ac-page b-ac-page_incomplete" id="ac-page">
    <h1>AtContent</h1>
    <?php if ( isset( $_GET["step"] ) &&  $_GET["step"] == "1" ) { ?>
    <div class="update-nag">
        Congratulations, <?php echo $ac_show_name; ?>! Now you are connected to AtContent network of tens of thousands bloggers!
    </div>
    <?php } ?>

    <div class="b-ac-acc">
        
        <div class="b-ac-acc__pane<?php echo $guide_unread; ?>" <?php echo $guide_opened; ?> id="ac_tab_guide" data-id="guide">
            <h2 class="b-ac-acc__pane-title">
                <div class="b-ac-acc__pane-title-add">
                    <div class="b-ac-slider__nav" data-slider="slider">
                        <!--<button type="button" class="button button-secondary" data-role="prev">Prev</button>-->
                        <div class="b-ac-slider__nav-dots"></div>
                        <button type="button" class="button button-nav" data-role="next">Next</button>
                    </div>
                </div>
                A Short Guide
            </h2>
            
            <div class="b-ac-acc__pane-content">
                <div class="b-ac-slider" id="slider">
                    <div class="b-ac-slider__content">
                        <div class="b-ac-slider__pane">
                            <div class="b-ac-repost">
                                <div class="b-ac-repost__img"></div>
                                <div class="b-ac-repost__text">
                                    <h3>A new object at the bottom of your posts.</h3>
                                    <ul class="b-ac-list">
                                        <li>It allows to do reposts<span style="color: #62B551">*</span>.</li>
                                        <li>It indicates how popular your posts are.</li>
                                        <li>It looks nice with your posts. You can adjust the look in settings later.</li>
                                    </ul>
                                    <p>
                                        <span style="float: left;margin-left: -.8em;color: #62B551">*</span>A repost is when someone literally reposts your posts on other site or
                                        app in a couple clicks using AtContent. It dramatically increase your content's audience and helps to drive more traffic to your blog through links in reposts.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="b-ac-slider__pane">
                            <div class="b-ac-benefits">
                                <div class="b-ac-benefits__list">
                                    <h3>AtContent Benefits</h3>
                                    <ul class="b-ac-list">
                                        <li>
                                            It helps to build your audience and brings a whole host of lovely new people to visit your site through links in your reposted posts.
                                        </li>
                                        <li>
                                            It shows what of your content is shared by AtContent users and indicates which posts are most shareable and engaging.
                                        </li>
                                        <li class="b-ac-togglable-benefit b-ac-togglable-benefit_hidden">Author attribution follows the reposted post.</li>
                                        <li class="b-ac-togglable-benefit b-ac-togglable-benefit_hidden">If you update the post, it is updated on all sites.</li>
                                        <li class="b-ac-togglable-benefit b-ac-togglable-benefit_hidden">You see how many views your posts get on all sites.</li>
                                        <li class="b-ac-togglable-benefit b-ac-togglable-benefit_hidden">You see who reposts and how many times.</li>
                                        <li class="b-ac-togglable-benefit b-ac-togglable-benefit_hidden">Your posts can go viral as they can be reposted from any site and to any site.*</li>
                                        <li class="b-ac-togglable-benefit b-ac-togglable-benefit_hidden">All links in reposts have "nofollow" and "canonical" tags. Thus, it doesn't affect your SEO.</li>
                                    </ul>
                                    <a href="javascript:void(0);" class="b-ac-dashed" id="benefitsToggle" data-alt="Show the first two">Show all benefits</a>
                                </div>
                                <div class="b-ac-benefits__example">
                                    <h3>What Bloggers Say</h3>
                                    <div class="b-ac-user">
                                        <div class="b-ac-user__info">
                                            <div class="b-ac-user__photo">
                                                <img src="http://atcontent.blob.core.windows.net/avatar/ChMa6975/80-0.jpg" width="80" height="80" alt=""/>
                                            </div>
                                            <div class="b-ac-user__about">
                                                <span class="b-ac-user__name">Christine Macaulay</span><br />
                                                thefabuloustimes.com
                                                <div class="b-ac-user__metrics">
                                                    <small><b>Average monthly results:</b></small><br/>
                                                    <span class="b-ac-user__metrics-num">124</span> reposts on other blogs<br />
                                                    <span class="b-ac-user__metrics-num">5,857</span> views of her reposts
                                                </div>
                                            </div>
                                        </div>
                                        <div class="b-ac-user__testimonial">
                                            AtContent is super easy to use, helps to share your content, build your audience
                                            and brings a whole host of lovely new people to visit your site!
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="b-ac-slider__pane">
                        <div class="b-ac-following">
                            <h3>The Next Step:</h3>
                            <p>
                                <a href="http://atcontent.com/following-wp/" class="button button-nav button-hero" target="_blank" id="follow_bloggers_button">Follow bloggers with relevant content</a>
                            </p>
                            <p>
                                Bloggers whom you follow will follow you back and get your content recommended in their feeds for reposting.
                            </p>
                            <p>
                                The more reposts you get, the faster your audience and traffic grow.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="b-ac-acc__pane<?php echo $fourways_unread; ?>" <?php echo $fourways_opened; ?> data-id="fourways">
            <h2 class="b-ac-acc__pane-title">
                4 Ways to Get Maximum of AtContent
            </h2>

            <div class="b-ac-acc__pane-content">
                <div class="b-ac-features">
                    <div class="b-ac-features__col">
                        <div class="b-ac-features__col-img b-ac-features__col-img_chart"></div>
                        <h3 class="b-ac-features__title b-ac-features__title_chart">Follow Relevant Bloggers to Grow Your Audience and Drive More Traffic</h3>
                        <div class="b-ac-features__cta">
                            <a href="https://atcontent.com/following-wp/" target="_blank" class="button button-primary button-large">Follow Bloggers</a>
                        </div>
                        <p>
                            Bloggers who like your content will follow you back and get your content recommended in their feeds for reposting.
                        </p>
                        <p>
                            The more reposts you get, the faster your audience and traffic grow.
                        </p>
                    </div>
                    <div class="b-ac-features__col">
                        <div class="b-ac-features__col-img b-ac-features__col-img_book"></div>
                        <h3 class="b-ac-features__title b-ac-features__title_book">Repost Relevant Content and Increase Page Views on Your Blog</h3>
                        <div class="b-ac-features__cta">
                            <a href="admin.php?page=atcontent/repost.php" class="button button-primary button-large">Get Content</a>
                        </div>
                        <p>
                            Increase time readers spend on your blog by discovering and reposting relevant
                            stories from other bloggers. It’s free!
                        </p>
                        <p>You can find posts on “Content Feed” page in the menu to the left.</p>
                    </div>
                </div>
                <div class="b-ac-features">
                    <div class="b-ac-features__col">
                        <div class="b-ac-features__col-img b-ac-features__col-img_post"></div>
                        <h3 class="b-ac-features__title b-ac-features__title_post">Feature Your Posts to Speed Up Audience Reach and Traffic Growth</h3>
                        <div class="b-ac-features__cta">
                            <a href="https://atcontent.com/subscribe/?wp" target="_blank" class="button button-primary button-large">Become Featured</a>
                        </div>
                        <p>
                            Subscribe to one of our plans and feature your best posts. Be reposted on 10x of
                            blogs and increase your audience by 50% or more! Also, get traffic to your site
                            through links in your reposted posts.
                        </p>
                        <p>On average, one featured post is being reposted on 5 to 10 blogs.</p>
                    </div>
                    <div class="b-ac-features__col">
                        <div class="b-ac-features__col-img b-ac-features__col-img_wallet"></div>
                        <h3 class="b-ac-features__title b-ac-features__title_wallet">Skyrocket Your Promotion and Monetize Your Blog</h3>
                        <div class="b-ac-features__cta">
                            <a href="http://atcontent.com/landing/nativead/promote-blog-1/?wp&s=1" target="_blank" class="button button-primary button-large">Promote Post</a>
                            <span class="b-ac-features__cta-or">or</span>
                            <a href="http://atcontent.com/landing/nativeadforbloggers/?wp" target="_blank" class="button button-primary button-large">Monetize Blog</a>
                        </div>
                        <p>
                            Start a promotional campaign for your best posts and be reposted on 100x of relevant
                            blogs. Thus, you can promote your business, blog, or just your best posts.
                        </p>
                        <p>
                            Monetize your blog by reposting relevant sponsored posts. Get your blog approved and earn up to
                            $50 per 1,000 views!
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="b-ac-acc__pane<?php echo $settings_unread; ?>" <?php echo $settings_opened; ?> data-id="settings">
            <h2 class="b-ac-acc__pane-title">
                Settings &amp; Administration
            </h2>

            <div class="b-ac-acc__pane-content">
                <h3>Settings</h3>
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
                                <br />
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
                    
                    <p>
                        <button id="b-save-settings" type="button" class="button button-primary">Save Settings</button>
                        <span class="b-ac-settings__note b-ac-settings__note_aside" id="save-settings-success" style="display: none;">
                            Congtats, settings saved!
                        </span>
                    </p>
                    
                </div>
                </form>
                <?php if ( current_user_can( 'manage_options' ) ) {?>
                <div class="b-ac-settings-section">
                    <h3>Administration</h3>
                    <form id="f-invite">
                    <input type="hidden" name="action" value="atcontent_send_invites">
                    <p>
                        Invite all authors of your blog to connect their profiles with AtContent.<br>
                        Thus your blog posts will get more reposts, reach a wider audience and drive more traffic!
                    </p>
                    <p>
                        <button id="b-invite" type="button" class="button button-primary">Invite all authors</button>
                        <span class="b-ac-settings__note b-ac-settings__note_aside" id="invite-success" style="display: none;">
                            Congtats, the invitation sent. We have sent its copy to <?php echo wp_get_current_user()->user_email; ?> for you to check it.
                        </span>
                    </p>
                    
                    </form>
                </div>
                <?php } ?>

                <div class="b-ac-settings-section">
                    <h3>You are connected to AtContent as</h3>
                    <p>
                        <div class="b-ac-user">
                            <div class="b-ac-user__info">
                                <div class="b-ac-user__photo">
                                    <a href="http://atcontent.com/profile/<?php echo $ac_pen_name; ?>/" target="_blank"><img src="<?php echo $ac_avatar_80; ?>" width="80" height="80" alt=""/></a>
                                </div>
                                <div class="b-ac-user__about">
                                    <span class="b-ac-user__name"><?php echo $ac_show_name; ?></span>
                                    <br />
                                    <a href="#" id="b-change-account">change account</a>
                                </div>
                            </div>
                        </div>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    ac_ga_s( 'settingsTab', 'view' );
    (function ($) {
    'use strict';
    
    $(function () {
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

        $('#b-invite').on('click', function(e){
            e.preventDefault();
            if ($("#invite-loader").length == 0) {
                ac_ga_s( 'settingsTab', 'invite' );
                $('#b-invite').after('<span id="invite-loader" class="spinner"></span>');
                $("#invite-success").hide();
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', $("#f-invite").serialize() , function(r) {
                    $("#invite-loader").remove();
                    $("#invite-success").show().delay(6000).fadeOut(300);
                });
            }
        });

        $('#b-change-account').on('click', function(e){
            e.preventDefault();
            ac_ga_s( 'settingsTab', 'disconnectQuestion' );
            if (confirm("Are you sure you want to change AtContent profile?")) {
                ac_ga_s( 'settingsTab', 'disconnect' );
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
        });
    });
})(jQuery);
</script>
