<?php

function atcontent_publish_publication( $post_id ){
    atcontent_save_meta( $post_id );
	if ( !wp_is_post_revision( $post_id ) ) {
		$post_url = get_permalink( $post_id );
		$post = get_post( $post_id );
        if ( $post == null ) return;
        $ac_api_key = get_user_meta( intval( $post->post_author ), "ac_api_key", true );
        if ( strlen( $ac_api_key ) > 0 ) {
            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
            $ac_cost = get_post_meta( $post->ID, "ac_cost", true );
            $ac_is_copyprotect = get_post_meta( $post->ID, "ac_is_copyprotect", true );
            $ac_type = get_post_meta( $post->ID, "ac_type", true );
            $ac_paid_portion = get_post_meta( $post->ID, "ac_paid_portion", true );
            $ac_is_import_comments = get_post_meta( $post->ID, "ac_is_import_comments", true );
            if ( $ac_is_process != "1" ) return;

            atcontent_coexistense_fixes();

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

    if ( $_POST['atcontent_is_process'] == null ) 
        return;

    if ( !current_user_can( 'edit_post', $post_id ) )
        return;

    // OK, we're authenticated: we need to find and save the data

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
}

?>