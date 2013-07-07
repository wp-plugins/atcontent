<?php $form_action = admin_url( 'admin-ajax.php' ); ?>
<div class="atcontent_invite">
    <h1>AtContent is the easiest way to increase readership and SEO,</h1>
    <h1>Get backlinks and plagiarism prevention!</h1>
	<h2>To start using the plugin, please connect it to AtContent:</h2>
	<iframe id="ac_connect" src="http://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $form_action ?>" style="width:302px;height:50px;" frameborder="0" scrolling="no"></iframe>
   <hr />
        <table>
            <tr>
                <td style="width: 275px;">
                    <div class="discl">
                        After connection to<br>
                        AtContent your posts<br>
                        will be displayed on <br>
                        AtContent without any<br>
                        content duplication.<br>
                        You'll get backlinks,<br>
                        additional readership<br>
                        and plagiarism<br>
                        prevention!<br>
					</div>
                    <div class="addit">
                        650,000 posts processed<br>
                        by AtContent to date.
                    </div>
                </td>
                <td style="text-align: center;">
                    <iframe width="425" height="313" src="http://www.youtube.com/embed/1U4zq5qhRmk?rel=0&showinfo=0" frameborder="0" allowfullscreen></iframe>
                    <br><br>
                    <div id="ac_connect_result"></div>
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) window.location.reload();
            else $("#ac_connect_result").html( 
                    'Something get wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
    })(jQuery);
</script>
                    <br><br>
                </td>
            </tr>
        </table>
    </div>