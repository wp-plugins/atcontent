<?php
function atcontent_readership() {
    $userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    if ( current_user_can( 'edit_posts' ) ) {
        $posts_id = array();
        $response = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );
        header( "Content-Type: application/json" );
        echo json_encode( $response );
    }
    exit;
}

function atcontent_ajax_gate() {
    $command = $_POST["command"];
    switch ( $command ) {
        case "repost":
            $userid = $_POST["userid"];
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            $ac_pen_name = get_user_meta( $userid, "ac_pen_name", true );
            if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "AtContent";
            $ac_postid = $_POST["postid"];
            if ( strlen( $ac_api_key ) > 0 && ($ac_api_key == $_POST["key"]) ) {
                $repost_title = $_POST["title"];
                $ac_content = "<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --><script data-cfasync=\"false\" src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face\"></script><!--more--><script data-cfasync=\"false\" src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body\"></script>";
                $ac_repost_setting = atcontent_get_user_settings_oneclick_repost( intval( $userid ) );
                $post_status = $ac_repost_setting == "1" ? "publish" : "draft";
                // Create post object
                $new_post = array(
                    'post_title'    => $repost_title,
                    'post_content'  => $ac_content,
                    'post_status'   => $post_status,
                    'post_author'   => $userid,
                    'post_category' => array()
                );
                kses_remove_filters();
                $new_post_id = wp_insert_post( $new_post );
                update_post_meta( $new_post_id, "ac_is_process", "0" );
                kses_init_filters();
                $original_uri = get_permalink ( $new_post_id );
                echo json_encode( array ( "IsOK" => true, "Url" => $original_uri ) );
            }
            break;
        case "getpost":
            $userid = $_POST["userid"];
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            $postid = $_POST["postid"];
            if ( strlen( $ac_api_key ) > 0 && ( $ac_api_key == $_POST["key"] ) ) {
                $post = get_post( $postid );
                if ($post == null) exit;
                $ac_user_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
                if ( strlen( $ac_user_copyprotect ) == 0 ) $ac_user_copyprotect = "1";
                $ac_is_copyprotect = get_post_meta( $post->ID, "ac_is_copyprotect", true );
                if ( strlen( $ac_is_copyprotect ) == 0 ) { 
                    $ac_is_copyprotect = $ac_user_copyprotect;
                    update_post_meta($postid, "ac_is_copyprotect", $ac_is_copyprotect);
                }
                $ac_is_advanced_tracking = get_post_meta( $post->ID, "ac_is_advanced_tracking", true );
                if ( strlen( $ac_is_advanced_tracking ) == 0 ) { 
                    $ac_is_advanced_tracking = "1";
                    update_post_meta( $postid, "ac_is_advanced_tracking", $ac_is_advanced_tracking );
                }
                $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
                atcontent_coexistense_fixes();
                $post_title = $post->post_title;
                $post_content = apply_filters( "the_content",  $post->post_content );
                $comments_json = "";
                $comments = get_comments( array(
                    'post_id' => $post->ID,
                    'order' => 'ASC',
                    'orderby' => 'comment_date_gmt',
                    'status' => 'approve',
                ) );
                if ( !empty( $comments ) ) {
                    $comments_json .= json_encode( $comments );
                }
                $tags_json = json_encode( wp_get_post_tags( $post->ID,  array( 'fields' => 'slugs' ) ) );
                $cats_json = json_encode( wp_get_post_categories( $post->ID, array( 'fields' => 'slugs' ) ) );
                $post_published = get_gmt_from_date( $post->post_date );
                $post_original_url = get_permalink( $post->ID );
                $repost_post_id = '';
                $embedid = '';
                if ( preg_match_all( '/<script[^<]+src="https?:\/\/w\.atcontent\.com\/(\-\/[^\/]+\/)?([^\/]+)\/([^\/]+)\/([^\"]+)/', $post->post_content, $matches ) )
                {
                    if ( strpos( $matches[1][0], "-/" ) === 0 ) {
                        $embedid = substr( $matches[1][0], 2, strlen( $matches[1][0] ) - 3 );
                    }
                    $repost_post_id = $matches[3][0];
                    update_post_meta( $postid, "ac_repost_postid", $repost_post_id );                    
                }
                $ac_vglink_apikey = get_user_meta(intval( $userid ), "ac_vglink_apikey", true );
                echo json_encode( array( 
                    "IsOK" => true,
                    "Title" => $post_title,
                    "Content" => $post_content,
                    "CopyProtection" => $ac_is_copyprotect,
                    "AdvancedTracking" => $ac_is_advanced_tracking,
                    "Published" => $post_published,
                    "OriginalUrl" => $post_original_url,
                    "Comments" => $comments_json,
                    "Tags" => $tags_json,
                    "Categories" => $cats_json,
                    "PostId" => $ac_postid,
                    "RepostPostId" => $repost_post_id,
                    "EmbedId" => $embedid,
                    "SiteUrl" => get_site_url(),
                    "VigLinkApiKey" => $ac_vglink_apikey
                    ) );
            }
            break;
        case "newpost":
            $userid = $_POST["userid"];
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            $ac_pen_name = get_user_meta( $userid, "ac_pen_name", true );
            if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "AtContent";
            $ac_postid = $_POST["postid"];
            $ac_embedid = $_POST["embedid"];
            $ac_published = $_POST["published"];
            $repost_preview = $_POST["preview"];
            $impactradiusid = $_POST["impactradiusid"];
            $impactradiustrackingurl = $_POST["imactradiusurl"];
            $embedid = '';
            if ( strlen( $ac_embedid ) > 0 ) {
                $embedid .= "-/" . $ac_embedid . "/"; 
            }
            $repost_title = $_POST["title"];
            if ( strlen( $ac_api_key ) > 0 && ($ac_api_key == $_POST["key"]) ) {
                remove_filter( 'the_content', 'atcontent_the_content', 1 );
                remove_filter( 'the_content', 'atcontent_the_content_after', 100 );
                remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100 );
                remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
                if ( $impactradiusid != null && strlen( $impactradiusid ) > 0 ) {
                    $embedid_ir = str_replace( "/", "%2F", $embedid );
                    $ac_content = 
                    "<div class=\"atcontent_widget\" data-ac-options=\"impct-rad-id\" data-ac-impct-rad-id=\"{$impactradiusid}\"><div class=\"atcontent_preview\"><p>" . 
                       $repost_preview . "</p></div>" .
                    "<!-- Copying the code of this post you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) -->" .
                    "<script data-cfasync=\"false\" src=\"{$impactradiustrackingurl}?u=https%3A%2F%2Fw.atcontent.com%2F{$embedid_ir}{$ac_pen_name}%2F{$ac_postid}%2FFace\"></script><!--more-->" . 
                    "<script data-cfasync=\"false\" data-ac-src=\"https://w.atcontent.com/{$embedid}{$ac_pen_name}/{$ac_postid}/Body\"></script></div>";
                } else {
                    $ac_content = 
                    "<div class=\"atcontent_widget\"><div class=\"atcontent_preview\"><p>" . $repost_preview . "</p></div>\r\n" . 
                    "<script data-cfasync=\"false\" src=\"https://w.atcontent.com/{$embedid}{$ac_pen_name}/{$ac_postid}/Face\"></script><!--more-->" . 
                    "<script data-cfasync=\"false\" data-ac-src=\"https://w.atcontent.com/{$embedid}{$ac_pen_name}/{$ac_postid}/Body\"></script>\r\n" . 
                    "<!-- Copying the code of this post you agree with Terms of services AtContent™ (http://atcontent.com/terms/) -->\r\n" .
                    "</div>";
                }
                $ac_repost_setting = atcontent_get_user_settings_oneclick_repost( intval( $userid ) );
                $post_status = $ac_repost_setting == "1" ? "publish" : "draft";
                // Create post object
                $new_post = array(
                    'post_title'    => $repost_title,
                    'post_content'  => $ac_content,
                    'post_status'   => $post_status,
                    'post_author'   => $userid,
                    'post_category' => array()
                );
                kses_remove_filters();
                // Insert the post into the database
                remove_all_actions( 'publish_post' );
                $new_post_id = wp_insert_post( $new_post );
                update_post_meta( $new_post_id, "ac_is_process", "0" );
                update_post_meta( $new_post_id, "ac_embedid", $embedid );
                update_post_meta( $new_post_id, "ac_repost_postid", $ac_postid );
                kses_init_filters();
                echo json_encode ( array ( "IsOK" => true, "PostId" => $new_post_id ) );
            }
            break;
        case "updatepost":
            $userid = $_POST["userid"];
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            $postid = $_POST["blogpostid"];
            $embedid = $_POST["embedid"];
            $ac_published = $_POST["published"];
            $ac_postid = $_POST["postid"];
            if ( strlen( $ac_api_key ) > 0 && ( $ac_api_key == $_POST["key"] ) ) {
                $repost_post_id = get_post_meta( intval( $postid ), "ac_repost_postid", true );
                if ( strlen( $repost_post_id ) == 0 )
                {
                    update_post_meta( intval( $postid ), "ac_postid", $ac_postid );
                    update_post_meta( intval( $postid ), "ac_is_process", "1" );
                }
                else
                {
                    update_post_meta( intval( $postid ), "ac_postid", '' );
                }
                update_post_meta( intval( $postid ), "ac_embedid", $embedid );
                $post = get_post( $postid );
                $post_content = $post -> post_content;
                $repost_post_id = get_post_meta( intval( $postid ), "ac_repost_postid", true );
                if ( strlen( $repost_post_id ) > 0 )
                {         
                    remove_filter( 'the_content', 'atcontent_the_content', 1 );
                    remove_filter( 'the_content', 'atcontent_the_content_after', 100 );
                    remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100 );
                    remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
                    $ac_pen_name = get_user_meta( intval( $userid ), "ac_pen_name", true );
                    $embedid = '-/' . $embedid . '/';
                    if ( preg_match_all( '/<script[^<]+src="https?:\/\/w\.atcontent\.com\/(\-\/[^\/]+\/)?([^\/]+)\/([^\/]+)\/([^\"]+)/', $post_content, $matches ) )
                    {
                        for ( $scriptIndex = 0; $scriptIndex < count( $matches[0] ); $scriptIndex++ ) {
                            $scriptToReplace = "<script src=\"https://w.atcontent.com/{$embedid}{$matches[2][$scriptIndex]}/{$matches[3][$scriptIndex]}/{$matches[4][$scriptIndex]}";
                            $post_content = str_replace( $matches[0][$scriptIndex], $scriptToReplace, $post_content );
                        }
                    }
                    if ( preg_match_all( '/<script[^<]+src="(https?:\/\/w\.atcontent\.com\/[^\"]+)\"/', $post_content, $matches ) ) {
                        for ( $index = 0; $index < count( $matches[1] ); $index++ )
                        {
                            $post_content = str_replace( 
                                $matches[0][$index], 
                                "<script " . ( $index > 0 ? "data-ac-" : "" ) . "src=\"" . $matches[1][$index] . "\"", 
                                $post_content );
                        }
                    }
                }
                kses_remove_filters();
                remove_all_actions( 'publish_post' );
                wp_update_post( array(
                        'ID' => intval( $postid ),
                        'post_date' => get_date_from_gmt( date( "Y-m-d H:i:s", $ac_published ) ),
                        'post_content' => $post_content
                    ) );
                kses_init_filters();
                echo json_encode ( array ( "IsOK" => true ) );
            }
            break;
        case "deletepost":
            $userid = $_POST["userid"];
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            $postid = $_POST["blogpostid"];
            $ac_postid = $_POST["postid"];
            if ( strlen( $ac_api_key ) > 0 && ( $ac_api_key == $_POST["key"] ) ) {
                $ac_postid_meta = get_post_meta( intval( $postid ), "ac_repost_postid", true );
                if ( $ac_postid_meta == $ac_postid ) {
                    wp_delete_post( intval( $postid ) );
                    echo json_encode ( array ( "IsOK" => true ) );
                } else {
                    echo json_encode ( array ( "IsOK" => false ) );
                }
            }
            break;
        case 'getusers':
            $key = $_POST['key'];
            $ac_blog_api_key = get_option( 'ac_blog_api_key' );
            if ( strlen( $ac_blog_api_key ) == 0 || $key != $ac_blog_api_key ) break;
            global $wpdb;
            $blog_users = array();
            $offset = 0;
            $limit = 20;
            do {
                $wp_user_search = $wpdb->get_results("SELECT ID, user_email FROM {$wpdb->users} ORDER BY ID LIMIT {$offset}, {$limit}");
                foreach ( $wp_user_search as $user ) {
                    $userid = $user->ID;
                    $email = $user->user_email;
                    if ( user_can( $userid, 'edit_posts' ) ) {
                        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
                        $ac_fake_key = get_user_meta( $userid, "ac_fake_key", true );
                        $userinfo = get_userdata( $userid );
                        $username = $userinfo -> display_name;
                        $blog_users[] = array( "Email" => $email, "Id" => $userid, "Key" => $ac_api_key, 'Fake' => $ac_fake_key, 'Username' => $username );
                    }
                }
                $wpdb->flush();
                $offset += $limit;
            } while ( count( $wp_user_search ) > 0 );
            echo json_encode( $blog_users );
            break;
        case 'updateuserinfo':
            $key = $_POST['key'];
            $ac_blog_api_key = get_option( 'ac_blog_api_key' );
            if ( strlen( $ac_blog_api_key ) == 0 || $key != $ac_blog_api_key ) break;
            $userid = intval( $_POST['userid'] );
            $apikey = $_POST['apikey'];
            $nickname = $_POST["nickname"];
            $showname = $_POST['showname'];
            $avatar_20 = $_POST['avatar20'];
            $avatar_80 = $_POST['avatar80'];
            $avatar_200 = $_POST['avatar200'];
            $fake_key = $_POST['fakekey'];
            $ac_blogid = $_POST["blogid"];
            $ac_syncid = $_POST["syncid"];
            update_user_meta( $userid, 'ac_api_key', $apikey );
            update_user_meta( $userid, 'ac_pen_name', $nickname );
            update_user_meta( $userid, 'ac_showname', $showname );
            update_user_meta( $userid, 'ac_avatar_20', $avatar_20 );
            update_user_meta( $userid, 'ac_avatar_80', $avatar_80 );
            update_user_meta( $userid, 'ac_avatar_200', $avatar_200 );
            update_user_meta( $userid, 'ac_fake_key', $fake_key );
            update_user_meta( $userid, 'ac_blogid', $ac_blogid );
            update_user_meta( $userid, 'ac_syncid', $ac_syncid );
            echo json_encode( array( 'IsOK' => true ) );
            break;
        case 'updatebloginfo':
            $key = $_POST['key'];
            $ac_blog_api_key = get_option( 'ac_blog_api_key' );
            if ( strlen( $ac_blog_api_key ) == 0 || $key != $ac_blog_api_key ) break;
            $blogid = $_POST["blogId"];
            $syncid = $_POST["syncId"];
            update_option( "ac_blog_id", $blogid );
            update_option( "ac_sync_id", $syncid );
            echo json_encode( array( 'IsOK' => true ) );
            break;
        case 'getposts':
            $key = $_POST['key'];
            $userid = intval( $_POST['userid'] );
            $ac_api_key = get_user_meta( $userid, 'ac_api_key', true );
            if ( strlen( $ac_api_key ) == 0 || $ac_api_key != $key ) break;
            global $wpdb;
            $offset = 0;
            $limit = 20;
            $posts_id = array();
            do {
                $posts = $wpdb->get_results( 
	        "
	        SELECT ID, post_title, post_author
	        FROM {$wpdb->posts}
	        WHERE post_status = 'publish' 
		        AND post_author = {$userid} AND post_type = 'post'
                ORDER BY post_date DESC LIMIT {$offset},{$limit}
	        "
                );
                foreach ( $posts as $post ) 
                {
                    array_push( $posts_id, $post->ID );
                }
                $wpdb->flush();
                $offset += $limit;
            } while ( count( $posts ) > 0 );
            echo json_encode( $posts_id );
            break;
        case 'getinfo':
            $key = $_POST['key'];
            $userid = intval( $_POST['userid'] );
            $ac_blog_api_key = get_option( 'ac_blog_api_key' );
            if ( strlen( $ac_blog_api_key ) == 0 || $key != $ac_blog_api_key ) break;
            echo json_encode( array( 
                    'Url' => atcontent_get_blog_url(),
                    'Title' => get_bloginfo( 'name' ),
                    'BlogId' => get_user_meta( $userid, 'ac_blogid', true ),
                    'SyncId' => get_user_meta( $userid, 'ac_syncid', true )
                ) );
            break;
        case 'getuserinfo':
            $key = $_POST['key'];
            $userid = intval( $_POST['userid'] );
            $ac_api_key = get_user_meta( $userid, 'ac_api_key', true );
            if ( strlen( $ac_api_key ) == 0 || $ac_api_key != $key ) break;
            echo json_encode( array(
                'BlogId' => get_user_meta( $userid, 'ac_blogid', true ),
                'SyncId' => get_user_meta( $userid, 'ac_syncid', true ),
                'BlogKey' => get_option( 'ac_blog_api_key' ),
                'Main' => get_option( 'ac_main_userid' )
            ) );
            break;
        case 'getversion':
            echo AC_VERSION;
            break;
    }
    exit;
}

function atcontent_api_key() {
    $userid = wp_get_current_user()->ID;
    if ( current_user_can( 'edit_posts' ) ) {
        $result = "";
        $api_key_result = atcontent_api_get_key( $_GET["nounce"], $_GET["grant"] );
        if (!$api_key_result["IsOK"]) {
            $result .= "false";
        } else {
            $ac_api_key = $api_key_result["APIKey"];
            update_user_meta( $userid, "ac_api_key", $api_key_result["APIKey"] );
            update_user_meta( $userid, "ac_pen_name", $api_key_result["Nickname"] );
            update_user_meta( $userid, "ac_showname", $api_key_result["Showname"] );
            update_user_meta( $userid, "ac_avatar_20", $api_key_result["Avatar20"] );
            update_user_meta( $userid, "ac_avatar_80", $api_key_result["Avatar80"] );
            $result .= "true";
        }
	    header( "Content-Type: text/html" );
	    echo <<<END
<html>
<body>
<script type="text/javascript">
window.parent.parent.ac_connect_res({$result});
</script>
</body>
</html>
END;
    }
    // IMPORTANT: don't forget to "exit"
    exit;
}

function atcontent_connect_blog() {
    $bloguserid = $_POST['bloguserid'];
    $apikey = get_user_meta( intval( $bloguserid ), 'ac_api_key', true );
    $sitetitle = $_POST['sitetitle'];
    $blogtitle = '';
    if ( isset( $_POST['blogtitle'] ) ){
        $blogtitle = $_POST['blogtitle'];
    }
    $gate = admin_url('admin-ajax.php');
    $blog = $_POST['blog'];
    $blog_url = home_url();
    $connect_data = 
        "bloguserid=" . urlencode($bloguserid) . 
        "&apikey=" . urlencode($apikey) . 
        "&sitetitle=" . urlencode($sitetitle) . 
        "&blogtitle=" . urlencode($blogtitle) . 
        "&gate=" . urlencode($gate) . 
        "&blog=" . urlencode($blog) . 
        "&appurl=" . urlencode( $blog_url );
    $connect_answer = atcontent_do_post( 'http://api.atcontent.com/v1/native/connectblog', $connect_data );
    if ( isset ( $connect_answer["IsOK"] ) && $connect_answer["IsOK"] == TRUE)
    {
        $userid = $bloguserid;
        update_user_meta( $userid, "ac_blogid", $connect_answer["BlogId"] );
        update_user_meta( $userid, "ac_blog_title", $connect_answer["BlogTitle"] );
        update_user_meta( $userid, "ac_syncid", $connect_answer["SyncId"] );
        echo json_encode ( array ( "IsOK" => true ) ); 
    }
    else
    {
        if ( isset ( $connect_answer["Error"] ) && $connect_answer["Error"] == "select" )
        {
            echo json_encode ( array ( "IsOK" => FALSE, "Error" => "select", "blogs" => $connect_answer["blogs"] ) ); 
        }
        else
        {
            if ( isset ( $connect_answer["ErrorCode"] ) && $connect_answer["ErrorCode"] == "101")
            {
                echo json_encode ( array ( "IsOK" => FALSE, "ErrorCode" => 101 ) ); 
            }
            else
            {
                echo json_encode ( array ( "IsOK" => FALSE, "Error" => $connect_answer["Error"]) ); 
            }
        }
    }
    exit;
}

function atcontent_disconnect() {
    $userid = wp_get_current_user()->ID;
    update_user_meta( $userid, "ac_api_key", "" );
    update_user_meta( $userid, "ac_syncid", "" );
    echo json_encode ( array ( "IsOK" => true ) ); 
    exit;
}

function atcontent_save_settings() {
    include( "atcontent_userinit.php" );
    $ac_oneclick_repost = $_POST["ac_oneclick_repost"];
    if ( $ac_oneclick_repost != "0" && $ac_oneclick_repost != "1" ) $ac_oneclick_repost = "1";
    atcontent_set_user_settings_oneclick_repost( $userid, $ac_oneclick_repost );
    update_user_meta( intval( $userid ), "ac_settings_tab_settings", "1" );
    $ac_mainpage_repost = "1";
    if ( !isset( $_POST["ac_mainpage_repost"] ) ) $ac_mainpage_repost = "0";
    atcontent_set_user_settings_mainpage_repost( $userid, $ac_mainpage_repost );

    $ac_use_vglink = "0";
    if ( isset($_POST["ac_use_vglink"])) 
    {
        $ac_use_vglink = $_POST["ac_use_vglink"];
        if ( $ac_use_vglink != "on" ) $ac_use_vglink = "0";
        else $ac_use_vglink = "1";
    }

    $ac_vglink_apikey = "";
    $ac_vglink_apikey = $_POST["ac_vglink_apikey"];
    if ($ac_use_vglink == "0") $ac_vglink_apikey = "";
    atcontent_set_user_settings_use_viglink($userid, $ac_use_vglink);
    atcontent_set_user_settings_viglink_apikey($userid, $ac_vglink_apikey);
    atcontent_api_set_viglink_api_key( $ac_vglink_apikey );
    
    echo json_encode ( array ( "IsOK" => true )); 
    exit;
}

function atcontent_save_credentials() {
    $userid = intval($_POST["userid"]);
    update_user_meta( $userid, "ac_api_key", $_POST["apikey"] );
    update_user_meta( $userid, "ac_non_delete_api_key", $_POST["apikey"] );
    update_user_meta( $userid, "ac_pen_name", $_POST["nickname"] );
    update_user_meta( $userid, "ac_showname", $_POST["showname"] );
    update_user_meta( $userid, "ac_avatar_20", $_POST["Avatar20"] );
    update_user_meta( $userid, "ac_avatar_80", $_POST["Avatar80"] );
    echo json_encode ( array ( "IsOK" => true )); 
    exit;
}

function atcontent_connect() {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $auth_data = "email=".urlencode($email)."&username=".urlencode($username);
    $connect_answer = atcontent_do_post( 'http://api.atcontent.com/v1/native/connect', $auth_data );
    if($connect_answer["IsOK"] == TRUE)
    {
        echo json_encode ( array ( "IsOK" => true , "APIKey" => $connect_answer["APIKey"], "Nickname" => $connect_answer["Nickname"], "Showname" => $connect_answer["Showname"], "Avatar20" => $connect_answer["Avatar20"], "Avatar80" => $connect_answer["Avatar80"]) ); 
    } 
    else 
    {
        echo json_encode ( array ( "IsOK" => false , "Error" => $connect_answer["Error"] ) ); 
    }
    exit;
}

function atcontent_ajax_get_sync_stat() {    
        $userid = wp_get_current_user()->ID;
        $syncid = get_user_meta( $userid, "ac_syncid", TRUE );
        $blogid = get_user_meta( $userid, "ac_blogid", TRUE );
        $stats = atcontent_api_get_sync_stat( $syncid, $blogid );
        echo json_encode( array ( "stats" => $stats ) );
        exit;
}

function atcontent_ajax_repost() {
      include( "atcontent_userinit.php" );
      $ac_postid = $_POST['ac_post'];
      $matches = preg_grep("/^[0-9a-zA-Z\-_]{11}\.text$/", explode("\n", $ac_postid));
      if ( count( $matches) !== 1 ) return;
      $repost_title = "";
      $repost_preview = "";
      $new_post = array(
          'post_title'    => 'New repost',
          'post_content'  => ''
          );
      $new_post_id = wp_insert_post( $new_post );
      $repost_result = atcontent_api_repost_publication( $ac_postid, $new_post_id );
      $embedid = '';
      if ( $repost_result["IsOK"] == TRUE ) {
          $embedid = "-/" . $repost_result["EmbedId"] . "/";
          $repost_title = $repost_result["Title"];
          $repost_preview = $repost_result["Preview"];
      }
      remove_filter( 'the_content', 'atcontent_the_content', 1 );
      remove_filter( 'the_content', 'atcontent_the_content_after', 100 );
      remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100 );
      remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
      $ac_content = 
      "<div class=\"atcontent_widget\"><div class=\"atcontent_preview\"><p>" . $repost_preview . "</p></div>" .
      "<!-- Copying the code of this post you agree with Terms of services AtContent™ (http://atcontent.com/terms/) -->" .
      "<script src=\"https://w.atcontent.com/{$embedid}{$ac_pen_name}/{$ac_postid}/Face\"></script><!--more-->" . 
      "<script data-ac-src=\"https://w.atcontent.com/{$embedid}{$ac_pen_name}/{$ac_postid}/Body\"></script></div>";
      $ac_repost_setting = atcontent_get_user_settings_oneclick_repost( intval( $userid ) );
      $post_status = $ac_repost_setting == "1" ? "publish" : "draft";
      // Create post object
      $new_post = array(
          'ID'            => $new_post_id,
          'post_title'    => $repost_title,
          'post_content'  => $ac_content,
          'post_status'   => $post_status,
          'post_author'   => $userid,
          'post_category' => array()
      );
      kses_remove_filters();
      // Insert the post into the database
      remove_all_actions( 'publish_post' );
      wp_update_post( $new_post );
      update_post_meta( $new_post_id, "ac_is_process", "0" );
      update_post_meta( $new_post_id, "ac_embedid", $embedid );
      update_post_meta( $new_post_id, "ac_repost_postid", $ac_postid );
      kses_init_filters();
      echo json_encode ( array ( "IsOK" => true ) );
      exit;
}

function atcontent_ajax_reposts_count() {
    $since = get_user_meta( wp_get_current_user()->ID, "ac_last_repost_visit", true );
    if ( strlen( $since ) == 0 ) $since = "2013-12-31";
    $new_reposts_count_answer = atcontent_api_reposts_count( $since );
    echo json_encode( $new_reposts_count_answer );
    exit;
}

function atcontent_ajax_feed_count() {
    include( "atcontent_userinit.php" );
    $since = get_user_meta( wp_get_current_user()->ID, "ac_last_repost_visit", true );
    if ( strlen( $since ) == 0 ) $since = "2014-05-30";
    $new_reposts_count_answer = atcontent_api_feed_count( $ac_api_key, $since );
    echo json_encode( $new_reposts_count_answer );
    exit;
}

function atcontent_ajax_syncqueue() {
    global $wpdb;
    include( "atcontent_userinit.php" );
    $syncid = get_user_meta( $userid, "ac_syncid", true );
    $offset = 0;
    $limit = 20;
    $posts_id = array();
    do {
        $posts = $wpdb->get_results( 
	        "
	        SELECT ID, post_title, post_author
	        FROM {$wpdb->posts}
	        WHERE post_status = 'publish' 
		        AND post_author = {$userid} AND post_type = 'post'
            ORDER BY post_date DESC LIMIT {$offset},{$limit}
	        "
        );
        foreach ( $posts as $post ) 
        {
            array_push( $posts_id, $post->ID );
        }
        $wpdb->flush();
        $offset += $limit;
    } while ( count( $posts ) > 0 );
    atcontent_api_syncqueue( $ac_api_key, $syncid, $userid, $posts_id );
    exit;
}

function atcontent_save_tags() {
    include( "atcontent_userinit.php" );
    $tags = $_POST["tags"];
    $api_result = atcontent_api_settags( $ac_api_key, $tags );
    echo json_encode( $api_result );
    exit;
}

function atcontent_ajax_set_envato_purchase(){
    include( "atcontent_userinit.php" );
    $envato_purchase_id = $_POST["ac_envato_purchase_id"];
    $api_result = atcontent_api_set_envato_purchase( $ac_api_key, $envato_purchase_id );
    update_user_meta(intval( $userid ), "ac_envato_is_ok", $api_result );
    update_user_meta(intval( $userid ), "ac_envato_purchase_id", $envato_purchase_id);
    update_user_meta(intval( $userid ), "ac_envato_activate_date", new DateTime); 
    echo json_encode( $api_result );
    exit;
}

function atcontent_save_country() {
    include( "atcontent_userinit.php" );
    $country = $_POST["country"];
    $api_result = atcontent_api_setcountry( $ac_api_key, $country );
    echo json_encode( $api_result );
    exit;
}

function atcontent_send_invites() {
    $current_user = wp_get_current_user();
    $headers[] = "From: {$current_user->display_name} <{$current_user->user_email}>";
    global $wpdb;
    $offset = 0;
    $limit = 20;
    $ac_api_key = get_user_meta( intval( $current_user->ID ), "ac_api_key", true );
    do {
        $wp_user_search = $wpdb->get_results("SELECT ID FROM {$wpdb->users} ORDER BY ID LIMIT {$offset}, {$limit}");
        foreach ( $wp_user_search as $user_id ) {
            $user = get_userdata( $user_id->ID ); 
            $userid = intval( $user->ID );
            $email = $user->user_email;
            $user_ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            if ( user_can( $userid, 'edit_posts' ) && is_string( $user_ac_api_key ) && strlen( $user_ac_api_key ) == 0 ) {
                atcontent_api_sendinvite( $ac_api_key, $current_user->display_name, $user->user_email, $user->display_name );
            }
        }
        $wpdb->flush();
        $offset += $limit;
    } while ( count( $wp_user_search ) > 0 );
    atcontent_api_sendinvite( $ac_api_key, $current_user->display_name, $current_user->user_email, $current_user->display_name );
    echo json_encode( array( "IsOK" => true ) );
    exit;
}

function atcontent_ajax_settings_tab() {
    $current_user = wp_get_current_user();
    $tab_id = $_POST["id"];
    update_user_meta( intval( $current_user->ID ), "ac_settings_tab_" . $tab_id, "1" );
    echo json_encode( array( "IsOK" => true ) );
    exit;
}

function atcontent_ajax_settings_val() {
    $current_user = wp_get_current_user();
    $id = $_POST["id"];
    $val = $_POST["val"];
    atcontent_set_user_settings_value( intval( $current_user->ID ), $id, $val );
    echo json_encode( array( "IsOK" => true ) );
    exit;
}

function atcontent_ajax_highlighted_hide() {
    include( "atcontent_userinit.php" );
    atcontent_api_highlighted_hide( $ac_api_key, $_POST["postId"] );
    echo json_encode( array( "IsOK" => true ) );
    exit;
}

function atcontent_ajax_invitefollowup() {
    echo <<<END
<script>
window.parent.parent.followup('{$_GET['key']}');
</script>
END;
    exit;
}

function atcontent_ajax_blogactivate(){
    $activate = atcontent_api_activate();
    if ( !is_array( $activate ) || $activate["IsOK"] == false ) {
        update_option( 'ac_jsonly', '1' );
    }
    echo json_encode( $activate );
    exit;
}

function atcontent_ajax_renewinfo(){
    echo json_encode(atcontent_api_renewinfo());
    exit;
}

function atcontent_ajax_wipe(){
    atcontent_wipe();
    exit;
}

?>