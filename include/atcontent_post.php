<?php

function atcontent_publish_publication( $post_id ) {
    atcontent_save_meta( $post_id );
    if ( !wp_is_post_revision( $post_id ) ) {
            atcontent_process_publication( $post_id );
    }
}

function atcontent_process_publication( $post_id ) {
    $post_url = get_permalink( $post_id );
	$post = get_post( $post_id );
    if ( $post == null ) return;
    $userid = intval( $post->post_author );
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_blogid = get_user_meta( $userid, "ac_blogid", true );
    $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
    if ( strlen( $ac_api_key ) > 0 ) {
        $ac_user_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
        if ( strlen( $ac_user_copyprotect ) == 0 ) $ac_user_copyprotect = "1";
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_type = "free";
        $ac_cost = "2.50";
        if ( strlen( $ac_is_process ) == 0 ) { 
            $ac_is_process = "1";
            update_post_meta( $post_id, "ac_is_process", $ac_is_process );
        }
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
        if ( $ac_is_process == "0" ) return;
        atcontent_coexistense_fixes();
        $testcontent = apply_filters( "the_content",  $post->post_content );
        $testcontent .= $post -> post_content;
        if ( preg_match_all("/<script[^<]+src=\"https?:\/\/w\.atcontent\.com/", $testcontent, $ac_scripts_test ) && count( $ac_scripts_test ) > 0 ) {
            update_post_meta( $post_id, "ac_is_process", "2" );
        }
        if ( !ac_isjsonly() ) {
            atcontent_api_import_publication( $ac_api_key, $ac_blogid, $ac_syncid, $post_id, $userid );
        }
    }
}

function atcontent_save_post( $post_id ){
    atcontent_save_meta( $post_id );
}

function atcontent_save_meta( $post_id ) {

    if (  ! isset( $_POST['atcontent_save_meta'] ) ) 
        return;

    if ( !current_user_can( 'edit_post', $post_id ) )
        return;

    $ac_is_process = "0";
    if ( isset( $_POST['atcontent_is_process'] ) ) {
        $ac_is_process = $_POST['atcontent_is_process'];
    }
    $ac_is_copyprotect =  "0";
    if ( isset( $_POST['atcontent_is_copyprotect'] ) ) {
        $ac_is_copyprotect = $_POST['atcontent_is_copyprotect'];
    }
    $ac_is_advanced_tracking = "0";
    if ( isset( $_POST["atcontent_is_advanced_tracking"] ) ) {
        $ac_is_advanced_tracking = $_POST["atcontent_is_advanced_tracking"];
    }

    if ( $ac_is_process != "1" ) $ac_is_process = "0";
    update_post_meta( $post_id, "ac_is_process", $ac_is_process );
        
    if ( $ac_is_copyprotect != "1" ) $ac_is_copyprotect = "0";
    if ( $ac_is_copyprotect == "1"){
        update_post_meta( $post_id, "ac_is_copyprotect", $ac_is_copyprotect );
    }

    if ( $ac_is_advanced_tracking != "1" ) $ac_is_advanced_tracking = "0";
    if ( $ac_is_advanced_tracking == "1" ){
        update_post_meta( $post_id, "ac_is_advanced_tracking", $ac_is_advanced_tracking );
    }
    
    remove_filter( 'the_content', 'atcontent_the_content', 1 );
    
    $post = get_post( $post_id );
    $testcontent = "";
    if ( $post != null ) {
        $testcontent = apply_filters( "the_content",  $post->post_content );
    }

    add_filter( 'the_content', 'atcontent_the_content', 1 );

    if ( preg_match_all("/<script[^<]+src=\"https?:\/\/w.atcontent.com/", $testcontent, $ac_scripts_test ) && count( $ac_scripts_test ) > 0 ) {
        update_post_meta( $post_id, "ac_is_process", "2" );
    }
}

?>