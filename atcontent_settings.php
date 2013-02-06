<?php 
         $userid = wp_get_current_user()->ID;
         $hidden_field_name = 'ac_submit_hidden';
         $form_message = '';
         $form_script = '';
         $form_message_block = '';
         if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
              isset( $_POST[ "ac_api_key" ] ) ) {
             $ac_api_key = trim( $_POST[ "ac_api_key" ] );
             update_user_meta( $userid, "ac_api_key", $ac_api_key );
             $ac_pen_name = atcontent_api_get_nickname( $_POST[ "ac_api_key" ] );
             update_user_meta( $userid, "ac_pen_name", $ac_pen_name );
             $form_message .= 'Settings saved.';
         }
         $ac_api_key = get_user_meta($userid, "ac_api_key", true );
         $ac_pen_name = get_user_meta($userid, "ac_pen_name", true );
         if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
              isset( $_POST[ "ac_advanced_settings" ] ) ) {
             $ac_excerpt_image_remove = (isset( $_POST[ "ac_excerpt_image_remove" ] ) && $_POST[ "ac_excerpt_image_remove" ] == "Y") ? "1" : "0";
             update_user_meta( $userid, "ac_excerpt_image_remove", $ac_excerpt_image_remove );
             $ac_excerpt_no_process = (isset( $_POST[ "ac_excerpt_no_process" ] ) && $_POST[ "ac_excerpt_no_process" ] == "Y") ? "1" : "0";
             update_user_meta( $userid, "ac_excerpt_no_process", $ac_excerpt_no_process );
             $ac_comments_disable = (isset( $_POST[ "ac_comments_disable" ] ) && $_POST[ "ac_comments_disable" ] == "Y") ? "1" : "0";
             update_user_meta( $userid, "ac_comments_disable", $ac_comments_disable );
             $ac_hint_panel_disable = (isset( $_POST[ "ac_hint_panel_disable" ] ) && $_POST[ "ac_hint_panel_disable" ] == "Y") ? "1" : "0";
             update_user_meta( $userid, "ac_hint_panel_disable", $ac_hint_panel_disable );
             update_user_meta( $userid, "ac_script_init", $_POST[ "ac_script_init" ] );
             $form_message .= 'Settings saved.';
         }
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
                if ($posts_query->post->post_author == $userid) {
                    array_push( $posts_id, $posts_query->post->ID );
                }
            endwhile;

            $copyProtection = isset($_POST["ac_copyprotect"]) && $_POST["ac_copyprotect"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_copyprotect", $copyProtection);
            $paidRepost = isset($_POST["ac_paidrepost"]) && $_POST["ac_paidrepost"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_paidrepost", $paidRepost);
            $paidRepostCost = isset($_POST["ac_paidrepostcost"]) && is_numeric($_POST["ac_paidrepostcost"]) ? doubleval($_POST["ac_paidrepostcost"]) : 2.5;
            update_user_meta($userid, "ac_paidrepostcost", $paidRepostCost);
            $importComments = isset($_POST["ac_comments"]) && $_POST["ac_comments"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_is_import_comments", $importComments);

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
                paidRepost: {$paidRepost}, cost: {$paidRepostCost}, comments: {$importComments}}, function(d){
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
         echo $form_message_block;
?>
<form action="" method="POST">
<div class="wrap">
<div class="icon32" id="icon-tools"><br></div><h2>AtContent Settings</h2>
<div class="tool-box">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">    
<?php
         if ( strlen($ac_api_key) == 0 ) {
             $form_action = admin_url( 'admin-ajax.php' );
             ?>
<p style="max-width: 600px;">With AtContent plugin for Wordpress you can protect your publications from plagiarism, monetize reposts, increase search ranking for your site, track and manage your content across the Internet and even sell your premium articles, music and other content (available in February).</p>
<p>To start using AtContent you need to have an AtContent account, connected to your blog.</p>
<p>To connect your blog to AtContent, please press button below.</p>
<div id="ac_connect_result"></div>
<iframe id="ac_connect" src="https://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $form_action ?>" style="width:75px;height:40px;" border="0" scrolling="no"></iframe>
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) window.location.reload();
            else $("#ac_connect_result").html( 
                    'Something get wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
    })(jQuery);
</script>
<?php
         } else {
?>
<p>You have connected blog to AtContent as <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><?php echo $ac_pen_name; ?></a>.
<input type="hidden" name="ac_api_key" value="">
<span class="submit" style="padding-left: 2em;"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Disconnect') ?>" /></span>
</p>
<?php           
         }
?>
</div>
</div>
</form>

<?php 
if (strlen($ac_api_key) > 0) {
?>
<form action="" method="POST" name="import-form">
<div class="wrap">
<div class="icon32" id="icon-plugins"><br></div><h3 style="padding-top: 7px;margin-bottom:0;">Plugin activation for existing posts</h3>
<p style="padding: 0;margin: 0;">To activate the <a href="http://wordpress.org/extend/plugins/atcontent/" target="_blank">AtCotnent plugin features</a>
     for your existing articles, please choose options below and click on Import.</p>
    <?php 
             $ac_copyprotect = get_user_meta($userid, "ac_copyprotect", true );
             if (strlen($ac_copyprotect) == 0) $ac_copyprotect = "1";
             $ac_paidrepost = get_user_meta($userid, "ac_paidrepost", true );
             if (strlen($ac_paidrepost) == 0) $ac_paidrepost = "0";
             $ac_paidrepostcost = get_user_meta($userid, "ac_paidrepostcost", true );
             if (strlen($ac_paidrepostcost) == 0) $ac_paidrepostcost = "2.50";
             $ac_is_import_comments = get_user_meta($userid, "ac_is_import_comments", true );
             if (strlen($ac_is_import_comments) == 0) $ac_is_import_comments = "1";

             $ac_copyprotect_checked = $ac_copyprotect == "1" ? "checked=\"checked\"" : "";
             $ac_paidrepost_checked = $ac_paidrepost == "1" ? "checked=\"checked\"" : "";
             $ac_is_import_comments_checked = $ac_is_import_comments == "1" ? "checked=\"checked\"" : "";

             echo $form_script;
?>
<div class="tool-box">
    
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <input type="hidden" name="ac_import" value="Y">
    <p><input type="checkbox" name="ac_copyprotect" id="ac_copyprotect" value="Y" <?php echo $ac_copyprotect_checked ?>> Prevent copy action for all publications</p>
    <p><input type="checkbox" name="ac_paidrepost" id="ac_paidrepost" value="Y" <?php echo $ac_paidrepost_checked ?>> Turn on paid repost for all publications</p>
    Cost for paid repost, $<br>
    <input type="text" name="ac_paidrepostcost" id="ac_paidrepostcost" value="<?php echo $ac_paidrepostcost ?>"><br>
    * If you have professional, popular blog, we recommend you to set $20 price for repost.<br>
    <p><input type="checkbox" name="ac_comments" id="ac_comments" value="Y" <?php echo $ac_is_import_comments_checked ?>> Import post comments into AtContent plugin comments<br>
    * We recomend you to import comments into AtContent plugin and disable WordPress comments,<br>
    because the comments people leave on your posts appear on every site where posts are reposted.<br>
    Users on different sites will discuss your content in the comment section on their site and you will <br>
    collaborate with them all by replying on your site!</p>

    <span class="submit">
        <input type="submit" name="Submit" class="button button-primary" value="<?php esc_attr_e('Import') ?>" />
    </span>
</div>
</div>
</form>
<?php
    $ac_excerpt_image_remove = get_user_meta($userid, "ac_excerpt_image_remove", true );
    if (strlen($ac_excerpt_image_remove) == 0) $ac_excerpt_image_remove = "0";
    $ac_excerpt_no_process = get_user_meta($userid, "ac_excerpt_no_process", true );
    if (strlen($ac_excerpt_no_process) == 0) $ac_excerpt_no_process = "0";
    $ac_comments_disable = get_user_meta($userid, "ac_comments_disable", true );
    if (strlen($ac_comments_disable) == 0) $ac_comments_disable = "0";
    $ac_hint_panel_disable = get_user_meta($userid, "ac_hint_panel_disable", true );
    if (strlen($ac_hint_panel_disable) == 0) $ac_hint_panel_disable = "0";
    $ac_script_init = get_user_meta($userid, "ac_script_init", true );

    $ac_excerpt_image_remove_checked = "";
    if ($ac_excerpt_image_remove == "1") $ac_excerpt_image_remove_checked = "checked=\"checked\"";
    $ac_excerpt_no_process_checked = "";
    if ($ac_excerpt_no_process == "1") $ac_excerpt_no_process_checked = "checked=\"checked\"";
    $ac_comments_disable_checked = "";
    if ($ac_comments_disable == "1") $ac_comments_disable_checked = "checked=\"checked\"";
    $ac_hint_panel_disable_checked = "";
    if ($ac_hint_panel_disable == "1") $ac_hint_panel_disable_checked = "checked=\"checked\"";
    

?>
<form action="" method="POST">
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div><h3 style="padding-top: 7px;margin-bottom:0;">Advanced Settings</h3>
<p style="color:#f00;background:#fff;padding:0;margin:0;">You could have excerpts displaying problems on the main page. Fix it easy by the options below</p>

<div class="tool-box">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <input type="hidden" name="ac_advanced_settings" value="Y">
    <p><input type="checkbox" name="ac_excerpt_image_remove" value="Y" <?php echo $ac_excerpt_image_remove_checked ?>>
    Hide images in excerpts for AtContent processed posts (if you still have problems — use the option below)</p>
    <p><input type="checkbox" name="ac_excerpt_no_process" value="Y" <?php echo $ac_excerpt_no_process_checked ?>>
    Turn off plugin features for excerpts on your main page (don't worry, all features are working good for the articles pages, check it out)</p>
    <p><input type="checkbox" name="ac_comments_disable" value="Y" <?php echo $ac_comments_disable_checked ?>>
    Turn off plugin comments</p>
    <p><input type="checkbox" name="ac_hint_panel_disable" value="Y" <?php echo $ac_hint_panel_disable_checked ?>>
    Turn off line "Share  and repost and get $$$..."</p>
    <p>JavaScript Code for Plugin Init Script<br>
        <textarea rows="5" cols="80" name="ac_script_init"><?php echo $ac_script_init ?></textarea><br>
        * this code will run after AtContent widget load. If you have plugins that interact with your post content (like Lightbox, FancyBox, etc.) you should use this option.
        See <a href="<?php echo admin_url('admin.php?page=atcontent/atcontent_knownplugins.php') ?>">examples of known plugins</a>.
    </p>
     <span class="submit">
        <input type="submit" name="Submit" class="button button-primary" value="<?php esc_attr_e('Save changes') ?>" />
    </span>
</div>
</div>
</form>

<p>If you have some problems, ideas, feedback, questions — please <a href="http://atcontent.com/Support/">contact us</a>. We will use your help to make plugin better! :)</p>
<p>If you interested in plugin features description, please read it on <a href="http://wordpress.org/extend/plugins/atcontent/" target="_blank">AtCotnent plugin page</a></p>

<?php 
}

$form_action = admin_url( 'admin-ajax.php' );
?>
<script type="text/javascript">
    jQuery(function(){
        jQuery.post('<?php echo $form_action ?>', {action: 'atcontent_pingback'}, function(d){
            if (d.IsOK) {
            }
        }, "json");
    });
</script>

<?php
