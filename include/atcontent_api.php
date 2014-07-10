<?php


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

function atcontent_api_get_nickname( $ac_api_key ) {
    $post_content = 'Key='.
        urlencode( $ac_api_key ) . '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/nickname', $post_content );
}

function atcontent_api_get_userinfo( $ac_api_key ) {
    $post_content = 'Key='.
        urlencode( $ac_api_key ) . '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/userinfo', $post_content );
}

function atcontent_api_settags( $ac_api_key, $tags ) {
    $post_content = 
        'Key=' . urlencode( $ac_api_key ) . 
        '&Tags=' . urlencode( $tags ) . 
        '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/setuserinfo', $post_content );
}

function atcontent_api_setcountry( $ac_api_key, $country ) {
    $post_content = 
        'Key=' . urlencode( $ac_api_key ) . 
        '&Country=' . urlencode( $country ) . 
        '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/setuserinfo', $post_content );
}

function atcontent_api_marketplace( $ac_api_key ) {
    $post_content = 
        'Key=' . urlencode( $ac_api_key ) .
        '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/marketplace', $post_content );
}

function atcontent_api_get_key( $nounce, $grant ) {
    $post_content = 'Nounce=' . urlencode( $nounce ) . 
        '&Grant=' . urlencode( $grant ) . 
        '&AppID=' . urlencode( 'WordPress' ) ;
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/requestkey', $post_content );
}

function atcontent_api_pingback( $email, $status, $api_key, $referral ) {    
    if ( strlen($api_key) == 0 )
    {
        $userid = wp_get_current_user() -> ID;
        $api_key = get_user_meta( $userid, "ac_non_delete_api_key", true );        
    }
    $post_content = 'Email=' . urlencode( $email ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $status != NULL ? '&Status=' . urlencode( $status ) : '' ) .
        ( $api_key != NULL ? '&APIKey=' . urlencode( $api_key ) : '' ) .
        ( $referral != NULL ? '&Referral=' . urlencode( $referral ) : '' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/pingback', $post_content );
}

function atcontent_api_readership( $siteuri, $postids, $api_key ) {
    $post_content = 'SiteUri=' . urlencode( $siteuri ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        '&PostIDs=' . urlencode( $postids ) .
        '&Key=' . urlencode( $api_key ) .
        '&v4=1' .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/readership', $post_content );
}

function atcontent_api_reposts_count( $since ) {
    $post_content = 
        'since=' . urlencode( $since ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/general/repostscount', $post_content );
}

function atcontent_api_feed_count( $api_key, $since ) {
    $post_content = 
        'since=' . urlencode( $since ) .
        '&Key=' . urlencode( $api_key ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/feedcount', $post_content );
}

function atcontent_api_feed( $api_key, $tag,  $page ) {
    $post_content = 
        'Key=' . urlencode( $api_key ) .
        '&Tag=' . urlencode( $tag ) .
        '&Page=' . urlencode( $page ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/feed', $post_content );
}

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

function atcontent_api_sendinvite( $api_key, $name, $toemail, $toname ) {
    $siteuri = get_bloginfo( 'url' );
    $sitetitle = get_bloginfo( 'name' );
    $post_content = 
        'AppID=' . urlencode( 'WordPress' ) .
        '&Key=' . urlencode( $api_key ) .
        '&name=' . urlencode( $name ) .
        '&toemail=' . urlencode( $toemail ) .
        '&toname=' . urlencode( $toname ) .
        '&siteurl=' . urlencode( $siteuri ) .
        '&sitetitle=' . urlencode( $sitetitle ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/invite', $post_content );
}

function atcontent_do_post( $url, $data ) {
    $wp_response = wp_remote_post( $url, array(
        'method' => 'POST',
        'timeout' => 300,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
        'body' => $data,
        'cookies' => array()
        )    
     );
    if ( is_wp_error( $wp_response ) ){
        $out_array = array( 'IsOK' => FALSE, 'Error' => json_encode( $wp_response ) );
    } else {
        try {
            $out_array = json_decode( $wp_response['body'], true );
        } catch (Exception $e) { }
        if ( !is_array( $out_array ) ) {
            $out_array = array( 'IsOK' => FALSE, 'Error' =>  $wp_response );
        }
    }
    return $out_array;
}

?>