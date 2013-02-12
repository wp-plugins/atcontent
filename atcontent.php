<?php
    /*
    Plugin Name: AtContent Plugin
    Plugin URI: http://atcontent.com/Plugins/WordPress/
    Description: AtContent Plugin
    Version: 1.7.10
    Author: AtContent, IFFace, Inc.
    Author URI: http://atcontent.com/
    */

    define( 'AC_VERSION', "1.7.10" );
    define( 'AC_NO_PROCESS_EXCERPT_DEFAULT', "1" );

    require_once("atcontent_api.php");
    require_once("pingback.php"); 
    add_action( 'admin_menu', 'atcontent_add_tools_menu' );
    add_filter( 'the_content', 'atcontent_the_content', 1 );
    add_filter( 'the_content', 'atcontent_the_content_after', 100);
    add_filter( 'the_excerpt', 'atcontent_the_content_after', 100);
    add_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
    add_action( 'save_post', 'atcontent_save_post' );
    add_action( 'publish_post', 'atcontent_publish_publication', 20 );
    add_action( 'comment_post', 'atcontent_comment_post' );
    add_action( 'deleted_comment', 'atcontent_comment_post' );
    add_action( 'trashed_comment', 'atcontent_comment_post' );
    add_action( 'add_meta_boxes', 'atcontent_add_meta_boxes' );
    add_action( 'wp_ajax_atcontent_import', 'atcontent_import_handler' );
    add_action( 'wp_ajax_atcontent_api_key', 'atcontent_api_key' );
    add_action( 'wp_ajax_atcontent_pingback', 'atcontent_pingback' );

    register_activation_hook( __FILE__, 'atcontent_activate' );
    register_deactivation_hook( __FILE__, 'atcontent_deactivate' );
    register_uninstall_hook( __FILE__, 'atcontent_uninstall' );

    //
    //add_settings_field();
    function atcontent_add_tools_menu() {
        add_menu_page( 'AtContent Settings', 'AtContent', 'publish_posts', 'atcontent/settings.php', '' );
        add_submenu_page( 'atcontent/settings.php', 'Connect Settings', 'Connection', 'publish_posts', 'atcontent/connect.php',  '');
        add_submenu_page( 'atcontent/settings.php', 'Known Plugins Issues', 'Known Issues', 'publish_posts', 'atcontent/knownissues.php',  '');
    }

    function atcontent_publish_publication( $post_id ){
	    if ( !wp_is_post_revision( $post_id ) ) {
		    $post_url = get_permalink( $post_id );
		    $post = get_post( $post_id );
            if ($post == null) return;
            $ac_api_key = get_user_meta(intval($post->post_author), "ac_api_key", true);
            if (strlen($ac_api_key) > 0) {
                $ac_postid = get_post_meta($post->ID, "ac_postid", true);
                $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
                $ac_cost = get_post_meta($post->ID, "ac_cost", true);
                $ac_is_copyprotect = get_post_meta($post->ID, "ac_is_copyprotect", true);
                $ac_type = get_post_meta($post->ID, "ac_type", true);
                $ac_paid_portion = get_post_meta($post->ID, "ac_paid_portion", true);
                $ac_is_import_comments = get_post_meta($post->ID, "ac_is_import_comments", true);
                if ($ac_is_process != "1") return;
                remove_filter( 'the_content', 'atcontent_the_content', 1 );
                remove_filter( 'the_content', 'atcontent_the_content_after', 100 );
                remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
                remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100 );
                $comments_json = "";
                if ($ac_is_import_comments == "1") {
                    $comments = get_comments( array(
                        'post_id' => $post->ID,
                        'order' => 'ASC',
                        'orderby' => 'comment_date_gmt',
                        'status' => 'approve',
                    ) );
                    if(!empty($comments)){
                        $comments_json .= json_encode($comments);
                    }
                }
                if (strlen($ac_postid) == 0) {
                    $api_answer = atcontent_create_publication( $ac_api_key, $post->post_title, 
                            apply_filters( "the_content", $post->post_content ), apply_filters( "the_content", $ac_paid_portion), $ac_type,
                        $post->post_date_gmt, get_permalink($post->ID),
                        $ac_cost, $ac_is_copyprotect, $comments_json );
                    if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                        $ac_postid = $api_answer["PublicationID"];
                        update_post_meta($post->ID, "ac_postid", $ac_postid);
                    } else {
                        update_post_meta($post->ID, "ac_is_process", "0");
                    }
                } else {
                    $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title, 
                        apply_filters( "the_content", $post->post_content ) , apply_filters( "the_content", $ac_paid_portion ) ,
                        $ac_type , $post->post_date_gmt, get_permalink($post->ID),
                        $ac_cost, $ac_is_copyprotect, $comments_json
                            );
                    if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                    } else {
                        update_post_meta($post->ID, "ac_is_process", "0");
                    }
                }
            }
	    }
    }

    function atcontent_the_content( $content = '' ) {
        global $post, $wp_current_filter;
        if ( in_array( 'the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }
        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
		    return $content;
	    }
        $ac_excerpt_no_process = get_user_meta( intval($post->post_author), "ac_excerpt_no_process", true );
        if (strlen($ac_excerpt_no_process) == 0) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
        if ( !is_single() && $ac_excerpt_no_process == "1" ) return $content;
        $ac_postid = get_post_meta($post->ID, "ac_postid", true);
        $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
        $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
        $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
        $ac_additional_classes = "";
        if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
        if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
        if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        if ($ac_is_process == "1" && strlen($ac_postid) > 0) {
            $code = <<<END
<div class="atcontent_widget{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --></div>
END;
            if (is_single()) {
                $code = <<<END
<div class="atcontent_widget{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --><script src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body"></script></div>
END;
            }
            $code = str_replace( PHP_EOL, " ", $code );           
            return $code;
        }
        return $content;
    } 

    function atcontent_the_excerpt( $content = '' ) {
        global $post, $wp_current_filter;
        $ac_postid = get_post_meta($post->ID, "ac_postid", true);
        $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
        $ac_pen_name = get_user_meta(intval($post->post_author), "ac_pen_name", true);
        if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        $ac_excerpt_image_remove = get_user_meta( intval($post->post_author), "ac_excerpt_image_remove", true );
        if ( strlen($ac_excerpt_image_remove) == 0 ) $ac_excerpt_image_remove = "0";
        $ac_excerpt_no_process = get_user_meta( intval($post->post_author), "ac_excerpt_no_process", true );
        if (strlen($ac_excerpt_no_process) == 0) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
        if ($ac_excerpt_no_process == "1") {
            return $content;
        }
        if ($ac_is_process == "1" && strlen($ac_postid) > 0 && $ac_excerpt_no_process == "0") {
            $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
            $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
            $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
            $ac_additional_classes = "";
            if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
            if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
            $ac_excerpt_class = "atcontent_excerpt";
            if ($ac_excerpt_image_remove == "1") $ac_excerpt_class = "atcontent_excerpt_no_image";
            $code = <<<END
<div class="{$ac_excerpt_class}{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --></div>
END;
            $code = str_replace( PHP_EOL, " ", $code );
            return $code;
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
        $ac_postid = get_post_meta($post->ID, "ac_postid", true);
        $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
        $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
        $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
        $ac_additional_classes = "";
        if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
        if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
        if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        if ($ac_is_process == "1" && strlen($ac_postid) > 0) {
            if (strlen($ac_script_init) > 0) {
                $content .= <<<END
<script>
CPlase.evt.add('load', function (event, p, w) {
{$ac_script_init}    
});
</script>
END;
            }
        }
        return $content;
    } 

    function atcontent_add_meta_boxes(){
         add_meta_box( 
            'atcontent_sectionid',
            __( 'AtContent Post Settings', 'atcontent_textdomain' ),
            'atcontent_inner_custom_box',
            'post' 
        );

        add_meta_box( 
            'atcontent_secondeditor',
            __( 'AtContent Paid Portion', 'atcontent_textdomain' ),
            'atcontent_paid_portion',
            'post'
        );
    }

    function atcontent_inner_custom_box($post) {
          // Use nonce for verification
          wp_nonce_field( plugin_basename( __FILE__ ), 'atcontent_noncename' );
          
          $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
          $ac_is_process_checked = "";
          if ($ac_is_process == "1" || $ac_is_process == "") {
              $ac_is_process_checked = "checked=\"checked\"";
          }

          $ac_postid = get_post_meta($post->ID, "ac_postid", true);

          $ac_is_copyprotect = get_post_meta($post->ID, "ac_is_copyprotect", true);
          $ac_is_copyprotect_checked = "";
          if ($ac_is_copyprotect == "1") {
              $ac_is_copyprotect_checked = "checked=\"checked\"";
          }          

          $ac_is_paidrepost = get_post_meta($post->ID, "ac_is_paidrepost", true);
          $ac_is_paidrepost_checked = "";
          if ($ac_is_paidrepost == "1") {
              $ac_is_paidrepost_checked = "checked=\"checked\"";
          }

          $ac_is_import_comments = get_post_meta( $post->ID, "ac_is_import_comments", true );
          $ac_is_import_comments_checked = "";
          if ($ac_is_import_comments == "1") {
              $ac_is_import_comments_checked = "checked=\"checked\"";
          }

          $ac_paidrepost_cost = get_post_meta($post->ID, "ac_paidrepost_cost", true);
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
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_process" name="atcontent_is_process" value="1" <?php echo $ac_is_process_checked ?> /> Process post through AtContent API</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_copyprotect" name="atcontent_is_copyprotect" value="1" <?php echo $ac_is_copyprotect_checked ?> /> Protect post from plagiarism</div>
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
<a href="https://www.atcontent.com/Studio/Publication/Stat/<?php echo $ac_postid ?>/" target="_blank">View statistics</a>
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

    function atcontent_save_post( $post_id ){
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if ( !wp_verify_nonce( $_POST['atcontent_noncename'], plugin_basename( __FILE__ ) ) )
            return;

        if ( !current_user_can( 'edit_post', $post_id ) )
            return;

        // OK, we're authenticated: we need to find and save the data

        $ac_is_process = $_POST['atcontent_is_process'];
        $ac_is_copyprotect = $_POST['atcontent_is_copyprotect'];
        $ac_is_paidrepost = $_POST['atcontent_is_paidrepost'];
        $ac_cost = $_POST['atcontent_cost'];
        $ac_is_import_comments = $_POST['atcontent_is_import_comments'];
        $ac_paid_portion = $_POST['ac_paid_portion'];
        $ac_type = $_POST['atcontent_type'];

        if ($ac_is_process != "1") $ac_is_process = "0";
        update_post_meta($post_id, "ac_is_process", $ac_is_process);
        
        if ($ac_is_copyprotect != "1") $ac_is_copyprotect = "0";
        update_post_meta($post_id, "ac_is_copyprotect", $ac_is_copyprotect);

        if ($ac_is_paidrepost != "1") $ac_is_paidrepost = "0";
        update_post_meta($post_id, "ac_is_paidrepost", $ac_is_paidrepost);

        update_post_meta($post_id, "ac_cost", $ac_cost);

        if ($ac_is_import_comments != "1") $ac_is_import_comments = "0";
        update_post_meta( $post_id, "ac_is_import_comments", $ac_is_import_comments );

        update_post_meta( $post_id, 'ac_type', $ac_type );

        if ($ac_paid_portion != NULL) {
            update_post_meta( $post_id, "ac_paid_portion", $ac_paid_portion );
        }
    }

    function atcontent_import_handler(){

        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta($userid, "ac_api_key", true );
        if ( current_user_can( 'edit_posts' ) && strlen($ac_api_key) > 0 ) {
            remove_filter( 'the_content', 'atcontent_the_content', 1 );
            remove_filter( 'the_content', 'atcontent_the_content_after', 100 );
            remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
            remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100 );

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

            if ($ac_type == "") {
                if ($ac_is_paidrepost == "1") { 
                    $ac_type = "paidrepost";
                } else {
                    $ac_type = "free";
                }
            }

            if ($ac_cost == "") $ac_cost = $ac_paidrepost_cost;

            $ac_action = "";
            $post = get_post( $postID );
            if ( $post == null || $ac_is_process == "0" ) { 
                $ac_action = "skiped";
            } else {
                $comments_json = "";
                if ($ac_is_import_comments == "1") {
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
	            if ( strlen($ac_postid) == 0 ) {
                    $api_answer = atcontent_create_publication( $ac_api_key, $post->post_title, 
                            apply_filters( "the_content", $post->post_content ), apply_filters( "the_content", $ac_paid_portion ), $ac_type,
                        $post->post_date_gmt, get_permalink( $post->ID ),
                        $ac_cost, $ac_is_copyprotect, $comments_json );
                    if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                        $ac_postid = $api_answer["PublicationID"];
                        update_post_meta($post->ID, "ac_postid", $ac_postid);
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_type" , $ac_type );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        update_post_meta($post->ID, "ac_is_process", "1");
                        $ac_action = "created";
                    } else {
                        $ac_action = "skiped";
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    }
                } else {
                    $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title, 
                        apply_filters( "the_content", $post->post_content ) , apply_filters( "the_content", $ac_paid_portion ) ,
                        $ac_type , $post->post_date_gmt, get_permalink($post->ID),
                        $ac_cost, $ac_is_copyprotect, $comments_json );
                    if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                        update_post_meta($post->ID, "ac_is_process", "1");
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_type" , $ac_type );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        $ac_action = "updated";
                    } else {
                        $ac_action = "skiped";
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    }
                }
            }

	        // generate the response
	        $response = json_encode( array( 'IsOK' => true, "AC_action" => $ac_action ) );
 
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

    function atcontent_comment_post($comment_id, $status) {
        $comment = get_comment( $comment_id );
        if ( $comment != NULL ) {
            atcontent_process_comments( $comment->comment_post_ID );
        }
    }

    function atcontent_process_comments($post_id) {
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
                
                atcontent_api_update_publication_comments($ac_api_key, $ac_postid, $comments_json);
            }
        }
    }
?>