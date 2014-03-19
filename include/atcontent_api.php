<?php

function atcontent_api_create_publication( $ac_api_key, 
        $post_title, $post_content, $paid_portion, $commercial_type, $post_published, $original_url,
        $cost, $is_copyprotect, $is_advanced_tracking, $comments, $tags, $categories, $blogId, $syncId, 
        $postIdInApp, $appUserId ) {
    if ( preg_match( '/<script[^>]*src="https?:\/\/w.atcontent.com/', $post_content ) == 1 ) return NULL;
    if ( preg_match( '/<script[^>]*src="https?:\/\/w.atcontent.com/', $paid_portion ) == 1 ) return NULL;
    $post_content = str_replace( "http://youtube.com", "https://youtube.com", $post_content );
    $post_content = str_replace( "http://www.youtube.com", "https://youtube.com", $post_content );
    $post_content = str_replace( "http://youtu.be", "https://youtu.be", $post_content );
    $post_content = str_replace( "http://www.youtu.be", "https://youtu.be", $post_content );
    $post_splited_content = split( "<!--more-->", $post_content );
    $post_face = $post_splited_content[0];
    $post_body = count( $post_splited_content ) > 0 ? $post_splited_content[1] : "";
    $post_paid_splited_content = split("<!--more-->", $paid_portion);
    $paid_face = $post_paid_splited_content[0];
    $paid_content = count( $post_paid_splited_content ) > 0 ? $post_paid_splited_content[1] : "";
    $post_content = 
        'Key=' . urlencode( $ac_api_key ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        '&Title=' . urlencode( $post_title ) .
        '&CommercialType=' . urlencode( $commercial_type ) .
        '&Language=' . urlencode( 'en' ) .
        '&Price=' . urlencode( $cost ).
        '&FreeFace=' . urlencode( $post_face ) .
        '&FreeContent=' . urlencode( $post_body ) .
        '&PaidFace=' . urlencode( $paid_face ) .
        '&PaidContent=' . urlencode( $paid_content ) .
        '&IsCopyProtected=' . urlencode( $is_copyprotect ) .
        '&IsAdvancedTracking=' . urlencode( $is_advanced_tracking ) .
        '&IsPaidRepost=' . urlencode( $is_paidrepost ) .
        '&Published=' . urlencode( $post_published ) .
        '&Comments=' . urlencode( $comments ) .
        '&Tags=' . urlencode( $tags ) .
        '&WPCategories=' . urlencode( $categories ) .
        '&BlogId='.urlencode( $blogId ) .
        '&SyncId='.urlencode( $syncId ) .
        '&PostId='.urlencode( $postIdInApp ) .
        '&UserId='.urlencode( $appUserId ) .
        '&AddToIndex=true' .
        ( ( $original_url != NULL && strlen( $original_url ) > 0 ) ? ( '&OriginalUrl=' . urlencode($original_url) ) : ( '' ) ) .
        '';
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/create', $post_content );
}

function atcontent_api_update_publication( $ac_api_key, 
        $post_id, $post_title, $post_content, $paid_portion, $commercial_type, $post_published, $original_url,
        $cost, $is_copyprotect, $is_advanced_tracking, $comments, $tags, $categories, $blogId, 
        $syncId, $postIdInApp, $appUserId ) {
    if ( preg_match( '/<script[^>]*src="https?:\/\/w.atcontent.com/', $post_content ) == 1 ) return NULL;
    if ( preg_match( '/<script[^>]*src="https?:\/\/w.atcontent.com/', $paid_portion ) == 1 ) return NULL;
    $post_content = str_replace( "http://youtube.com", "https://youtube.com", $post_content );
    $post_content = str_replace( "http://youtu.be", "https://youtu.be", $post_content );
    $post_splited_content = split( "<!--more-->", $post_content );
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $post_paid_splited_content = split( "<!--more-->", $paid_portion );
    $paid_face = $post_paid_splited_content[0];
    $paid_content = count( $post_paid_splited_content ) > 0 ? $post_paid_splited_content[1] : "";
    $post_content = 
        'Key=' . urlencode( $ac_api_key ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        '&PublicationID=' . urlencode( $post_id ) .
        '&Title=' . urlencode( $post_title ) .
        '&CommercialType=' . urlencode( $commercial_type ) .
        '&Language=' . urlencode( 'en' ) .
        '&Price=' . urlencode( $cost ) .
        '&FreeFace=' . urlencode( $post_face ) .
        '&FreeContent=' . urlencode( $post_body ) .
        '&PaidFace=' . urlencode( $paid_face ) .
        '&PaidContent=' . urlencode( $paid_content ) .
        '&IsCopyProtected=' . urlencode( $is_copyprotect ) .
        '&IsAdvancedTracking=' . urlencode( $is_advanced_tracking ) .
        '&Published=' . urlencode( $post_published ) .
        '&Comments=' . urlencode( $comments ) .
        '&IsPaidRepost=' . urlencode( $is_paidrepost ) .
        '&Tags=' . urldecode( $tags ) .
        '&WPCategories=' . urlencode( $categories ) .
        '&BlogId=' . urlencode( $blogId ) .
        '&SyncId=' . urlencode( $syncId ) .
        '&PostId=' . urlencode( $postIdInApp ) .
        '&UserId=' . urlencode( $appUserId ) .
        '&AddToIndex=true'.
        ( ( $original_url != NULL && strlen($original_url) > 0 ) ? ( '&OriginalUrl=' . urlencode($original_url) ) : ( '' ) ).
        '';
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/update', $post_content );
}

function atcontent_api_import_publication($ac_api_key, $blogId, $syncId, $postIdInApp, $appUserId)
{
    $post_content = 
        'Key=' . urlencode( $ac_api_key ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        '&BlogId=' . urlencode( $blogId ) .
        '&SyncId=' . urlencode( $syncId ) .
        '&PostId=' . urlencode( $postIdInApp ) .
        '&UserId=' . urlencode( $appUserId );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/importpost', $post_content );
}

function atcontent_api_update_publication_comments($ac_api_key, $post_id, $comments) {
    $post_content = 'Key='.
        urlencode($ac_api_key).'&AppID='.urlencode('WordPress').
        '&PublicationID='.urlencode($post_id).
        '&Comments='.urlencode($comments);
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/update', $post_content );
}

function atcontent_api_get_nickname( $ac_api_key ) {
    $post_content = 'Key='.
        urlencode( $ac_api_key ) . '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/nickname', $post_content );
}

function atcontent_api_get_userinfo( $ac_api_key ) {
    $post_content = 'Key='.
        urlencode( $ac_api_key ) . '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://atcontent.com/api/v1/native/userinfo.ashx', $post_content );
}

function atcontent_api_get_key( $nounce, $grant ) {
    $post_content = 'Nounce='. urlencode( $nounce ) . 
        '&Grant='. urlencode( $grant ) . 
        '&AppID=' . urlencode( 'WordPress' ) ;
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/requestkey', $post_content );
}

function atcontent_api_pingback( $email, $status, $api_key, $referral ) {    
    if (strlen($api_key) == 0)
    {
        $userid = wp_get_current_user() -> ID;
        $api_key = get_user_meta($userid, "ac_non_delete_api_key", true);        
    }
    $post_content = 'Email='. urlencode( $email ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $status != NULL ? '&Status=' . urlencode( $status ) : '' ) .
        ( $api_key != NULL ? '&APIKey=' . urlencode( $api_key ) : '' ) .
        ( $referral != NULL ? '&Referral=' . urlencode( $referral ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/pingback', $post_content );
}

function atcontent_api_sitecategory( $siteuri, $category, $country, $state, $api_key ) {
    $post_content = 'SiteUri='. urlencode( $siteuri ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        '&Category=' . urlencode( $category ) . 
        '&Country=' . urlencode( $country ) . 
        '&State=' . urlencode( $state ) . 
        ( $api_key != NULL ? '&APIKey=' . urlencode( $api_key ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/sitecategory', $post_content );
}

function atcontent_api_readership( $siteuri, $postids, $api_key ) {
    $post_content = 'SiteUri=' . urlencode( $siteuri ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        '&PostIDs=' . urlencode( $postids ) .
        '&Key=' . urlencode( $api_key ) .
        '&v2=1' .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/readership', $post_content );
}

function atcontent_api_reposts_count( $since ) {
    $post_content = 
        'since=' . urlencode( $since ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/general/repostscount', $post_content );
}

function atcontent_api_reposts( $category, $page ) {
    $post_content = 
        'Category=' . urlencode( $category ) .
        '&Page=' . urlencode( $page ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/general/repost', $post_content );
}
/////////////////////////////////////////////////////////////////////
function atcontent_api_repost_publication($postid, $post_id_in_app){
    $userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true ); 
    $syncid = get_user_meta( $userid, "ac_syncid", true ); 
    $ac_blogid = get_user_meta( $userid, "ac_blogid", true ); 
    $post_content =
        'PostId=' . urlencode( $postid ) .
        '&ApiKey=' . urlencode( $ac_api_key ) .
        '&PostIdInApp=' . urlencode( $post_id_in_app ) .
        '&SyncId=' . urlencode( $syncid ) .
        '&BlogId=' . urlencode( $ac_blogid );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/repost', $post_content );
}

function atcontent_api_get_title( $postid ) {
    $post_content = 
        'PostId=' . urlencode( $postid ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/general/title', $post_content );
}

function atcontent_api_get_quotas( $api_key ) {
    $post_content = 
        'AppID=' . urlencode( 'WordPress' ) .
        '&Key=' . urlencode( $api_key ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/quotas', $post_content );
}

function atcontent_api_syncqueue( $api_key, $syncid, $userid, $postids ) {
    $post_content = 
        'AppID=' . urlencode( 'WordPress' ) .
        '&Key=' . urlencode( $api_key ) .
        '&SyncId=' . urlencode( $syncid ) .
        '&UserId=' . urlencode( $userid ) .
        '&PostIds=' . urlencode( json_encode( $postids ) ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/syncqueue', $post_content );
}

function atcontent_api_get_sync_stat($syncId, $blogId  ) {
    $post_content = 
        'syncid=' . urlencode( $syncId ) .
        '&blogid=' . urlencode( $blogId );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/syncstat', $post_content );
}

function atcontent_do_post( $url, $data ) {
    $wp_response = wp_remote_post( $url, array(
        'method' => 'POST',
	    'timeout' => 300,
	    'redirection' => 5,
	    'httpversion' => '1.0',
	    'blocking' => true,
	    'headers' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL . 'User-Agent: IE 10.00' . PHP_EOL,
	    'body' => $data,
	    'cookies' => array()
        )    
     );
    if (is_wp_error($wp_response)){
        $out_array = array( 'IsOK' => FALSE, 'error' =>  $res );
    } else {
        try {
            $out_array = json_decode( $wp_response['body'], true );
        } catch (Exception $e) { }
        if ( !is_array( $out_array ) ) {
            $out_array = array( 'IsOK' => FALSE, 'error' =>  $wp_response );
        }
    }
    return $out_array;
}

?>