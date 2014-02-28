<?php
    wp_register_style( 'atcontentAdminStylesheetInvite', plugins_url( 'assets/invite.css?v=0', __FILE__ ) );
    wp_enqueue_style( 'atcontentAdminStylesheetInvite' );
    $ajax_action = admin_url( 'admin-ajax.php' );
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    $currentuser = wp_get_current_user();
    $userid = $currentuser -> ID;
    if ( strlen( $_GET['connectas'] ) > 0 )
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
        <h1>Get quality posts for your site and boost readership 2,5x in 30 days!</h1>
	        <p id="connection_rules_title" style="font-size: 1.6em; font-weight: 300;display: none;">The connection will create an account on AtContent.com.</p>
                <div id="user_data_form" style="display: none;">
                    <p class="caption"><label for="username">Username</label></p>
                    <input id="username" type="text" name="username" value="<?php echo $username?>"></input></br>
                    <p class="caption"><label for="email">Email</label></p>
                    <input id="email" type="text" name="email" value="<?php echo $email?>"></input></br>
                </div>
                <div id="blogs"></div>
            <div id="sign_changer" style="display: none;"><a href="#" id="ac_have_account">I already have an AtContent account</a></div>
        <div id="ac_connect_result">
            <img alt="loading..." src="<?php echo($loader_url);?>" width="30" />  
        </div>
	    <a id="b_connect" class="likebutton b_green b_big" style="display: none;" href="#">Connect with AtContent</a>
        <p id="ac_we_will_send">We will send your password by email</p>
       <hr />
<script type="text/javascript">
    var ConnectBlog;
    var AutoSignIn;
    
    function signInWindow() {
        email = document.getElementById("email").value;
        _window = window.open("http://atcontent.com/Auth/SignInWP?email="+email, "ac_auth", "width=460, height=420, resizable=no, scrollbars=no, status=yes, menubar=no, toolbar=no,  location=yes, directories=no ");
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
        var buttonDisabled = false;
        var apikey = '';  
        var avatar_20 = '<?php echo $ac_avatar_20; ?>';
        var username = '<?php echo $ac_pen_name; ?>';
        var showname = '<?php echo $ac_show_name; ?>'
        var selectedBlog = '';

        $(function(){
            $('#footer-thankyou').before('<a href="https://atcontent.zendesk.com/anonymous_requests/new" target="_blank">AtContent Support Center</a><br>');
            $('#footer-upgrade').prepend('<br>');
    
            <?php
            if ( $_GET["noauto"] != "1" ) {
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

        function CreateBlogsPanel(blogs) {
            $("#connection_rules_title").hide();
            var blogsHtml = '<h2><a href="https://atcontent.com/Profile/' + 
                username + 
                '" target="_blank"><img src="' + 
                avatar_20 + 
                '" alt="" width="16" height="16"> ' + 
                showname + 
                '</a>, please choose a blog.</h2><div id="blocker"></div><div class="blogs">';            
            for (var i in blogs) {
                blogsHtml += '<input type="radio" onclick="javascript:jQuery(\'#blog_data_form\').hide();" name="blog" class="blog_radio" id="blog_' + 
                    blogs[i].BlogId + 
                    '" value="' + 
                    blogs[i].BlogId + 
                    '" /><label for="blog_' + 
                    blogs[i].BlogId + '">' + 
                    blogs[i].BlogTitle + 
                    '</label><br>';
            }
            blogsHtml += '<input type="radio" onclick="javascript:jQuery(\'#blog_data_form\').show();" name="blog" class="blog_radio" id="blog_new" value="-1" /><label for="blog_new">Create new blog</label><br></div><div id="blog_data_form" style="display: none;"><label for="newblogtitle">New blog title </label></br><input id="newblogtitle" type="text" name="newblogtitle" value=""></input></br></div>'
            $("#user_data_form").hide();
            $("#b_connect").unbind('click').click(function() {
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
                    apikey : apikey,
                    sitetitle : '<?php echo bloginfo('name'); ?>',
                    blogtitle: $('#newblogtitle').val(),
                    gate : '<?php echo $ajax_form_action; ?>',
                    blog: selectedBlog
                },
                success: function(d){
                    if (d.IsOK) {
                        SyncQueue();                     
                    } else {
                        if(d.Error == "select") {
                            CreateBlogsPanel(d.blogs);     
                            EnableButton();                       
                        } else {
                            $("#ac_connect_result").html(
                                        'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
                            EnableButton();  
                        }
                    }
                },
			    dataType: "json"    
		    });  
        }

        function SyncQueue() {
            $.ajax({url: '<?php echo $ajax_form_action; ?>', 
                type: 'post', 
                data: {action: 'atcontent_syncqueue'},
                dataType: "json",
                success: function(d) {                                
                    location.href = 'admin.php?page=atcontent/dashboard.php&step=1';
                },
                error: function(d, s, e) {
                }
            });            
        }
        <?php 
            $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
            if ( strlen( $ac_api_key ) > 0 ) {
        ?>
            apikey = '<?php echo($ac_api_key); ?>';
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
                        apikey =  credentials.APIKey;   
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
            $.ajax({
                url: 'http://api.atcontent.com/v1/native/checkauth',
                jsonp: 'jsonp_callback',
                data : {
                    email : email
                },
                success: function(d){
                    if (d.IsOK) {
                        SaveCredentials(d);
                        $("#sign_changer").hide();
                    } else {
                        if (d.state == 'error') {
                            somethingWrong();
                            return;
                        }
                        if (d.state == 'unauth' || d.state == 'noemail') {
                            initAuthForm();
                            return;
                        }
                        showEmailExists();
                    }
                },
                error: function() {					
					somethingWrong();
				},
			    dataType: "jsonp"    
		    });  
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
            $.ajax({
                url: 'http://api.atcontent.com/v1/native/connect.jsonp',
                jsonp: 'jsonp_callback',
			    data: {
                    email : email,
                    username : username
			    },
                success: function(d){
				    if (d.IsOK) {
                        SaveCredentials(d);    
                        $("#sign_changer").hide();
                    } else {
                        if (d.Error == null){
                            somethingWrong();
                        } else {
                            if (d.state == "emailexists") {
                                showEmailExists();
                                return;
                            } else if (d.state == "usernameexists") {
                                $('#ac_connect_result').html('<div class="update-nag" style="margin:0 0 25px 0;">Username “' + username + '” already exists. Please choose a different username.</div>');
                                EnableButton();
                                return;
                            } else {
                                $('#ac_connect_result').html('<div class="update-nag" style="margin:0 0 25px 0;">' + d.Error + '</div>');
                                EnableButton();
                            }
                        }
                    }
			    },
			    error: function() {
				    somethingWrong();
			    },
			    dataType: "jsonp"
		    });
        }

        function somethingWrong(){
            $("#ac_connect_result").html('<div class="update-nag" style="margin:0 0 25px 0;">Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.</div>');
        }
    })(jQuery);
</script>
</div>
</form>
