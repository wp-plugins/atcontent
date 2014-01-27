<?php

function atcontent_api_create_publication($ac_api_key, 
$post_title, $post_content, $paid_portion, $commercial_type, $post_published, $original_url,
$cost, $is_copyprotect, $is_advanced_tracking, $comments, $tags, $categories, $blogId, $syncId, $postIdInApp
) {
    if (preg_match('/<script[^>]*src="https?:\/\/w.atcontent.com/', $post_content) == 1) return NULL;
    if (preg_match('/<script[^>]*src="https?:\/\/w.atcontent.com/', $paid_portion) == 1) return NULL;
    $post_content = str_replace( "http://youtube.com", "https://youtube.com", $post_content );
    $post_content = str_replace( "http://www.youtube.com", "https://youtube.com", $post_content );
    $post_content = str_replace( "http://youtu.be", "https://youtu.be", $post_content );
    $post_content = str_replace( "http://www.youtu.be", "https://youtu.be", $post_content );
    $post_splited_content = split("<!--more-->", $post_content);
    $post_face = $post_splited_content[0];
    $post_body = count( $post_splited_content ) > 0 ? $post_splited_content[1] : "";
    $post_paid_splited_content = split("<!--more-->", $paid_portion);
    $paid_face = $post_paid_splited_content[0];
    $paid_content = count( $post_paid_splited_content ) > 0 ? $post_paid_splited_content[1] : "";
    $post_content = 'Key='.
        urlencode( $ac_api_key ) . '&AppID=' . urlencode( 'WordPress' ) .
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
        '&BlogId='.urlencode($blogId).
        '&SyncId='.urlencode($syncId).
        '&PostId='.urlencode($postIdInApp).
        '&AddToIndex=true' .
        ( ( $original_url != NULL && strlen($original_url) > 0 ) ? ( '&OriginalUrl=' . urlencode($original_url) ) : ( '' ) ).
        '';
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/create', $post_content );
}

function atcontent_api_update_publication($ac_api_key, 
$post_id, $post_title, $post_content, $paid_portion, $commercial_type, $post_published, $original_url,
$cost, $is_copyprotect, $is_advanced_tracking, $comments, $tags, $categories, $blogId, $syncId, $postIdInApp
) {
    if (preg_match('/<script[^>]*src="https?:\/\/w.atcontent.com/', $post_content) == 1) return NULL;
    if (preg_match('/<script[^>]*src="https?:\/\/w.atcontent.com/', $paid_portion) == 1) return NULL;
    $post_content = str_replace( "http://youtube.com", "https://youtube.com", $post_content );
    $post_content = str_replace( "http://youtu.be", "https://youtu.be", $post_content );
    $post_splited_content = split("<!--more-->", $post_content);
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $post_paid_splited_content = split("<!--more-->", $paid_portion);
    $paid_face = $post_paid_splited_content[0];
    $paid_content = count($post_paid_splited_content) > 0 ? $post_paid_splited_content[1] : "";
    $post_content = 'Key='.
        urlencode($ac_api_key).'&AppID='.urlencode('WordPress').
        '&PublicationID='.urlencode($post_id).
        '&Title='.urlencode($post_title).
        '&CommercialType='.urlencode($commercial_type).
        '&Language='.urlencode('en').
        '&Price='.urlencode($cost).
        '&FreeFace='.urlencode($post_face).
        '&FreeContent='.urlencode($post_body).
        '&PaidFace='.urlencode($paid_face).
        '&PaidContent='.urlencode($paid_content).
        '&IsCopyProtected='.urlencode($is_copyprotect).
        '&IsAdvancedTracking='.urlencode($is_advanced_tracking).
        '&Published='.urlencode($post_published).
        '&Comments='.urlencode($comments).
        '&IsPaidRepost='.urlencode($is_paidrepost).
        '&Tags='.urldecode($tags).
        '&WPCategories='.urlencode($categories).
        '&BlogId='.urlencode($blogId).
        '&SyncId='.urlencode($syncId).
        '&PostId='.urlencode($postIdInApp).
        '&AddToIndex=true'.
        ( ( $original_url != NULL && strlen($original_url) > 0 ) ? ( '&OriginalUrl=' . urlencode($original_url) ) : ( '' ) ).
        '';
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/update', $post_content );
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

function atcontent_api_extended_readership( $api_key, $siteuri, $postids, $detailed ) {
    $post_content = 'SiteUri=' . urlencode( $siteuri ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        '&PostIDs=' . urlencode( $postids ) .
        '&Details=' . urlencode( $detailed ) .
        '&Key=' . urlencode( $api_key )  .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/extendedreadership', $post_content );
}

function atcontent_api_connectgate( $apikey, $userid, $siteuri, $gateuri ) {
    $post_content = 'AppID=' . urlencode( 'WordPress' ) . 
        '&site=' . urlencode( $siteuri ) .
        '&authid=' . urlencode( $userid ) .
        '&gate=' . urlencode( $gateuri ) .
        '&APIKey=' . urlencode( $apikey ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/connectgate', $post_content );
}

function atcontent_api_disconnectgate( $apikey, $siteuri ) {
    $post_content = 'AppID=' . urlencode( 'WordPress' ) . 
        '&site=' . urlencode( $siteuri ) .
        '&APIKey=' . urlencode( $apikey ) .
        '&Disconnect=1' .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/native/connectgate', $post_content );
}

function atcontent_api_guestposts_incoming( $siteuri, $api_key ) {
    $post_content = 'Uri=' . urlencode( $siteuri ) . 
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $api_key != NULL ? '&Key=' . urlencode( $api_key ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/list', $post_content );
}

function atcontent_api_guestposts_outgoing( $api_key ) {
    $post_content = 
        'AppID=' . urlencode( 'WordPress' ) .
        ( $api_key != NULL ? '&Key=' . urlencode( $api_key ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/list', $post_content );
}

function atcontent_api_guestposts_create( $api_key, $siteuri, $targeturi, $title, $content, $status ) {
    $post_splited_content = split("<!--more-->", $content);
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $post_content = 
        'uri=' . urlencode( $siteuri ) .
        '&targeturi=' . urlencode( $targeturi ) .
        '&title=' . urlencode( $title ) .
        '&face=' . urlencode( $post_face ) .
        '&body=' . urlencode( $post_body ) .
        '&status=' . urlencode( $status ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $api_key != NULL ? '&Key=' . urlencode( $api_key ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/create', $post_content );
}

function atcontent_api_guestposts_update( $api_key, $id, $siteuri, $targeturi, $title, $content, $status ) {
    $post_splited_content = split("<!--more-->", $content);
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $post_content = 
        'id=' . urlencode( $id ) .
        '&uri=' . urlencode( $siteuri ) .
        '&targeturi=' . urlencode( $targeturi ) .
        '&title=' . urlencode( $title ) .
        '&face=' . urlencode( $post_face ) .
        '&body=' . urlencode( $post_body ) .
        '&status=' . urlencode( $status ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $api_key != NULL ? '&Key=' . urlencode( $api_key ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/update', $post_content );
}

function atcontent_api_guestposts_get( $api_key, $id ) {
    $post_content = 
        'Id=' . urlencode( $id ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $api_key != NULL ? '&Key=' . urlencode( $api_key ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/get', $post_content );
}

function atcontent_api_guestposts_status_update( $api_key, $id, $status ) {
    $post_content = 
        'id=' . urlencode( $id ) .
        '&status=' . urlencode( $status ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        ( $api_key != NULL ? '&Key=' . urlencode( $api_key ) : '' ) .
        ( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/status', $post_content );
}

function atcontent_api_guestposts_preview( $api_key, $id, $siteuri ) {
    $post_content = 
        'Id=' . urlencode( $id ) .
        '&Uri=' . urlencode( $siteuri ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&Key=' . urlencode( $api_key ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/preview', $post_content );
}

function atcontent_api_guestposts_accept( $api_key, $id, $siteuri, $original_url ) {
    $post_content = 
        'Id=' . urlencode( $id ) .
        '&Uri=' . urlencode( $siteuri ) .
        '&OriginalUri=' . urlencode( $original_url ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&Key=' . urlencode( $api_key ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/approve', $post_content );
}

function atcontent_api_guestposts_decline( $api_key, $id, $siteuri ) {
    $post_content = 
        'Id=' . urlencode( $id ) .
        '&Uri=' . urlencode( $siteuri ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&Key=' . urlencode( $api_key ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/guestpost/approve', $post_content );
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

function atcontent_api_get_title( $postid ) {
    $post_content = 
        'PostId=' . urlencode( $postid ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/general/title', $post_content );
}

function atcontent_api_category_sites_rating( $category ) {
    $post_content = 
        'category=' . urlencode( $category ) .
        '&month=' . urlencode( "1" ) .
        '&AppID=' . urlencode( 'WordPress' ) .
        '&ExternalVersion=' . urlencode( AC_VERSION );
    return atcontent_do_post( 'http://api.atcontent.com/v1/general/categorysitesrating', $post_content );
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
    $old_error_level = error_reporting(0);
    if ( function_exists( 'curl_init' ) ) {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_HEADER, 0 );
        curl_setopt( $curl, CURLOPT_POST, 1 );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'IE 10.00' );
        $res = curl_exec( $curl );
        if ( !$res ) {
	        $error = curl_error($curl).'('.curl_errno($curl).')';
	        $out = $error;
            $out_array = array( 'IsOK' => FALSE, 'error' =>  $error );
        } else {
            try {
                $out_array = json_decode( $res, true );
            } catch (Exception $e) { }
            if ( !is_array( $out_array ) ) {
                $out_array = array( 'IsOK' => FALSE, 'error' =>  $res );
            }
        }
        curl_close( $curl );
    } else if ( function_exists('fsockopen') ) {
        $res = atcontent_socket_post_request( $url, $data );
        if ( $res["status"] == "ok" ) {
            try {
                $out_array = json_decode( $res["content"], true );
            } catch (Exception $e) { }
            if ( !is_array( $out_array ) ) {
                $out_array = array( 'IsOK' => FALSE, 'error' =>  $res["content"] );
            }
        } else {
            $out_array = array( 'IsOK' => FALSE, 'error' =>  $res["error"] );
        }
    } else {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL .
                            'User-Agent: IE 10.00' . PHP_EOL,
                'content' => $data,
            ),
        ));
        $res = file_get_contents(
            $file = $url,
            $use_include_path = false,
            $context);
        $out_array = FALSE;
        try {
            $out_array = json_decode( $res, true );
        } catch (Exception $e) { 
            $out_array = array( 'IsOK' => FALSE, 'error' =>  $res );
        }
    }
    error_reporting( $old_error_level );
    return $out_array;
}

function atcontent_socket_post_request( $url, $data ) {
 
    // Convert the data array into URL Parameters like a=b&foo=bar etc.
    if ( is_array ( $data ) ) $data = http_build_query($data);
 
    // parse the given URL
    $url = parse_url($url);
 
    if ($url['scheme'] != 'http') { 
        die('Error: Only HTTP request are supported !');
    }
 
    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];
 
    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($host, 80, $errno, $errstr, 30);
 
    if ($fp){
 
        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
 
        if ($referer != '')
            fputs($fp, "Referer: $referer\r\n");
 
        fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" );
        fputs( $fp, "Content-length: " . strlen( $data ) . "\r\n" );
        fputs( $fp, "Connection: close\r\n\r\n" );
        fputs( $fp, $data );
 
        $result = ''; 
        while( ! feof( $fp ) ) {
            // receive the results of the request
            $result .= fgets( $fp, 128 );
        }
    }
    else { 
        return array(
            'status' => 'err', 
            'error' => "$errstr ($errno)"
        );
    }
 
    // close the socket connection:
    fclose( $fp );
 
    // split the result header from the content
    $result = explode( "\r\n\r\n", $result, 2 );
 
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';
 
    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );
}


?>