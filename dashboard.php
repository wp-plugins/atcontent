<?php
    $ref_url = "http://wordpress.org/plugins/atcontent/";
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    require_once( "include/atcontent_userinit.php" );
    wp_register_style( 'atcontentSettingsPage',  plugins_url( 'assets/settings.css?v=1', __FILE__ ), array(), true );
    wp_enqueue_style( 'atcontentSettingsPage' );
    wp_register_script( 'atcontentSettingsScript',  plugins_url( 'assets/settings.js?v=1', __FILE__ ), array(), true );
    wp_enqueue_script( 'atcontentSettingsScript' );
    include( 'include/atcontent_analytics.php' );
    $currentuser = wp_get_current_user();
    $userid = intval( $currentuser->ID );
    if ( ( isset ( $_GET['connectas'] ) ) && ( strlen( $_GET['connectas'] ) > 0 ) )
    {
        $userid = intval( $_GET['connectas'] );
    }
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
    if ( strlen( $ac_api_key ) != 0 && strlen( $ac_syncid ) != 0 ) {
        $ac_blogid = get_user_meta( $userid, "ac_blogid", true );
        $currentuser = wp_get_current_user();
        $userinfo = get_userdata( $currentuser -> ID );
        
?>


<div class="atcontent_wrap">
    <?php
        // PingBack
        if ( ! atcontent_pingback_inline() ) {
            echo "<div class=\"error\">" . 'Could not connect to the <a href="http://atcontent.com" target=_blank>AtContent</a> server. Contact your hosting provider.' . "</div>";
        }
        //End PingBack
        include("include/atcontent_settings.php");
    }
    else
    {
        include( "invite.php" );
    }
?>    
</div>