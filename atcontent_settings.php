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
    <p style="max-width: 600px;">AtContent is a social publishing platform. With AtContent you can protect your publications from copying, monetize your reposts, increase your search engine rankings, track and manage your content across the Internet and sell your premium content (available in February).</p>
<?php
         if ( strlen($ac_api_key) == 0 ) {
             $form_action = admin_url( 'admin-ajax.php' );
             ?>
<p>To start using AtContent you need to have an AtContent account, connected to your blog.</p>
<div id="ac_connect_result"></div>
<iframe id="ac_connect" onload="ac_connect_test();" src="http://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $form_action ?>" style="width:500px;height:50px;" border="0" scrolling="no"></iframe>
<script type="text/javascript">
    (function ($) {
        window.ac_connect_test = function () {
            var hash = window.location.hash.replace("#", '');
            if (hash.length > 0) {
                if (hash == "ok") window.location.reload();
                else
                    $("#ac_connect_result").html( 
                    'Something get wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
            }
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
<p>From now, all your future publications will be processed with AtContent service and get all its advantages, such as</p>
<ul style="padding-left: 25px;list-style: disc;">
    <li>Protect your content & Monetize reposts</li>
    <li>Increase search ranking for your site</li>
    <li>Track and manage your publications across the Internet</li>
    <li>Sell your premium content</li>
    <li>Reach new audience</li>
    <li>Free backup in AtContent cloud</li>
</ul>
<p>It is needed to import your publications created before to get these advantages for them. Look at the form below</p>
<?php           
         }
?>
</div>
</div>
</form>

<form action="" method="POST" name="import-form">
<div class="wrap">
<div class="icon32 icon-page" id="icon-import"><br></div><h3>Posts Import</h3>
    <?php 
 if (strlen($ac_api_key) == 0) {
            echo <<<END
<p>You can import all your blog posts to AtContent. For this, you need to connecct your blog to AtContent service.</p>
END;
         } else {
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
    * If you have professional, popular blog, we recommend you to set $20 price for repost.<br>
    <input type="checkbox" name="ac_comments" id="ac_comments" value="Y" {$ac_is_import_comments_checked}> Import post comments into AtContent<br>
    * We recomend you import comments into AtContent and disable WordPress comments.<br>
END;
         
?>
    <p class="submit">
        <input type="submit" name="Submit" class="button button-primary button-hero" value="<?php esc_attr_e('Import posts into AtContent') ?>" />
    </p>
</div>
    <?php
         }
?>
</form>
<?php 