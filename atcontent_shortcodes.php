<?php

function atcontent_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'id' => '',
		'nickname' => '',
	), $atts ) );

    if ( strlen( $id ) == 0 ) return '';

    $ac_postid = $id;
    if ( strlen( $nickname ) == 0 ) {
        $userid = wp_get_current_user()->ID;
        $nickname = get_user_meta( $userid, "ac_pen_name", true );
    }
    if ( strlen( $nickname ) == 0 ) {
        $nickname = "AtContent";
    }
    $ac_pen_name = $nickname;

	return <<<END
<div class="atcontent_widget{$ac_additional_classes}"><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body"></script></div>
END;
}

add_shortcode( 'atcontent', 'atcontent_shortcode' );    


?>