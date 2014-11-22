<?php
    wp_register_style( 'atcontentAdminStylesheetInvite', plugins_url( '../assets/invite.css?v=0', __FILE__ ) );
    wp_enqueue_style( 'atcontentAdminStylesheetInvite' );
    $currentuser = wp_get_current_user();
    $userid = $currentuser -> ID;
    $userinfo = get_userdata( $userid );
    $email = $userinfo -> user_email;
    $username = $userinfo -> display_name;
    $site = $_SERVER['HTTP_HOST'];
    $loader_url = plugins_url( '../assets/loader.gif', __FILE__ );
    $ac_blog_api_key = get_option( 'ac_blog_api_key' );
?>

<div class="atcontent_invite">
    <h1 id="connection_header">AtContent helps dramatically increase your audience and drive more traffic to your blog. You can monetize your blog with relevant sponsored posts.<br>It's free to join!</h1>
    <p id="connection_rules_title" style="font-size: 1.6em; font-weight: 300;display: none;">The connection will create an account on AtContent.com.</p>
    <div id="user_data_form" style="display:none">
        <p class="caption"><label for="username">Username</label></p>
        <input id="b-ac-user-data__username" type="text" name="username" value="<?php echo $username?>"><br>
        <p class="caption"><label for="email">Email</label></p>
        <input id="b-ac-user-data__email" type="text" name="email" value="<?php echo $email?>"><br><br>
        <a id="b_connect" class="likebutton b_green b_big" href="#">Connect to AtContent</a>
        <div id="b-ac-user__signuploader" style="display:none">
            <br>
            <img src="<?php echo $loader_url ?>"><br>
            <p>creating profile on AtContent</p>
        </div>
        <div id="b-ac-user__signuperror" style="display:none">
        </div>
        <p><small>You can <a href="#" id="b-ac-user-data__signinsmall">sign in</a> AtContent if you already have profile.</small></p>
    </div>
    <div id="user_info_form" style="display: none">
        <p>We found your AtContent profile</p>
        <div class="b-ac-user">
            <div class="b-ac-user__info">
                <div class="b-ac-user__photo">
                    <a href="http://atcontent.com/profile/test/" target="_blank" id="b-ac-user__link"><img src="" width="80" height="80" alt="" id="b-ac-user__avatar" ></a>
                </div>
                <div class="b-ac-user__about">
                    <span class="b-ac-user__name" id="b-ac-user__username">Test Username</span>
                </div>
            </div>
            <br>
            <a href="#" id="b-ac-user__changeaccount" class="button button-large">Change profile</a>
            <a href="#" id="b-ac-user__continue" class="button button-primary button-large">Continue</a><br><br>
            <small>or <a href="#" id="b-createnewaccount">create new one</a></small>
        </div>
    </div>
    <div id="loader_process">
        <img src="<?php echo $loader_url ?>"><br>
        <p>connecting blog to AtContent</p>
    </div>

</div>
<script type="text/javascript">
    ac_ga_s('connectTab', 'view');
    (function ($) {
    'use strict';

        var _window, signUpInProcess = false;

        $("#b_connect").on('click', function(e){
            if (signUpInProcess) return;
            disableSignUp();            
            $('#b-ac-user__signuperror').hide();
            var email = $('#b-ac-user-data__email').val(),
                username = $('#b-ac-user-data__username').val(),
                script = document.createElement('script');
            script.src = 'https://api.atcontent.com/v2/user/create?email=' + encodeURIComponent(email) + '&username=' + encodeURIComponent(username) + '&appId=WordPress&jsonp_callback=ac_signup_callback&r=' + Math.random();
            document.body.appendChild(script);
        });
        
        window.ac_signup_callback = function(e) {
            enableSignUp();
            if (e.IsOK) {
                setApiKey();
            } else {
                if (e.state == 'emailexists') {
                    $('#b-ac-user__signuperror').html('<br><p>We found AtContent profile with the same email. Please <a href="#" id="b-ac-user__signin">sign in</a> or change email.</p>').show();
                    $('#b-ac-user__signin').on('click', function(e){
                        e.preventDefault();
                        signIn();
                    });
                } else if (e.state == 'usernameexists') {
                    $('#b-ac-user__signuperror').html('<br><p>We found AtContent profile with the same username. Please change username to continue.</p>').show();
                } else {
                    $('#b-ac-user__signuperror').html('<br><p>' + e.Error + '</p>').show();
                }
            }
        };
        
        $('#b-ac-user__continue').on('click', function(e){
            e.preventDefault();
            $('#user_info_form').hide();
            setApiKey();
        });
        
        function setApiKey() {
            $('#user_data_form').hide();
            $('#loader_process').show();
            var script = document.createElement('script');
            script.src = 'https://api.atcontent.com/v2/blog/setapikey?key=' + encodeURIComponent('<?php echo $ac_blog_api_key ?>') + '&id=<?php echo $userid ?>&gate=' + encodeURIComponent('<?php echo admin_url('admin-ajax.php') ?>') + '&jsonp_callback=ac_setapikey_callback&r=' + Math.random();
            document.body.appendChild(script);
        }
        
        window.ac_setapikey_callback = function(e){
            if (e.state == 'ok') {
                doActivate();
            }            
        };
        
        function doActivate(){
            $.post('<?php echo admin_url('admin-ajax.php') ?>', { 'action' : 'atcontent_blogactivate' }, function(d){
                if (d.IsOK) {
				  <?php 
					$ac_is_envato_user = get_user_meta( wp_get_current_user() -> ID, "ac_is_envato_version", true );
					if($ac_is_envato_user == "1" ){?>
					  location.href = 'admin.php?page=atcontent&step=envatoUser';
				  <?php } else{?>
					  location.href = 'admin.php?page=atcontent&step=1';
				  <?php }?>
                } else {
                    var script = document.createElement('script');
                    script.src = 'https://api.atcontent.com/v2/blog/activatejs?key=' + encodeURIComponent('<?php echo $ac_blog_api_key ?>') + '&userId=<?php echo $userid ?>&gate=' + encodeURIComponent('<?php echo admin_url('admin-ajax.php') ?>') + '&jsonp_callback=ac_activate_callback&r=' + Math.random();
                    document.body.appendChild(script);
                }
            }, "json");
        }
        
        window.ac_activate_callback = function(e) {
            if (e.state == 'ok') {
                <?php 
					$ac_is_envato_user = get_user_meta( wp_get_current_user() -> ID, "ac_is_envato_version", true );
					if($ac_is_envato_user == "1" ){?>
					  location.href = 'admin.php?page=atcontent&step=envatoUser';
				  <?php } else{?>
					  location.href = 'admin.php?page=atcontent&step=1';
				  <?php }?>
            } else {
                alert('Sorry, your blog is isolated from AtContent service.');
            }
        }
        
        function disableSignUp() {
            $("#b_connect").removeClass('b_green').addClass('b_enable');
            $('#b-ac-user-data__email').prop('disabled', true);
            $('#b-ac-user-data__username').prop('disabled', true);
            $('#b-ac-user__signuploader').show();
            signUpInProcess = true;
        }
        
        function enableSignUp() {    
            $("#b_connect").addClass('b_green').removeClass('b_enable');
            $('#b-ac-user-data__email').prop('disabled', false);
            $('#b-ac-user-data__username').prop('disabled', false);
            $('#b-ac-user__signuploader').hide();
            signUpInProcess = false;
        }
        
        $('#b-createnewaccount').on('click', function(e){
            e.preventDefault();
            $('#user_info_form').hide();
            $('#user_data_form').show();
        });
        
        function signInWindowChecker(){
            if (_window.closed) {
                checkAuth();
            } else {
                setTimeout(signInWindowChecker, 10);
            }
        }
        
        $('#b-ac-user-data__signinsmall').on('click', function(e) {
            e.preventDefault();
            signIn();
        });
        
        $('#b-ac-user__changeaccount').on('click', function(e){
            e.preventDefault();
            ac_ga_s('connectTab', 'changeaccount');
            signIn();
        });
        
        function signIn(){
            var width = 460,
                height = 420,
                top = (screen.availHeight - height) / 2,
                left = (screen.availWidth - width) / 2;
            _window = window.open("http://atcontent.com/signout/?redirect=" + encodeURIComponent('/auth/signinwpflat'), "ac_auth", 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=' + height + ',width=' + width + ',top=' + top + ',left=' + left);
            _window.opener = window;
            setTimeout(signInWindowChecker, 10);
        }
                
        window.ac_userinfo_callback = function(e){
            $('#loader_process').hide();
            if (e.IsAuth) {
                $("#b-ac-user__link")[0].href = "http://atcontent.com/profile/" + e.Nickname;
                $("#b-ac-user__avatar")[0].src = e.Avatar80;
                $("#b-ac-user__username").html(e.Showname);
                $('#user_info_form').show();
            } else {
                $('#user_data_form').show();
            }
        };
        
        function checkAuth() {
            $('#loader_process').show();
            $('#user_info_form').hide();
            $('#user_data_form').hide();
            var script = document.createElement('script');
            script.src = "https://api.atcontent.com/v2/user/info?jsonp_callback=ac_userinfo_callback&r=" + Math.random();
            document.body.appendChild(script);
        }
        
        $(function(){
           checkAuth(); 
        });
        
    })(jQuery);
</script>