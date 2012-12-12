<?php
    /*
    Plugin Name: AtContent Plugin
    Plugin URI: http://atcontent.com/Plugins/WordPress/
    Description: AtContent Plugin
    Version: 0.9
    Author: Vadim Novitskiy
    Author URI: http://fb.com/vadim.novitskiy/
    */

    require_once("atcontent_api.php");
    add_action( 'admin_menu', 'atcontent_add_tools_menu' );
    add_filter( 'the_content', 'atcontent_the_content', 1 );
    add_action( 'save_post', 'atcontent_save_post' );
    add_action( 'publish_post', 'atcontent_publish_publication', 20 );
    add_action( 'add_meta_boxes', 'atcontent_add_meta_boxes' );
    //
    //add_settings_field();
    function atcontent_add_tools_menu() {
        add_menu_page( 'AtContent Settings', 'AtContent', 'publish_posts', 'atcontent', 'atcontent_setting_section' );
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
                    $ac_is_paidrepost =  get_post_meta($post->ID, "ac_is_paidrepost", true);
                    if ($ac_is_process != "1") return;
                    if (strlen($ac_postid) == 0) {
                        $api_answer = atcontent_create_publication( $ac_api_key, $post->post_title, atcontent_convert_paragraphs( $post->post_content ), NULL,
                            $ac_paidrepost_cost, $ac_is_copyprotect, $ac_is_paidrepost );
                        if (is_array($api_answer) && strlen($api_answer["PublicationID"]) > 0 ) {
                            $ac_postid = $api_answer["PublicationID"];
                            update_post_meta($post->ID, "ac_postid", $ac_postid);
                        }
                    } else {
                        $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title, atcontent_convert_paragraphs( $post->post_content ), NULL,
                            $ac_paidrepost_cost, $ac_is_copyprotect, $ac_is_paidrepost );
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
        if ($ac_is_process == "1" && strlen($ac_postid) > 0) {
            $code = <<<END
<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) -->
<script src="https://w.atcontent.com/vadim/{$ac_postid}/Face"></script>
END;
            if (is_single()) {
                $code .= <<<END
<!--more-->
<script src="https://w.atcontent.com/vadim/{$ac_postid}/Body"></script>
END;
            }
            return $code;
        }
        return $content;
    } 
 
     function atcontent_setting_section() {
         $userid = wp_get_current_user()->ID;
         $hidden_field_name = 'ac_submit_hidden';
         if (isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y') {
             update_user_meta($userid, "ac_api_key", $_POST["ac_api_key"]);
         }
         $ac_api_key = get_user_meta($userid, "ac_api_key", true );
         echo <<<END
<form action="" method="POST">
<div class="wrap">
<div class="icon32" id="icon-tools"><br></div><h2>AtContent Settings</h2>
<div class="tool-box">
    <p>AtContent API key connects your WordPress content with the AtContent social publishing and distribution platform.<br>
    With this key, all your newly published posts get wrapped in special distribution widgets that adjust to any website, 
    and are simultaneously published on WordPress and AtContent.<br>
    The key can be obtained here: 
    <a href="https://atcontent.com/Profile/NativeAPIKey">https://atcontent.com/Profile/NativeAPIKey</a>.<br>
    <h3>AtContent Native API Key</h3>
    <input type="hidden" name="{$hidden_field_name}" value="Y">
    <input type="text" name="ac_api_key" value="$ac_api_key" size="50">
END;
?>
    <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
    </p>

</div>
</div>
</form>
<?php 
     }

     function atcontent_convert_paragraphs($content){
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

          $ac_paidrepost_cost = get_post_meta($post->ID, "ac_paidrepost_cost", true);
          if ($ac_paidrepost_cost == "") { $ac_paidrepost_cost = "0.10"; }
          // The actual fields for data entry
          echo <<<END
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_process" name="atcontent_is_process" value="1" {$ac_is_process_checked} /> Process post throught AtContent API</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_copyprotect" name="atcontent_is_copyprotect" value="1" {$ac_is_copyprotect_checked} /> Prevent copy actions</div>
<div class="misc-pub-section"><input type="checkbox" id="atcontent_is_paidrepost" name="atcontent_is_paidrepost" value="1" {$ac_is_paidrepost_checked} /> Paid repost<br><br>
<label for="atcontent_paidrepost_cost">Paid repost cost, $</label> <input type="text" name="atcontent_paidrepost_cost" value="{$ac_paidrepost_cost}" size="10" /></div>
END;

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

        if ($ac_is_process != "1") $ac_is_process = "0";
        update_post_meta($post_id, "ac_is_process", $ac_is_process);
        
        if ($ac_is_copyprotect != "1") $ac_is_copyprotect = "0";
        update_post_meta($post_id, "ac_is_copyprotect", $ac_is_copyprotect);

        if ($ac_is_paidrepost != "1") $ac_is_paidrepost = "0";
        update_post_meta($post_id, "ac_is_paidrepost", $ac_is_paidrepost);

        update_post_meta($post_id, "ac_paidrepost_cost", $ac_paidrepost_cost);

     }
?>