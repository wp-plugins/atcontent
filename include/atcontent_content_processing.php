<?php
     function atcontent_the_content( $content = '' ) {
    
        global $post, $wp_current_filter, $currentNumPost_ac;

        (!$currentNumPost_ac ? $currentNumPost_ac = 1 : $currentNumPost_ac++);

        if ( in_array( 'the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }
        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
		    return $content;
	    }
        $ac_excerpt_no_process = get_user_meta( intval( $post->post_author ), "ac_excerpt_no_process", true );
        if ( strlen( $ac_excerpt_no_process ) == 0 ) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
        if ( !is_single() && $ac_excerpt_no_process == "1" ) return $content;
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
        $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
        $ac_adtest = get_user_meta( intval( $post->post_author ), "ac_adtest", true );
        $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
        $ac_additional_classes = "";
        $isSinlgePost = is_single();
        if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
        if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
        if ( is_string ( $ac_pen_name ) && strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";

        $ac_share_panel_disable = get_user_meta( intval( $post->post_author ), "ac_share_panel_disable", true );
        $ac_share_panel_data_option = "";
        if ( $ac_share_panel_disable == "1" ) $ac_share_panel_data_option = 'data-options="hide_shares"';

        $ac_adtest_messages = array();
        $ac_adtest_messages[] = "<h3>How online retailers solve the problem of reaching new buyers in a new way.</h3><p>Hi folks.<br/>My friend is an online retailer and he says that one of the largest problem that retailers face is the lack of methods to reach new buyers. He checked out a couple services and found Happy Retailer.  <br/>I think this is a very interesting idea to allow retailers upload inventory in one place and sell them simultaneously on Amazon, EBay, Bonanza and more than 1001 websites! <br />If you a retailer it might be interesting for you. <br/>You can get more details here <a href=\"http://happyretailer.com/?from=atcontent\" target=_blank>http://happyretailer.com</a></p>";

		$ac_adtest_messages[] = "<h3>New types of services that make this world a better place.</h3><p>I recently learned of a new type of services. They allow to donate money to charity and in return you can get cool experiences, such as flights on a private plane, meetings with interesting personalities, ride in MR2 Spyder, advises & mentorships, try international cuisines, etc.</p><p>One of the services is SunnyKarma.com. They are based out of San Francisco & are having a huge fundraising networking mixer/party on July 17th where they will be raffling off a flight on a private plane, you can get more details here: <a href=\"http://bit.ly/169mChq\" target=_blank>http://sunnykarma.com</a></p>";

        //print_r($ac_adtest_messages);

        shuffle($ac_adtest_messages);

        //$ac_adtest_message_randkeys = array_rand($ac_adtest_messages, 1);

        //print_r($ac_adtest_message_randkeys);

        //$ac_adtest_message = $ac_adtest_messages[$ac_adtest_message_randkeys[0]];
        $ac_adtest_message = $ac_adtest_messages[0];

        //print_r($ac_adtest_message);

        $ac_adtest_numOfmsgApears = 2;

        if ( $ac_is_process == "1" && is_string ( $ac_postid ) && strlen( $ac_postid ) > 0 ) {
            $code = <<<END
<div {$ac_share_panel_data_option} class="atcontent_widget{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --></div>
END;

            if ( $isSinlgePost ) {
                $code = <<<END
<div {$ac_share_panel_data_option} class="atcontent_widget{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Body"></script></div>
END;
            }

            /*
            if ($ac_adtest == "1" && ($isSinlgePost || $currentNumPost_ac == $ac_adtest_numOfmsgApears)) {
				$code .= $ac_adtest_message;
            }
            //*/

            $code = str_replace( PHP_EOL, " ", $code );
            $inline_style = "";
            preg_match_all( '@<style[^>]*?>.*?</style>@siu', do_shortcode( $content ), $style_matches );
            foreach ( $style_matches[0] as $style_item ) {
                $inline_style .= $style_item;
            }
            return $inline_style . $code;
        }

        return $content.
			(($ac_adtest == "1" && ($isSinlgePost || $currentNumPost_ac == $ac_adtest_numOfmsgApears))
				?$ac_adtest_message
				:"");
    }

    function atcontent_the_excerpt( $content = '' ) {
        global $post, $wp_current_filter;
        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        if ( strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        $ac_excerpt_image_remove = get_user_meta( intval( $post->post_author ), "ac_excerpt_image_remove", true );
        if ( strlen( $ac_excerpt_image_remove ) == 0 ) $ac_excerpt_image_remove = "0";
        $ac_excerpt_no_process = get_user_meta( intval( $post->post_author ), "ac_excerpt_no_process", true );
        if ( strlen( $ac_excerpt_no_process ) == 0 ) $ac_excerpt_no_process = AC_NO_PROCESS_EXCERPT_DEFAULT;
        if ( $ac_excerpt_no_process == "1" ) return $content;
        if ( $ac_is_process == "1" && strlen( $ac_postid ) > 0 && $ac_excerpt_no_process == "0" ) {
            $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
            $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
            $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );

            $ac_share_panel_disable = get_user_meta( intval( $post->post_author ), "ac_share_panel_disable", true );
            $ac_share_panel_data_option = "";
            if ( $ac_share_panel_disable == "1" ) $ac_share_panel_data_option = 'data-options="hide_shares"';

            $ac_additional_classes = "";
            if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
            if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
            $ac_excerpt_class = "atcontent_excerpt";
            if ( $ac_excerpt_image_remove == "1" ) $ac_excerpt_class = "atcontent_excerpt_no_image";
            $code = <<<END
<div {$ac_share_panel_data_option} class="{$ac_excerpt_class}{$ac_additional_classes}"><script>var CPlaseE = CPlaseE || {}; CPlaseE.Author = CPlaseE.Author || {}; CPlaseE.Author['{$ac_postid}'] = 0;</script><script async src="https://w.atcontent.com/{$ac_pen_name}/{$ac_postid}/Face"></script><!-- Copying this AtContent publication you agree with Terms of services AtContent™ (https://www.atcontent.com/Terms/) --></div>
END;
            $code = str_replace( PHP_EOL, " ", $code );
            $inline_style = "";
            preg_match_all( '@<style[^>]*?>.*?</style>@siu', do_shortcode( $content ), $style_matches );
            foreach ( $style_matches[0] as $style_item ) {
                $inline_style .= $style_item;
            }
            return $inline_style . $code;
        }
        return $content;
    }

    function atcontent_the_content_after( $content = '' ) {
        global $post, $wp_current_filter;
        if ( in_array( 'the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }
        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
		    return $content;
	    }

        $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
        $ac_is_process = get_post_meta( $post->ID, "ac_is_process", true );
        $ac_pen_name = get_user_meta( intval( $post->post_author ), "ac_pen_name", true );
        $ac_comments_disable = get_user_meta( intval( $post->post_author ), "ac_comments_disable", true );
        $ac_hint_panel_disable = get_user_meta( intval( $post->post_author ), "ac_hint_panel_disable", true );
        $ac_script_init = get_user_meta( intval( $post->post_author ), "ac_script_init", true );
        $ac_additional_classes = "";
        if ( $ac_comments_disable == "1" ) $ac_additional_classes .= " atcontent_no_comments";
        if ( $ac_hint_panel_disable == "1" ) $ac_additional_classes .= " atcontent_no_hint_panel";
        if ( !is_string( $ac_pen_name ) || strlen( $ac_pen_name ) == 0 ) $ac_pen_name = "vadim";
        if ( $ac_is_process == "1" && strlen( $ac_postid ) > 0) {
             //Chameleon theme thumb fix
            if (function_exists( 'get_thumbnail' ) && get_option( 'chameleon_thumbnails' ) == 'on' ) {
                $ac_script_init .= <<<END
(function($) {
$(".CPlase_face").prepend($(".post-thumbnail").clone());
$(".post-thumbnail:first").remove();
})(jQuery)
END;
            }
            //Chameleon theme thumb fix end

            //RefTagger
            if ( function_exists ( 'lbsFooter' ) ) {
                $ac_script_init .= <<<END
try { Logos.ReferenceTagging.tag(); } catch (ex) {}
END;
            }
            //End RefTagger

            //FancyBox for WordPress
            if ( defined( 'FBFW_VERSION' ) ) {
                $ac_script_init .= <<<END
jQuery(function(){

jQuery.fn.getTitle = function() { // Copy the title of every IMG tag and add it to its parent A so that fancybox can show titles
	var arr = jQuery("a.fancybox");
	jQuery.each(arr, function() {
		var title = jQuery(this).children("img").attr("title");
		jQuery(this).attr('title',title);
	})
}

// Supported file extensions
var thumbnails = jQuery("a:has(img)").not(".nolightbox").filter( function() { return /\.(jpe?g|png|gif|bmp)$/i.test(jQuery(this).attr('href')) });

thumbnails.addClass("fancybox").attr("rel","fancybox").getTitle();
jQuery("a.fancybox").fancybox({
	'cyclic': false,
	'autoScale': true,
	'padding': 10,
	'opacity': true,
	'speedIn': 500,
	'speedOut': 500,
	'changeSpeed': 300,
	'overlayShow': true,
	'overlayOpacity': "0.3",
	'overlayColor': "#666666",
	'titleShow': true,
	'titlePosition': 'inside',
	'enableEscapeButton': true,
	'showCloseButton': true,
	'showNavArrows': true,
	'hideOnOverlayClick': true,
	'hideOnContentClick': false,
	'width': 560,
	'height': 340,
	'transitionIn': "fade",
	'transitionOut': "fade",
	'centerOnScroll': true,
});

});
END;
            }

            //End FancyBox for WordPress


            //NextGEN Gallery
            if ( is_array( get_option( 'ngg_options' ) ) ) {
            $ac_script_init .= <<<END
jQuery(".ngg-slideshow").each(function(){
    var i = jQuery(this), id = i.attr("id"), galid = parseInt(id.substr(id.lastIndexOf("-") + 1));
    jQuery("#" + id).nggSlideshow( {id: galid,fx:"fade",width:320,height:240,domain: "http://" + document.location.host + "/",timeout:10000});
});
END;
            }
            //End NextGEN Gallery

            //eBible
            if ( function_exists('includeJSHeader') ) {
                $ac_script_init .= <<<END
CPlase.l('http://www.ebible.com/assets/verselink/ebible.verselink.js');
END;
            }
            //End eBible

            //Lightbox Plus ColorBox
            if ( class_exists( 'wp_lightboxplus' ) ) {
                global $wp_lightboxplus;
                if ( ob_start() ) {
                    $wp_lightboxplus->lightboxPlusColorbox();
                    $lbp_script = ob_get_contents();
                    ob_end_clean();
                    if (preg_match_all("/<script[^>]+>([^<]*)<\/script>/mi", $lbp_script, $output_array)) {
                        $ac_script_init .= $output_array[1][0];
                    }
                }
            }
            //End Lightbox Plus ColorBox

            //Easy Media Gallery
            if ( defined( 'EASYMEDIA_VERSION' ) ) {
                $ac_script_init .= '
(function( $, undefined ) {$.HoverDir=function(e,t){this.$el=$(t);this._init(e)};$.HoverDir.defaults={hoverDelay:0,reverse:false};$.HoverDir.prototype={_init:function(e){this.options=$.extend(true,{},$.HoverDir.defaults,e);this._loadEvents()},_loadEvents:function(){var e=this;this.$el.bind("mouseenter.hoverdir, mouseleave.hoverdir",function(t){var n=$(this),r=t.type,i=n.find("article"),s=e._getDir(n,{x:t.pageX,y:t.pageY}),o=e._getClasses(s);i.removeClass();if(r==="mouseenter"){i.hide().addClass(o.from);clearTimeout(e.tmhover);e.tmhover=setTimeout(function(){i.show(0,function(){$(this).addClass("da-animate").addClass(o.to)})},e.options.hoverDelay)}else{i.addClass("da-animate");clearTimeout(e.tmhover);i.addClass(o.from)}})},_getDir:function(e,t){var n=e.width(),r=e.height(),i=(t.x-e.offset().left-n/2)*(n>r?r/n:1),s=(t.y-e.offset().top-r/2)*(r>n?n/r:1),o=Math.round((Math.atan2(s,i)*(180/Math.PI)+180)/90+3)%4;return o},_getClasses:function(e){var t,n;switch(e){case 0:!this.options.reverse?t="da-slideFromTop":t="da-slideFromBottom";n="da-slideTop";break;case 1:!this.options.reverse?t="da-slideFromRight":t="da-slideFromLeft";n="da-slideLeft";break;case 2:!this.options.reverse?t="da-slideFromBottom":t="da-slideFromTop";n="da-slideTop";break;case 3:!this.options.reverse?t="da-slideFromLeft":t="da-slideFromRight";n="da-slideLeft";break}return{from:t,to:n}}};var logError=function(e){if(this.console){console.error(e)}};$.fn.hoverdir=function(e){if(typeof e==="string"){var t=Array.prototype.slice.call(arguments,1);this.each(function(){var n=$.data(this,"hoverdir");if(!n){logError("cannot call methods on hoverdir prior to initialization; "+"attempted to call method \'"+e+"\'");return}if(!$.isFunction(n[e])||e.charAt(0)==="_"){logError("no such method \'"+e+"\' for hoverdir instance");return}n[e].apply(n,t)})}else{this.each(function(){var t=$.data(this,"hoverdir");if(!t){$.data(this,"hoverdir",new $.HoverDir(e,this))}})}return this}})( jQuery );
jQuery(function(){jQuery(window).scroll(function(){if(jQuery("#mbCenter").size()>0){var e=parseInt(jQuery(document).scrollTop());var t=jQuery("#mbCenter").offset();var n=parseInt(t.top+jQuery("#mbCenter").height()+90-e);var r=jQuery(window).height()-n;if(e<t.top-90){setTimeout(function(){jQuery("#mbCenter").stop().animate({top:jQuery(window).scrollTop()+340},500)},150)}if(r>1&&jQuery(window).height()<jQuery("#mbCenter").height()-90){setTimeout(function(){jQuery("#mbCenter").stop().animate({top:t.top+340},500)},150)}else if(r>1&&jQuery(window).height()>jQuery("#mbCenter").height()+90){setTimeout(function(){jQuery("#mbCenter").stop().animate({top:jQuery(window).scrollTop()+340},500)},150)}}})})
jQuery(function($){$("div.da-thumbs").hoverdir(); $(".emgfittext").fitText(1.2,{ maxFontSize: "12px" });}); window.addEvent(\'domready\', function() { Easymedia.scanPage();});
';
            }
            //End Easy Media Gallery
            
            if ( strlen( $ac_script_init ) > 0 ) {
                $content .= <<<END
<script type="text/javascript">
CPlase = window.CPlase || {};
CPlase.evt = CPlase.evt || [];
CPlase.evt.push(function (event, p, w) {
    {$ac_script_init}
});
</script>
END;
            }
        }
        return $content;
    }

    
    function atcontent_import_handler(){
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        if ( current_user_can( 'edit_posts' ) && strlen( $ac_api_key ) > 0 ) {

            atcontent_coexistense_fixes();

	        // get the submitted parameters
	        $postID = $_POST['postID'];
            $ac_is_copyprotect = $_POST['copyProtection'];
            $ac_paidrepost_cost = $_POST['cost'];

            $ac_user_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
            if ( strlen( $ac_user_copyprotect ) == 0 ) $ac_user_copyprotect = "1";

            $ac_postid = get_post_meta( $postID, "ac_postid", true );
            $ac_is_process = get_post_meta( $postID, "ac_is_process", true );

            $ac_cost = get_post_meta( $postID, "ac_cost", true );
            $ac_type = get_post_meta( $postID, "ac_type", true );
            $ac_paid_portion = get_post_meta( $postID, "ac_paid_portion", true );

            $ac_is_advanced_tracking = get_post_meta( $post->ID, "ac_is_advanced_tracking", true );
            if ( strlen( $ac_is_advanced_tracking ) == 0 ) { 
                $ac_is_advanced_tracking = "1";
            }
            if ( strlen( $ac_is_copyprotect ) == 0 ) {
                $ac_is_copyprotect = "1";
            }

            if ( strlen( $ac_type ) == 0 ) {
                if ($ac_is_paidrepost == "1") {
                    $ac_type = "paidrepost";
                } else {
                    $ac_type = "free";
                }
            }

            if ($ac_cost == "") $ac_cost = $ac_paidrepost_cost;

            $ac_action = "";
            $additional = NULL;
            $post = get_post( $postID );
            if ( $post == null || $ac_is_process == "0" ) {
                $ac_action = "skipped";
            } else {
                $comments_json = "";
                $comments = get_comments( array(
                        'post_id' => $post->ID,
                        'order' => 'ASC',
                        'orderby' => 'comment_date_gmt',
                        'status' => 'approve',
                ) );
                if( !empty($comments) ) {
                    $comments_json .= json_encode($comments);
                }
                $tags_json = json_encode( wp_get_post_tags( $post->ID,  array( 'fields' => 'slugs' ) ) );
                $cats_json = json_encode( wp_get_post_categories( $post->ID, array( 'fields' => 'slugs' ) ) );

	            if ( strlen( $ac_postid ) == 0 ) {
                    $api_answer = atcontent_api_create_publication( $ac_api_key, $post->post_title,
                            apply_filters( "the_content", $post->post_content ),
                            apply_filters( "the_content", $ac_paid_portion ),
                            $ac_type, get_gmt_from_date( $post->post_date ), get_permalink( $post->ID ),
                        $ac_cost, $ac_is_copyprotect, $ac_is_advanced_tracking, $comments_json, $tags_json, $cats_json );
                    if ( is_array( $api_answer ) && strlen( $api_answer["PublicationID"] ) > 0 ) {
                        $ac_postid = $api_answer["PublicationID"];
                        update_post_meta($post->ID, "ac_postid", $ac_postid);
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_type" , $ac_type );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        update_post_meta($post->ID, "ac_is_process", "1");
                        $ac_action = "created";
                    } else if ( is_array ( $api_answer ) ) {
                        $ac_action = "error";
                        $additional = $api_answer["error"];
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    } else {
                        $ac_action = "skipped";
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    }
                } else {
                    $api_answer = atcontent_api_update_publication( $ac_api_key, $ac_postid, $post->post_title,
                        apply_filters( "the_content", $post->post_content ) ,
                        apply_filters( "the_content", $ac_paid_portion ) ,
                        $ac_type , get_gmt_from_date( $post->post_date ), get_permalink($post->ID),
                        $ac_cost, $ac_is_copyprotect, $ac_is_advanced_tracking, $comments_json, $tags_json, $cats_json );
                    if ( is_array( $api_answer ) && strlen( $api_answer["PublicationID"] ) > 0 ) {
                        update_post_meta($post->ID, "ac_is_process", "1");
                        update_post_meta($post->ID, "ac_is_copyprotect" , $ac_is_copyprotect );
                        update_post_meta($post->ID, "ac_type" , $ac_type );
                        update_post_meta($post->ID, "ac_paidrepost_cost" , $ac_paidrepost_cost );
                        update_post_meta($post->ID, "ac_is_import_comments" , $ac_is_import_comments );
                        $ac_action = "updated";
                    } else if ( is_array ( $api_answer ) ) {
                        $ac_action = "error";
                        $additional = $api_answer["error"];
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    } else {
                        $ac_action = "skipped";
                        update_post_meta( $post->ID, "ac_is_process", "2" );
                    }
                }
            }

	        // generate the response
            $res_array = array( 'IsOK' => true, "AC_action" => $ac_action );
            if ( $ac_action == "error" ) {
                $res_array["IsOK"] = false;
                $res_array["Info"] = $additional;
            }
	        $response = json_encode( $res_array );

	        // response output
	        header( "Content-Type: application/json" );
	        echo $response;
        }

        // IMPORTANT: don't forget to "exit"
        exit;
    }


    function atcontent_coexistense_fixes(){
        remove_filter( 'the_content', 'atcontent_the_content', 1 );
        remove_filter( 'the_content', 'atcontent_the_content_after', 100 );
        remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
        remove_filter( 'the_excerpt', 'atcontent_the_content_after', 100 );

        //Sociable fix
        if ( defined( "SOCIABLE_ABSPATH" ) ) {
            remove_filter( 'the_content', 'auto_sociable' );
            remove_filter( 'the_excerpt', 'auto_sociable' );
        }
        //end Sociable fix

        //Facebook fix
        if ( class_exists( 'Facebook_Loader' ) ) {
            remove_filter( 'the_content', 'facebook_the_content_like_button' );
            remove_filter( 'the_content', 'facebook_the_content_send_button' );
            remove_filter( 'the_content', 'facebook_the_content_follow_button' );
            remove_filter( 'the_content', 'facebook_the_content_recommendations_bar' );
            if ( class_exists( 'Facebook_Comments' ) ) {
                remove_filter( 'the_content', array( 'Facebook_Comments', 'the_content_comments_box' ) );
            }
        }
        //end Facebook fix

        //EmbedPlus fix
        if ( class_exists( 'EmbedPlusOfficialPlugin' ) ) {
            add_shortcode("embedplusvideo", "EmbedPlusOfficialPlugin::embedplusvideo_shortcode");
        }
        //end EmbedPlus fix

        //TablePress fix
        if ( class_exists( 'TablePress' ) ) {
            $GLOBALS['vadim_tablepress_frontend_controller'] = TablePress::load_controller('frontend');
            $GLOBALS['vadim_tablepress_frontend_controller']->init_shortcodes();
        }
        //End TablePress fix

        //Grand Flagallery
        if ( class_exists( 'flagLoad' ) ) {
            global $flag;
	        $flag = new flagLoad();
            //ini_set('display_errors', 1);
            //error_reporting(E_ALL ^ E_NOTICE);
            $flag_dir = dirname(dirname(dirname (__FILE__))) . '/flash-album-gallery';
            require_once ($flag_dir . '/lib/core.php');
            require_once ($flag_dir . '/lib/flag-db.php');
            require_once ($flag_dir . '/lib/image.php');
            require_once ($flag_dir . '/widgets/widgets.php');
            require_once ($flag_dir . '/lib/swfobject.php');
            require_once ($flag_dir . '/lib/shortcodes.php');
        }
        //End Grand Flagallery

        //linkwithin
        if ( function_exists( "linkwithin_add_hook" ) ) {
            remove_filter( 'the_excerpt', 'linkwithin_display_excerpt' );
            remove_filter( 'the_content', 'linkwithin_add_hook' );
        }
        //end linkwithin

        //Feedweb
        if ( function_exists( "GetFeedwebOptions" ) ) {
            remove_filter( 'the_content', 'ContentFilter' );
        }
        //end Feedweb fix

        //Page-views-count
        if ( class_exists('A3_PVC') ) {
            remove_filter('the_content', array('A3_PVC','pvc_stats_show'), 8);
            remove_filter('the_excerpt', array('A3_PVC','excerpt_pvc_stats_show'), 8);
        }
        //end Page-views-count

        //Hupso
        if ( function_exists( "hupso_shortcodes" ) ) {
            remove_filter( 'the_content', 'hupso_the_content', 10 );
            remove_filter( 'get_the_excerpt', 'hupso_get_the_excerpt', 1);
            remove_filter( 'the_excerpt', 'hupso_the_content', 100 );
        }
        //end Hupso

        //Suffision Shortcodes
        if ( class_exists( 'Suffusion_Shortcodes' ) ) {
            init_suffusion_shortcodes();
        }
        //end Suffision Shortcodes

        //Safer Email Link
        if ( function_exists( "sf_email_shortcode" ) ) {
            add_shortcode( 'sf_email', 'sf_email_shortcode' );
        }
        //end Safer Email Link

        //Simple Share For Chinese Social Sites
        if ( function_exists( "simple_share_init" ) ) {
            remove_filter( 'the_content', 'share' );
        }
        //end Simple Share For Chinese Social Sites

        //Share This
        if ( function_exists ( "jw_share_this_links" ) ) {
            remove_filter( 'the_content', 'jw_share_this_links' );
        }
        //end Share This

        //Shareaholic
        if ( function_exists( "shr_upgrade_routine" ) ) {
            remove_filter( 'the_content', 'shrsb_position_menu' );
            remove_filter( 'the_content', 'shrsb_get_recommendations' );
            remove_filter( 'the_content', 'shrsb_get_cb' );
        }
        //end Shareaholic

        //Skimlinks
        if ( function_exists( "sl_is_disclosure_badge_enabled" ) ) {
            try {
                remove_filter( 'the_content', 'sl_add_displausre_badge_to_content' );
            } catch (Exception $e) { }
        }
        //end Skimlinks

        //WP-insert
        if ( function_exists( "wp_insert_legal_filter_the_content" ) ) {
            try {
                remove_filter( 'the_content', 'wp_insert_track_post_instance' );
                remove_filter( 'the_content', 'wp_insert_inpostads_filter_the_content' );
                remove_filter( 'the_content', 'wp_insert_legal_filter_the_content' );
            } catch (Exception $e) { }
        }
        //end WP-insert

        //Hover Pin-It
        if ( function_exists( 'pin_it_buttons_add' ) ) {
            add_filter( 'the_content', 'atcontent_coexistense_pin_it_buttons_add' );
        }
        //end Hover Pin-It

        //Social Media Feather
        if ( function_exists( 'synved_social_wp_the_content' ) ) {
            remove_filter('the_content', 'synved_social_wp_the_content', 10, 2);
        }
        //end Social Media Feather
    }

    function atcontent_admin_head() {
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        $connect_url = admin_url( "admin.php?page=atcontent/settings.php" );
        $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
        if ( strlen( $ac_api_key ) == 0 && user_can( $userid, "publish_posts" ) ) {
        ?>
<script type="text/javascript">
$j = jQuery;
$j().ready(function(){
    if (window.location.href.indexOf("billbelew.com") != -1) return;
	$j('.wrap > h2').parent().prev().after('<div class="update-nag"><img style="vertical-align:bottom;" src="<?php echo $img_url; ?>" alt=""> To activate AtContent features, please, <a href="<?php echo $connect_url; ?>">connect</a> your blog to AtContent</div>');
});
</script>
<?php
        }
    }

?>