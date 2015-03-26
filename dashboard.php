<?php
    $ref_url = "http://wordpress.org/plugins/atcontent/";
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    require_once( "include/atcontent_userinit.php" );
    wp_register_style( 'atcontentSettingsPage',  plugins_url( 'assets/settings.css?v=2', __FILE__ ), array(), true );
    wp_enqueue_style( 'atcontentSettingsPage' );
    wp_register_script( 'atcontentSettingsScript',  plugins_url( 'assets/settings.js?v=2', __FILE__ ), array(), true );
    wp_enqueue_script( 'atcontentSettingsScript' );
    include( 'include/atcontent_analytics.php' );
    $currentuser = wp_get_current_user();
    $userid = intval( $currentuser->ID );
    if ( ( isset ( $_GET['connectas'] ) ) && ( strlen( $_GET['connectas'] ) > 0 ) )
    {
        $userid = intval( $_GET['connectas'] );
    }
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    if ( strlen( $ac_api_key ) == 0 ) {
        $ac_non_delete_key = get_user_meta( $userid, "ac_non_delete_api_key", true );
        if ( strlen( $ac_non_delete_key ) == 0 ) {
            update_user_meta( $userid, "ac_fake_key", "inited" );
        }
    }
    $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
    $ac_fakekey = get_user_meta( $userid, "ac_fake_key", true );
?>
<div class="atcontent_wrap">
<?php
    if ( isset($_GET["wipe"]) && $_GET["wipe"] == '1' ) {
        ?>
    <p>WARNING: All of AtContent data on your blog will be deleted. Are your sure?</p>
    <p><button id="b-wipe" type="button" class="button button-primary">Yes, delete</button> <button id="b-wipe-cancel" type="button" class="button">No, cancel</button></p>
    <script>
        (function ($) {
            $(function () {
                $('#b-wipe').on('click', function(){
                    $.post("admin-ajax.php", {
                        "action": "atcontent_wipe"
                    }, function (d) {
                        window.location = 'admin.php?page=atcontent';
                    }, "json");
                });
                $('#b-wipe-cancel').on('click', function(){
                    window.location = 'admin.php?page=atcontent';
                });
            });
        })(jQuery);
    </script>
        <?php
    } else if ( ( strlen( $ac_api_key ) != 0 && ( strlen( $ac_syncid ) != 0 || $ac_fakekey == 'cleared' ) ) ) {
        $ac_blogid = get_user_meta( $userid, "ac_blogid", true );
        $currentuser = wp_get_current_user();
        $userinfo = get_userdata( $currentuser -> ID );        
        // PingBack
        ?>
    <div id="ac_pingback_error" style="display:none" class="error">Could not connect to the <a href="http://atcontent.com" target=_blank>AtContent.com</a> server. Please, contact your hosting provider to solve this issue.</div>
    <script>
        (function ($) {
            $(function () {
                $.post("admin-ajax.php", {
                    "action": "atcontent_pingback"
                }, function (d) {
                    if (d.IsOK == false) {
                        $("#ac_pingback_error").show();
                    }
                }, "json");
            });
        })(jQuery);
    </script>
<?php
        //End PingBack
        include("include/atcontent_settings.php");
    } else if ( strlen( $ac_fakekey ) > 0 ) {
        include( 'include/atcontent_connect.php' );
    } else {
        include( "invite.php" );
    }
?>    
</div>