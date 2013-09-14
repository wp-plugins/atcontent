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

?>