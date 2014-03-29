<?php
     function atcontent_the_content( $content = '' ) {
        global $post, $wp_current_filter;
        if ( preg_match_all( '/<script[^<]+src="(https?:\/\/w\.atcontent\.com\/[^\"]+)\"/', $content, $matches ) ) {
            for ( $index = 0; $index < count( $matches[1] ); $index++ )
            {
                $content = str_replace( 
                    $matches[0][$index], 
                    "<script data-cfasync=\"false\" " . ($index > 0 ? "data-ac-" : "") . "src=\"" . $matches[1][$index] . "\"", 
                    $content );
            }
            return $content;
        }
        if ( in_array( 'the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }
        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
		    return $content;
	    }        
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_embedid = get_post_meta( $post->ID, "ac_embedid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        if ( is_string ( $ac_pen_name ) && strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "AtContent";
        if ( $ac_is_process == "1" && is_string ( $ac_postid ) && strlen( $ac_postid ) > 0 && is_single() ) {
            $embedid = "-/00000000000/";
            if ( strlen( $ac_embedid ) > 0 ) {
                $embedid = "-/" . $ac_embedid . "/";
            }
            $code = <<<END
<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://atcontent.com/Terms/) -->
<script async="true" src="https://w.atcontent.com/{$embedid}{$ac_pen_name}/{$ac_postid}/Panel"></script>
END;
            $code = str_replace( PHP_EOL, " ", $code );
            return $content . $code;
        }
        return $content;
    }

    function atcontent_the_excerpt( $content = '' ) {
        global $post, $wp_current_filter;
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_embedid = get_post_meta( $post->ID, "ac_embedid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        if ( is_string ( $ac_pen_name ) && strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "AtContent";
        if ( $ac_is_process == "1" && is_string ( $ac_postid ) && strlen( $ac_postid ) > 0 && is_single() ) {
            $embedid = "-/00000000000/";
            if ( strlen( $ac_embedid ) > 0 ) {
                $embedid = "-/" . $ac_embedid . "/";
            }
            $code = <<<END
<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) -->
<script src="https://w.atcontent.com/{$embedid}{$ac_pen_name}/{$ac_postid}/Panel"></script>
END;
            $code = str_replace( PHP_EOL, " ", $code );
            return $content . $code;
        }
        return $content;
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

        //Grand Flagallery
        if ( class_exists( 'flagLoad' ) ) {
            global $flag;
	        $flag = new flagLoad();
            //ini_set('display_errors', 1);
            //error_reporting(E_ALL ^ E_NOTICE);
            $flag_dir = dirname(dirname(dirname (__FILE__))) . '/flash-album-gallery';
            require_once ($flag_dir . '/lib/core.php');
            require_once ($flag_dir . '/lib/flag-db.php');
            require_once ($flag_dir . '/lib/image.php');
            require_once ($flag_dir . '/widgets/widgets.php');
            require_once ($flag_dir . '/lib/swfobject.php');
            require_once ($flag_dir . '/lib/shortcodes.php');
        }
        //End Grand Flagallery

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

        //Hover Pin-It
        if ( function_exists( 'pin_it_buttons_add' ) ) {
            add_filter( 'the_content', 'atcontent_coexistense_pin_it_buttons_add' );
        }
        //end Hover Pin-It

        //Social Media Feather
        if ( function_exists( 'synved_social_wp_the_content' ) ) {
            remove_filter('the_content', 'synved_social_wp_the_content', 10, 2);
        }
        //end Social Media Feather
    }

    function atcontent_admin_head() {
        ?>
        <script type="text/javascript">
            jQuery().ready(function () {
                jQuery("#toplevel_page_atcontent-getpaid > a").attr('href', 'http://bit.ly/1j7vVbM').attr('target', '_blank');
            });
        </script>
        <?php
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        $ac_syncid = get_user_meta($userid, "ac_syncid", true );
        $connect_url = admin_url( "admin.php?page=atcontent/dashboard.php" );
        $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
        if ( (strlen( $ac_api_key ) == 0 || strlen( $ac_syncid ) == 0 ) && user_can( $userid, "edit_posts" ) ) {
        ?>
<script type="text/javascript">
$j = jQuery;
$j().ready(function(){
    if (window.location.href.indexOf("billbelew.com") != -1) return;
	$j('.wrap > h2').parent().prev().after('<div class="update-nag"><table><tr><td><a class="button button-primary ac-connect-button" href="<?php echo $connect_url; ?>">Connect your account to AtContent</a></td><td>Almost done — connect your account to start grow your audience and monetize your blog with native advertizing!</td></tr></table></div>');
});
</script>
<?php
        }
    }

?>