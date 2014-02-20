<?php 
    $ajax_action = admin_url( 'admin-ajax.php' );
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    $currentuser = wp_get_current_user();
    $userid = $currentuser -> ID;
    if (strlen($_GET['connectas']) > 0)
    {
        $userid = intval($_GET['connectas']);
    }
    $userinfo = get_userdata($userid);
    $email = $userinfo -> user_email;
    $username = $userinfo -> display_name;
    $site = $_SERVER['HTTP_HOST']; 
    $loader_url = plugins_url( 'assets/loader.gif', __FILE__ );
       
?>

<style>
    input {
        padding: 3px 8px;
        font-size: 1.7em;
        line-height: 100%;
        height: 1.7em;
        width: 300px;
        outline: 0;
        margin: 0 0 0 0;
    }

    .caption {
        font-size: 12px;
        width: 300px;
        margin: auto;
        margin-top: 15px;        
        text-align: left;    
    }
    
    .blocked {
        background-color: darkgrey;
        padding-left: 10px;
        width: 40%;
        margin-left: 20%;
        position: absolute;
        opacity: 0.5;
    }
    
    .blogs {
        text-align: left;
        width: 30%;
        margin-left: 35%;
        font-size: 18px;
        margin-bottom: 20px;
    }

    #blog_data_form {
        margin-bottom: 20px;
    }

    #credential_show_block {
        margin-bottom: 20px;
    }
    
    .blogs > input
    {
        margin-top: 10px;
        margin-bottom: 8px;
    }
    
</style>
<script>
    var gate = '<?php echo admin_url('admin-ajax.php'); ?>';
    var email = '<?php echo $email; ?>';    
    var site = '<?php echo $site; ?>';
    var title = '<?php bloginfo('name'); ?>';
    var userid = '<?php echo $userid; ?>';
</script>
<form id="connect_form" method="post" action="">
<div class="atcontent_invite">
    <?php if (strlen($ac_api_key) == 0) {     
    ?>  
        <h1>Get quality posts for your site and boost readership 2,5x in 30 days!</h1>
	    <p id="connection_rules_title" style="font-size: 1.6em; font-weight: 300;">The connection will create an account on AtContent.com.</p>
            <div id="user_data_form">
                <p class="caption">Email</p>
                <input id="email" type="text" name="email" value="<?php echo $email?>"></input></br>
                <p id="username_caption" class="caption">Username</p>
                <input id="username" type="text" name="username" value="<?php echo $username?>"></input></br>
                <p id="password_caption" class="caption">Password</p>
                <input id="password" type="password" name="password" value=""></input></br>
                <p id="confirm_caption" class="caption">Confirm password</p>
                <input id="confirm" type="password" name="confirm" value=""></input></br>                
            </div>
        <div id="sign_changer"><a href="#" onclick="signIn()">I already have an AtContent account</a></div>
        <div id="ac_connect_result"></div>       
	    <a id="b_connect" class="likebutton b_green b_big" href="#">Connect with AtContent</a>

       <hr />           
    <?php 
    } else {  
    ?>     
        <h1>AtContent is a cross-blogging and content distribution platform that boosts your readership 2.5x in 30 days</h1>
	
        <div id="ac_connect_result">
            <img alt="loading..." src="<?php echo($loader_url);?>" width="30" />  
        </div>     
	    <a id="b_connect" class="likebutton b_green b_big" href="#">Connect with AtContent</a>
    <?php
    }
    ?>
<script type="text/javascript">
    var ConnectBlog;
    var AutoSignIn;        

    function signIn()
    { 
        jQuery('#user_data_form').css('height', '40px'); 
        jQuery("#password").hide();
        jQuery("#password_caption").hide();
        jQuery("#confirm").hide();
        jQuery("#confirm_caption").hide();
        jQuery("#username").hide();
        jQuery("#username_caption").hide();
        jQuery("#sign_changer").html('<a href="#" onclick="signUp()">I want to sign up</a>');
        jQuery("#b_connect").unbind('click').click(function() {
            AutoSignIn();        
        });
    }

    function signUp()
    { 
        jQuery('#user_data_form').css('height', '260px');
        jQuery("#password").show();
        jQuery("#password_caption").show();
        jQuery("#confirm").show();
        jQuery("#confirm_caption").show();
        jQuery("#username").show();
        jQuery("#username_caption").show();
        jQuery("#sign_changer").html('<a href="#" onclick="signIn()">I already have an AtContent account</a>');
        jQuery("#b_connect").unbind('click').click(function() {
            Connect();        
        });
    }

    function beforechangeaccount() {
        if (confirm("Are you sure you want to change account?")) {
            jQuery.ajax({url: '<?php echo $ajax_form_action; ?>',
			    type: 'post',
			    data: {
					    action: 'atcontent_disconnect'
				}, 
                success: function(d) {  
                    if (d.IsOK) {
                        location.reload();
                    }
                },                   
			    dataType: "json"
		    });
        }
    }

    function signInWindow() {
        email = document.getElementById("email").value;
        _window = window.open("http://www.atcontent.com/Auth/SignInWP?email="+email, "ac_auth", "width=460, height=420, resizable=no, scrollbars=no, status=yes, menubar=no, toolbar=no,  location=yes, directories=no ");
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
        window.ac_connect_res = function (d) {
            if (d) {   
                document.getElementById("connect_form").submit();
            } else {
                $("#ac_connect_result").html(
                    'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
            }    
        }


        function DisableButton() {    
            $("#b_connect").removeClass('b_green').addClass('b_enable');
            buttonDisabled = true;
        }


        $(document).keypress(function (e) {
            if (e.which == 13) {
                jQuery("#b_connect").click.call(jQuery("#b_connect"));  
            }
        });
        
        function EnableButton() {    
            $("#b_connect").addClass('b_green').removeClass('b_enable');
            buttonDisabled = false;
        }

        function CreateBlogsPanel(blogs) {
            $("#connection_rules_title").hide();
            $("#site").val(title);
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
            blogsHtml += '<input type="radio" onclick="javascript:jQuery(\'#blog_data_form\').show();" name="blog" class="blog_radio" id="blog_new" value="-1" /><label for="blog_new">Create new blog</label><br></div><div id="blog_data_form" style="display: none;"><label for="email">New blog title </label></br><input id="site" type="text" name="site" value=""></input></br></div>'
            $("#user_data_form").hide();
            $("#b_connect").unbind('click').click(function() {
                title  = $("#site").val();
                var blog = $('input:radio[name=blog]:checked').val();
                if (blog!=null) {
                    DisableButton();
                    ConnectBlog(blog);
                } 
            });
            $("#b_connect").after('<p style="text-align: center"><a href="#"  id="disconnect" onclick="beforechangeaccount();">Not you? Change account</a></p>');
            $("#ac_connect_result").html(blogsHtml);
        }
        
        ConnectBlog = function (selectedBlog) {
            selectedBlog = selectedBlog || "";
            if (selectedBlog!="") {
		        $(".blogs").after('<img src="<?php echo($loader_url);?>" width="30">');
                $('[name = blog]').attr('disabled', 'disabled');
            }
            var email = $("#email").val();
            $.ajax({
                url: '<?php echo $ajax_form_action; ?>',
			    type: 'post',
                data : {
                    action: 'atcontent_connect_blog',
                    bloguserid : userid,
                    apikey : apikey,
                    sitetitle : title,
                    gate : gate,
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
            if (strlen($ac_api_key) != 0) {
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
                        userid : userid,
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
                        ConnectBlog();
                    } else {
                        $("#ac_connect_result").html('Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
                    }
                },                   
			    dataType: "json"
		    });
        }

        AutoSignIn = function() {
		    $("#ac_connect_result").html('<img src="<?php echo ($loader_url);?>" width="30">');
            DisableButton();
            var email = $("#email").val();
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
                        EnableButton();                        
				        $("#ac_connect_result").html('<h2>We have found an AtContent account associated with ' +
                            email +
                            '. Please <a onclick="signInWindow();" href="#">sign in</a> to your AtContent account</h2>'); 
                    }
                },
                error: function() {					
					$("#ac_connect_result").html('Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
				},
			    dataType: "jsonp"    
		    });  
        }
        
        $("#b_connect").click(function(){
            Connect()
        });
    
        function Connect() {
            $(".discl").html('');
		    $("#ac_connect_result").html('<img src="<?php echo($loader_url);?>" width="30">');
            if (buttonDisabled) {
                return;
            }    
            DisableButton();
            var password = $("#password").val();
            var confirm = $("#confirm").val(); 
            if (password != confirm) {         
                $("#ac_connect_result").html('Passwords does not match');
                EnableButton(); 
            } else{
                var email = $("#email").val();
                var username = $("#username").val();
                $.ajax({
                    url: 'http://api.atcontent.com/v1/native/connect.jsonp',
                    jsonp: 'jsonp_callback',
			        data: {
				        action: 'atcontent_connect',
                        email : email,
                        username : username,
                        password : password
			        },
                    success: function(d){
				        if (d.IsOK) {
                            SaveCredentials(d);    
                            $("#sign_changer").hide();
                        } else {
                            if (d.Error == null){
                                $("#ac_connect_result").html('Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
                            } else {
                                if (d.Error.indexOf("email already exist")!=-1) {
                                    AutoSignIn();
                                } else {
                                    $("#ac_connect_result").html(d.Error);
                                    EnableButton();
                                }
                            }
                        }
			        },
			        error: function() {
				        $("#ac_connect_result").html('Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
			        },
			        dataType: "jsonp"
		        });
            }
        }
    })(jQuery);
</script>
</div>
</form>
