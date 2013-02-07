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
<p style="max-width: 600px;">With AtContent plugin for Wordpress you can protect your publications from plagiarism, monetize reposts, increase search ranking for your site, track and manage your content across the Internet and even sell your premium articles, music and other content (available in February).</p>
<p>To start using AtContent plugin you need to connect it to AtContent platform.</p>
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
            if (d.IsOK) {
            }
        }, "json");
    });
</script>

<?php