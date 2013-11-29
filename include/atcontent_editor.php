<?php

    function atcontent_add_meta_boxes() {

        add_meta_box(
            'atcontent_sectionid',
            __( 'AtContent Post Settings', 'atcontent_textdomain' ),
            'atcontent_inner_custom_box',
            'post', 'side', 'high'
        );

        $version = get_bloginfo('version');
        if ( version_compare( $version, '3.3', '>=' ) ) {
            add_meta_box(
                'atcontent_secondeditor',
                __( 'AtContent Paid Portion', 'atcontent_textdomain' ),
                'atcontent_paid_portion',
                'post'
            );
        }
        
    }

    function atcontent_inner_custom_box( $post ) {
          // Use nonce for verification
          wp_nonce_field( plugin_basename( __FILE__ ), 'atcontent_noncename' );

          $userid = $post->post_author;
          $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
          if ( strlen( $ac_api_key ) == 0 ) return;

          $ac_pen_name = get_user_meta( $userid, "ac_pen_name", true );
          $ac_show_name = get_user_meta( $userid, "ac_showname", true );
          $ac_avatar_20 = get_user_meta( $userid, "ac_avatar_20", true );
          $ac_avatar_80 = get_user_meta( $userid, "ac_avatar_80", true );
          $ac_avatar_200 = get_user_meta( $userid, "ac_avatar_200", true );
          if ( strlen( $ac_avatar_20 ) == 0 ) {
              $ac_avatar_20 = "https://atcontent.blob.core.windows.net/avatar/{$ac_pen_name}/20-0.jpg";
          }
          if ( strlen( $ac_avatar_80 ) == 0 ) {
              $ac_avatar_80 = "https://atcontent.blob.core.windows.net/avatar/{$ac_pen_name}/80-0.jpg";
          }
          if ( strlen( $ac_avatar_200 ) == 0 ) {
              $ac_avatar_200 = "https://atcontent.blob.core.windows.net/avatar/{$ac_pen_name}/200-0.jpg";
          }

          $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
          $ac_is_process_checked = "";
          if ( $ac_is_process == "1" || $ac_is_process == "" ) {
              $ac_is_process_checked = "checked=\"checked\"";
          }

          $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
          $ac_user_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
          if ( strlen( $ac_user_copyprotect ) == 0 ) $ac_user_copyprotect = "1";
          $ac_user_paidrepost = get_user_meta( $userid, "ac_paidrepost", true );
          if ( strlen( $ac_user_paidrepost ) == 0 ) $ac_user_paidrepost = "0";
          $ac_user_paidrepostcost = get_user_meta( $userid, "ac_paidrepostcost", true );
          if ( strlen( $ac_user_paidrepostcost ) == 0 ) $ac_user_paidrepostcost = "2.50";
          $ac_user_is_import_comments = get_user_meta( $userid, "ac_is_import_comments", true );
          if ( strlen( $ac_user_is_import_comments ) == 0 ) $ac_user_is_import_comments = "1";

          $ac_is_copyprotect = get_post_meta( $post->ID, "ac_is_copyprotect", true );
          if ( strlen( $ac_is_copyprotect ) == 0 ) $ac_is_copyprotect = $ac_user_copyprotect;
          $ac_is_copyprotect_checked = "";
          if ( $ac_is_copyprotect == "1" ) {
              $ac_is_copyprotect_checked = "checked=\"checked\"";
          }

          $ac_is_advanced_tracking = get_post_meta( $post->ID, "ac_is_advanced_tracking", true );
          if ( strlen( $ac_is_advanced_tracking ) == 0 ) $ac_is_advanced_tracking = "1";
          $ac_is_advanced_tracking_checked = "";
          if ( $ac_is_advanced_tracking == "1" ) {
              $ac_is_advanced_tracking_checked = "checked=\"checked\"";
          }

          $ac_is_paidrepost = get_post_meta( $post->ID, "ac_is_paidrepost", true );
          if ( strlen( $ac_is_paidrepost ) == 0 ) $ac_is_paidrepost = $ac_user_paidrepost;
          $ac_is_paidrepost_checked = "";
          if ($ac_is_paidrepost == "1") {
              $ac_is_paidrepost_checked = "checked=\"checked\"";
          }

          $ac_is_import_comments = get_post_meta( $post->ID, "ac_is_import_comments", true );
          if ( strlen( $ac_is_import_comments ) == 0 ) $ac_is_import_comments = $ac_user_is_import_comments;
          $ac_is_import_comments_checked = "";
          if ($ac_is_import_comments == "1") {
              $ac_is_import_comments_checked = "checked=\"checked\"";
          }

          $ac_paidrepost_cost = get_post_meta( $post->ID, "ac_paidrepost_cost", true );
          if ($ac_paidrepost_cost == "") { $ac_paidrepost_cost = $ac_user_paidrepostcost; }
          if ($ac_paidrepost_cost == "") { $ac_paidrepost_cost = "2.50"; }

          $ac_cost = get_post_meta($post->ID, "ac_cost", true);
          if ($ac_cost == "") $ac_cost = $ac_paidrepost_cost;

          $ac_type = get_post_meta( $post->ID, "ac_type", true );
          if ($ac_type == "") {
              if ($ac_is_paidrepost == "1") $ac_type = "paidrepost";
              else $ac_type = "free";
          }

          $ac_type_free_selected = ($ac_type == "free") ? "selected=\"selected\"" : "";
          $ac_type_paidrepost_selected = ($ac_type == "paidrepost") ? "selected=\"selected\"" : "";
          $ac_type_donate_selected = ($ac_type == "donate") ? "selected=\"selected\"" : "";
          $ac_type_paid_selected = ($ac_type == "paid") ? "selected=\"selected\"" : "";
          
          $plagiarism_quota = 0;
          $advanced_tracking_quota = 0;
          $quotas_result = atcontent_api_get_quotas ( $ac_api_key );
          $subscriptions_count = 0;
          if ( $quotas_result["IsOK"] == TRUE ) {
              $subscriptions_count = count ( $quotas_result["Subscriptions"] );
              $plagiarism_quota = intval( $quotas_result["Quotas"]["PlagiarismProtection"]["Count"] );
              $advanced_tracking_quota = intval( $quotas_result["Quotas"]["DetailedStat"]["Count"] );
          }

          $ac_is_copyprotect_enabled = $plagiarism_quota > 0;
          $ac_is_advanced_tracking_enabled = $advanced_tracking_quota > 0;

          ?>
<script type="text/javascript">
    (function ($) {
        $(function () {
            $("#atcontent_type").change(function () {
                ac_type_init($(this).val());
            });
            ac_type_init('<?php echo $ac_type ?>');
        });
        window.ac_type_init = function (val) {
            $("#atcontent_cost").hide();
            $("#atcontent_secondeditor").hide();
            if (val == 'paid' || val == 'paidrepost') {
                $("#atcontent_cost").show();
            }
            if (val == 'paid') {
                $("#atcontent_secondeditor").show();
            }
        };
    })(jQuery)
</script>
<div class="misc-pub-section"><label><input type="checkbox" id="atcontent_is_process" name="atcontent_is_process" value="1" <?php echo $ac_is_process_checked; ?> /> Use AtContent for this post</label>
<br>as <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><img style="vertical-align: middle; margin-right: .3em" 
            src="<?php echo $ac_avatar_20; ?>" alt=""><?php echo $ac_show_name; ?></a>
</div>
<div class="misc-pub-section"><label><input type="checkbox" id="atcontent_is_copyprotect" name="atcontent_is_copyprotect" value="1" <?php echo $ac_is_copyprotect_checked; ?> <?php echo $ac_is_copyprotect_enabled ? '' : 'disabled="disabled"'; ?> > Protect post from copy-paste</label><br>Available posts: <?php echo $plagiarism_quota; ?>.
<?php if ($ac_is_copyprotect_enabled == false) { ?> 
<?php if ( $subscriptions_count == 0 ) { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">choose a suitable plan</a>
    <?php } else { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">upgrade your subscription</a> or wait for the next month
    <?php } ?>
<?php } ?>
<input type="hidden" name="atcontent_is_copyprotect_enabled" value="<?php echo $ac_is_copyprotect_enabled ? "1" : "0"; ?>">
</div>
<div class="misc-pub-section"><label><input type="checkbox" id="atcontent_is_advanced_tracking" name="atcontent_is_advanced_tracking" value="1" <?php echo $ac_is_advanced_tracking_checked; ?> <?php echo $ac_is_advanced_tracking_enabled ? '' : 'disabled="disabled"'; ?> > Enable advanced statistics</label><br>Available posts: <?php echo $advanced_tracking_quota; ?>.
<?php if ( $ac_is_advanced_tracking_enabled == false ) { ?> 
    <?php if ( $subscriptions_count == 0 ) { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">choose a suitable plan</a>
    <?php } else { ?>
<br>To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">upgrade your subscription</a> or wait for the next month
    <?php } ?>
<?php } ?>
<input type="hidden" name="atcontent_is_advanced_tracking_enabled" value="<?php echo $ac_is_advanced_tracking_enabled ? "1" : "0"; ?>">
</div>
<div class="misc-pub-section">
    Post type:
<select name="atcontent_type" id="atcontent_type">
    <option value="free" <?php echo $ac_type_free_selected; ?>>Free</option>
    <option value="paidrepost" <?php echo $ac_type_paidrepost_selected; ?>>Paid repost</option>
    <option value="donate" <?php echo $ac_type_donate_selected; ?>>Donate</option>
    <option value="paid" <?php echo $ac_type_paid_selected; ?>>Paid</option>
</select>
</div>
<div class="misc-pub-section" id="atcontent_cost">
<label for="atcontent_paidrepost_cost">Cost, $</label> <input type="text" name="atcontent_cost" value="<?php echo $ac_cost ?>" size="10" /><br>
</div>
<div class="misc-pub-section"><label><input type="checkbox" id="atcontent_is_import_comments" name="atcontent_is_import_comments" value="1" <?php echo $ac_is_import_comments_checked?> /> Import post comments into AtContent</label></div>
<?php
        if ( strlen( $ac_postid ) > 0 ) {
        ?>
<div class="misc-pub-section">
<a href="<?php echo atcontent_get_statistics_link( $post->ID ); ?>" target="_blank">View statistics</a>
</div>
        <?php
        }
    }

    function atcontent_paid_portion( $post ) {

        $userid = $post->post_author;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        if ( strlen( $ac_api_key ) == 0 ) return;

        // Use nonce for verification
        $args = array(
            'wpautop' => 1
            ,'media_buttons' => 1
            ,'textarea_name' => 'ac_paid_portion'
            ,'textarea_rows' => 20
            ,'tabindex' => null
            ,'editor_css' => ''
            ,'editor_class' => ''
            ,'teeny' => 0
            ,'dfw' => 0
            ,'tinymce' => 1
            ,'quicktags' => 1
        );
        $ac_paid_portion = get_post_meta( $post->ID, "ac_paid_portion", true );
        wp_editor( $ac_paid_portion, "atcontentpaidportion", $args );
    }

?>