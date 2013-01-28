<?php 
         $userid = wp_get_current_user()->ID;
         $hidden_field_name = 'ac_submit_hidden';
         $form_message = '';
         $form_script = '';
         $form_message_block = '';
         if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
              isset( $_POST[ "ac_advanced_settings" ] ) ) {
             $ac_excerpt_image_remove = (isset( $_POST[ "ac_excerpt_image_remove" ] ) && $_POST[ "ac_excerpt_image_remove" ] == "Y") ? "1" : "0";
             update_user_meta( $userid, "ac_excerpt_image_remove", $ac_excerpt_image_remove );
             $ac_excerpt_no_process = (isset( $_POST[ "ac_excerpt_no_process" ] ) && $_POST[ "ac_excerpt_no_process" ] == "Y") ? "1" : "0";
             update_user_meta( $userid, "ac_excerpt_no_process", $ac_excerpt_no_process );
             $form_message .= 'Settings saved.';
         }
         if (strlen($form_message) > 0) {
             $form_message_block .= <<<END
<div class="updated settings-error" id="setting-error-settings_updated"> 
<p><strong>{$form_message}</strong></p></div>
END;
         }
         echo $form_message_block;
         $ac_excerpt_image_remove = get_user_meta($userid, "ac_excerpt_image_remove", true );
         if (strlen($ac_excerpt_image_remove) == 0) $ac_excerpt_image_remove = "0";
         $ac_excerpt_no_process = get_user_meta($userid, "ac_excerpt_no_process", true );
         if (strlen($ac_excerpt_no_process) == 0) $ac_excerpt_no_process = "1";

         $ac_excerpt_image_remove_checked = "";
         if ($ac_excerpt_image_remove == "1") $ac_excerpt_image_remove_checked = "checked=\"checked\"";
         $ac_excerpt_no_process_checked = "";
         if ($ac_excerpt_no_process == "1") $ac_excerpt_no_process_checked = "checked=\"checked\"";
?>
<form action="" method="POST">
<div class="wrap">
<div class="icon32" id="icon-tools"><br></div><h2>AtContent Advanced Settings</h2>
<p>If you are not sure what any option means, do not change it. Please, <a href="http://atcontent.com/Support/">write us</a> in this case.</p>
<div class="tool-box">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <input type="hidden" name="ac_advanced_settings" value="Y">
    <p>
    <input type="checkbox" name="ac_excerpt_image_remove" value="Y" <?php echo $ac_excerpt_image_remove_checked ?>>
    Remove any images from excerpts in AtContent processed posts.</p>
    <p>
    <input type="checkbox" name="ac_excerpt_no_process" value="Y" <?php echo $ac_excerpt_no_process_checked ?>>
    Don't process excerpts with AtContent.</p>
     <p class="submit">
        <input type="submit" name="Submit" class="button button-primary" value="<?php esc_attr_e('Save changes') ?>" />
    </p>
</div>
</div>
</form>

<?php 