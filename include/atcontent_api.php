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
    if ( strlen( $api_key ) == 0 )
    {
        $userid = wp_get_current_user() -> ID;
        $api_key = get_user_meta( $userid, "ac_non_delete_api_key", true );        
    }
    $siteuri = atcontent_get_blog_url();
    $post_content = 'Email=' . urlencode( $email ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $status != NULL ? '&Status=' . urlencode( $status ) : '' ) .
        ( $api_key != NULL ? '&APIKey=' . urlencode( $api_key ) : '' ) .
        ( $referral != NULL ? '&Referral=' . urlencode( $referral ) : '' ) . 
        '&Url=' . urlencode( $siteuri ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/pingback', $post_content );
}

function atcontent_api_activate() {
    $ac_blog_api_key = get_option('ac_blog_api_key');
    $gate_url = admin_url("admin-ajax.php");
    $siteuri = atcontent_get_blog_url();
    $blog_title = get_bloginfo( 'name' );
    $userid = wp_get_current_user()->ID;
    $blogid = get_user_meta( $userid, 'ac_blogid', true );
    $syncid = get_user_meta( $userid, "ac_syncid", true );
    $data = 'key=' . urlencode( $ac_blog_api_key ) . 
            '&appId=' . urlencode( 'WordPress' ) . 
            '&gate=' . urlencode( $gate_url ) . 
            '&url=' . urlencode( $siteuri ) . 
            '&title=' . urlencode( $blog_title ) . 
            '&userId=' . urlencode( $userid ) . 
            '&blogId=' . urlencode( $blogid ) . 
            '&syncId=' . urlencode( $syncid ) . 
            '&externalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( "https://api.atcontent.com/v2/blog/activate", $data );
}

function atcontent_api_readership( $siteuri, $postids, $api_key ) {
    $post_content = 'SiteUri=' . urlencode( $siteuri ) . 
        '&AppID=' . urlencode( 'WordPress' ) . 
        '&PostIDs=' . urlencode( $postids ) . 
        '&Key=' . urlencode( $api_key ) . 
        '&v5=1' .
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
        '&Highlighted=1' .
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

function atcontent_api_get_sync_stat( $syncId, $blogId ) {
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

function atcontent_api_highlighted_hide( $api_key, $postid ) {
    $post_content =
        'AppID=' . urlencode( 'WordPress' ) .
        '&PostId=' . urlencode( $postid ) .
        '&Key=' . urlencode( $api_key ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/highlightedhide', $post_content );
}


function atcontent_api_set_viglink_api_key( $viglink_api_key ) {    
    $userid = wp_get_current_user() -> ID;
    $ac_blogid = get_user_meta( $userid, "ac_blogid", true );
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true ); 
    $post_content = 
        'Key='. urlencode( $ac_api_key ) . 
         '&VigLinkApiKey=' .  urlencode( $viglink_api_key ) .
         '&blogId=' . urlencode( $ac_blogid ) . 
         '&AppID=' . urlencode( 'WordPress' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/setviglinkapikey', $post_content );
}

function atcontent_api_renewinfo(){
    $userid = wp_get_current_user() -> ID;
    $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
    $gate = admin_url( 'admin-ajax.php' );
    $siteuri = atcontent_get_blog_url();
    $ac_blog_api_key = get_option( 'ac_blog_api_key' );
    $post_content = 
            'syncId=' . urlencode( $ac_syncid ) . 
            '&userId=' . urlencode( $userid ) . 
            '&gate=' . urlencode( $gate ) . 
            '&url=' . urlencode( $siteuri ) . 
            '&blogKey=' . urlencode( $ac_blog_api_key ) . 
            '&appId=' . urlencode( 'WordPress' ) . 
            '&extVer=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v2/blog/renewinfo', $post_content );
}

function atcontent_api_blog_ping( $ac_blog_key, $state ) {
    $post_content = 
            'key=' . urlencode( $ac_blog_key ) . 
            '&state=' . urlencode( $state ) . 
            '&appId=' . urlencode( 'WordPress' ) .
            '&extVer=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v2/blog/ping', $post_content );
}

function atcontent_api_set_envato_purchase($ac_api_key, $envato_key){
    $post_content = 
            'key=' . urlencode( $ac_api_key ) . 
            '&envatopurchaseid=' . urlencode( $envato_key ) . 
            '&appId=' . urlencode( 'WordPress' ) .
            '&extVer=' . urlencode( AC_VERSION );    
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/envatoconfirmpurchase', $post_content );
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
        $out_array = array( 'IsOK' => FALSE, 'Error' => $wp_response -> get_error_message() );
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