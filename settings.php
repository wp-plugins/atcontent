<?php
    $atcontent_menu_section = "settings";
    
    require( "include/atcontent_userinit.php" );

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
        update_user_meta( $userid, "ac_adtest", $adTest);

        $siteCategory = isset( $_POST["ac_sitecategory"] ) ? $_POST["ac_sitecategory"] : "";
        update_user_meta($userid, "ac_sitecategory", $siteCategory);

        $country = isset( $_POST["ac_country"] ) ? $_POST["ac_country"] : "";
        update_user_meta($userid, "ac_country", $country);

        $state = isset( $_POST["ac_state"] ) ? $_POST["ac_state"] : "";
        update_user_meta($userid, "ac_state", $state);

        $referral = $_POST["ac_referral"];
        update_user_meta( $userid, "ac_referral", $referral );

        atcontent_api_sitecategory( site_url(), $siteCategory, $country, $state, $ac_api_key );

        $ac_share_panel_disable = isset( $_POST["ac_share_panel_disable"] ) && $_POST["ac_share_panel_disable"] == "Y" ? 1 : 0;
        update_user_meta( $userid, "ac_share_panel_disable", $ac_share_panel_disable );

        $ac_excerpt_no_process = ( isset( $_POST[ "ac_excerpt_no_process" ] ) && $_POST[ "ac_excerpt_no_process" ] == "Y" ) ? "1" : "0";
        update_user_meta( $userid, "ac_excerpt_no_process", $ac_excerpt_no_process );
        $ac_comments_disable = ( isset( $_POST[ "ac_comments_disable" ] ) && $_POST[ "ac_comments_disable" ] == "Y" ) ? "1" : "0";
        $ac_comments_disable = AC_NO_COMMENTS_DEFAULT;
        update_user_meta( $userid, "ac_comments_disable", $ac_comments_disable );
        $ac_hint_panel_disable = ( isset( $_POST[ "ac_hint_panel_disable" ] ) && $_POST[ "ac_hint_panel_disable" ] == "Y" ) ? "1" : "0";
        update_user_meta( $userid, "ac_hint_panel_disable", $ac_hint_panel_disable );
        if ( $_GET["afterconnect"] == "1" )
            $form_message .= '<div class="updated"><p><b>Settings saved.</b></p>' . 
            '<script type="text/javascript">window.location = \'' . admin_url("admin.php?page=atcontent/sync.php") . '&afterconnect=1\';</script>' .
            '</div>';
        else
            $form_message .= '<div class="updated"><p><b>Settings saved.</b></p>' . 
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
    <div class="b-cols">
        
    
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

    $ac_copyprotect_checked = $ac_copyprotect == "1" ? "checked" : "";
    $ac_adtest_checked = $ac_adtest == "1" ? "checked" : "";
    $ac_paidrepost_checked = $ac_paidrepost == "1" ? "checked" : "";
    $ac_is_import_comments_checked = $ac_is_import_comments == "1" ? "checked" : "";

    $ac_oneclick_repost = get_user_meta( $userid, "ac_oneclick_repost", true );
    if ( strlen( $ac_oneclick_repost ) == 0 ) $ac_oneclick_repost = "1";
    $ac_oneclick_repost_checked = $ac_oneclick_repost == "1" ? "checked" : "";

?>
    <div class="b-column">
<?php 
if ( $_GET["afterconnect"] == "1" ) {
    ?>
<div class="b-note success">
    Well done, now you have connected your blog with AtContent<br>
    Adjust site settings to get better results in promotion of your posts.
</div>
    <?php
}
?>
        <fieldset>
            <legend>Site Settings</legend>
            <table class="b-settings-table">
                <tr>
                    <th>Category</th>
                    <td>
                        <select name="ac_sitecategory">
<?php
    foreach ($atcontent_categories as $category => $description) {
        $category_selected = $ac_sitecategory == $category ? "selected" : "";
        echo <<<END
<option value="{$category}" {$category_selected}>{$description}</option>
END;
    }
?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Country</th>
                    <td>
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
                    </td>
                </tr>
                <tr id="ac_state">
                    <th>State</th>
                    <td>
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
                <tr>
                    <th>Referral (optional)</th>
                    <td><input type="text" name="ac_referral" value="<?php echo $ac_referral ?>"></td>
                </tr>
            </table>
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
        </fieldset>
    </div>
    <div class="b-column">
        <fieldset>
            <legend>Posts Settings</legend>
            <div class="b-checkbox-row">
                <label>
                    <input type="checkbox" name="ac_copyprotect" id="ac_copyprotect" value="Y" <?php echo $ac_copyprotect_checked ?>>
                    Prevent copy-paste of my posts
                </label>
            </div>
        </fieldset>
<?php
    $ac_excerpt_no_process = get_user_meta( $userid, "ac_excerpt_no_process", true );
    if ( strlen( $ac_excerpt_no_process ) == 0 ) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
    $ac_comments_disable = get_user_meta( $userid, "ac_comments_disable", true );
    if ( strlen( $ac_comments_disable ) == 0 ) $ac_comments_disable = AC_NO_COMMENTS_DEFAULT;
    $ac_comments_disable = AC_NO_COMMENTS_DEFAULT;
    $ac_hint_panel_disable = get_user_meta( $userid, "ac_hint_panel_disable", true );
    if (strlen($ac_hint_panel_disable) == 0) $ac_hint_panel_disable = "1";
    $ac_script_init = get_user_meta($userid, "ac_script_init", true );
    $ac_share_panel_disable = get_user_meta($userid, "ac_share_panel_disable", true );
    if ( strlen( $ac_share_panel_disable ) == 0 ) $ac_share_panel_disable = "0";

    $ac_excerpt_no_process_checked = "";
    if ($ac_excerpt_no_process == "1") $ac_excerpt_no_process_checked = "checked=\"checked\"";
    $ac_comments_disable_checked = "";
    if ( $ac_comments_disable == "1" ) $ac_comments_disable_checked = "checked=\"checked\"";
    $ac_hint_panel_disable_checked = "";
    if ( $ac_hint_panel_disable == "1" ) $ac_hint_panel_disable_checked = "checked=\"checked\"";
    $ac_share_panel_disable_checked = "";
    if ( $ac_share_panel_disable == "1" ) $ac_share_panel_disable_checked = "checked=\"checked\"";

?>

        <fieldset>
            <legend>Advanced Settings</legend>
            <div class="b-checkbox-row">
                <label>
                    <input type="checkbox" name="ac_excerpt_no_process" value="Y" <?php echo $ac_excerpt_no_process_checked ?>>
                    Turn off plugin features for the main page
                </label>
                <div class="ac-small">Should be marked for sites with not standard themes</div>
            </div>
            <div class="b-checkbox-row">
                <label>
                        <input type="checkbox" name="ac_share_panel_disable" value="Y" <?php echo $ac_share_panel_disable_checked ?>>
                        Turn off share buttons
                </label>
                <div class="ac-small">If you have a social share plugin on your blog you can turn off our share buttons to prevent duplication</div>
            </div>
        </fieldset>
    </div>        
    </div>

    <p>
        <button type="submit" class="button-color-orange" name="settingsSubmit" id="settingsSubmit"><?php esc_attr_e('Apply Settings') ?></button>
    </p>
</form>
</div>

<p style="margin-top: 80px;"><a href="http://wordpress.org/extend/plugins/atcontent/" target="_blank">AtContent plugin page</a> &nbsp; 
    <a href="http://atcontent.com/Support/" target="_blank">Support</a> &nbsp; 
    <a href="http://atcontent.com/About/" target="_blank">About AtContent</a> &nbsp; 
    <a href="http://atcontent.com/Privacy/" target="_blank">Privacy Policy</a> &nbsp; 
    <a href="http://atcontent.com/Terms/" target="_blank">Terms and Conditions</a> &nbsp; 
</p>

</div>

<script>
    (function($){
        $('label input[type!=checkbox][type!=radio]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });
        var changed = false,
            fields = {},
            type, val;
         $('#settings-form').on('submit', function () {
            changed = false;
        }).find('select, input, textarea').each(function () {
            type = this.type;
            switch (this.type) {
                case 'checkbox':
                case 'radio':
                    val = this.checked;
                    break;
                default:
                    val = this.value;
                    break;
            }
            fields[this.name] = {
                def: val,
                field: this
            }
            $(this).on('change', watchChanges);
        });
        $(window).on('beforeunload', function () {
            if (changed) return 'You have changed data in some fields. Do you really want to leave without saving?';
        });
        function watchChanges() {
            changed = false;
            var field;
            for (var i in fields) {
                field = fields[i].field;
                if (field.type == 'checkbox' || field.type == 'radio') {
                    if (fields[i].def != field.checked) {
                    changed = true;
                    break;
                    }
                } else {
                    if (fields[i].def != fields[i].field.value) {
                        changed = true;
                        break;
                    }
                }
            }
        }
    })(jQuery);
</script>