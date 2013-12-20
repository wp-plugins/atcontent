<?php 
    $ajax_action = admin_url( 'admin-ajax.php' );
    $currentuser = wp_get_current_user();
    $form_action = admin_url( 'admin.php?page=atcontent/connect.php' );
?>
<form id="connect_form" method="post" action="<?php echo $form_action; ?>">
    <input type="hidden" name="atcontent_invite" value="Y">
<div class="atcontent_invite">
    <h1>AtContent is the easiest way to increase readership and SEO, get backlinks and copy-paste protection!</h1>
	<p style="font-size: 1.6em; margin: 1em 0px 0.5em;">To start using the plugin, please</p>
	<iframe id="ac_connect" src="http://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $ajax_action ?>" style="width:302px;height:50px;" frameborder="0" scrolling="no"></iframe>
   <hr />
        
                    <div class="discl">
                        The connection creates an account on AtContent.com. The account will be used to prevent copy-paste, expand your audience, gather readership stats and do other great things.
					</div>
                    <div class="addit">
                        1,250,000 posts processed by AtContent to date.
                    </div>
                
                    <div id="ac_connect_result"></div>
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) document.getElementById("connect_form").submit();
            else $("#ac_connect_result").html( 
                    'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
    })(jQuery);
</script>
</div>
</form>
