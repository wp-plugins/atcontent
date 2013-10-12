<?php
    /*
    Plugin Name: AtContent
    Plugin URI: http://atcontent.com/
    Description: Why 3,500 Sites Have Chosen AtContent? Because it’s the easiest way to Reach new readership & Increase search ranking!
    Version: 4.3.1
    Author: AtContent, IFFace, Inc.
    Author URI: http://atcontent.com/
    */

    define( 'AC_VERSION', "4.3.1.89" );
    define( 'AC_NO_PROCESS_EXCERPT_DEFAULT', "1" );

    require_once( "atcontent_api.php" );
    require_once( "atcontent_pingback.php" );
    require_once( "atcontent_ajax.php" );
    require_once( "atcontent_dashboard.php" );
    require_once( "atcontent_lists.php" );
    require_once( "atcontent_post.php" );
    require_once( "atcontent_shortcodes.php" );
    add_action( 'admin_init', 'atcontent_admin_init' );
    add_action( 'admin_menu', 'atcontent_add_tools_menu' );
    add_filter( 'the_content', 'atcontent_the_content', 1 );
    add_filter( 'the_content', 'atcontent_the_content_after', 100);
    add_filter( 'the_excerpt', 'atcontent_the_content_after', 100);
    add_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
    add_action( 'save_post', 'atcontent_save_post' );
    add_action( 'publish_post', 'atcontent_publish_publication' );
    add_action( 'comment_post', 'atcontent_comment_post' );
    add_action( 'deleted_comment', 'atcontent_comment_post' );
    add_action( 'trashed_comment', 'atcontent_comment_post' );
    add_action( 'add_meta_boxes', 'atcontent_add_meta_boxes' );
    add_action( 'wp_ajax_atcontent_import', 'atcontent_import_handler' );
    add_action( 'wp_ajax_atcontent_api_key', 'atcontent_api_key' );
    add_action( 'wp_ajax_atcontent_pingback', 'atcontent_pingback' );
    add_action( 'wp_ajax_atcontent_readership', 'atcontent_readership' );
    add_action( 'wp_ajax_nopriv_atcontent_guestpost', 'atcontent_ajax_guestpost' );
    add_action( 'wp_ajax_atcontent_guestpost', 'atcontent_ajax_guestpost' );
    add_action( 'wp_ajax_atcontent_guestpost_check_url', 'atcontent_ajax_guestpost_check_url' );
    add_action( 'admin_head', 'atcontent_admin_head' );
    add_filter( 'manage_posts_columns', 'atcontent_column_head' );
    add_action( 'manage_posts_custom_column', 'atcontent_column_content', 10, 2 );
    add_action( 'wp_dashboard_setup', 'atcontent_add_dashboard_widgets' );


    register_activation_hook( __FILE__, 'atcontent_activate' );
    register_deactivation_hook( __FILE__, 'atcontent_deactivate' );
    register_uninstall_hook( __FILE__, 'atcontent_uninstall' );

    function atcontent_admin_init(){
         wp_register_style( 'atcontentAdminStylesheet', plugins_url( 'assets/atcontent.css?v=3', __FILE__ ) );
         wp_enqueue_style( 'atcontentAdminStylesheet' );
         wp_enqueue_style( 'wp-pointer' );
         wp_enqueue_script( 'wp-pointer' );
    }

    function atcontent_add_tools_menu() {
        add_utility_page( 'AtContent', 'AtContent', 'publish_posts', 'atcontent/settings.php', '',
            plugins_url( 'assets/logo.png', __FILE__ ) );
        
        add_menu_page( 'Guest Posts', 'Guest Posts', 'publish_posts', 'atcontent/guestpost.php', '', 
            plugins_url( 'assets/logo.png', __FILE__ ), 6 );
        add_submenu_page( 'atcontent/settings.php', 'Guest Posts', 'Guest Posts', 'publish_posts', 'atcontent/guestpost.php',  '');

        add_submenu_page( 'atcontent/settings.php', 'Connect Settings', 'Connection', 'publish_posts', 'atcontent/connect.php',  '');
        add_submenu_page( 'atcontent/settings.php', 'Statistics', 'Statistics', 'publish_posts', 'atcontent/statistics.php',  '');
        add_submenu_page( 'atcontent/settings.php', 'Credits', 'Credits', 'publish_posts', 'atcontent/quotas.php',  '');
        
        add_menu_page( 'Reposting', 'Reposting', 'publish_posts', 'atcontent/repost.php', '', 
            plugins_url( 'assets/logo.png', __FILE__ ), 7 );
        add_submenu_page( 'atcontent/settings.php', 'Content for reposting', 'Content for reposting', 'publish_posts', 'atcontent/repost.php',  '');
        add_submenu_page( 'atcontent/settings.php', 'Geek Page', 'Geek Page', 'publish_posts', 'atcontent/knownissues.php',  '');
        add_action( 'admin_print_styles', 'atcontent_admin_styles' );
        add_action( 'admin_print_footer_scripts', 'atcontent_footer_scripts' );
    }

    function atcontent_admin_styles(){
        wp_enqueue_style( 'atcontentAdminStylesheet' );
    }

    function atcontent_the_content( $content = '' ) {
    
        global $post, $wp_current_filter, $currentNumPost_ac;

        (!$currentNumPost_ac ? $currentNumPost_ac = 1 : $currentNumPost_ac++);

        if ( in_array( 'the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }
        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
		    return $content;
	    }
        $ac_excerpt_no_process = get_user_meta( intval( $post->post_author ), "ac_excerpt_no_process", true );
        if ( strlen( $ac_excerpt_no_process ) == 0 ) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
        if ( !is_single() && $ac_excerpt_no_process == "1" ) return $content;
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
        $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
        $ac_adtest = get_user_meta( intval( $post->post_author ), "ac_adtest", true );
        $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
        $ac_additional_classes = "";
        $isSinlgePost = is_single();
        if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
        if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
        if ( is_string ( $ac_pen_name ) && strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";


        $ac_adtest_messages = array();
        $ac_adtest_messages[] = "<h3>How online retailers solve the problem of reaching new buyers in a new way.</h3><p>Hi folks.<br/>My friend is an online retailer and he says that one of the largest problem that retailers face is the lack of methods to reach new buyers. He checked out a couple services and found Happy Retailer.  <br/>I think this is a very interesting idea to allow retailers upload inventory in one place and sell them simultaneously on Amazon, EBay, Bonanza and more than 1001 websites! <br />If you a retailer it might be interesting for you. <br/>You can get more details here <a href=\"http://happyretailer.com/?from=atcontent\" target=_blank>http://happyretailer.com</a></p>";

		$ac_adtest_messages[] = "<h3>New types of services that make this world a better place.</h3><p>I recently learned of a new type of services. They allow to donate money to charity and in return you can get cool experiences, such as flights on a private plane, meetings with interesting personalities, ride in MR2 Spyder, advises & mentorships, try international cuisines, etc.</p><p>One of the services is SunnyKarma.com. They are based out of San Francisco & are having a huge fundraising networking mixer/party on July 17th where they will be raffling off a flight on a private plane, you can get more details here: <a href=\"http://bit.ly/169mChq\" target=_blank>http://sunnykarma.com</a></p>";

        //print_r($ac_adtest_messages);

        shuffle($ac_adtest_messages);

        //$ac_adtest_message_randkeys = array_rand($ac_adtest_messages, 1);

        //print_r($ac_adtest_message_randkeys);

        //$ac_adtest_message = $ac_adtest_messages[$ac_adtest_message_randkeys[0]];
        $ac_adtest_message = $ac_adtest_messages[0];

        //print_r($ac_adtest_message);

        $ac_adtest_numOfmsgApears = 2;

        if ( $ac_is_process == "1" && is_string ( $ac_postid ) && strlen( $ac_postid ) > 0 ) {
            $code = <<<END
<div class="atcontent_widget{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --></div>
END;

            if ( $isSinlgePost ) {
                $code = <<<END
<div class="atcontent_widget{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body"></script></div>
END;
            }

            /*
            if ($ac_adtest == "1" && ($isSinlgePost || $currentNumPost_ac == $ac_adtest_numOfmsgApears)) {
				$code .= $ac_adtest_message;
            }
            //*/

            $code = str_replace( PHP_EOL, " ", $code );
            $inline_style = "";
            preg_match_all( '@<style[^>]*?>.*?</style>@siu', do_shortcode( $content ), $style_matches );
            foreach ( $style_matches[0] as $style_item ) {
                $inline_style .= $style_item;
            }
            return $inline_style . $code;
        }

        return $content.
			(($ac_adtest == "1" && ($isSinlgePost || $currentNumPost_ac == $ac_adtest_numOfmsgApears))
				?$ac_adtest_message
				:"");
    }

    function atcontent_the_excerpt( $content = '' ) {
        global $post, $wp_current_filter;
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        $ac_excerpt_image_remove = get_user_meta( intval( $post->post_author ), "ac_excerpt_image_remove", true );
        if ( strlen( $ac_excerpt_image_remove ) == 0 ) $ac_excerpt_image_remove = "0";
        $ac_excerpt_no_process = get_user_meta( intval( $post->post_author ), "ac_excerpt_no_process", true );
        if ( strlen( $ac_excerpt_no_process ) == 0 ) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
        if ( $ac_excerpt_no_process == "1" ) return $content;
        if ( $ac_is_process == "1" && strlen( $ac_postid ) > 0 && $ac_excerpt_no_process == "0" ) {
            $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
            $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
            $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
            $ac_additional_classes = "";
            if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
            if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
            $ac_excerpt_class = "atcontent_excerpt";
            if ( $ac_excerpt_image_remove == "1" ) $ac_excerpt_class = "atcontent_excerpt_no_image";
            $code = <<<END
<div class="{$ac_excerpt_class}{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --></div>
END;
            $code = str_replace( PHP_EOL, " ", $code );
            $inline_style = "";
            preg_match_all( '@<style[^>]*?>.*?</style>@siu', do_shortcode( $content ), $style_matches );
            foreach ( $style_matches[0] as $style_item ) {
                $inline_style .= $style_item;
            }
            return $inline_style . $code;
        }
        return $content;
    }

    function atcontent_the_content_after( $content = '' ) {
        global $post, $wp_current_filter;
        if ( in_array( 'the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }
        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
		    return $content;
	    }
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
        $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
        $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
        $ac_additional_classes = "";
        if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
        if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
        if ( !is_string( $ac_pen_name ) || strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        if ( $ac_is_process == "1" && strlen( $ac_postid ) > 0) {
             //Chameleon theme thumb fix
            if (function_exists( 'get_thumbnail' ) && get_option( 'chameleon_thumbnails' ) == 'on' ) {
                $ac_script_init .= <<<END
(function($) {
$(".CPlase_face").prepend($(".post-thumbnail").clone());
$(".post-thumbnail:first").remove();
})(jQuery)
END;
            }
            //Chameleon theme thumb fix end

            //RefTagger
            if ( function_exists ( 'lbsFooter' ) ) {
                $ac_script_init .= <<<END
try { Logos.ReferenceTagging.tag(); } catch (ex) {}
END;
            }
            //End RefTagger

            //FancyBox for WordPress
            if ( defined( 'FBFW_VERSION' ) ) {
                $ac_script_init .= <<<END
jQuery(function(){

jQuery.fn.getTitle = function() { // Copy the title of every IMG tag and add it to its parent A so that fancybox can show titles
	var arr = jQuery("a.fancybox");
	jQuery.each(arr, function() {
		var title = jQuery(this).children("img").attr("title");
		jQuery(this).attr('title',title);
	})
}

// Supported file extensions
var thumbnails = jQuery("a:has(img)").not(".nolightbox").filter( function() { return /\.(jpe?g|png|gif|bmp)$/i.test(jQuery(this).attr('href')) });

thumbnails.addClass("fancybox").attr("rel","fancybox").getTitle();
jQuery("a.fancybox").fancybox({
	'cyclic': false,
	'autoScale': true,
	'padding': 10,
	'opacity': true,
	'speedIn': 500,
	'speedOut': 500,
	'changeSpeed': 300,
	'overlayShow': true,
	'overlayOpacity': "0.3",
	'overlayColor': "#666666",
	'titleShow': true,
	'titlePosition': 'inside',
	'enableEscapeButton': true,
	'showCloseButton': true,
	'showNavArrows': true,
	'hideOnOverlayClick': true,
	'hideOnContentClick': false,
	'width': 560,
	'height': 340,
	'transitionIn': "fade",
	'transitionOut': "fade",
	'centerOnScroll': true,
});

});
END;
            }

            //End FancyBox for WordPress


            //NextGEN Gallery
            if ( is_array( get_option( 'ngg_options' ) ) ) {
            $ac_script_init .= <<<END
jQuery(".ngg-slideshow").each(function(){
    var i = jQuery(this), id = i.attr("id"), galid = parseInt(id.substr(id.lastIndexOf("-") + 1));
    jQuery("#" + id).nggSlideshow( {id: galid,fx:"fade",width:320,height:240,domain: "http://" + document.location.host + "/",timeout:10000});
});
END;
            }
            //End NextGEN Gallery

            //eBible
            if ( function_exists('includeJSHeader') ) {
                $ac_script_init .= <<<END
CPlase.l('http://www.ebible.com/assets/verselink/ebible.verselink.js');
END;
            }
            //End eBible

            //Lightbox Plus ColorBox
            if (class_exists('wp_lightboxplus')) {
                global $wp_lightboxplus;
                if ( ob_start() ) {
                    $wp_lightboxplus->lightboxPlusColorbox();
                    $lbp_script = ob_get_contents();
                    ob_end_clean();
                    if (preg_match_all("/<script[^>]+>([^<]*)<\/script>/mi", $lbp_script, $output_array)) {
                        $ac_script_init .= $output_array[1][0];
                    }
                }
            }
            //End Lightbox Plus ColorBox

            if ( strlen( $ac_script_init ) > 0 ) {
                $content .= <<<END
<script type="text/javascript">
CPlase = window.CPlase || {};
CPlase.evt = CPlase.evt || [];
CPlase.evt.push(function (event, p, w) {
    {$ac_script_init}
});
</script>
END;
            }
        }
        return $content;
    }

    function atcontent_add_meta_boxes() {
         add_meta_box(
            'atcontent_sectionid',
            __( 'AtContent Post Settings', 'atcontent_textdomain' ),
            'atcontent_inner_custom_box',
            'post'
        );

        $version = get_bloginfo('version');
        if ( version_compare( $version, '3.3', '>=' ) ) {
            add_meta_box(
                'atcontent_secondeditor',
                __( 'AtContent Paid Portion', 'atcontent_textdomain' ),
                'atcontent_paid_portion',
                'post'
            );
        }


    }

    function atcontent_inner_custom_box( $post ) {
          // Use nonce for verification
          wp_nonce_field( plugin_basename( __FILE__ ), 'atcontent_noncename' );
          $userid = wp_get_current_user()->ID;

          $ac_api_key = get_user_meta( $userid, "ac_api_key", true );

          $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
          $ac_is_process_checked = "";
          if ( $ac_is_process == "1" || $ac_is_process == "" ) {
              $ac_is_process_checked = "checked=\"checked\"";
          }

          $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
          $ac_user_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
          if ( strlen( $ac_user_copyprotect ) == 0 ) $ac_user_copyprotect = "1";
          $ac_user_paidrepost = get_user_meta( $userid, "ac_paidrepost", true );
          if ( strlen( $ac_user_paidrepost ) == 0 ) $ac_user_paidrepost = "0";
          $ac_user_paidrepostcost = get_user_meta( $userid, "ac_paidrepostcost", true );
          if ( strlen( $ac_user_paidrepostcost ) == 0 ) $ac_user_paidrepostcost = "2.50";
          $ac_user_is_import_comments = get_user_meta( $userid, "ac_is_import_comments", true );
          if ( strlen( $ac_user_is_import_comments ) == 0 ) $ac_user_is_import_comments = "1";

          $ac_is_copyprotect = get_post_meta( $post->ID, "ac_is_copyprotect", true );
          if ( strlen( $ac_is_copyprotect ) == 0 ) $ac_is_copyprotect = $ac_user_copyprotect;
          $ac_is_copyprotect_checked = "";
          if ( $ac_is_copyprotect == "1" ) {
              $ac_is_copyprotect_checked = "checked=\"checked\"";
          }

          $ac_is_advanced_tracking = get_post_meta( $post->ID, "ac_is_advanced_tracking", true );
          if ( strlen( $ac_is_advanced_tracking ) == 0 ) $ac_is_advanced_tracking = "1";
          $ac_is_advanced_tracking_checked = "";
          if ( $ac_is_advanced_tracking == "1" ) {
              $ac_is_advanced_tracking_checked = "checked=\"checked\"";
          }

          $ac_is_paidrepost = get_post_meta( $post->ID, "ac_is_paidrepost", true );
          if ( strlen( $ac_is_paidrepost ) == 0 ) $ac_is_paidrepost = $ac_user_paidrepost;
          $ac_is_paidrepost_checked = "";
          if ($ac_is_paidrepost == "1") {
              $ac_is_paidrepost_checked = "checked=\"checked\"";
          }

          $ac_is_import_comments = get_post_meta( $post->ID, "ac_is_import_comments", true );
          if ( strlen( $ac_is_import_comments ) == 0 ) $ac_is_import_comments = $ac_user_is_import_comments;
          $ac_is_import_comments_checked = "";
          if ($ac_is_import_comments == "1") {
              $ac_is_import_comments_checked = "checked=\"checked\"";
          }

          $ac_paidrepost_cost = get_post_meta($post->ID, "ac_paidrepost_cost", true);
          if ($ac_paidrepost_cost == "") { $ac_paidrepost_cost = $ac_user_paidrepostcost; }
          if ($ac_paidrepost_cost == "") { $ac_paidrepost_cost = "2.50"; }

          $ac_cost = get_post_meta($post->ID, "ac_cost", true);
          if ($ac_cost == "") $ac_cost = $ac_paidrepost_cost;

          $ac_type = get_post_meta( $post->ID, "ac_type", true );
          if ($ac_type == "") {
              if ($ac_is_paidrepost == "1") $ac_type = "paidrepost";
              else $ac_type = "free";
          }

          $ac_type_free_selected = ($ac_type == "free") ? "selected=\"selected\"" : "";
          $ac_type_paidrepost_selected = ($ac_type == "paidrepost") ? "selected=\"selected\"" : "";
          $ac_type_donate_selected = ($ac_type == "donate") ? "selected=\"selected\"" : "";
          $ac_type_paid_selected = ($ac_type == "paid") ? "selected=\"selected\"" : "";
          
          $plagiarism_quota = 0;
          $advanced_tracking_quota = 0;
          $quotas_result = atcontent_api_get_quotas ( $ac_api_key );
          $subscriptions_count = 0;
          if ( $quotas_result["IsOK"] == TRUE ) {
              $subscriptions_count = count ( $quotas_result["Subscriptions"] );
              $plagiarism_quota = intval( $quotas_result["Quotas"]["PlagiarismProtection"]["Count"] );
              $advanced_tracking_quota = intval( $quotas_result["Quotas"]["DetailedStat"]["Count"] );
          }

          $ac_is_copyprotect_enabled = $plagiarism_quota > 0;
          $ac_is_advanced_tracking_enabled = $advanced_tracking_quota > 0;

          ?>
<script type="text/javascript">
    (function ($) {
        $(function () {
            $("#atcontent_type").change(function () {
                ac_type_init($(this).val());
            });
            ac_type_init('<?php echo $ac_type ?>');
        });
        window.ac_type_init = function (val) {
            $("#atcontent_cost").hide();
            $("#atcontent_secondeditor").hide();
            if (val == 'paid' || val == 'paidrepost') {
                $("#atcontent_cost").show();
            }
            if (val == 'paid') {
                $("#atcontent_secondeditor").show();
            }
        };
    })(jQuery)
</script>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_process" name="atcontent_is_process" value="1" <?php echo $ac_is_process_checked; ?> /> Use AtContent for this post</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_copyprotect" name="atcontent_is_copyprotect" value="1" <?php echo $ac_is_copyprotect_checked; ?> <?php echo $ac_is_copyprotect_enabled ? '' : 'disabled="disabled"'; ?> > Protect post from plagiarism<br>Available credits: <?php echo $plagiarism_quota; ?>.
<?php if ($ac_is_copyprotect_enabled == false) { ?> 
<?php if ( $subscriptions_count == 0 ) { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">choose the appropriate plan</a>
    <?php } else { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">upgrade your subscription</a> or wait for the next month
    <?php } ?>
<?php } ?>
<input type="hidden" name="atcontent_is_copyprotect_enabled" value="<?php echo $ac_is_copyprotect_enabled ? "1" : "0"; ?>">
</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_advanced_tracking" name="atcontent_is_advanced_tracking" value="1" <?php echo $ac_is_advanced_tracking_checked; ?> <?php echo $ac_is_advanced_tracking_enabled ? '' : 'disabled="disabled"'; ?> > Enable advanced statistics<br>Available credits: <?php echo $advanced_tracking_quota; ?>.
<?php if ( $ac_is_advanced_tracking_enabled == false ) { ?> 
    <?php if ( $subscriptions_count == 0 ) { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">choose the appropriate plan</a>
    <?php } else { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">upgrade your subscription</a> or wait for the next month
    <?php } ?>
<?php } ?>
<input type="hidden" name="atcontent_is_advanced_tracking_enabled" value="<?php echo $ac_is_advanced_tracking_enabled ? "1" : "0"; ?>">
</div>
<div class="misc-pub-section">
    Post type:
<select name="atcontent_type" id="atcontent_type">
    <option value="free" <?php echo $ac_type_free_selected; ?>>Free</option>
    <option value="paidrepost" <?php echo $ac_type_paidrepost_selected; ?>>Paid repost</option>
    <option value="donate" <?php echo $ac_type_donate_selected; ?>>Donate</option>
    <option value="paid" <?php echo $ac_type_paid_selected; ?>>Paid</option>
</select>
</div>
<div class="misc-pub-section" id="atcontent_cost">
<label for="atcontent_paidrepost_cost">Cost, $</label> <input type="text" name="atcontent_cost" value="<?php echo $ac_cost ?>" size="10" /><br>
* If you have professional, popular blog, we recommend you to set $20 price for repost.
</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_import_comments" name="atcontent_is_import_comments" value="1" <?php echo $ac_is_import_comments_checked?> /> Import post comments into AtContent</div>
<?php
        if ( strlen( $ac_postid ) > 0 ) {
        ?>
<div class="misc-pub-section">
<a href="<?php echo atcontent_get_statistics_link( $post->ID ); ?>" target="_blank">View statistics</a>
</div>
        <?php
        }
    }

    function atcontent_paid_portion($post) {
        // Use nonce for verification
        $args = array(
            'wpautop' => 1
            ,'media_buttons' => 1
            ,'textarea_name' => 'ac_paid_portion'
            ,'textarea_rows' => 20
            ,'tabindex' => null
            ,'editor_css' => ''
            ,'editor_class' => ''
            ,'teeny' => 0
            ,'dfw' => 0
            ,'tinymce' => 1
            ,'quicktags' => 1
        );
        $ac_paid_portion = get_post_meta( $post->ID, "ac_paid_portion", true );
        wp_editor( $ac_paid_portion, "atcontentpaidportion", $args);
    }

    function atcontent_import_handler(){
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta($userid, "ac_api_key", true );
        if ( current_user_can( 'edit_posts' ) && strlen( $ac_api_key ) > 0 ) {

            atcontent_coexistense_fixes();

	        // get the submitted parameters
	        $postID = $_POST['postID'];
            $ac_is_copyprotect = $_POST['copyProtection'];
            $ac_is_paidrepost = $_POST['paidRepost'];
            $ac_paidrepost_cost = $_POST['cost'];
            $ac_is_import_comments = $_POST['comments'];

            $ac_postid = get_post_meta( $postID, "ac_postid", true );
            $ac_is_process = get_post_meta( $postID, "ac_is_process", true );

            $ac_cost = get_post_meta( $postID, "ac_cost", true );
            $ac_type = get_post_meta( $postID, "ac_type", true );
            $ac_paid_portion = get_post_meta( $postID, "ac_paid_portion", true );

            if ( strlen( $ac_type ) == 0 ) {
                if ($ac_is_paidrepost == "1") {
                    $ac_type = "paidrepost";
                } else {
                    $ac_type = "free";
                }
            }

            if ($ac_cost == "") $ac_cost = $ac_paidrepost_cost;

            $ac_action = "";
            $additional = NULL;
            $post = get_post( $postID );
            if ( $post == null || $ac_is_process == "0" ) {
                $ac_action = "skipped";
            } else {
                $comments_json = "";
                if ( $ac_is_import_comments == "1" ) {
                    $comments = get_comments( array(
                        'post_id' => $post->ID,
                        'order' => 'ASC',
                        'orderby' => 'comment_date_gmt',
                        'status' => 'approve',
                    ) );
                    if( !empty($comments) ) {
                        $comments_json .= json_encode($comments);
                    }
                }
	            if ( strlen( $ac_postid ) == 0 ) {
                    $api_answer = atcontent_create_publication( $ac_api_key, $post->post_title,
                            apply_filters( "the_content", $post->post_content ),
                            apply_filters( "the_content", $ac_paid_portion ),
                            $ac_type, get_gmt_from_date( $post->post_date ), get_permalink( $post->ID ),
                        $ac_cost, $ac_is_copyprotect, $comments_json );
                    if ( is_array( $api_answer ) && strlen( $api_answer["PublicationID"] ) > 0 ) {
                        $ac_postid = $api_answer["PublicationID"];
                        update_post_meta($post->ID, "ac_postid", $ac_postid);
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_type" , $ac_type );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        update_post_meta($post->ID, "ac_is_process", "1");
                        $ac_action = "created";
                    } else if ( is_array ( $api_answer ) ) {
                        $ac_action = "error";
                        $additional = $api_answer["error"];
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    } else {
                        $ac_action = "skipped";
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    }
                } else {
                    $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title,
                        apply_filters( "the_content", $post->post_content ) ,
                        apply_filters( "the_content", $ac_paid_portion ) ,
                        $ac_type , get_gmt_from_date( $post->post_date ), get_permalink($post->ID),
                        $ac_cost, $ac_is_copyprotect, $comments_json );
                    if ( is_array( $api_answer ) && strlen( $api_answer["PublicationID"] ) > 0 ) {
                        update_post_meta($post->ID, "ac_is_process", "1");
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_type" , $ac_type );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        $ac_action = "updated";
                    } else if ( is_array ( $api_answer ) ) {
                        $ac_action = "error";
                        $additional = $api_answer["error"];
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    } else {
                        $ac_action = "skipped";
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    }
                }
            }

	        // generate the response
            $res_array = array( 'IsOK' => true, "AC_action" => $ac_action );
            if ( $ac_action == "error" ) {
                $res_array["IsOK"] = FALSE;
                $res_array["Info"] = $additional;
            }
	        $response = json_encode( $res_array );

	        // response output
	        header( "Content-Type: application/json" );
	        echo $response;
        }

        // IMPORTANT: don't forget to "exit"
        exit;
    }

    function atcontent_api_key()
    {
        $userid = wp_get_current_user()->ID;
        if ( current_user_can( 'edit_posts' ) ) {

            $result = "";

            $api_key_result = atcontent_api_get_key($_GET["nounce"], $_GET["grant"]);

            if (!$api_key_result["IsOK"]) {
                $result .= "false";
            } else {
                update_user_meta( $userid, "ac_api_key", $api_key_result["APIKey"] );
                update_user_meta( $userid, "ac_pen_name", $api_key_result["Nickname"] );
                update_user_meta( $userid, "ac_showname", $api_key_result["Showname"] );
                $result .= "true";
            }

            //$response = "alert('grant:{$_GET["grant"]}');";

	        // response output
	        header( "Content-Type: text/html" );

	        echo <<<END
<html>
<body>
<script type="text/javascript">
    window.parent.parent.ac_connect_res({$result});
</script>
</body>
</html>
END;

        }

        // IMPORTANT: don't forget to "exit"
        exit;
    }

    function atcontent_comment_post( $comment_id, $status = 1 ) {
        $comment = get_comment( $comment_id );
        if ( $comment != NULL ) {
            atcontent_process_comments( $comment->comment_post_ID );
        }
    }

    function atcontent_process_comments( $post_id ) {
        $post = get_post( $post_id );
        if ($post == null) return;
        $ac_api_key = get_user_meta(intval($post->post_author), "ac_api_key", true);
        if (strlen($ac_api_key) > 0) {
            $ac_postid = get_post_meta($post->ID, "ac_postid", true);
            $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
            $ac_is_import_comments = get_post_meta($post->ID, "ac_is_import_comments", true);
            if ($ac_is_process == "1" && $ac_is_import_comments == "1") {
                $comments_json = "";
                $comments = get_comments( array(
                    'post_id' => $post->ID,
                    'order' => 'ASC',
                    'orderby' => 'comment_date_gmt',
                    'status' => 'approve',
                ) );
                if(!empty($comments)){
                    $comments_json .= json_encode($comments);
                }

                atcontent_api_update_publication_comments( $ac_api_key, $ac_postid, $comments_json );
            }
        }
    }

    function atcontent_coexistense_fixes(){
        remove_filter( 'the_content', 'atcontent_the_content', 1 );
        remove_filter( 'the_content', 'atcontent_the_content_after', 100 );
        remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
        remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100 );

        //Sociable fix
        if ( defined( "SOCIABLE_ABSPATH" ) ) {
            remove_filter( 'the_content', 'auto_sociable' );
            remove_filter( 'the_excerpt', 'auto_sociable' );
        }
        //end Sociable fix

        //Facebook fix
        if ( class_exists( 'Facebook_Loader' ) ) {
            remove_filter( 'the_content', 'facebook_the_content_like_button' );
            remove_filter( 'the_content', 'facebook_the_content_send_button' );
            remove_filter( 'the_content', 'facebook_the_content_follow_button' );
            remove_filter( 'the_content', 'facebook_the_content_recommendations_bar' );
            if ( class_exists( 'Facebook_Comments' ) ) {
                remove_filter( 'the_content', array( 'Facebook_Comments', 'the_content_comments_box' ) );
            }
        }
        //end Facebook fix

        //EmbedPlus fix
        if ( class_exists( 'EmbedPlusOfficialPlugin' ) ) {
            add_shortcode("embedplusvideo", "EmbedPlusOfficialPlugin::embedplusvideo_shortcode");
        }
        //end EmbedPlus fix

        //TablePress fix
        if ( class_exists( 'TablePress' ) ) {
            $GLOBALS['vadim_tablepress_frontend_controller'] = TablePress::load_controller('frontend');
            $GLOBALS['vadim_tablepress_frontend_controller']->init_shortcodes();
        }
        //End TablePress fix

        //linkwithin
        if ( function_exists( "linkwithin_add_hook" ) ) {
            remove_filter( 'the_excerpt', 'linkwithin_display_excerpt' );
            remove_filter( 'the_content', 'linkwithin_add_hook' );
        }
        //end linkwithin

        //Feedweb
        if ( function_exists( "GetFeedwebOptions" ) ) {
            remove_filter( 'the_content', 'ContentFilter' );
        }
        //end Feedweb fix

        //Page-views-count
        if ( class_exists('A3_PVC') ) {
            remove_filter('the_content', array('A3_PVC','pvc_stats_show'), 8);
            remove_filter('the_excerpt', array('A3_PVC','excerpt_pvc_stats_show'), 8);
        }
        //end Page-views-count

        //Hupso
        if ( function_exists( "hupso_shortcodes" ) ) {
            remove_filter( 'the_content', 'hupso_the_content', 10 );
            remove_filter( 'get_the_excerpt', 'hupso_get_the_excerpt', 1);
            remove_filter( 'the_excerpt', 'hupso_the_content', 100 );
        }
        //end Hupso

        //Suffision Shortcodes
        if ( class_exists( 'Suffusion_Shortcodes' ) ) {
            init_suffusion_shortcodes();
        }
        //end Suffision Shortcodes

        //Safer Email Link
        if ( function_exists( "sf_email_shortcode" ) ) {
            add_shortcode( 'sf_email', 'sf_email_shortcode' );
        }
        //end Safer Email Link

        //Simple Share For Chinese Social Sites
        if ( function_exists( "simple_share_init" ) ) {
            remove_filter( 'the_content', 'share' );
        }
        //end Simple Share For Chinese Social Sites

        //Share This
        if ( function_exists ( "jw_share_this_links" ) ) {
            remove_filter( 'the_content', 'jw_share_this_links' );
        }
        //end Share This

        //Shareaholic
        if ( function_exists( "shr_upgrade_routine" ) ) {
            remove_filter( 'the_content', 'shrsb_position_menu' );
            remove_filter( 'the_content', 'shrsb_get_recommendations' );
            remove_filter( 'the_content', 'shrsb_get_cb' );
        }
        //end Shareaholic

        //Skimlinks
        if ( function_exists( "sl_is_disclosure_badge_enabled" ) ) {
            try {
                remove_filter( 'the_content', 'sl_add_displausre_badge_to_content' );
            } catch (Exception $e) { }
        }
        //end Skimlinks

        //WP-insert
        if ( function_exists( "wp_insert_legal_filter_the_content" ) ) {
            try {
                remove_filter( 'the_content', 'wp_insert_track_post_instance' );
                remove_filter( 'the_content', 'wp_insert_inpostads_filter_the_content' );
                remove_filter( 'the_content', 'wp_insert_legal_filter_the_content' );
            } catch (Exception $e) { }
        }
        //end WP-insert
    }

    function atcontent_admin_head() {
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        $connect_url = admin_url( "admin.php?page=atcontent/settings.php" );
        $img_url = plugins_url( 'assets/logo.png', __FILE__ );
        if ( strlen( $ac_api_key ) == 0 ) {
        ?>
<script type="text/javascript">
$j = jQuery;
$j().ready(function(){
    if (window.location.href.indexOf("billbelew.com") != -1) return;
	$j('.wrap > h2').parent().prev().after('<div class="update-nag"><img style="vertical-align:bottom;" src="<?php echo $img_url; ?>" alt=""> To activate AtContent features, please, <a href="<?php echo $connect_url; ?>">connect</a> your blog to AtContent</div>');
});
</script>
<?php
        }
    }

    function atcontent_get_statistics_link($postID) {
        $ac_postid = get_post_meta( $postID, "ac_postid", true );
        if ( strlen( $ac_postid ) > 0 ) {
            return admin_url( 'admin.php?page=atcontent/statistics.php' ) . "&postid=" . $ac_postid;
        }
        return "";
    }


    function atcontent_column_head($defaults) {
	    $defaults['atcontent_column'] = 'AtContent';
	    return $defaults;
    }


    function atcontent_column_content( $column_name, $post_ID ) {
	    if ( $column_name == 'atcontent_column' ) {
		    $stat_link = atcontent_get_statistics_link( $post_ID );
		    if ( strlen( $stat_link) > 0 ) {
			    echo "<a href=\"{$stat_link}\">Statistics</a>";
		    } else {
		        echo "N/A";
		    }
	    }
    }

    function atcontent_footer_scripts() {
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta($userid, "ac_api_key", true );
?>
<script type="text/javascript">
function ACsetCookie (name, value, expires, path, domain, secure) {
      document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}
function ACgetCookie(name) {
	var results = document.cookie.match ( '(^|;) ?' + name + '=([^;]*)(;|$)' );
 
  if ( results )
    return ( unescape ( results[2] ) );
  else
    return null;
}
</script>
<?php
        if ( strlen( $ac_api_key ) == 0 ) {
            $connect_url = admin_url( "admin.php?page=atcontent/settings.php" );
            $img_url = plugins_url( 'assets/logo.png', __FILE__ );
            $pointer_content = '<h3>Connect to AtContent</h3>';
            $pointer_content .= '<p><img style="vertical-align:bottom;" src="' . $img_url .
                '" alt=""> To activate AtContent features, please, <a href="' . $connect_url . '">connect</a> your blog to AtContent</p>';
?>
<script type="text/javascript">
jQuery(document).ready( function($) {
    if (window.location.href.indexOf("billbelew.com") != -1) return;
    if (ACgetCookie("ac-connect-dismiss") != "1") {
        $('#toplevel_page_atcontent-settings').pointer({
            content: '<?php echo $pointer_content; ?>',
            position: 'top',
            close: function() {
                ACsetCookie("ac-connect-dismiss", "1", null, "/");
            }
        }).pointer('open');
    }
});
</script>
<?php
        } else {
            $connect_url = admin_url( "admin.php?page=atcontent/settings.php" );
            $ac_country = get_user_meta($userid, "ac_country", true );
            if ( strlen( $ac_country ) == 0 ) {
                $pointer_content = '<h3>Better Interaction with AtContent</h3>';
                $pointer_content .= '<p>Please, <a href="' . $connect_url . '">select location</a> of your blog</p>';
?>
<script type="text/javascript">
jQuery(document).ready( function($) {
    if (window.location.href.indexOf("billbelew.com") != -1) return;
    if (ACgetCookie("ac-location-dismiss") != "1") {
        jQuery('#toplevel_page_atcontent-settings').pointer({
            content: '<?php echo $pointer_content; ?>',
            position: 'top',
            close: function() {
                ACsetCookie("ac-location-dismiss", "1", null, "/");
            }
        }).pointer('open');
    }
   });
</script>
<?php
            }
        }
    }

?>