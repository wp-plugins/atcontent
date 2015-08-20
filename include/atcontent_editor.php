<?php
    function atcontent_add_meta_boxes() {
        add_meta_box(
            'atcontent_sectionid', 
            __( 'AtContent Post Settings', 'atcontent_textdomain' ),
            'atcontent_inner_custom_box',
            'post', 'side', 'high'
        );
    }

    function atcontent_inner_custom_box( $post ) {
          wp_nonce_field( plugin_basename( __FILE__ ), 'atcontent_noncename' );
          $userid = $post->post_author;
          $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
          if ( strlen( $ac_api_key ) == 0 ) {
              $connect_url = admin_url( "admin.php?page=atcontent" );
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
              $ac_is_process_checked = 'checked="checked"';
          }
          $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
          ?>
    <div class="misc-pub-section">
        <label>
            <input type="checkbox" id="atcontent_is_process" name="atcontent_is_process" value="1" <?php echo $ac_is_process_checked; ?> /> Use AtContent for this post
        </label><br />
        as 
        <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank">
            <img style="vertical-align: middle;  margin-right: .3em" src="<?php echo $ac_avatar_20; ?>"  onerror="this.parentNode.removeChild(this)" alt="">
            <?php echo $ac_show_name; ?>
        </a>
    </div>
<?php
          $ac_is_copyprotect = get_post_meta( $post->ID, "ac_is_copyprotect", true );
          if ( strlen( $ac_is_copyprotect ) == 0 ) $ac_is_copyprotect = '1';
          $ac_is_copyprotect_checked = '';
          if ( $ac_is_copyprotect == '1' ) {
              $ac_is_copyprotect_checked = "checked=\"checked\"";
          }          
          $plagiarism_quota = 0;
          $advanced_tracking_quota = 0;
        if ( ac_isjsonly() ) {
        ?>
        <?php
        } else {
            $quotas_result = atcontent_api_get_quotas ( $ac_api_key );
            $subscriptions_count = 0;
            if ( isset( $quotas_result["IsOK"] ) && $quotas_result["IsOK"] == TRUE ) {
                $subscriptions_count = count ( $quotas_result["Subscriptions"] );
                $plagiarism_quota = intval( $quotas_result["Quotas"]["PlagiarismProtection"]["Count"] );
                $advanced_tracking_quota = intval( $quotas_result["Quotas"]["DetailedStat"]["Count"] );
            }
            $ac_is_copyprotect_enabled = $plagiarism_quota > 0;
            $ac_is_advanced_tracking_enabled = $advanced_tracking_quota > 0;
            ?>
    
    <div class="misc-pub-section">
        <label>
            <input type="checkbox" id="atcontent_is_copyprotect" name="atcontent_is_copyprotect" value="1" <?php echo $ac_is_copyprotect_checked; ?> <?php echo $ac_is_copyprotect_enabled ? '' : 'disabled="disabled"'; ?> /> 
                Protect post from copy-paste
        </label><br />
        Available posts: <?php echo $plagiarism_quota; ?>.
        <?php if ( $ac_is_copyprotect_enabled == false ) { 
            if ( $subscriptions_count == 0 ) { ?>
                <br />To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">choose a suitable plan</a>
            <?php } else { ?>
                <br />To enable this feature, please <a href="https://atcontent.com/Subscribe" target="_blank">upgrade your subscription</a> or wait for the next month
            <?php } 
        } ?>
        <input type="hidden" name="atcontent_is_copyprotect_enabled" value="<?php echo $ac_is_copyprotect_enabled ? "1" : "0"; ?>" />
    </div>
    <?php
        }
        $ac_postid_to_promote = $ac_postid;
        if ( strlen( $ac_postid_to_promote ) == 0 ) {
            $ac_postid_to_promote = get_post_meta( $post->ID, "ac_repost_postid", true ); 
        }
        if ( strlen ( $ac_postid_to_promote ) > 0 ) {
            ?>
                <a class="button-primary ac-button-promote" target="_blank" href="https://atcontent.com/campaigns/create/<?php echo($ac_postid_to_promote)?>/">
                    <span class="ac-logo-promote"></span>
                    Promote with AtContent
                </a>
            <?php
        }
        if ( preg_match_all( '/<script[^<]+src="(https?:\/\/w\.atcontent\.com\/[^\"]+)\"/', $post->post_content, $matches ) ) {
            ?>
<script>
    (function ($) {
        $(function () {
            $(".wp-editor-tabs").remove();
        });
    })(jQuery);
</script>
<?php
        } 
    ?>
    <input type="hidden" name="atcontent_save_meta" value="1">
    <?php
    }
    
?>