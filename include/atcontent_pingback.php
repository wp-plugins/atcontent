<?php

function atcontent_pingback() {
    $userid = wp_get_current_user()->ID;
    $email = wp_get_current_user()->user_email;
    $ac_api_key = get_user_meta($userid, "ac_api_key", true );
    $ac_referral = get_user_meta($userid, "ac_referral", true );
    if ( current_user_can( 'edit_posts' ) ) {
        $status = 'Installed';
        if ( strlen( $ac_api_key ) > 0 ) { 
            $status = 'Connected';
        } else {
            $status = 'Disconnected';
        }
        atcontent_api_pingback( $email, $status, $ac_api_key, $ac_referral );
	    // generate the response
	    $response = json_encode( array( 'IsOK' => true ) );
	    // response output
	    header( "Content-Type: application/json" );
	    echo $response;
    }
    // IMPORTANT: don't forget to "exit"
    exit;
}

function atcontent_pingback_inline() {
    $userid = wp_get_current_user()->ID;
    $email = wp_get_current_user()->user_email;
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_referral = get_user_meta( $userid, "ac_referral", true );
    if ( current_user_can( 'edit_posts' ) ) {
        $status = 'Installed';
        if ( strlen( $ac_api_key ) > 0 ) { 
            $status = 'Connected';
        } else {
            $status = 'Disconnected';
        }
        $res = atcontent_api_pingback( $email, $status, $ac_api_key, $ac_referral );
        if ( is_array( $res ) && $res["IsOK"] == TRUE ) return TRUE; 
    }
    return FALSE;
}

function atcontent_activate() {
    try {
        global $wpdb;
        $offset = 0;
        $limit = 20;
        do {
            $wp_user_search = $wpdb->get_results("SELECT ID, user_email FROM {$wpdb->users} ORDER BY ID LIMIT {$offset}, {$limit}");
            foreach ( $wp_user_search as $user ) {
                $userid = $user->ID;
                $email = $user->user_email;
                $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
                $ac_referral = get_user_meta( $userid, "ac_referral", true );
                if ( user_can( $userid, 'edit_posts' ) ) {
                    $status = 'Activated';
                    if ( strlen( $ac_api_key ) > 0 ) {
                        $status = 'Connected'; 
                    } else {
                        $ac_pen_name = get_user_meta( intval( $userid ), "ac_pen_name", true );
                        if ( strlen( $ac_pen_name ) > 0 ) { 
                            $status = 'Disconnected';
                        }
                    }
                    if ( $status == 'Connected' || $status == 'Disconnected' ) {
                        atcontent_api_pingback( $email, $status, $ac_api_key, $ac_referral );
                    }
                }
            }
            $wpdb->flush();
            $offset += $limit;
        } while ( count( $wp_user_search ) > 0 );
    } catch ( Exception $e ) { }
}

function atcontent_deactivate() {
    try {
        global $wpdb;
        $offset = 0;
        $limit = 20;
        do {
            $wp_user_search = $wpdb->get_results("SELECT ID, user_email FROM {$wpdb->users} ORDER BY ID LIMIT {$offset}, {$limit}");
            foreach ( $wp_user_search as $user ) {
                $userid = $user->ID;
                $email = $user->user_email;
                $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
                if ( user_can( $userid, 'edit_posts' ) && strlen( $ac_api_key ) > 0 ) {
                    $status = 'Deactivated';
                    atcontent_api_pingback( $email, $status, $ac_api_key, "" );
                }
            }
            $wpdb->flush();
            $offset += $limit;
        } while ( count( $wp_user_search ) > 0 );
    } catch ( Exception $e ) { }
}

function atcontent_uninstall() {
    try {
        global $wpdb;
        $offset = 0;
        $limit = 20;
        do {
            $wp_user_search = $wpdb->get_results("SELECT ID, user_email FROM {$wpdb->users} ORDER BY ID LIMIT {$offset}, {$limit}");
            foreach ( $wp_user_search as $user ) {
                $userid = $user->ID;
                $email = $user->user_email;
                $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
                if ( user_can( $userid, 'edit_posts' ) && strlen( $ac_api_key ) > 0 ) {
                    $status = 'Uninstalled';
                    atcontent_api_pingback( $email, $status, $ac_api_key, "" );
                }
            }
            $wpdb->flush();
            $offset += $limit;
        } while ( count( $wp_user_search ) > 0 );
    } catch ( Exception $e ) { }
}