<?php

function atcontent_create_publication($ac_api_key, 
$post_title, $post_content, $post_published, $original_url,
$cost, $is_copyprotect, $is_paidrepost, $comments
) {
    $post_splited_content = split("<!--more-->", $post_content);
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $commercial_type = "free";
    if ($is_paidrepost != NULL && $is_paidrepost == "1") { $commercial_type = "paidrepost"; }
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
$post_id, $post_title, $post_content, $post_published, $original_url,
$cost, $is_copyprotect, $is_paidrepost, $comments
) {
    $post_splited_content = split("<!--more-->", $post_content);
    $post_face = $post_splited_content[0];
    $post_body = count($post_splited_content) > 0 ? $post_splited_content[1] : "";
    $commercial_type = "free";
    if ($is_paidrepost != NULL && $is_paidrepost == "1") { $commercial_type = "paidrepost"; }
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


?>