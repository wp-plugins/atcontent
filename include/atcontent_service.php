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
    
    function atcontent_wipe() {
        global $wpdb;
        $offset = 0;
        $limit = 20;
        $posts_id = array();
        do {
            $posts = $wpdb->get_results( 
                    "
                    SELECT ID, post_title, post_author
                    FROM {$wpdb->posts}
                    WHERE post_type = 'post'
                ORDER BY post_date DESC LIMIT {$offset},{$limit}
                    "
            );
            foreach ( $posts as $post ) 
            {
                delete_post_meta( $post->ID, "ac_postid" );
                delete_post_meta( $post->ID, "ac_is_advanced_tracking" );
                delete_post_meta( $post->ID, "ac_is_copyprotect" );
                delete_post_meta( $post->ID, "ac_is_process" );
                delete_post_meta( $post->ID, "ac_embedid" );
            }
            $wpdb->flush();
            $offset += $limit;
        } while ( count( $posts ) > 0 );
        $offset = 0;
        $limit = 20;
        do {
            $wp_user_search = $wpdb->get_results("SELECT ID, user_email FROM {$wpdb->users} ORDER BY ID LIMIT {$offset}, {$limit}");
            foreach ( $wp_user_search as $user ) {
                $userid = $user->ID;
                delete_user_meta( $userid, "ac_api_key" );
                delete_user_meta( $userid, "ac_non_delete_api_key" );
                delete_user_meta( $userid, "ac_pen_name" );
                delete_user_meta( $userid, "ac_showname" );
                delete_user_meta( $userid, "ac_avatar_20" );
                delete_user_meta( $userid, "ac_avatar_80" );
                delete_user_meta( $userid, "ac_syncid" );
                delete_user_meta( $userid, "ac_blogid" );
                delete_user_meta( $userid, "ac_blog_title" );
                delete_user_meta( $userid, "ac_settings_tab_settings" );
                delete_user_meta( $userid, "ac_last_repost_visit" );
            }
            $wpdb->flush();
            $offset += $limit;
        } while ( count( $wp_user_search ) > 0 );
        delete_option( 'atcontent_inited' );
        delete_option( 'ac_blog_api_key' );
        delete_option( 'ac_jsonly' );
    }
    
    function ac_isjsonly() {
       return get_option( 'ac_jsonly' ) == '1';
    }
?>