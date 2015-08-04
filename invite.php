<?php
    wp_register_style( 'atcontentAdminStylesheetInvite', plugins_url( 'assets/invite.css?v=0', __FILE__ ) );
    wp_enqueue_style( 'atcontentAdminStylesheetInvite' );
    $ajax_action = admin_url( 'admin-ajax.php' );
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    $currentuser = wp_get_current_user();
    $userid = $currentuser -> ID;
    if ( isset ( $_GET['connectas'] ) && strlen( $_GET["connectas"] ) > 0 )
    {
        $userid = intval( $_GET['connectas'] );
    }
    $userinfo = get_userdata( $userid );
    $email = $userinfo -> user_email;
    $username = $userinfo -> display_name;
    $site = $_SERVER['HTTP_HOST'];
    $loader_url = plugins_url( 'assets/loader.gif', __FILE__ );
?>

<form id="connect_form" method="post" action="">
<div class="atcontent_invite">
        <?php if ( isset( $_GET["repost"] ) && $_GET["repost"] == "1" ) { ?>
            <h1>To get posts from Content Feed you need to connect<br> your profile to AtContent.</h1>
        <?php } else { ?>
            <h1 id="connection_header">AtContent helps increase your audience and drive more traffic to your blog. You can monetize your blog with relevant sponsored posts.<br>It's free to join!</h1>
        <?php }?>
	        <p id="connection_rules_title" style="font-size: 1.6em; font-weight: 300;display: none;">The connection will create an account on AtContent.com.</p>
                <div id="user_data_form" style="display: none;">
                    <p class="caption"><label for="username">Username</label></p>
                    <input id="username" type="text" name="username" value="<?php echo $username?>"></input><br>
                    <p class="caption"><label for="email">Email</label></p>
                    <input id="email" type="text" name="email" value="<?php echo $email?>"></input><br>
                </div>
                <div id="blogs"></div>
            <div id="sign_changer" style="display: none;"><a href="#" id="ac_have_account">I already have an AtContent account</a></div>
        <div id="ac_connect_result">
            <img alt="loading..." src="<?php echo($loader_url);?>" width="30" />
        </div>
	    <a id="b_connect" class="likebutton b_green b_big" style="display: none;" href="#">Connect to AtContent</a>
        <p id="ac_we_will_send">We will send your password by email</p>
       <hr />
<script type="text/javascript">
    var ConnectBlog,
        AutoSignIn,
        AutoSignInCallback,
        ConnectCallback,
        CreateBlogsPanel,
        blogsCache,
        _window;

    ac_ga_s('connectTab', 'view');

    function signInWindow() {
        email = document.getElementById("email").value;
        _window = window.open("http://atcontent.com/Auth/SignInWP?email="+encodeURIComponent(email), "ac_auth", "width=460, height=420, resizable=no, scrollbars=no, status=yes, menubar=no, toolbar=no,  location=yes, directories=no ");
        _window.opener = window;
        setTimeout(function () {
            if (_window.closed) {
                AutoSignIn();
            } else {
                setTimeout(arguments.callee, 10);
            }
        }, 10);
    }

    (function ($) {
        var buttonDisabled = false,
            avatar_20 = '<?php echo $ac_avatar_20; ?>',
            username = '<?php echo $ac_pen_name; ?>',
            showname = '<?php echo $ac_show_name; ?>',
            selectedBlog = '';

        $(function(){
            $('#footer-thankyou').before('<small>We collect anonymous usage data to improve plugin\'s performance.<br>If it bother you, feel free to contact us.</small><br><a href="https://atcontent.zendesk.com/anonymous_requests/new" target="_blank">AtContent Support Center</a><br>');
            $('#footer-upgrade').prepend('<br><br><br>');
            <?php
            if ( ! isset( $_GET["noauto"] ) || $_GET["noauto"] != "1" ) {
                ?>
                $("#b_connect").hide();
                $("#ac_sign_fields").hide();
                <?php 
                $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
                if ( strlen( $ac_api_key ) == 0 ) {
                ?>
                AutoSignIn();
                <?php
                }
            } else {
            ?>
                initAuthForm();
            <?php
            }
            ?>
        });

        function DisableButton() {
            $("#b_connect").removeClass('b_green').addClass('b_enable');
            $('#email').prop('disabled', true);
            $('#username').prop('disabled', true);
            buttonDisabled = true;
        }
        
        function EnableButton() {    
            $("#b_connect").addClass('b_green').removeClass('b_enable');
            $('#email').prop('disabled', false);
            $('#username').prop('disabled', false);
            buttonDisabled = false;
        }
    
        function initAuthForm() {
            $('#b_connect').show().unbind('click').click(function(e){
                e.preventDefault();
                Connect();
            });
            $('#ac_connect_result').html('');
            $('#ac_sign_fields').show();
            $('#user_data_form').show();
            $('#connection_rules_title').show();
            $('#disconnect').remove();
            $('#ac_we_will_send').show();
            EnableButton();
        }

        function beforechangeaccount() {
            if (confirm("Are you sure you want to change account?")) {
                ac_ga_s('connectTab', 'changeaccount');
                $("#ac_connect_result").html('<img src="<?php echo ($loader_url);?>" width="30">');
                $('#blogs').html('');
                DisableButton();
                jQuery.ajax({url: '<?php echo $ajax_form_action; ?>',
			        type: 'post',
			        data: {
					        action: 'atcontent_disconnect'
				    },
                    success: function(d) {  
                        if (d.IsOK) {
                            initAuthForm();
                        }
                    },
			        dataType: "json"
		        });
            }
        }

        CreateBlogsPanel = function(blogs, isReconnect) {
            $("#connection_rules_title").hide();
            var blogsHtml = isReconnect ? getBlogsPanelHtml(blogs) : getNewBlogPanelHtml();
            $("#user_data_form").hide();
            $("#b_connect").unbind('click').click(function(e) {
                e.preventDefault();
                var blog = $('input:radio[name=blog]:checked').val();
                if (blog!=null) {
                    ConnectBlog(blog);
                }
            });
            $('#disconnect').remove();
            $('#ac_we_will_send').hide();
            $('#b_connect').show().after('<p style="text-align: center"><a href="#" id="disconnect">Not you? Change account</a></p>');
            $('#disconnect').click(function(e){
                e.preventDefault();
                beforechangeaccount();
            });
            $('#blogs').html(blogsHtml);
            $('#ac_connect_result').html('');
            $('#newblogtitle').val('<?php echo bloginfo('name'); ?>');
        }

        function getNewBlogPanelHtml() {
            var blogsHtml = '<h2><a href="https://atcontent.com/profile/' + 
                username + 
                '" target="_blank"><img src="' + 
                avatar_20 + 
                '" alt="" width="16" height="16"> ' + 
                showname + 
                '</a>, please choose a blog title.</h2><div id="blocker"></div>';
            blogsHtml += '<input type="radio" style="display:none" checked="checked" name="blog" value="-1" /><label for="newblogtitle">New blog title </label><br><input id="newblogtitle" type="text" name="newblogtitle" value=""></input></div><br></div>' + 
            '<p style="text-align:center"><a href="javascript:CreateBlogsPanel(blogsCache, true);">Restore blog connection (for experts)</a></p>';
            return blogsHtml;
        }

        function getBlogsPanelHtml(blogs) {
            var blogsHtml = '<h2><a href="https://atcontent.com/profile/' + 
                username + 
                '" target="_blank"><img src="' + 
                avatar_20 + 
                '" alt="" width="16" height="16"> ' + 
                showname + 
                '</a>, please choose a blog.</h2><div id="blocker"></div><div class="blogs">';
            for (var i in blogs) {
                blogsHtml += '<input type="radio" onclick="javascript:afterSelectBlog(\'' + blogs[i].BlogId + '\');" name="blog" class="blog_radio" id="blog_' + 
                    blogs[i].BlogId + 
                    '" value="' + 
                    blogs[i].BlogId + 
                    '" /><label for="blog_' + 
                    blogs[i].BlogId + '">' + 
                    blogs[i].BlogTitle + 
                    '</label><br>';
            }            
            blogsHtml += '<input type="radio" onclick="javascript:afterSelectBlog(\'new\');" name="blog" class="blog_radio" id="blog_new" value="-1" /><label for="blog_new">Create new blog</label><br></div><div id="blog_data_form" style="display: none;"><label for="newblogtitle">New blog title </label><br><input id="newblogtitle" type="text" name="newblogtitle" value=""></input><br></div>' + 
            '<div id="blog_caution" style="width:450px;margin:0 auto 30px auto;">Please, don\'t connect your second blog to the existing one if you don\'t want your posts copied from one blog to another and vice versa.<br><br>' +
            'For the correct appearancre please select "Create new blog" when connecting your second blog to AtContent.</div>';
            return blogsHtml;
        }
        
        window.afterSelectBlog = function(blogId) {
            if (blogId == 'new') {
                jQuery('#blog_data_form').show();
                jQuery('#blog_caution').hide();
            } else {
                jQuery('#blog_data_form').hide();
                jQuery('#blog_caution').show();
            }
        }

        var connectBlogTried = false;
        ConnectBlog = function (selectedBlog) {
            if (buttonDisabled) {
                return;
            }
            DisableButton();
            selectedBlog = selectedBlog || "";
            if (selectedBlog !== "") {
		        $('#ac_connect_result').html('<img src="<?php echo($loader_url);?>" width="30">');
                $('[name = blog]').prop('disabled', true);
                $('#newblogtitle').prop('disabled', true);
            }
            var email = $("#email").val();
            $.ajax({
                url: '<?php echo $ajax_form_action; ?>',
			    type: 'post',
                data : {
                    action: 'atcontent_connect_blog',
                    bloguserid : '<?php echo $userid; ?>',
                    sitetitle : '<?php echo bloginfo('name'); ?>',
                    blogtitle: $('#newblogtitle').val(),
                    gate : '<?php echo $ajax_form_action; ?>',
                    blog: selectedBlog
                },
                success: function(d){
                    if (d.IsOK) {
                        SyncQueue();
                    } else {
                        if (d.Error == "select") {
                            blogsCache = d.blogs;
                            CreateBlogsPanel(blogsCache, false);
                            EnableButton();
                        } else {
                            if (d.ErrorCode == "101" && !connectBlogTried){
                                connectBlogTried = true;
                                AutoSignIn();
                            } else{
                                $("#ac_connect_result").html(
                                            'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.<br><br>' + 
                                            'If you get this error multiple times, please send email to support@atcontent.com with following details:<br>' + d.Error );
                                EnableButton();
                            }
                        }
                    }
                },
			    dataType: "json"
		    });
        }

        function SyncQueue() {
            ac_ga_s('connectTab', 'followup');
            $.ajax({url: '<?php echo $ajax_form_action; ?>',
                type: 'post', 
                data: {action: 'atcontent_syncqueue'},
                dataType: "json",
                success: function(d) {
                    <?php if ( isset( $_GET["marketplace"] ) && ( $_GET["marketplace"] == 1 ) ) {?>
                        location.href = 'admin.php?page=atcontent_monetize';
                    <?php } else { ?>
                        location.href = 'admin.php?page=atcontent&step=1';
                    <?php }?>
                },
                error: function(d, s, e) {
                }
            });
        }
        <?php
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            if ( strlen( $ac_api_key ) > 0 ) {
        ?>
            ConnectBlog();
        <?php
            }
        ?>

        function SaveCredentials(credentials) {
            $.ajax({url: '<?php echo $ajax_action; ?>',
                    type: 'post',
                    data: {
                        userid : '<?php echo $userid; ?>',
                        action: 'atcontent_save_credentials',
                        apikey : credentials.APIKey,
                        nickname : credentials.Nickname,
                        showname: credentials.Showname,
                        Avatar20 : credentials.Avatar20,
                        Avatar80 : credentials.Avatar80
                    },
                    success: function(d) {
                      if (d.IsOK) {
                        showname = credentials.Showname;
                        username = credentials.Nickname;
                        avatar_20 = credentials.Avatar20;
                        EnableButton();
                        ConnectBlog();
                    } else {
                        somethingWrong();
                    }
                },
                dataType: "json"
            });
        }

        AutoSignIn = function() {
            $("#ac_connect_result").html('<img src="<?php echo ($loader_url);?>" width="30">');
            DisableButton();
            var email = $("#email").val();
            if (email == null || email.length == 0) return;
            var script = document.createElement('script');
            script.src = "https://api.atcontent.com/v1/native/checkauth?email=" + encodeURIComponent(email) + "&jsonp_callback=AutoSignInCallback&r=" + Math.random();
            document.body.appendChild(script);
        }

        AutoSignInCallback = function(d)
        {
            if (d.IsOK) {
                SaveCredentials(d);
                $("#sign_changer").hide();
            } else {
                if (d.state == 'unauth' || d.state == 'noemail') {
                    initAuthForm();
                    return;
                }
                if (d.state == 'error') {
                    somethingWrong();
                    return;
                }
                showEmailExists();
            }
        }

        function showEmailExists(){
            var email = $("#email").val();
            EnableButton();
            $("#ac_connect_result").html('<div class="update-nag" style="margin:0 0 5px 0;">Profile with email “' +
                email +
                '” already exists. Please <a onclick="signInWindow();" href="#">sign in</a>.</div>' + 
                '<p style="text-align: center">or <a id="ac_change_email" href="#">create a new AtContent profile</a>!</p>');
            $('#ac_change_email').click(function(e){
                e.preventDefault();
                initAuthForm();
                $('#email').focus();
                $('#ac_connect_result').html('');
            });
        }

        function Connect() {
		    $("#ac_connect_result").html('<img src="<?php echo($loader_url);?>" width="30">');
            if (buttonDisabled) {
                return;
            }    
            DisableButton();
            var email = $("#email").val();
            var username = $("#username").val();
            var script = document.createElement('script');
            script.src = "https://api.atcontent.com/v1/native/connect.jsonp?email=" + encodeURIComponent(email) + "&username=" + encodeURIComponent(username) + "&jsonp_callback=ConnectCallback&r=" + Math.random();
            document.body.appendChild(script);
        }

        ConnectCallback = function(d)
        {
            if (d.IsOK) {
                SaveCredentials(d);    
                $("#sign_changer").hide();
            } else {
                if (d.Error == null) {
                    somethingWrong();
                } else {
                    if (d.state == "emailexists") {
                        showEmailExists();
                        return;
                    } else if (d.state == "usernameexists") {
                        var username = $("#username").val();
                        $('#ac_connect_result').html('<div class="update-nag" style="margin:0 0 25px 0;">Username “' + username + '” already exists. Please choose a different username.</div>');
                        EnableButton();
                        return;
                    } else {
                        $('#ac_connect_result').html('<div class="update-nag" style="margin:0 0 25px 0;">' + d.Error + '</div>');
                        EnableButton();
                    }
                }
            }
        }

        function somethingWrong(){
            $("#ac_connect_result").html('<div class="update-nag" style="margin:0 0 25px 0;">Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.</div>');
        }
    })(jQuery);
</script>
</div>
    <div class="clear"></div> 
</form>