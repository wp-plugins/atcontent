<?php 
    $ajax_action = admin_url( 'admin-ajax.php' );
    $currentuser = wp_get_current_user();
    $userinfo = get_userdata($currentuser -> ID);
    $form_action = admin_url( 'admin.php?page=atcontent/connect.php' );
    $email = $userinfo -> user_email;
    $site = $_SERVER['HTTP_HOST'];
?>
<script src="/wp-content/plugins/atcontent/interface.js" type="text/javascript"></script>
<script>
    var email = '<?php echo $email?>';    
    var site = '<?php echo $site?>';
    window.CPlase_ga = window.CPlase_ga || [];
                CPlase_ga.push({
                    category: 'connectTab <?php echo AC_VERSION?>',
                    action: 'opened',
                    label: site + '      ' + email
                });
</script>
<form id="connect_form" method="post" action="<?php echo $form_action; ?>">
    <input type="hidden" name="atcontent_invite" value="Y">
<div class="atcontent_invite">
    <h1>Get quality posts for your site and boost readership 2,5x in 30 days!</h1>
	<p style="font-size: 1.6em; margin: 1em 0px 0.5em;">Click and complete the last step to increase your audience</p>
	<iframe id="ac_connect" src="http://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $ajax_action ?>&email=<?php echo $email?>&site=<?php echo $site?>&version=<?php echo AC_VERSION?>" style="width:302px;height:50px;" frameborder="0" scrolling="no"></iframe>
   <hr />
        
                    <div class="discl">
                        The connection creates an account on AtContent.com. The account will be used to expand your audience, gather readership stats and improve SEO.
					</div>
                    <div class="addit">
                        What bloggers tweet about us
                    </div>

    <div style="width: 400px;margin: 0 auto;">
        <blockquote class="twitter-tweet" lang="en"><p>860% increase in readership in 3 weeks WordPress with AtContent — even better. Check it! <a href="http://t.co/STLK6S5xK1">http://t.co/STLK6S5xK1</a></p>&mdash; Christine Cope (@christine_cope) <a href="https://twitter.com/christine_cope/statuses/412073834557100032">December 15, 2013</a></blockquote>
        <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
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
