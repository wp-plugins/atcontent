<?php
    function atcontent_update_user_meta( $userid, $ac_userinfo ) {
        update_user_meta( $userid, "ac_pen_name", $ac_userinfo["Nickname"] );
        update_user_meta( $userid, "ac_showname", $ac_userinfo["Showname"] );
        update_user_meta( $userid, "ac_avatar_20", $ac_userinfo["Avatar20"] );
        update_user_meta( $userid, "ac_avatar_80", $ac_userinfo["Avatar80"] );
        update_user_meta( $userid, "ac_avatar_200", $ac_userinfo["Avatar200"] );
    }

    function atcontent_get_user_settings_oneclick_repost( $userid ) {
        $ac_oneclick_repost = get_user_meta( $userid, "ac_oneclick_repost", true );
        if ( strlen( $ac_oneclick_repost ) == 0 ) {
            $ac_oneclick_repost = 1;
        }
        return $ac_oneclick_repost;
    }

    function atcontent_set_user_settings_oneclick_repost( $userid, $value ) {
        update_user_meta( $userid, "ac_oneclick_repost", $value );
    }

    function atcontent_get_user_settings_mainpage_repost( $userid ) {
        $ac_mainpage_repost = get_user_meta( $userid, "ac_mainpage_repost", true );
        if ( strlen( $ac_mainpage_repost ) == 0 ) {
            $ac_mainpage_repost = "0";
        }
        return $ac_mainpage_repost;
    }
    
    function atcontent_set_user_settings_use_viglink($userid, $value)
    {
        update_user_meta( $userid, "ac_use_vglink", $value );
    }

    function atcontent_set_user_settings_viglink_apikey($userid, $value)
    {
        update_user_meta( $userid, "ac_vglink_apikey", $value );   
    }

    function atcontent_set_user_settings_mainpage_repost( $userid, $value ) {
        update_user_meta( $userid, "ac_mainpage_repost", $value );
    }

    function atcontent_get_settings_unread_count( $userid ) {
        $ac_settings_tab_guide = get_user_meta( $userid, "ac_settings_tab_guide", true );
        $ac_settings_tab_fourways = get_user_meta( $userid, "ac_settings_tab_fourways", true );
        $ac_settings_tab_settings = get_user_meta( $userid, "ac_settings_tab_settings", true );
        $count = 0;
        if ( $ac_settings_tab_guide != "1" ) $count += 1;
        if ( $ac_settings_tab_fourways != "1" ) $count += 1;
        if ( $ac_settings_tab_settings != "1" ) $count += 1;
        return $count;
    }

    function atcontent_get_user_settings_value( $userid, $id ) {
        $ac_value = get_user_meta( $userid, "ac_settings_" . $id, true );
        if ( strlen( $ac_value ) == 0 ) {
            $ac_value = 0;
        }
        return $ac_value;
    }

    function atcontent_set_user_settings_value( $userid, $id, $value ) {
        update_user_meta( $userid, "ac_settings_" . $id, $value );
    }
    
    function atcontent_get_blog_url(){
        $siteuri = get_bloginfo( 'url' );
        if ( strlen( $siteuri ) == 0 ) {
            $siteuri = get_bloginfo( 'wpurl' );
        }
        if ( strlen( $siteuri ) == 0 ) {
            $siteuri = $_SERVER["SERVER_NAME"];
        }
        return $siteuri;
    }
?>