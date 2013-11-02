<?php 
    $userid = intval( wp_get_current_user()->ID );
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
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
?>