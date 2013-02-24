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
if ( isset( $_POST[ $hidden_field_name ] ) && ( $_POST[ $hidden_field_name ] == 'Y' ) &&
    isset( $_POST[ "ac_api_key" ] ) ) {
    $ac_api_key = trim( $_POST[ "ac_api_key" ] );
    update_user_meta( $userid, "ac_api_key", $ac_api_key );
    $ac_pen_name = atcontent_api_get_nickname( $_POST[ "ac_api_key" ] );
    update_user_meta( $userid, "ac_pen_name", $ac_pen_name );
    $admin_url_main = admin_url("admin.php?page=atcontent/settings.php");
    ?>
<script>window.location = '<?php echo $admin_url_main ?>';</script>
<?php
    $form_message .= 'Settings saved.';
}
$ac_api_key = get_user_meta($userid, "ac_api_key", true );
$ac_pen_name = get_user_meta($userid, "ac_pen_name", true );
?>
<form action="" method="POST">
<div class="wrap">
<div class="icon32" id="icon-tools"><br></div><h2>AtContent Connect Settings</h2>
<div class="tool-box">
    <input type="hidden" name="<?php echo $hidden_field_name ?>" value="Y">    
<?php
         if ( strlen($ac_api_key) == 0 ) {
             $form_action = admin_url( 'admin-ajax.php' );
             ?>
<p style="width: 640px;">Why Over 3000 sites have chosen AtContent plugin? Because it’s the easiest and fastest way to reach new readership, 
    increase search ranking, protect and monetize your content!</p>
<p>To personalize your experience with AtContent plugin connect it to <a href="javascript:AtContentPlatform();">AtContent platform</a>.</p>
<p id="ac_platform_description" style="display: none;width: 640px;">
AtContent is a digital content platform which is actually brands content by your name, provide new readership, 
    backlinks for search ranking and many other valuable features. AtContent plugin is a part of AtContent platform on your site. 
    Our professional team working on it for 3 years already. We are US company and based in Silicon Valley and use cloud technologies. 
    So, be sure it's safety.<br><br>
    Watch the video how AtContent plugin works:<br>
    <iframe width="640" height="360" src="http://www.youtube.com/embed/Ex8EKJOjYJI" frameborder="0" allowfullscreen></iframe>
</p>
<script>
    function AtContentPlatform() {
        jQuery("#ac_platform_description").toggle();
    }
</script>
<div id="ac_connect_result"></div>
<iframe id="ac_connect" src="https://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $form_action ?>" style="width:75px;height:40px;" border="0" scrolling="no"></iframe>
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) window.location.reload();
            else $("#ac_connect_result").html( 
                    'Something get wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
    })(jQuery);
</script>
<?php
         } else {
?>
<p>You have connected blog to AtContent as <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><?php echo $ac_pen_name; ?></a>.
<input type="hidden" name="ac_api_key" value="">
<span class="submit" style="padding-left: 2em;"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Disconnect') ?>" /></span>
</p>
<?php           
         }
?>
</div>
</div>
</form>
<?php
$form_action = admin_url( 'admin-ajax.php' );
?>
<script type="text/javascript">
    jQuery(function(){
        jQuery.post('<?php echo $form_action ?>', {action: 'atcontent_pingback'}, function(d){
        }, "json");
    });
</script>