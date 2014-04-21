<?php
    
function atcontent_coexistense_pin_it_buttons_add( $content ) {
	global $wp_query;
	$options    = get_option('pin_it_buttons_options');
	$post_url   = rawurlencode(get_permalink()); 
	$post_title = rawurlencode(get_the_title());
	$pin_count  = $options['pinit_pincount']; // Position of the pin counter
	//$pin_button    = '<span class="pinit-button-'.$pin_count.'"><a href="http://pinterest.com/pin/create/button/?url=%s&media=%s&description=%s" class="pin-it-button" count-layout="%s"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a></span>';
	// The shortcodes run after the filter. So we'll check for them in $content instead.
	$pinit = $options['pinit_pinit'];
	if ( strpos($content, '[nopinit]') )
			$pinit = false;
	if ( strpos($content, '[dopinit]') )
			$pinit = true;
	// First see if we want to show pins according to the settings.
	$options = get_option('pin_it_buttons_options');
	if ( $pinit==false ) 
					return $content;

	// Tag all images
	$html = str_get_html( $content, true, true, DEFAULT_TARGET_CHARSET, false );     
  if ( $html == false) return $content;
	foreach( $html->find('img') as $e )
	    $e->class .= ' pin-it';
	$content = $html;
	unset($html);
	return $content;
}

?>