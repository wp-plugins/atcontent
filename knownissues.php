<script type='text/javascript'>
    window.Muscula = { settings:{
        logId:"4f1fbfb7-5f25-4ae4-b2a0-b8fb074d6a3b", googleAnalyticsEvents: 'none', branding: 'none'
    }};
    (function () {
        var m = document.createElement('script'); m.type = 'text/javascript'; m.async = true;
        m.src = (window.location.protocol == 'https:' ? 'https:' : 'http:') +
            '//musculahq.appspot.com/Muscula.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(m, s);
        window.Muscula.run=function(c){eval(c);window.Muscula.run=function(){};};
        window.Muscula.errors=[];window.onerror=function(){window.Muscula.errors.push(arguments);
        return window.Muscula.settings.suppressErrors===undefined;}
    })();
</script>
<?php 
$userid = wp_get_current_user()->ID;
$hidden_field_name = 'ac_submit_hidden';
$form_message = '';
$form_script = '';
$form_message_block = '';
$ac_api_key = get_user_meta($userid, "ac_api_key", true );
$ac_pen_name = get_user_meta($userid, "ac_pen_name", true );
if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
    isset( $_POST[ "ac_advanced_settings" ] ) ) {
    update_user_meta( $userid, "ac_script_init", $_POST[ "ac_script_init" ] );
    $form_message .= 'Settings saved.';
}
if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
    isset( $_POST[ "ac_reset_posts_processing" ] ) ) {
         $wp_query_args = array(
                'post_author' => $userid,
                'post_status' => array('publish'),
                'nopaging' => true
                );
                $posts_query = new WP_Query( $wp_query_args );
            remove_filter( 'the_content', 'atcontent_the_content', 1 );
            remove_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );

            $posts_id = array();
            $posts_title = array();

            while( $posts_query->have_posts() ):
	            $posts_query->next_post();
                if ($posts_query->post->post_author == $userid) {
                    $ac_postid = get_post_meta($post->ID, "ac_postid", true);
                    $ac_is_process = ($ac_postid == "") ? "" : "1";
                    update_post_meta( $posts_query->post->ID, "ac_is_process", $ac_is_process );
                }
            endwhile;
            $form_message .= "Post processing settings are reseted.";
    }
?>
<div class="icon32" id="icon-tools"><br></div><h2>Known Plugins List</h2>
<?php 
 if (strlen($form_message) > 0) {
    $form_message_block .= <<<END
<div class="updated settings-error" id="setting-error-settings_updated"> 
<p><strong>{$form_message}</strong></p></div>
END;
}
echo $form_message_block;
  ?>
<br><br>
<h3>FancyBox for WordPress</h3>
<p><a href="http://plugins.josepardilla.com/fancybox-for-wordpress/">Visit plugin site</a></p>
<p>To integrate this plugin with AtContent copy code in textarea into "JavaScript Code for Plugin Init Script" field on AtContent Settings Page.
    <br><textarea>
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
</textarea></p>

<?php 
if (strlen($ac_api_key) > 0) {
    $ac_script_init = get_user_meta($userid, "ac_script_init", true );
?>
<form action="" method="POST">
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div><h3 style="padding-top: 7px;margin-bottom:0;">Advanced Settings</h3>
<br>

<div class="tool-box">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <input type="hidden" name="ac_advanced_settings" value="Y">
    <p>JavaScript Code for Plugin Init Script<br>
        <textarea rows="5" cols="80" name="ac_script_init"><?php echo $ac_script_init ?></textarea><br>
        * this code will run after AtContent widget load. If you have plugins that interact with your post content (like Lightbox, FancyBox, etc.) you should use this option.
    </p>
     <span class="submit">
        <input type="submit" name="Submit" class="button button-primary" value="<?php esc_attr_e('Save changes') ?>" />
    </span>
</div>
</div>
</form>
<br><br><br>
<h3>Service functions</h3>
<form action="" method="POST">
<div class="wrap">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">
    <input type="hidden" name="ac_reset_posts_processing" value="Y">
    <span class="submit">
        <input type="submit" name="Submit" class="button button-primary" value="<?php esc_attr_e('Reset AtContent processing settings for all posts') ?>" />
    </span>
</div>
</form>

<p>If you have some problems, ideas, feedback, questions — please <a href="http://atcontent.com/Support/">contact us</a>. We will use your help to make plugin better! :)</p>
<p>If you interested in plugin features description, please read it on <a href="http://wordpress.org/extend/plugins/atcontent/" target="_blank">AtCotnent plugin page</a></p>

<br><br>
Diagnostic info<br>
<textarea id="diag" rows="10" cols="60">
<?php echo "Plugin version: " . AC_VERSION . "\r\n" ?>
</textarea>

<script>
    (function ($) {
        $(function () {
            var val = $("#diag").val();
            val += "jQuery: " + $().jquery + "\r\n";
            $("#diag").val(val);
        });
    })(jQuery)
</script>

<?php 
}
$form_action = admin_url( 'admin-ajax.php' );
?>
<script type="text/javascript">
    jQuery(function(){
        jQuery.post('<?php echo $form_action ?>', {action: 'atcontent_pingback'}, function(d){
            if (d.IsOK) {
            }
        }, "json");
    });
</script>

<?php
