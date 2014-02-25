<?php

    function atcontent_add_meta_boxes() {

        add_meta_box(
            'atcontent_sectionid',
            __( 'AtContent Post Settings', 'atcontent_textdomain' ),
            'atcontent_inner_custom_box',
            'post', 'side', 'high'
        );

        /*

        add_meta_box(
            'atcontent_repost_metabox',
            __( 'AtContent Repost', 'atcontent_textdomain' ),
            'atcontent_inner_repost_box',
            'post'
        );

        // */
    }

    function atcontent_inner_custom_box( $post ) {
          // Use nonce for verification
          wp_nonce_field( plugin_basename( __FILE__ ), 'atcontent_noncename' );

          
          $userid = $post->post_author;
          $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
          if ( strlen( $ac_api_key ) == 0 ) {
              $connect_url = admin_url( "admin.php?page=atcontent/dashboard.php" );
              if ( current_user_can( 'manage_options' ))
              {
                    $connect_url .= '&connectas='.$userid;
              }
              $author_user = get_userdata( intval( $userid ) );
              $author_username = "";
              if ( $author_user != false ) {
                  $author_username = $author_user->user_login;
              }
              ?>
                <div class="misc-pub-section">
                    To enjoy all AtContent advantages for this post, please 
                    <a href="<?php echo $connect_url; ?>">connect</a> 
                    <?php echo $author_username; ?> with AtContent.
                </div>
              <?php
              return;
          }

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
    <div class="misc-pub-section">
        <label>
            <input type="checkbox" id="atcontent_is_process" name="atcontent_is_process" value="1" <?php echo $ac_is_process_checked; ?> /> Use AtContent for this post
        </label><br>
        as 
        <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank">
            <img style="vertical-align: middle;  margin-right: .3em" src="<?php echo $ac_avatar_20; ?>"  onerror="this.parentNode.removeChild(this)" alt="">
            <?php echo $ac_show_name; ?>
        </a>
    </div>

    <div class="misc-pub-section">
        <label>
            <input type="checkbox" id="atcontent_is_copyprotect" name="atcontent_is_copyprotect" value="1" <?php echo $ac_is_copyprotect_checked; ?> <?php echo $ac_is_copyprotect_enabled ? '' : 'disabled="disabled"'; ?> /> 
                Protect post from copy-paste
        </label><br>
        Available posts: <?php echo $plagiarism_quota; ?>.
        <?php if ($ac_is_copyprotect_enabled == false) { 
            if ( $subscriptions_count == 0 ) { ?>
                <br />To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">choose a suitable plan</a>
            <?php } else { ?>
                <br />To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">upgrade your subscription</a> or wait for the next month
            <?php } 
        } ?>
        <input type="hidden" name="atcontent_is_copyprotect_enabled" value="<?php echo $ac_is_copyprotect_enabled ? "1" : "0"; ?>" />
    </div>

    <script type="text/javascript">
        (function ($) {
            $(function () {
                $("#submitpost #delete-action").before('<p id="atcontent-tags-announce" class="update-nag" style="margin:5px 5px 15px 5px;">Set post <a id="atcontent-tags-link" href="javascript:">tags</a> and <a id="atcontent-categories-link" href="javascript:">categories</a> to get <b>free&nbsp;promotion</b> with AtContent!</p>');
                $("#atcontent-tags-link").click(function () { 
                    $('html, body').animate({
                        scrollTop: $("#new-tag-post_tag").offset().top - 250
                    }, 300);
                });
                $("#atcontent-categories-link").click(function () { 
                    $('html, body').animate({
                        scrollTop: $("#category-all input[type=checkbox]:first").offset().top - 250
                    }, 300);
                });
            });
        })(jQuery);
    </script>
    <input type="hidden" name="atcontent_save_meta" value="1">
    <?php
    }

    function atcontent_inner_repost_box( $post ) {
        atcontent_coexistense_fixes();

        $testcontent = apply_filters( "the_content",  $post->post_content );
        $testcontent .= apply_filters( "the_content",  $ac_paid_portion );

        $ac_is_repost = ( preg_match_all("/<script[^<]+src=\"https?:\/\/w.atcontent.com/", $testcontent, $ac_scripts_test ) && count( $ac_scripts_test ) > 0 );
        if ($ac_is_repost) {

            $ac_share_panel_data_option = "";
            $ac_additional_classes = "";
            
            $ac_additional_classes .= " atcontent_excerpt";

            ?>
<script type="text/javascript">
    (function ($) {
        $(function () {
            $("#atcontent_sectionid").hide();
            $("#atcontent-tags-announce").remove();
            var repostbox = $("#atcontent_repost_metabox").detach();
            $("#postdivrich").hide().before(repostbox);
        });
    })(jQuery);
</script>
<p <?php echo $ac_share_panel_data_option ?> class="atcontent_widget<?php echo $ac_additional_classes ?>"><?php echo $post->post_content; ?></p>
            <?php
        } else {
            ?>
<script type="text/javascript">
    (function ($) {
        $(function () {
            $("#atcontent_repost_metabox").hide();
        });
    })(jQuery);
</script>
            <?php
        }
    }

?>