<?php
    $atcontent_menu_section = "settings";
    
    require( "atcontent_userinit.php" );

    $hidden_field_name = 'ac_submit_hidden';
    $form_message = '';
    $img_url = plugins_url( 'assets/logo.png', __FILE__ );

    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/connect.php" );
        ?>
<script>window.location = '<?php echo $connect_url; ?>';</script>
        <?php
    }

    // PingBack
    if ( ! atcontent_pingback_inline() ) {
        echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
    }
    //End PingBack

    if ( ( strlen($ac_api_key) > 0 ) && isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
        isset( $_POST[ "ac_settings" ] ) && ( $_POST[ "ac_settings" ] == 'Y' ) ) {
            
        $copyProtection = isset( $_POST["ac_copyprotect"] ) && $_POST["ac_copyprotect"] == "Y" ? 1 : 0;
        update_user_meta($userid, "ac_copyprotect", $copyProtection);

        $adTest = isset( $_POST["ac_adtest"] ) && $_POST["ac_adtest"] == "Y" ? 1 : 0;
        update_user_meta($userid, "ac_adtest", $adTest);

        $siteCategory = isset( $_POST["ac_sitecategory"] ) ? $_POST["ac_sitecategory"] : "";
        update_user_meta($userid, "ac_sitecategory", $siteCategory);

        $country = isset( $_POST["ac_country"] ) ? $_POST["ac_country"] : "";
        update_user_meta($userid, "ac_country", $country);

        $state = isset( $_POST["ac_state"] ) ? $_POST["ac_state"] : "";
        update_user_meta($userid, "ac_state", $state);

        $referral = $_POST["ac_referral"];
        update_user_meta( $userid, "ac_referral", $referral );

        atcontent_api_sitecategory( site_url(), $siteCategory, $country, $state, $ac_api_key );

        $paidRepost = isset($_POST["ac_paidrepost"]) && $_POST["ac_paidrepost"] == "Y" ? 1 : 0;
        update_user_meta( $userid, "ac_paidrepost", $paidRepost );
        $paidRepostCost = isset( $_POST["ac_paidrepostcost"] ) && is_numeric( $_POST["ac_paidrepostcost"] ) ? doubleval( $_POST["ac_paidrepostcost"] ) : 2.5;
        update_user_meta( $userid, "ac_paidrepostcost", $paidRepostCost );
        $importComments = isset( $_POST["ac_comments"] ) && $_POST["ac_comments"] == "Y" ? 1 : 0;
        update_user_meta( $userid, "ac_is_import_comments", $importComments );

        $ac_with_import = isset( $_POST['ac_with_import'] ) && $_POST['ac_with_import'] == "Y";

        $ac_excerpt_image_remove = ( isset( $_POST[ "ac_excerpt_image_remove" ] ) && $_POST[ "ac_excerpt_image_remove" ] == "Y" ) ? "1" : "0";
        update_user_meta( $userid, "ac_excerpt_image_remove", $ac_excerpt_image_remove );
        $ac_excerpt_no_process = ( isset( $_POST[ "ac_excerpt_no_process" ] ) && $_POST[ "ac_excerpt_no_process" ] == "Y" ) ? "1" : "0";
        update_user_meta( $userid, "ac_excerpt_no_process", $ac_excerpt_no_process );
        $ac_comments_disable = ( isset( $_POST[ "ac_comments_disable" ] ) && $_POST[ "ac_comments_disable" ] == "Y" ) ? "1" : "0";
        update_user_meta( $userid, "ac_comments_disable", $ac_comments_disable );
        $ac_hint_panel_disable = ( isset( $_POST[ "ac_hint_panel_disable" ] ) && $_POST[ "ac_hint_panel_disable" ] == "Y" ) ? "1" : "0";
        update_user_meta( $userid, "ac_hint_panel_disable", $ac_hint_panel_disable );
        $form_message .= '<div class="updated"><p><strong>Settings saved.</strond></p>' . 
        '<p><a href="' . admin_url("admin.php?page=atcontent/sync.php") . '">Follow Sync section</a></p>' .
        '</div>';
    }

?>

<div class="atcontent_wrap">

<?php include("settings_menu.php"); ?>

<?php if ( strlen ( $form_message ) > 0 ) { echo $form_message; } ?>

<div class="wrap">
    <form action="" method="POST" name="settings-form" id="settings-form">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <input type="hidden" name="ac_settings" value="Y">
    <div class="b-dashboard">
        
    
<?php 
    $ac_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
    if (strlen($ac_copyprotect) == 0) $ac_copyprotect = "1";

    $ac_sitecategory = get_user_meta( $userid, "ac_sitecategory", true );
    $ac_country = get_user_meta( $userid, "ac_country", true );
    $ac_state = get_user_meta( $userid, "ac_state", true );

    $ac_adtest = get_user_meta( $userid, "ac_adtest", true );
    if (strlen($ac_adtest) == 0) $ac_adtest = "1";
             
    $ac_paidrepost = get_user_meta($userid, "ac_paidrepost", true );
    if (strlen($ac_paidrepost) == 0) $ac_paidrepost = "0";
    $ac_paidrepostcost = get_user_meta($userid, "ac_paidrepostcost", true );
    if (strlen($ac_paidrepostcost) == 0) $ac_paidrepostcost = "2.50";
    $ac_is_import_comments = get_user_meta($userid, "ac_is_import_comments", true );
    if (strlen($ac_is_import_comments) == 0) $ac_is_import_comments = "1";

    $ac_referral = get_user_meta( $userid, "ac_referral", true );

    $ac_copyprotect_checked = $ac_copyprotect == "1" ? "checked=\"checked\"" : "";
    $ac_adtest_checked = $ac_adtest == "1" ? "checked=\"checked\"" : "";
    $ac_paidrepost_checked = $ac_paidrepost == "1" ? "checked=\"checked\"" : "";
    $ac_is_import_comments_checked = $ac_is_import_comments == "1" ? "checked=\"checked\"" : "";

?>
<script>
    function saveForm() {
        jQuery("#settings-form").submit();
    }
</script>
    
    <h2>Site Settings</h2>
        <div class="b-dashboard-table">
            <table>
                <tr><th>Category</th><td>
                     <select name="ac_sitecategory">
<?php
    foreach ($atcontent_categories as $category => $description) {
        $category_selected = $ac_sitecategory == $category ? "selected=\"selected\"" : "";
        echo <<<END
<option value="{$category}" {$category_selected}>{$description}</option>
END;
    }
?>
                    </select>
                </td></tr>
                <tr><th>Country</th><td>
                    <select id="ac_country" name="ac_country">
<?php
    foreach ($atcontent_countries as $code => $description) {
        $item_selected = $ac_country == $code ? "selected=\"selected\"" : "";
        echo <<<END
<option value="{$code}" {$item_selected}>{$description}</option>
END;
    }
?>
                    </select>
                </td></tr>
                <tr id="ac_state" style="display: none;"><th>State</th><td>
                    <select name="ac_state">
<?php
    foreach ($atcontent_states as $code => $description) {
        $item_selected = $ac_state == $code ? "selected=\"selected\"" : "";
        echo <<<END
<option value="{$code}" {$item_selected}>{$description}</option>
END;
    }
?>
        </select>
                </td>
                </tr>
                <tr><th>Referral (optional)</th><td><input type="text" name="ac_referral" value="<?php echo $ac_referral ?>"></td></tr>
            </table>
        </div>
<script type="text/javascript">
    var ac_j = jQuery;
    function ac_checkCountry() {
        var c = ac_j("#ac_country").val();
        if (c == "US") { ac_j("#ac_state").show(); } else { ac_j("#ac_state").hide(); }
    }
    ac_j(function () {
        ac_checkCountry();
        ac_j("#ac_country").change(ac_checkCountry);
    });
</script>
        <h2>Posts Settings</h2>
        <div class="b-dashboard-table">
            <table>
                <tr><th><input type="checkbox" name="ac_copyprotect" id="ac_copyprotect" value="Y" <?php echo $ac_copyprotect_checked ?>> Prevent plagiarism of my posts</th></tr>
                <tr style="display: none;"><th><input type="checkbox" name="ac_paidrepost" id="ac_paidrepost" value="Y" <?php echo $ac_paidrepost_checked ?>> Paid repost. People will pay $
    <input type="text" name="ac_paidrepostcost" id="ac_paidrepostcost" value="<?php echo $ac_paidrepostcost ?>"> for reposting my posts to other sites.</th></tr>
            </table>
        </div>

<?php
    $ac_excerpt_image_remove = get_user_meta($userid, "ac_excerpt_image_remove", true );
    if (strlen($ac_excerpt_image_remove) == 0) $ac_excerpt_image_remove = "0";
    $ac_excerpt_no_process = get_user_meta($userid, "ac_excerpt_no_process", true );
    if (strlen($ac_excerpt_no_process) == 0) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
    $ac_comments_disable = get_user_meta($userid, "ac_comments_disable", true );
    if (strlen($ac_comments_disable) == 0) $ac_comments_disable = AC_NO_COMMENTS_DEFAULT;
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

        <h2>Advanced Settings (optional)</h2>
        <div class="b-dashboard-table">
            <table>
                <tr><th><input type="checkbox" name="ac_excerpt_no_process" value="Y" <?php echo $ac_excerpt_no_process_checked ?>>
    Turn off plugin features for the main page (should be marked for sites with not standard themes)</th></tr>
                <tr style="display: none;"><th><input type="checkbox" name="ac_comments_disable" value="Y" <?php echo $ac_comments_disable_checked ?>>
    Turn off plugin comments</th></tr>
            </table>
        </div>
        <br class="nowrap">
    
        <a href="javascript:saveForm();" class="likebutton b_orange b_big"><?php esc_attr_e('Apply Settings') ?></a>

    </div>
</form>
</div>


<br><br><br>
<p><a href="http://wordpress.org/extend/plugins/atcontent/" target="_blank">AtCotnent plugin page</a> &nbsp; 
    <a href="http://atcontent.com/Support/" target="_blank">Support</a> &nbsp; 
    <a href="http://atcontent.com/About/" target="_blank">About AtContent</a> &nbsp; 
    <a href="http://atcontent.com/Privacy/" target="_blank">Privacy Policy</a> &nbsp; 
    <a href="http://atcontent.com/Terms/" target="_blank">Terms and Conditions</a> &nbsp; 
</p>

</div>