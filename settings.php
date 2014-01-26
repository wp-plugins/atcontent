<?php
    
    $ajax_action = admin_url( 'admin-ajax.php' );
    require( "include/atcontent_userinit.php" );

    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/dashboard.php" );
        ?>
<script>window.location = '<?php echo $connect_url; ?>';</script>
        <?php
    }

    // PingBack
    if ( ! atcontent_pingback_inline() ) {
        echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
    }
    //End PingBack
?>


    <form action="" method="POST" name="settings-form" id="settings-form" style="clear: both">
    <div class="b-cols">
        
    
<?php 
    $ac_sitecategory = get_user_meta( $userid, "ac_sitecategory", true );
    $ac_country = get_user_meta( $userid, "ac_country", true );
    $ac_state = get_user_meta( $userid, "ac_state", true );
?>
    <div>
<?php 
if ( $_GET["afterconnect"] == "1" ) {
    ?>
<div class="b-note success">
    Well done, now you have connected your blog with AtContent<br>
</div>
    <?php
}
?>
        <fieldset>
            <?php if ( user_can( $userid, "manage_options" ) ) { ?>
                <legend style="float: left">Site Settings</legend>
            <?php } else if ( user_can( $userid, "publish_posts" ) ) { ?>
                <legend>Profile Settings</legend>
            <?php } else { ?>
                <legend>Profile Settings</legend>
            <?php } ?>
            <table class="b-settings-table">
                <tr>
                    <th>Category</th>
                    <td>
                        <select id="ac_sitecategory" name="ac_sitecategory">
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
    echo ("<option value=\"US\">United States</option>");
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
                        <select id="ac_state_sel" name="ac_state">
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
                <!--
                <tr>
                    <th>Referral (optional)</th>
                    <td><input type="text" name="ac_referral" value="<?php echo $ac_referral ?>"></td>
                </tr>
                -->
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
<?php
    $ac_excerpt_no_process = get_user_meta( $userid, "ac_excerpt_no_process", true );
    if ( strlen( $ac_excerpt_no_process ) == 0 ) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
    $ac_share_panel_disable = get_user_meta($userid, "ac_share_panel_disable", true );
    if ( strlen( $ac_share_panel_disable ) == 0 ) $ac_share_panel_disable = "1";

    $ac_excerpt_no_process_checked = "";
    if ($ac_excerpt_no_process == "0") $ac_excerpt_no_process_checked = "checked=\"checked\"";
    $ac_share_panel_disable_checked = "";
    if ( $ac_share_panel_disable == "0" ) $ac_share_panel_disable_checked = "checked=\"checked\"";

?>

            <fieldset>
                <legend>Display settings</legend>
                <div class="b-checkbox-row">
                    <label>
                        <input type="checkbox" id="ac_excerpt_no_process" name="ac_excerpt_no_process" value="Y" <?php echo $ac_excerpt_no_process_checked ?>>
                        Enable plugin features for the home page
                    </label>
                    <div class="ac-small">Should be unmarked for sites with custom themes</div>
                </div>
                <div class="b-checkbox-row">
                    <label>
                        <input type="checkbox" id="ac_share_panel_disable" name="ac_share_panel_disable" value="Y" <?php echo $ac_share_panel_disable_checked ?>>
                        Enable share buttons
                    </label>
                    <div class="ac-small">Enable Facebook, Twitter, Linkedin and Google+ buttons under each post </div>
                </div>
            </fieldset>
        </div>        
    <p>
        <a id="b_save" class="likebutton b_orange" style="float: left" onclick="submit_settings()">Apply Settings</a>        
    </p>
        <div style="display: none;margin-top: 0px;height: 14px;margin-left: 20px;margin-top: -3px;" class="update-nag" id="settings_saved"></div>
    </div>

</form>

<script>
    function submit_settings() {
        jQuery("#b_save").removeClass('b_orange').addClass('b_enable');
        var ac_excerpt_no_process = document.getElementById('ac_excerpt_no_process').checked?'y':'n';
        var ac_share_panel_disable = document.getElementById('ac_share_panel_disable').checked?'y':'n';
        var ac_sitecategory = jQuery('#ac_sitecategory').val();
        var ac_country = jQuery('#ac_country').val();
        var ac_state = jQuery('#ac_state_sel').val();
        jQuery.ajax({
            url: '<?php echo $ajax_action; ?>',
            type: 'post',
            data: 
            {
                action : 'atcontent_save_settings',
                ac_sitecategory: ac_sitecategory,
                ac_country :ac_country,
                ac_state: ac_state,
                ac_excerpt_no_process: ac_excerpt_no_process,
                ac_share_panel_disable: ac_share_panel_disable
            },
            success: function (d) {
                jQuery("#b_save").addClass('b_orange').removeClass('b_enable');
                if (d.IsOK) 
                {
                    jQuery("#settings_saved").show();
                    jQuery("#settings_saved").html('Saved!');
                    setTimeout('jQuery("#settings_saved").hide()', 5000); 
                    if (isFirstTime)
                    {
                        third_welcome_step();
                    }                   
                }
                else {
                    jQuery("#ac_connect_result").html(
                                'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');

                }
            },
            dataType: "json"
        });
    }

    (function ($) {
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
<?php atcontent_ga("SettingsTab", "Settings page"); ?>