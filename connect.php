<?php

    $atcontent_menu_section = "connect";
        
    // PingBack
    if ( ! atcontent_pingback_inline() ) {
        echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
    }
    //End PingBack

    $userid = wp_get_current_user()->ID;
    $hidden_field_name = 'ac_submit_hidden';
    $form_message = '';
    if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
        isset( $_POST[ "ac_api_key" ] ) ) {
        $ac_api_key = trim( $_POST[ "ac_api_key" ] );
        update_user_meta( $userid, "ac_api_key", $ac_api_key );
        $admin_url_main = admin_url("admin.php?page=atcontent/connect.php");
    ?>
<script>window.location = '<?php echo $admin_url_main ?>';</script>
<?php
        $form_message .= 'Settings saved.';
    }
    $userid = intval( wp_get_current_user()->ID );
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_userinfo = atcontent_api_get_userinfo( $ac_api_key );
    if ( $ac_userinfo["IsOK"] == true ) {
        update_user_meta( $userid, "ac_pen_name", $ac_userinfo["Nickname"] );
        update_user_meta( $userid, "ac_showname", $ac_userinfo["Showname"] );
        update_user_meta( $userid, "ac_avatar_20", $ac_userinfo["Avatar20"] );
        update_user_meta( $userid, "ac_avatar_80", $ac_userinfo["Avatar80"] );
        update_user_meta( $userid, "ac_avatar_200", $ac_userinfo["Avatar200"] );
    }
    require( "atcontent_userinit.php" );
?>
<div class="atcontent_wrap">
<?php include("settings_menu.php"); ?>
<form action="" method="POST" id="disconnect-form">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">    
<?php
         if ( strlen($ac_api_key) == 0 ) {
             $form_action = admin_url( 'admin-ajax.php' );
             include("invite.php");
             ?> 
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) window.location = '<?php echo admin_url( 'admin.php?page=atcontent/subscription.php' ); ?>';
            else $("#ac_connect_result").html(
                    'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
    })(jQuery);
</script>
<?php
         } else {
?>
<div class="b-column-single">
    
    <p style="text-align: center;">You have connected blog to AtContent as</p>
    <p style="text-align: center">
        <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank">
            <img src="<?php echo $ac_avatar_200; ?>" alt="" width="200" height="200">
            <span style="font-size: 1.6em; display: block; margin-top: 10px;"><?php echo $ac_show_name; ?></span>
        </a>
        <input type="hidden" name="ac_api_key" value="">
    </p>
    <p style="text-align: center">
        <button type="submit" class="button-size-small button-color-white" name="disconnect" id="disconnect">
            <?php esc_attr_e('Change account') ?>
        </button>
    </p>
</div>
<?php
         }
?>

</form>
<?php
$form_action = admin_url( 'admin-ajax.php' );
?>
</div>
<div class="clear"></div>