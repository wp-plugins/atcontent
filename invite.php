<?php $form_action = admin_url( 'admin-ajax.php' ); ?>
<div class="atcontent_invite">
    <h1>AtContent is the easiest way to increase readership and SEO,</h1>
    <h1>Get backlinks and plagiarism prevention!</h1>
	<h2>To start using the plugin, please connect it to AtContent site:</h2>
	<iframe id="ac_connect" src="http://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $form_action ?>" style="width:302px;height:50px;" frameborder="0" scrolling="no"></iframe>
   <hr />
        
                    <div class="discl">
                        The connection creates an account on AtContent.com which will be used to prevent plagiarism, expand your audience, gather readership stats and do other great things.
					</div>
                    <div class="addit">
                        1,250,000 posts processed by AtContent to date.
                    </div>
                
                    <div id="ac_connect_result"></div>
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) window.location.reload();
            else $("#ac_connect_result").html( 
                    'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
    })(jQuery);
</script>                  
    </div>