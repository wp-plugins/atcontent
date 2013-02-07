<?php

function atcontent_create_publication($ac_api_key, 
$post_title, $post_content, $paid_portion, $commercial_type, $post_published, $original_url,
$cost, $is_copyprotect, $comments
) {
    if (preg_match('/<script[^>]*src="https?:\/\/w.atcontent.com/', $paid_portion) == 1) return NULL;
    $post_splited_content = split("<!--more-->", $post_content);
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $post_paid_splited_content = split("<!--more-->", $paid_portion);
    $paid_face = $post_paid_splited_content[0];
    $paid_content = count($post_paid_splited_content) > 0 ? $post_paid_splited_content[1] : "";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.atcontent.com/v1/native/create');
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, 'Key='.
urlencode($ac_api_key).'&AppID='.urlencode('WordPress').
'&Title='.urlencode($post_title).
'&CommercialType='.urlencode($commercial_type).
'&Language='.urlencode('en').
'&Price='.urlencode($cost).
'&FreeFace='.urlencode($post_face).
'&FreeContent='.urlencode($post_body).
'&PaidFace='.urlencode($paid_face).
'&PaidContent='.urlencode($paid_content).
'&IsCopyProtected='.urlencode($is_copyprotect).
'&IsPaidRepost='.urlencode($is_paidrepost).
'&Published='.urlencode($post_published).
'&Comments='.urlencode($comments).
'&AddToIndex=true'.
( ( $original_url != NULL && strlen($original_url) > 0 ) ? ( '&OriginalUrl=' . urlencode($original_url) ) : ( '' ) ).
'');
/*
            var OriginalUrl = PostValidation.GetOriginalUrl(Request.Form["OriginalUrl"]);
*/
curl_setopt($curl, CURLOPT_USERAGENT, 'IE 10.00');
 
$res = curl_exec($curl);
 
if(!$res){
	$error = curl_error($curl).'('.curl_errno($curl).')';
	$out = $error;
}

curl_close($curl);
$out_array = json_decode($res, true);
return $out_array;
}

function atcontent_api_update_publication($ac_api_key, 
$post_id, $post_title, $post_content, $paid_portion, $commercial_type, $post_published, $original_url,
$cost, $is_copyprotect, $comments
) {
    if (preg_match('/<script[^>]*src="https?:\/\/w.atcontent.com/', $post_content) == 1) return NULL;
    if (preg_match('/<script[^>]*src="https?:\/\/w.atcontent.com/', $paid_portion) == 1) return NULL;
    $post_splited_content = split("<!--more-->", $post_content);
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $post_paid_splited_content = split("<!--more-->", $paid_portion);
    $paid_face = $post_paid_splited_content[0];
    $paid_content = count($post_paid_splited_content) > 0 ? $post_paid_splited_content[1] : "";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.atcontent.com/v1/native/update');
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, 'Key='.
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
'&Published='.urlencode($post_published).
'&Comments='.urlencode($comments).
'&IsPaidRepost='.urlencode($is_paidrepost).
'&AddToIndex=true'.
( ( $original_url != NULL && strlen($original_url) > 0 ) ? ( '&OriginalUrl=' . urlencode($original_url) ) : ( '' ) ).
'');
curl_setopt($curl, CURLOPT_USERAGENT, 'IE 10.00');
 
$res = curl_exec($curl);
 
if(!$res){
	$error = curl_error($curl).'('.curl_errno($curl).')';
	$out = $error;
}

curl_close($curl);
$out_array = json_decode($res, true);
return $out_array;
}

function atcontent_api_update_publication_comments($ac_api_key, 
$post_id, $comments) {
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.atcontent.com/v1/native/update');
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, 'Key='.
urlencode($ac_api_key).'&AppID='.urlencode('WordPress').
'&PublicationID='.urlencode($post_id).
'&Comments='.urlencode($comments));
curl_setopt($curl, CURLOPT_USERAGENT, 'IE 10.00');
 
$res = curl_exec($curl);
 
if(!$res){
	$error = curl_error($curl).'('.curl_errno($curl).')';
	$out = $error;
}

curl_close($curl);
$out_array = json_decode($res, true);
return $out_array;
}

function atcontent_api_get_nickname( $ac_api_key ) {
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.atcontent.com/v1/native/nickname');
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, 'Key='.
urlencode( $ac_api_key ) . '&AppID=' . urlencode( 'WordPress' ) );
curl_setopt($curl, CURLOPT_USERAGENT, 'IE 10.00');
$res = curl_exec($curl);
if(!$res){
	$error = curl_error($curl).'('.curl_errno($curl).')';
	$out = $error;
}
curl_close($curl);
$out_array = json_decode($res, true);
return $out_array;
}

function atcontent_api_get_key( $nounce, $grant ) {
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.atcontent.com/v1/native/requestkey');
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, 
'Nounce='. urlencode( $nounce ) . 
'&Grant='. urlencode( $grant ) . 
'&AppID=' . urlencode( 'WordPress' ) );
curl_setopt($curl, CURLOPT_USERAGENT, 'IE 10.00');
$res = curl_exec($curl);
if(!$res){
	$error = curl_error($curl).'('.curl_errno($curl).')';
	$out = $error;
}
curl_close($curl);
$out_array = json_decode($res, true);
return $out_array;
}

function atcontent_api_pingback( $email, $status, $api_key ) {
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://api.atcontent.com/v1/native/pingback');
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, 
'Email='. urlencode( $email ) . 
'&AppID=' . urlencode( 'WordPress' ) .
( $status != NULL ? '&Status=' . urlencode( $status ) : '' ) .
( $api_key != NULL ? '&APIKey=' . urlencode( $api_key ) : '' ) .
( defined('AC_VERSION') ? '&ExternalVersion=' . urlencode( AC_VERSION ) : '' ) );
curl_setopt($curl, CURLOPT_USERAGENT, 'IE 10.00');
$res = curl_exec($curl);
if(!$res){
	$error = curl_error($curl).'('.curl_errno($curl).')';
	$out = $error;
}
curl_close($curl);
$out_array = json_decode($res, true);
return $out_array;
}


?>