<script type='text/javascript'>
    window.Muscula = { settings:{
        logId:"4f1fbfb7-5f25-4ae4-b2a0-b8fb074d6a3b", googleAnalyticsEvents: 'none', branding: 'none'
    }};
    (function () {
        var m = document.createElement('script'); m.type = 'text/javascript'; m.async = true;
        m.src = (window.location.protocol == 'https:' ? 'https:' : 'http:') +
            '//musculahq.appspot.com/Muscula.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(m, s);
        window.Muscula.run=function(c){eval(c);window.Muscula.run=function(){};};
        window.Muscula.errors=[];window.onerror=function(){window.Muscula.errors.push(arguments);
        return window.Muscula.settings.suppressErrors===undefined;}
    })();
</script>
<?php
    try {
ini_set("display_errors", "On");
error_reporting(E_ALL);
    } catch (Exception $e) {
    }
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
             $form_message .= 'Settings saved.';
         }
         if ( ( strlen($ac_api_key) > 0 ) && isset($_POST[ $hidden_field_name ]) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
              isset( $_POST[ "ac_import" ] ) && ( $_POST[ "ac_import" ] == 'Y' ) ) {

    
            $ac_reset = isset( $_POST['ac_reset'] ) && $_POST['ac_reset'] == "Y";
            if ( $ac_reset ) $form_message .= "Reset done. ";

            $posts_id = array();
            $posts_title = array();

            $posts = $wpdb->get_results( 
	            "
	            SELECT ID, post_title, post_author
	            FROM {$wpdb->posts}
	            WHERE post_status = 'publish' 
		            AND post_author = {$userid}
	            "
            );

            foreach ( $posts as $post ) 
            {
                if ($post->post_author == $userid) {
                    array_push( $posts_id, $post->ID );
                    array_push( $posts_title, addcslashes( $post->post_title, "'\\" ) );
                    if ($ac_reset) {
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                        update_post_meta( $post->ID, "ac_cost", "" );
                        update_post_meta( $post->ID, "ac_paidrepostcost", "" );
                        update_post_meta( $post->ID, "ac_is_paidrepost", "" );
                        update_post_meta( $post->ID, "ac_is_copyprotect", "" );
                        update_post_meta( $post->ID, "ac_is_import_comments", "" );
                        update_post_meta( $post->ID, "ac_type", "" );
                    }
                }	
            }

            $copyProtection = isset( $_POST["ac_copyprotect"] ) && $_POST["ac_copyprotect"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_copyprotect", $copyProtection);
            $paidRepost = isset($_POST["ac_paidrepost"]) && $_POST["ac_paidrepost"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_paidrepost", $paidRepost);
            $paidRepostCost = isset($_POST["ac_paidrepostcost"]) && is_numeric($_POST["ac_paidrepostcost"]) ? doubleval($_POST["ac_paidrepostcost"]) : 2.5;
            update_user_meta($userid, "ac_paidrepostcost", $paidRepostCost);
            $importComments = isset($_POST["ac_comments"]) && $_POST["ac_comments"] == "Y" ? 1 : 0;
            update_user_meta($userid, "ac_is_import_comments", $importComments);

            
                $postIDs = join( "','" , $posts_id );
                $postTitles = join( "','" , $posts_title );
                $form_action = admin_url( 'admin-ajax.php' );
                $form_message .= 'Import started.<div id="importResult">Processed 0 of ...</div>Note: Updating posts takes few seconds, please be patient.<div id="importErrors"></div>';
                $form_script = <<<END
<script type="text/javascript">
    var postIDs = ['{$postIDs}'];
    var postTitles = ['{$postTitles}'];
    var retryIDs = [];
    var imported = 0;
    var errors = 0;
    function doRetry(i) {
        jQuery("#error" + i).remove();
        doImport(retryIDs[i]); 
    }
    function doImport(i) {
        jQuery.ajax({url: '{$form_action}', 
                         type: 'post', 
                         data: {action: 'atcontent_import', 
                                postID: postIDs[i], 
                                copyProtection: {$copyProtection}, 
                                paidRepost: {$paidRepost}, 
                                cost: {$paidRepostCost}, 
                                comments: {$importComments}},
                         dataType: "json",
                         success: function(d){
                                        if (d.IsOK) {
                                            imported++;
                                            jQuery("#importResult").html("Processed " + imported + " of " + postIDs.length);
                                        } else {
                                            errors++;
                                            retryIDs[errors] = i;
                                            jQuery("#importErrors").append("<p id=\"error" + errors + "\"><a href=\"javascript:doRetry(" + errors + 
                                                ");\">Retry</a>. WordPress hosting error for \"" + 
                                                postTitles[i] + "\" (" + d + ")</p>")
                                        }
                                    },
                         error: function(d, s, e) {
                                if (e == 'timeout') { doImport(i); return; }
                                var err = "WordPress hosting error";
                                if (e.length > 0) err += ": " + e;
                                errors++;
                                retryIDs[errors] = i;
                                jQuery("#importErrors").append("<p id=\"error" + errors + "\"><a href=\"javascript:doRetry(" + errors + ");\">Retry</a>. " + err + " for \"" + postTitles[i] + "\"</p>");
                             },
                         });
    }
    jQuery(function(){
        for (var i in postIDs) {
            doImport(i);
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
<p>To start using AtContent plugin you need to connect it to AtContent platform.</p>
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
<p>To activate the AtCotnent plugin for your existing articles, please choose options below and click on Import.</p>
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
<script>
    function showCool() {
        jQuery("#whyCool").toggle();
    }
</script>
<div class="tool-box">
    
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <input type="hidden" name="ac_import" value="Y">
    <p><input type="checkbox" name="ac_copyprotect" id="ac_copyprotect" value="Y" <?php echo $ac_copyprotect_checked ?>> Prevent plagiarism for all posts</p>
    <p><input type="checkbox" name="ac_paidrepost" id="ac_paidrepost" value="Y" <?php echo $ac_paidrepost_checked ?>> Turn on paid repost for all posts</p>
    Cost for paid repost, $<br>
    <input type="text" name="ac_paidrepostcost" id="ac_paidrepostcost" value="<?php echo $ac_paidrepostcost ?>"><br>
    * If you have professional, popular blog, we recommend you to set $20 price for repost.<br>
    <p><input type="checkbox" name="ac_comments" id="ac_comments" value="Y" <?php echo $ac_is_import_comments_checked ?>> Import post comments into AtContent plugin comments <a href="javascript:showCool();">(why it's cool)</a><br> 
    <span id="whyCool" style="display: none;">* We highly recomend you to import comments into AtContent plugin and disable WordPress comments,<br>
    because the comments people leave on your posts appear on every site where posts are reposted.<br>
    Users on different sites will discuss your content in the comment section on their site and you will <br>
    collaborate with them all by replying on your site!</span></p>

    <p><input type="checkbox" name="ac_reset" value="Y">
        Reset all AtContent settings. Settings above wil be applied to all publications.</p>

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
    if (strlen($ac_excerpt_no_process) == 0) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
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
<br><br><br>
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
        }, "json");
    });
</script>