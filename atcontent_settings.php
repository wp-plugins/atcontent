<?php 
         $userid = wp_get_current_user()->ID;
         $hidden_field_name = 'ac_submit_hidden';
         $form_message = '';
         $form_script = '';
         $form_message_block = '';
         if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
              isset( $_POST[ "ac_api_key" ] ) ) {
             update_user_meta($userid, "ac_api_key", $_POST["ac_api_key"]);
             $form_message .= 'Settings saved.';
         }
         $ac_api_key = get_user_meta($userid, "ac_api_key", true );
         if ( ( strlen($ac_api_key) > 0 ) && isset($_POST[ $hidden_field_name ]) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
              isset( $_POST[ "ac_import" ] ) && ( $_POST[ "ac_import" ] == 'Y' ) ) {
            $wp_query_args = array(
                'post_author' => $userid,
                'post_status' => array('publish'),
                'nopaging' => true
                );
                $posts_query = new WP_Query( $wp_query_args );
            remove_filter( 'the_content', 'atcontent_the_content', 1 );
            remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );

            $posts_id = array();

            while( $posts_query->have_posts() ):
	            $posts_query->next_post();
                array_push( $posts_id, $posts_query->post->ID );
            endwhile;

            $copyProtection = isset($_POST["ac_copyprotect"]) && $_POST["ac_copyprotect"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_copyprotect", $copyProtection);
            $paidRepost = isset($_POST["ac_paidrepost"]) && $_POST["ac_paidrepost"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_paidrepost", $paidRepost);
            $paidRepostCost = isset($_POST["ac_paidrepostcost"]) && is_numeric($_POST["ac_paidrepostcost"]) ? doubleval($_POST["ac_paidrepostcost"]) : 0.1;
            update_user_meta($userid, "ac_paidrepostcost", $paidRepostCost);

            // Restore original Query & Post Data
            wp_reset_query();
            wp_reset_postdata();
                $postIDs = join( "','" , $posts_id );
                $form_action = admin_url( 'admin-ajax.php' );
                $form_message .= 'Import started.<div id="importResult">Imported 0 of ...</div>Note: Updating posts takes few seconds, please be patient. The old version will be displayed for a while.';
                $form_script = <<<END
<script type="text/javascript">
    var postIDs = ['{$postIDs}'];
    var imported = 0;
    jQuery(function(){
        for (var i in postIDs) {
            jQuery.post('{$form_action}', {action: 'atcontent_import', postID: postIDs[i], copyProtection: {$copyProtection}, 
                paidRepost: {$paidRepost}, cost: {$paidRepostCost}}, function(d){
                if (d.IsOK) {
                    imported++;
                    jQuery("#importResult").html("Imported " + imported + " of " + postIDs.length);
                }
            }, "json");
        }
    });
</script>
END;
            }
         if (strlen($form_message) > 0) {
             $form_message_block .= <<<END
<div class="updated settings-error" id="setting-error-settings_updated"> 
<p><strong>{$form_message}</strong></p></div>
END;
         }
         echo <<<END
{$form_message_block}
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
<form action="" method="POST" name="import-form">
<div class="wrap">
<div class="icon32 icon-page" id="icon-import"><br></div><h3>Posts Import</h3>
    <?php 
 if (strlen($ac_api_key) == 0) {
            echo <<<END
<p>You can import all your blog posts to AtContent. For this, you need to set up your API Key above.</p>
END;
         } else {
             $ac_copyprotect = get_user_meta($userid, "ac_copyprotect", true );
             if (strlen($ac_copyprotect) == 0) $ac_copyprotect = "1";
             $ac_paidrepost = get_user_meta($userid, "ac_paidrepost", true );
             if (strlen($ac_paidrepost) == 0) $ac_paidrepost = "0";
             $ac_paidrepostcost = get_user_meta($userid, "ac_paidrepostcost", true );
             if (strlen($ac_paidrepostcost) == 0) $ac_paidrepostcost = "0.10";

             $ac_copyprotect_checked = $ac_copyprotect == "1" ? "checked=\"checked\"" : "";
             $ac_paidrepost_checked = $ac_paidrepost == "1" ? "checked=\"checked\"" : "";

                             echo <<<END
{$form_script}
<div class="tool-box">
    <p>To import all your blog posts to AtContent press "Import".</p>
    <input type="hidden" name="{$hidden_field_name}" value="Y">
    <input type="hidden" name="ac_import" value="Y">
    <input type="checkbox" name="ac_copyprotect" id="ac_copyprotect" value="Y" {$ac_copyprotect_checked}> Prevent copy action for all publications<br>
    <input type="checkbox" name="ac_paidrepost" id="ac_paidrepost" value="Y" {$ac_paidrepost_checked}> Turn on paid repost for all publications<br>
    Cost for paid repost, $<br>
    <input type="input" name="ac_paidrepostcost" id="ac_paidrepostcost" value="{$ac_paidrepostcost}"><br>
END;
         
?>
    <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Import') ?>" />
    </p>
</div>
    <?php
         }
?>
</form>
<?php 