<?php
    /*
    Plugin Name: AtContent Plugin
    Plugin URI: http://atcontent.com/Plugins/WordPress/
    Description: AtContent Plugin
    Version: 1.2.1
    Author: Vadim Novitskiy
    Author URI: http://fb.com/vadim.novitskiy/
    */

    require_once("atcontent_api.php");
    add_action( 'admin_menu', 'atcontent_add_tools_menu' );
    add_filter( 'the_content', 'atcontent_the_content', 100 );
    add_filter( 'the_excerpt', 'atcontent_the_excerpt', 100 );
    add_action( 'save_post', 'atcontent_save_post' );
    add_action( 'publish_post', 'atcontent_publish_publication', 20 );
    add_action( 'add_meta_boxes', 'atcontent_add_meta_boxes' );
    add_action( 'wp_ajax_atcontent_import', 'atcontent_import_handler' );
    //
    //add_settings_field();
    function atcontent_add_tools_menu() {
        add_menu_page( 'AtContent Settings', 'AtContent', 'publish_posts', 'atcontent/atcontent_settings.php', '' );
        //add_submenu_page( 'atcontent', 'AtContent Import', 'Import', 'publish_posts', 'atcontent_import', 'atcontent_import_section' );
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
                    $ac_paidrepost_cost = get_post_meta($post->ID, "ac_paidrepost_cost", true);
                    $ac_is_copyprotect = get_post_meta($post->ID, "ac_is_copyprotect", true);
                    $ac_is_paidrepost = get_post_meta($post->ID, "ac_is_paidrepost", true);
                    $ac_is_import_comments = get_post_meta($post->ID, "ac_is_import_comments", true);
                    if ($ac_is_process != "1") return;
                    remove_filter( 'the_content', 'atcontent_the_content', 100 );
                    remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 100 );
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
                             apply_filters( "the_content", $post->post_content ), 
                            $post->post_date_gmt, get_permalink($post->ID),
                            $ac_paidrepost_cost, $ac_is_copyprotect, $ac_is_paidrepost, $comments_json );
                        if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                            $ac_postid = $api_answer["PublicationID"];
                            update_post_meta($post->ID, "ac_postid", $ac_postid);
                        }
                    } else {
                        $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title, 
                            apply_filters( "the_content", $post->post_content ) ,
                            $post->post_date_gmt, get_permalink($post->ID),
                            $ac_paidrepost_cost, $ac_is_copyprotect, $ac_is_paidrepost,
                            $comments_json
                             );
                        if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                        }
                    }
                }
	    }
    }

    function atcontent_the_content($content) {
        global $post;
        $ac_postid = get_post_meta($post->ID, "ac_postid", true);
        $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        if ($ac_is_process == "1" && strlen($ac_postid) > 0) {
            $code = <<<END
<script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script>
<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) -->
<script src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script>
END;
            if (is_single()) {
                $code .= <<<END
<script src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body"></script>
END;
            }
            $code = str_replace( PHP_EOL, " ", $code );

            
            return $code;
        }
        return $content;
    } 

    function atcontent_the_excerpt($content) {
        global $post;
        $ac_postid = get_post_meta($post->ID, "ac_postid", true);
        $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
        $ac_pen_name = get_user_meta(intval($post->post_author), "ac_pen_name", true);
        if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        if ($ac_is_process == "1" && strlen($ac_postid) > 0) {
            $code = <<<END
<script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script>
<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) -->
<script src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script>
END;
            $code = str_replace( PHP_EOL, " ", $code );
            return $code;
        }
        return $content;
    }

    function atcontent_convert_paragraphs($content){
        return $content;
        $content = explode(PHP_EOL . PHP_EOL, $content);
        $htmlcontent = '';
        foreach($content as $line){
            $htmlcontent .= '<p>' . str_replace(PHP_EOL, '<br />' , $line) . '</p>';
        }
        return $htmlcontent;  
    }

    function atcontent_add_meta_boxes(){
         add_meta_box( 
            'atcontent_sectionid',
            __( 'AtContent Post Settings', 'atcontent_textdomain' ),
            'atcontent_inner_custom_box',
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
          // The actual fields for data entry
          echo <<<END
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_process" name="atcontent_is_process" value="1" {$ac_is_process_checked} /> Process post throught AtContent API</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_copyprotect" name="atcontent_is_copyprotect" value="1" {$ac_is_copyprotect_checked} /> Prevent copy actions</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_paidrepost" name="atcontent_is_paidrepost" value="1" {$ac_is_paidrepost_checked} /> Paid repost<br><br>
<label for="atcontent_paidrepost_cost">Paid repost cost, $</label> <input type="text" name="atcontent_paidrepost_cost" value="{$ac_paidrepost_cost}" size="10" /><br>
* If you have professional, popular blog, we recommend you to set $20 price for repost.</div>
END;

            $ac_postid = get_post_meta($post->ID, "ac_postid", true);
            if (strlen($ac_postid) > 0) {
                echo <<<END
<div class="misc-pub-section">
You can make this post paid for users on <a href="https://atcontent.com/Studio/Publication/View/{$ac_postid}/">AtContent publication page</a>. We will add this functionality to WP plugin soon, thanks.<br>
Note. If you change publication outside WordPress do not update it within WordPress.
</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_import_comments" name="atcontent_is_import_comments" value="1" {$ac_is_import_comments_checked} /> Import post comments into AtContent</div>
END;
            }

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
        $ac_paidrepost_cost = $_POST['atcontent_paidrepost_cost'];
        $ac_is_import_comments = $_POST['atcontent_is_import_comments'];

        if ($ac_is_process != "1") $ac_is_process = "0";
        update_post_meta($post_id, "ac_is_process", $ac_is_process);
        
        if ($ac_is_copyprotect != "1") $ac_is_copyprotect = "0";
        update_post_meta($post_id, "ac_is_copyprotect", $ac_is_copyprotect);

        if ($ac_is_paidrepost != "1") $ac_is_paidrepost = "0";
        update_post_meta($post_id, "ac_is_paidrepost", $ac_is_paidrepost);

        update_post_meta($post_id, "ac_paidrepost_cost", $ac_paidrepost_cost);

        if ($ac_is_import_comments != "1") $ac_is_import_comments = "0";
        update_post_meta( $post_id, "ac_is_import_comments", $ac_is_import_comments );
    }

    function atcontent_import_handler(){

        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta($userid, "ac_api_key", true );
        if ( current_user_can( 'edit_posts' ) && strlen($ac_api_key) > 0 ) {
            remove_filter( 'the_content', 'atcontent_the_content', 100 );
            remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 100 );
	        // get the submitted parameters
	        $postID = $_POST['postID'];
            $ac_is_copyprotect = $_POST['copyProtection'];
            $ac_is_paidrepost = $_POST['paidRepost'];
            $ac_paidrepost_cost = $_POST['cost'];
            $ac_is_import_comments = $_POST['comments'];

            $ac_postid = get_post_meta( $postID, "ac_postid", true );
            $ac_is_process = get_post_meta( $postID, "ac_is_process", true );
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
                    if(!empty($comments)){
                        $comments_json .= json_encode($comments);
                    }
                }
	            if ( strlen($ac_postid) == 0 ) {
                    $api_answer = atcontent_create_publication( $ac_api_key, $post->post_title, 
                        apply_filters( "the_content", $post->post_content ),
                        $post->post_date_gmt, get_permalink($post->ID),
                        $ac_paidrepost_cost, $ac_is_copyprotect, $ac_is_paidrepost, $comments_json );
                    if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                        $ac_postid = $api_answer["PublicationID"];
                        update_post_meta($post->ID, "ac_postid", $ac_postid);
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_is_paidrepost" , $ac_is_paidrepost );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        update_post_meta($post->ID, "ac_is_process", "1");
                        $ac_action = "created";
                    }
                } else {
                    $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title, 
                        apply_filters( "the_content", $post->post_content ),
                        $post->post_date_gmt, get_permalink($post->ID),
                        $ac_paidrepost_cost, $ac_is_copyprotect, $ac_is_paidrepost, $comments_json );
                    if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                        update_post_meta($post->ID, "ac_is_process", "1");
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_is_paidrepost" , $ac_is_paidrepost );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        $ac_action = "updated";
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
?>