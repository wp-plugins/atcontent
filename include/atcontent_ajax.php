<?php
function atcontent_readership() {
    global $wpdb;
    $userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta($userid, "ac_api_key", true );
    if ( current_user_can( 'edit_posts' ) ) {

        $posts = $wpdb->get_results( 
	        "
	        SELECT ID, post_title, post_author
	        FROM {$wpdb->posts}
	        WHERE post_status = 'publish' 
		        AND post_author = {$userid} AND post_type = 'post'
	        "
        );

        $posts_id = array();

        foreach ( $posts as $post ) 
        {
            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            if ( strlen( $ac_postid ) > 0 ) { 
                array_push( $posts_id, $ac_postid );
            }
        }

        $response = atcontent_api_readership( site_url(), json_encode( $posts_id ), $ac_api_key );
 
	    // response output
	    header( "Content-Type: application/json" );
	    echo json_encode( $response );
    }
 
    // IMPORTANT: don't forget to "exit"
    exit;
}

function atcontent_ajax_guestpost(){
    $blogusers = get_users();
    $ac_is_active = false;
    $blogurl = "";
    foreach ($blogusers as $user) {
        $ac_api_key = get_user_meta( $user->ID, "ac_api_key", true );
        if ( strlen( $ac_api_key ) > 0 ) {
            $ac_is_active = true;
            $blogurl = site_url();
            break;
        }
    }
    echo json_encode( array ( "IsOK" => true, "IsActive" => $ac_is_active, "Url" => $blogurl ) );
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
                $ac_content = "<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --><script src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face\"></script><!--more--><script src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body\"></script>";
                //$ac_content = "[atcontent id=\"{$ac_postid}\"]";
                kses_remove_filters();
                $new_post = array(
                    'post_title'    => $repost_title,
                    'post_content'  => $ac_content,
                    'post_status'   => 'publish',
                    'post_author'   => $userid,
                    'post_category' => array()
                );
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
            if ( strlen( $ac_api_key ) > 0 && ($ac_api_key == $_POST["key"]) ) {
                $post = get_post( $postid );
                if ($post == null) exit;
                $ac_user_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
                if ( strlen( $ac_user_copyprotect ) == 0 ) $ac_user_copyprotect = "1";
                $ac_is_copyprotect = get_post_meta( $post->ID, "ac_is_copyprotect", true );
                if ( strlen( $ac_is_copyprotect ) == 0 ) { 
                    $ac_is_copyprotect = $ac_user_copyprotect;
                    update_post_meta($post_id, "ac_is_copyprotect", $ac_is_copyprotect);
                }
                $ac_is_advanced_tracking = get_post_meta( $post->ID, "ac_is_advanced_tracking", true );
                if ( strlen( $ac_is_advanced_tracking ) == 0 ) { 
                    $ac_is_advanced_tracking = "1";
                    update_post_meta( $post_id, "ac_is_advanced_tracking", $ac_is_advanced_tracking );
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
                    ) );
            }
            break;
        case "newpost":
            $userid = $_POST["userid"];
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            $ac_pen_name = get_user_meta( $userid, "ac_pen_name", true );
            if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "AtContent";
            $ac_postid = $_POST["postid"];
            $embedid = $_POST["embedid"];
            $title = $_POST["title"];
            if ( strlen( $ac_api_key ) > 0 && ($ac_api_key == $_POST["key"]) ) {
                remove_filter( 'the_content', 'atcontent_the_content', 1 );
                remove_filter( 'the_content', 'atcontent_the_content_after', 100);
                remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100);
                remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
                $ac_content = 
                "<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) -->" .
                "<script src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face\"></script><!--more-->" . 
                "<script data-ac-src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body\"></script>";
                // Create post object
                $new_post = array(
                    'post_title'    => $repost_title,
                    'post_content'  => $ac_content,
                    'post_status'   => 'publish',
                    'post_author'   => $userid,
                    'post_category' => array()
                );
                // Insert the post into the database
                $new_post_id = wp_insert_post( $new_post );
                echo json_encode ( array ( "IsOK" => true, "PostId" => $new_post_id ) );
            }
            break;
        case "updatepost":
            $userid = $_POST["userid"];
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            $postid = $_POST["blogpostid"];
            $embedid = $_POST["embedid"];
            $ac_postid = $_POST["postid"];
            if ( strlen( $ac_api_key ) > 0 && ($ac_api_key == $_POST["key"]) ) {
                update_post_meta( intval( $postid ), "ac_postid", $ac_postid );
                update_post_meta( intval( $postid ), "ac_is_process", "1" );
                update_post_meta( intval( $postid ), "ac_embedid", $embedid );
            }
            echo json_encode ( array ( "IsOK" => true ) );
            break;
    }
    exit;
}

function atcontent_ajax_guestpost_check_url(){
    $testurl = $_POST["url"];
    if ( strpos( $testurl, "http://" ) !== 0 &&
         strpos( $testurl, "https://" ) !== 0 ) {
             $testurl = "http://" . $testurl;
    }
    $urlparts = explode ( "/", $testurl );
    $requests = array();
    $answers = array();
    do {
        $post_content = 'action=atcontent_guestpost';
        $requesturl = implode ( "/", $urlparts ) . "/wp-admin/admin-ajax.php";
        $requests[] = $requesturl;
        try {
            $answer = atcontent_do_post( $requesturl , $post_content );
            $answers[] = $answer;
            if ( $answer["IsOK"] == true ) {
                echo json_encode( $answer );
                exit;
            }
        } catch (Exception $ex) { }
    } while ( ( $toppart = array_pop ( $urlparts ) ) != null );
    echo json_encode ( array ( "IsOK" => false, "Tests" => $requests, "Answers" => $answers ) );
    exit;
}


function atcontent_api_key()
{
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
            $connect_result = atcontent_api_connectgate( $ac_api_key, $userid, get_site_url(), admin_url("admin-ajax.php") );
            if ( $connect_result["IsOK"] == TRUE ) {
                update_user_meta( $userid, "ac_oneclick_repost", "1" );
            }
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

function atcontent_hide_rate(){    
    $userid = wp_get_current_user()->ID;
    update_user_meta( $userid, "ac_rated", true );
    echo json_encode ( array ( "IsOK" => true ) );
    exit;
}

function atcontent_save_credentials()
{
    update_user_meta( $userid, "ac_api_key", $_POST["APIKey"] );
    update_user_meta( $userid, "ac_pen_name", $_POST["Nickname"] );
    update_user_meta( $userid, "ac_showname", $_POST["Showname"] );
    update_user_meta( $userid, "ac_avatar_20", $_POST["Avatar20"] );
    update_user_meta( $userid, "ac_avatar_80", $_POST["Avatar80"] );
    echo json_encode ( array ( "IsOK" => true ) ); 
    exit;
}

function atcontent_connect()
{
    $email = $_POST['email'];
    $username = $_POST['username'];
    $key = md5(microtime().rand());
    update_user_meta($userid, "ac_temporary_key", $key);
    $site_title = $_POST['title'];
    $auth_data = "email=".urlencode($email)."&username=".urlencode($username)."&key=".$key."&sitetitle=".$site_title;
    $connect_answer = atcontent_do_post( 'http://api.atcontent.com/v1/native/connect', $auth_data );
    if($connect_answer["IsOK"] == TRUE)
    {
        echo json_encode ( array ( "IsOK" => true ) ); 
    } 
    else 
    {
        echo json_encode ( array ( "IsOK" => false , "Error" => $connect_answer["Error"])); 
    }
    exit;
}

function atcontent_ajax_repost(){
        include("atcontent_userinit.php");
        $ac_postid = $_POST['ac_post'];
        $repost_title_answer = atcontent_api_get_title( $ac_postid );
        $repost_title = "Not found";
        if ( $repost_title_answer["IsOK"] == true ) {
            $repost_title = $repost_title_answer["Title"];
        }
        remove_filter( 'the_content', 'atcontent_the_content', 1 );
        remove_filter( 'the_content', 'atcontent_the_content_after', 100);
        remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100);
        remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
        $ac_content = "<!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --><script src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face\"></script><!--more--><script data-ac-src=\"https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body\"></script>";
        // Create post object
        $new_post = array(
            'post_title'    => $repost_title,
            'post_content'  => $ac_content,
            'post_status'   => 'publish',
            'post_author'   => $userid,
            'post_category' => array()
        );
        // Insert the post into the database
        $new_post_id = wp_insert_post( $new_post );
        update_post_meta($new_post_id, "ac_repost_postid", $ac_postid);
        echo json_encode ( array ( "IsOK" => true ) );
        exit;
    }

function atcontent_ajax_syncqueue(){
    global $wpdb;
    include("atcontent_userinit.php");
    $posts_id = array();
    $posts = $wpdb->get_results( 
	    "
	    SELECT ID, post_author
	    FROM {$wpdb->posts}
	    WHERE post_status = 'publish' 
		    AND post_author = {$userid} AND post_type = 'post'
	    "
    );
    wp_cache_flush();
    foreach ( $posts as $post ) 
    {
        if ($post->post_author == $userid) {
            array_push( $posts_id, $post->ID );
        }
        wp_cache_flush();
    }
    atcontent_api_syncqueue( $ac_api_key, admin_url( 'admin-ajax.php' ), $userid, $posts_id );
    exit;
}

?>