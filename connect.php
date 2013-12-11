<?php

    $atcontent_menu_section = "connect";
        
    // PingBack
    if ( ! atcontent_pingback_inline() ) {
        echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
    }
    //End PingBack

    $currentuser = wp_get_current_user();
    $userid = intval( $currentuser->ID );
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
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
    if ( isset( $_POST[ "connectuser" ] ) && strlen( $ac_api_key ) > 0 ) {
        $ac_userinfo = atcontent_api_get_userinfo( $ac_api_key );
        if ( user_can( $userid, "manage_options" ) ) {
            foreach ( $_POST["connectuser"] as $connectuserid ) {
                update_user_meta( $connectuserid, "ac_api_key", $ac_api_key );
                if ( $ac_userinfo["IsOK"] == true ) {
                    atcontent_update_user_meta( intval( $connectuserid ), $ac_userinfo );
                }
            }
            $users = get_users("orderby=ID");
            foreach ( $users as $user ) {
                if ( $user->ID != $currentuser->ID && user_can( $user, "publish_posts" ) && !in_array( $user->ID . "", $_POST["connectuser"] ) ) {
                    $user_ac_api_key = get_user_meta( intval( $user->ID ), "ac_api_key", true );
                    if ( $user_ac_api_key == $ac_api_key ) update_user_meta( intval( $user->ID ), "ac_api_key", "" );
                }
            }
        }
    }
    if ( isset( $_POST["atcontent_invite"] ) && $_POST["atcontent_invite"] == "Y" ) {
?>
<script type="text/javascript">
    window.location = '<?php echo admin_url("admin.php?page=atcontent/settings.php") . "&afterconnect=1"; ?>';
</script>
<?php
    }
    
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_userinfo = atcontent_api_get_userinfo( $ac_api_key );
    if ( $ac_userinfo["IsOK"] == true ) {
        atcontent_update_user_meta( $userid, $ac_userinfo );
    }
    require( "include/atcontent_userinit.php" );
?>
<script>
    function beforechangeaccount() {
        return confirm("Are you sure you want to change account?");
    }
</script>
<div class="atcontent_wrap">
<?php 
    include("settings_menu.php");
    if ( strlen($ac_api_key) == 0 ) {
        $form_action = admin_url( 'admin-ajax.php' );
        include( "invite.php" );
    } else {
?>
<div class="connect_left">
<form action="" method="POST" id="disconnect-form" onsubmit="return beforechangeaccount();">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <p style="text-align: center;"><?php echo get_avatar( $currentuser->ID, 16 ) . " " . $currentuser->display_name; ?> is connected with AtContent as</p>
    <p style="text-align: center">
        <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank">
            <img src="<?php echo $ac_avatar_200; ?>" alt="" width="200" height="200">
            <span style="font-size: 1.6em; display: block; margin-top: 10px;"><?php echo $ac_show_name; ?></span>
        </a>
        <input type="hidden" name="ac_api_key" value="" >
    </p>
    <p style="text-align: center">
        <button type="submit" class="button-size-small button-color-white" name="disconnect" id="disconnect">
            <?php esc_attr_e('Change account') ?>
        </button>
    </p>
</form>
</div>
<?php
        $users = get_users("orderby=ID");
        $additionalUsersCount = 0;
        foreach ( $users as $user ) {
            if ( $user->ID != $currentuser->ID && user_can( $user, "edit_posts" ) ) $additionalUsersCount += 1;
        }
        if ( $additionalUsersCount > 1 && user_can( $currentuser->ID, "manage_options" ) ) {
?>
    <div class="connect_right">
        <form action="" method="post" name="updateconnect-form" id="updateconnect-form">
        <p>As an administrator of this blog you can connect/disconnect following authors with your AtContent account:</p>
        <div class="checkbox_group" id="usersList">
<?php
        foreach ( $users as $user ) {
            if ( $user->ID != $currentuser->ID && user_can( $user, "edit_posts" ) ) {
                $user_ac_api_key = get_user_meta( intval( $user->ID ), "ac_api_key", true );
                $checked = ( $user_ac_api_key == $ac_api_key ) ? "checked=\"checked\"" : "";
                echo "<label><input type=\"checkbox\" {$checked} name=\"connectuser[]\" value=\"{$user->ID}\"> " . get_avatar( $user->ID, 16 ) . " <span class=\"checkbox_group_text\">" . $user->display_name . "</span></label>";
            }
        }
?>
        </div>
        <button type="submit" class="button-size-small button-color-white" name="updateconnect" id="updateconnect">
            <?php esc_attr_e('Update') ?>
        </button>
        </form>
    </div>

<?php
        }
    }
$form_action = admin_url( 'admin-ajax.php' );
?>
</div>
<div class="clear"></div>
<?php atcontent_ga("ConnectTab", "Connect"); ?>