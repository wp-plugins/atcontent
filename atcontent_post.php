<?php

function atcontent_publish_publication( $post_id ){
    atcontent_save_meta( $post_id );
	if ( !wp_is_post_revision( $post_id ) ) {
		$post_url = get_permalink( $post_id );
		$post = get_post( $post_id );
        if ( $post == null ) return;
        $userid =  intval( $post->post_author );
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        if ( strlen( $ac_api_key ) > 0 ) {

            $ac_user_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
            if ( strlen( $ac_user_copyprotect ) == 0 ) $ac_user_copyprotect = "1";
            $ac_user_paidrepost = get_user_meta( $userid, "ac_paidrepost", true );
            if ( strlen( $ac_user_paidrepost ) == 0 ) $ac_user_paidrepost = "0";
            $ac_user_paidrepostcost = get_user_meta( $userid, "ac_paidrepostcost", true );
            if ( strlen( $ac_user_paidrepostcost ) == 0 ) $ac_user_paidrepostcost = "2.50";
            $ac_user_is_import_comments = get_user_meta( $userid, "ac_is_import_comments", true );
            if ( strlen( $ac_user_is_import_comments ) == 0 ) $ac_user_is_import_comments = "0";

            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );

            if ( strlen( $ac_is_process ) == 0 ) { 
                $ac_is_process = "1";
                update_post_meta($post_id, "ac_is_process", $ac_is_process);
            }
            
            $ac_is_copyprotect = get_post_meta( $post->ID, "ac_is_copyprotect", true );
            if ( strlen( $ac_is_copyprotect ) == 0 ) { 
                $ac_is_copyprotect = $ac_user_copyprotect;
                update_post_meta($post_id, "ac_is_copyprotect", $ac_is_copyprotect);
            }

            $ac_is_paidrepost = get_post_meta( $post->ID, "ac_is_paidrepost", true );
            if ( strlen( $ac_is_paidrepost ) == 0 ) { 
                $ac_is_paidrepost = $ac_user_paidrepost;
                update_post_meta($post_id, "ac_is_paidrepost", $ac_is_paidrepost);
            }

            $ac_is_import_comments = get_post_meta( $post->ID, "ac_is_import_comments", true );
            if ( strlen( $ac_is_import_comments ) == 0 ) {
                $ac_is_import_comments = $ac_user_is_import_comments;
                update_post_meta($post_id, "ac_is_import_comments", $ac_is_import_comments);
            }

            $ac_paidrepost_cost = get_post_meta($post->ID, "ac_paidrepost_cost", true);
            if ( strlen( $ac_paidrepost_cost ) == 0 ) { 
                $ac_paidrepost_cost = $ac_user_paidrepostcost; 
                update_post_meta($post_id, "ac_paidrepost_cost", $ac_paidrepost_cost);
            }

            $ac_cost = get_post_meta($post->ID, "ac_cost", true);
            if ( strlen( $ac_cost ) == 0 ) { 
                $ac_cost = $ac_user_paidrepostcost;
                update_post_meta($post_id, "ac_cost", $ac_cost);
            }

            $ac_type = get_post_meta( $post->ID, "ac_type", true );
            if ( strlen( $ac_type ) == 0 ) {
                if ($ac_is_paidrepost == "1") $ac_type = "paidrepost";
                else $ac_type = "free";
                update_post_meta($post_id, "ac_type", $ac_type);
            }

            if ( $ac_is_process != "1" ) return;
            
            atcontent_coexistense_fixes();

            $testcontent = apply_filters( "the_content",  $post->post_content );
            $testcontent .= apply_filters( "the_content",  $ac_paid_portion );

            if ( preg_match_all("/<script[^<]+src=\"https?:\/\/w.atcontent.com/", $testcontent, $ac_scripts_test ) && count( $ac_scripts_test ) > 0 ) {
                update_post_meta( $post_id, "ac_is_process", "2" );
                return;
            }

            $comments_json = "";
            if ( $ac_is_import_comments == "1" ) {
                $comments = get_comments( array(
                    'post_id' => $post->ID,
                    'order' => 'ASC',
                    'orderby' => 'comment_date_gmt',
                    'status' => 'approve',
                ) );
                if ( !empty( $comments ) ) {
                    $comments_json .= json_encode( $comments );
                }
            }
            if ( strlen( $ac_postid ) == 0 ) {
                $api_answer = atcontent_create_publication( $ac_api_key, $post->post_title, 
                        apply_filters( "the_content",  $post->post_content ) , 
                        apply_filters( "the_content",  $ac_paid_portion ),  
                        $ac_type, get_gmt_from_date( $post->post_date ), get_permalink( $post->ID ),
                    $ac_cost, $ac_is_copyprotect, $comments_json );
                if ( is_array( $api_answer ) && strlen( $api_answer["PublicationID"] ) > 0 ) {
                    $ac_postid = $api_answer["PublicationID"];
                    update_post_meta( $post->ID, "ac_postid", $ac_postid );
                } else {
                    update_post_meta( $post->ID, "ac_is_process", "2" );
                }
            } else {
                $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title, 
                    apply_filters( "the_content", $post->post_content  ) , 
                    apply_filters( "the_content",  $ac_paid_portion ), 
                    $ac_type , get_gmt_from_date( $post->post_date ), get_permalink( $post->ID ),
                    $ac_cost, $ac_is_copyprotect, $comments_json
                        );
                if ( is_array( $api_answer ) && strlen( $api_answer["PublicationID"] ) > 0 ) {
                } else {
                    update_post_meta( $post->ID, "ac_is_process", "2" );
                }
            }
        }
	}
}

function atcontent_save_post( $post_id ){
    atcontent_save_meta( $post_id );
}

function atcontent_save_meta( $post_id ) {

    if ( $_POST['atcontent_type'] == null ) 
        return;

    if ( !current_user_can( 'edit_post', $post_id ) )
        return;

    // OK, we're authenticated: we need to find and save the data

    $post = get_post( $post_id );
    if ( $post == null ) return;

    $ac_is_process = $_POST['atcontent_is_process'];
    $ac_is_copyprotect = $_POST['atcontent_is_copyprotect'];
    $ac_is_paidrepost = $_POST['atcontent_is_paidrepost'];
    $ac_cost = $_POST['atcontent_cost'];
    $ac_is_import_comments = $_POST['atcontent_is_import_comments'];
    $ac_paid_portion = $_POST['ac_paid_portion'];
    $ac_type = $_POST['atcontent_type'];

    if ($ac_is_process != "1") $ac_is_process = "0";
    update_post_meta($post_id, "ac_is_process", $ac_is_process);
        
    if ($ac_is_copyprotect != "1") $ac_is_copyprotect = "0";
    update_post_meta($post_id, "ac_is_copyprotect", $ac_is_copyprotect);

    if ($ac_is_paidrepost != "1") $ac_is_paidrepost = "0";
    update_post_meta($post_id, "ac_is_paidrepost", $ac_is_paidrepost);

    update_post_meta($post_id, "ac_cost", $ac_cost);

    if ($ac_is_import_comments != "1") $ac_is_import_comments = "0";
    update_post_meta( $post_id, "ac_is_import_comments", $ac_is_import_comments );

    update_post_meta( $post_id, 'ac_type', $ac_type );

    if ($ac_paid_portion != NULL) {
        update_post_meta( $post_id, "ac_paid_portion", $ac_paid_portion );
    }

    remove_filter( 'the_content', 'atcontent_the_content', 1 );
    
    $testcontent = apply_filters( "the_content",  $post->post_content );
    $testcontent .= apply_filters( "the_content",  $ac_paid_portion );

    add_filter( 'the_content', 'atcontent_the_content', 1 );

    if ( preg_match_all("/<script[^<]+src=\"https?:\/\/w.atcontent.com/", $testcontent, $ac_scripts_test ) && count( $ac_scripts_test ) > 0 ) {
        update_post_meta( $post_id, "ac_is_process", "2" );
    }

}

function atcontent_repost_preview( $posts ) {
	global $wp_query;
    global $wp;
    global $atcontent_reposts;

    $userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );

  	if ( $_GET['ac_repost_post'] != null ) {
        $repost_title = $atcontent_reposts[$_GET['ac_repost_post']];
        global $wp_filter;
        remove_filter( 'the_content', 'atcontent_the_content', 1 );
        remove_filter( 'the_content', 'atcontent_the_content_after', 100);
        remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100);
        remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
        $accept_uri = admin_url("admin.php?page=atcontent/repost.php&postid=" . $_GET['ac_repost_post']);
        $decline_uri = admin_url("admin.php?page=atcontent/repost.php");
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = "ac_guest_post";
        $post->guid = get_bloginfo('wpurl/ac_guest_post');
        $post->post_title = 'Preview ' . $repost_title;
        $post->post_content = '[atcontent id="' . $_GET['ac_repost_post'] . '"]' .
        <<<END
<script type="text/javascript">
var processed = false;
function accept_guest_post(){
    if (processed) return;
    processed = true;
    window.location = decodeuri('{$accept_uri}');
}
function decline_guest_post(){
    if (processed) return;
    processed = true;
    window.location = decodeuri('{$decline_uri}');
}
function decodeuri(uri) {
    var div = document.createElement('div');
    div.innerHTML = uri;
    return div.firstChild.nodeValue;
}
</script>
<p><input type="button" onClick="accept_guest_post()" value="Accept"> or <input type="button" onClick="decline_guest_post()" value="Decline"></p>
END;
        $post->ID = -42;
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql',1);

        $posts = NULL;
        $posts[] = $post;

        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        unset($wp_query->query["error"]);
        $wp_query->query_vars["error"]="";
        $wp_query->is_404 = false;
    }

    return $posts;
}
add_filter('the_posts', 'atcontent_repost_preview' );

?>