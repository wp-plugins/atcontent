<?php

    $ajax_form_action = admin_url( 'admin-ajax.php' );
    require_once( "include/atcontent_userinit.php" );
    if ( strlen( $ac_pen_name ) == 0 ) {
        $ac_pen_name = "AtContent";
    }
    $img_url = plugins_url( 'assets/logo.png', __FILE__ );

    $category1url = admin_url( "admin.php?page=atcontent/repost.php&category=1");
    $category2url = admin_url( "admin.php?page=atcontent/repost.php&category=2");
    $category3url = admin_url( "admin.php?page=atcontent/repost.php&category=7");
    $category4url = admin_url( "admin.php?page=atcontent/repost.php&category=4");
    $category5url = admin_url( "admin.php?page=atcontent/repost.php&category=5");
    $category6url = admin_url( "admin.php?page=atcontent/repost.php&category=6");

    update_user_meta( $userid, "ac_last_repost_visit", date("Y-m-d H:i:s") );

    $currentcategory = $_GET["category"];
    if ( strlen( $currentcategory ) == 0 ) $currentcategory = "1";

    $currentpage = $_GET["pageNum"];
    if ( strlen( $currentpage ) == 0 ) $currentpage = "1";

    $pageAnswer = atcontent_api_reposts( $currentcategory, $currentpage );
    if ( $pageAnswer["IsOK"] != true ) {
        wp_die( "Something gets wrong" . var_dump( $pageAnswer ) );
    }

    $atcontent_reposts = $pageAnswer["Page"]["PostIDs"];

    // PingBack
    if ( ! atcontent_pingback_inline() ) {
        echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
    }
    //End PingBack

    $currentuser = wp_get_current_user();
    $userinfo = get_userdata($currentuser -> ID);
    $email = $userinfo -> user_email;
    $site = $_SERVER['HTTP_HOST'];
?>
<script src="/wp-content/plugins/atcontent/assets/interface.js" type="text/javascript"></script>
<script>
    var email = '<?php echo $email?>';    
    var site = '<?php echo $site?>';
    gaSend('repost', 'opened','');
	var connected = true;
	
    function gaSend(category, action, p)
    {
        window.CPlase_ga = window.CPlase_ga || [];
                        CPlase_ga.push({
                            category: category + ' <?php echo AC_VERSION?>',
                            action: action,
                            label: site + '      ' + email + '      ' + p
                        });
    }

    function submitClick()
    {
        gaSend('repost', 'submit my posts clicked', '');
    }

    function repostClick(p)
    {
        gaSend('repost', 'repost clicked', p);
    }

    (function ($) {
        $(function () {
            $(".hide").click(function() { hide(); });
        });

        function hide()
        {
            document.getElementById('rate-block').style.visibility = 'hidden';
            document.getElementById('popup-bg').style.visibility = 'hidden';
            $.ajax({url: '<?php echo $ajax_form_action; ?>',
			    type: 'post',
			    data: {
					    action: 'atcontent_hide_rate',
					    },
			    dataType: "json"
		    });  
        }      
    })(jQuery);
</script>      
<div class="atcontent_wrap">
<?php if ( strlen( $ac_api_key ) == 0 ) { ?>
    <?php include("invite.php"); ?>
    <hr />
	<script>
		connected = false;
	</script>
    <br>
<?php } else
 {
    $rated = get_user_meta($userid, "ac_rated", true );
    $hide_form_action = "include/rate-hide.php";
    $usermeta = atcontent_api_get_userinfo($ac_api_key); 
    $unix_user_created = strtotime ($usermeta["UserCreated"]);
    $unix_now = strtotime("now");
    $days_diff = ($unix_now-$unix_user_created)/(60*60*24);    
    if ($unix_user_created > 0 && $days_diff > 30 && $rated != 1)
    {
        ?>
        <script>
            gaSend('repost', 'rate us showed', '');
        </script>
        <div id="popup-bg" class="popup-bg">
        </div>
        <div id="rate-block" class="rate-hidden">
            <p class="hide close-ico">&times;</p>
            <h1>Like AtContent plugin?</h1>
            <p>Please take a minute and rate it. Thanks for your support!</p>
            <img class="stars" src="/wp-content/plugins/atcontent/assets/stars.png" />
            <p>                
                <a class="hide likebutton b_green b_big" style="margin-bottom:15px;" href="http://wordpress.org/support/view/plugin-reviews/atcontent#postform" target="_blank">Rate now</a><br>
                <a class="hide dashed" style="font-size: 18px;" href="#">No, thanks</a>
            </p>
        </div>
        <?php        
    }    
 }
 
 ?>
<div class="wrap">
    <div class="icon32" id="icon-link"><br></div><h2>Content&nbsp;for&nbsp;reposting</h2>
</div>
    <br><br>
    <style>
        
        .close-ico{
            position: absolute;
            top: -20px;
            right: 0px;
            font-size: 44px!important;
            margin: 15px;
            font-weight: 900;
            
        }
        
        .close-ico:hover{
            cursor: pointer;
        }
        
    .rate-hidden {
        background: #fff;
        width: 40%;
        margin-left: 30%;
        font-size: larger;
        text-align: center;
        position: absolute;
        top: 200px;
        z-index: 201;
    }
        .rate-hidden > p {
            font-size: large;
        }
        
        .stars{
            width: 30%;
        }
    
    .contentColumns:after {
        clear: both;
        content: "";
        display: block;
    }
    .contentColumns {
        margin-left: -5px;
    }
    .mainCol {
        overflow: hidden;
        padding-left: 5px;
    }
        
    .asideCol {
        float: left;
        margin-left: 5px;
        margin-right: 35px;
        min-height: 1px;
        position: relative;
        width: 200px;
    }
    .article-inline {
        display: inline-block;
        margin-bottom: 50px;
        min-width: 350px;
        vertical-align: top;
        width: 48%;
    }
    </style>

    <div class="contentColumns">
        <div class="asideCol">
            <h2>Categories</h2>
            <p>
                <?php if ($currentcategory !== "1") { ?>
                    <a href="<?php echo $category1url; ?>">Business & Marketing</a><br>
                <?php } else { ?>
                    <strong>Business &amp; Marketing</strong><br>
                <?php }?>
                <?php if ($currentcategory !== "2") { ?>
                    <a href="<?php echo $category2url; ?>">Fashion & Style</a><br>
                <?php } else { ?>
                    <strong>Fashion &amp; Style</strong><br>
                <?php }?>
                <?php if ($currentcategory !== "7") { ?>
                    <a href="<?php echo $category3url; ?>">Self Improvement</a><br>
                <?php } else { ?>
                    <strong>Self Improvement</strong><br>
                <?php }?>
                <?php if ($currentcategory !== "4") { ?>
                    <a href="<?php echo $category4url; ?>">Tech</a><br>
                <?php } else { ?>
                    <strong>Tech</strong><br>
                <?php }?>
                <?php if ($currentcategory !== "5") { ?>
                    <a href="<?php echo $category5url; ?>">Politics</a><br>
                <?php } else { ?>
                    <strong>Politics</strong><br>
                <?php }?>
                <?php if ($currentcategory !== "6") { ?>
                    <a href="<?php echo $category6url; ?>">Religion &amp; Spirituality</a><br>
                <?php } else { ?>
                    <strong>Religion &amp; Spirituality</strong><br>
                <?php }?>
            </p>
            
            <br>
<p style="line-height: 12px">

<?php

$email_subject = $_SERVER['HTTP_HOST'] . " would like to be featured";

$email_body = "Hey AtContent team, \n" .
	"I would like to submit my posts from " . $_SERVER['HTTP_HOST'] . " to be on the Featured page.\n\n\n\n" .
	"%% You also can share your feedback right here - so, we'll be able to improve AtContent for you\n\n".
	"Thanks,\n".
	$_SERVER['HTTP_HOST'];

?>

<a href="mailto:mail@atcontent.com?subject=<?php
	echo str_replace('+', '%20', urlencode($email_subject)); ?>&body=<?php
	echo str_replace('+', '%20', urlencode($email_body)); ?>" onclick="submitClick()" class="likebutton b_green">Submit my Posts</a><br>
<br>
<small>


<span style="padding-left:13px">* Submit your posts to be</span><br>
<span style="padding-left:20px">featured on this page</span>

</small></p>


        </div>
    <style>
        .CPlase_panel { display: none; }
    </style>

    <div class="mainCol">
        <h3>Posts below can be published on your blog. Click "Repost to my blog" to try it.</h3>
        <div class="postList b-publications-columns">
            <?php foreach ( $atcontent_reposts as $postid ) { ?>
                <div class="article-inline" data-options="hide_shares" >
<script src="https://w.atcontent.com/CPlase/<?php echo $postid; ?>/Title/h3"></script>
<script data-ac-src="https://w.atcontent.com/CPlase/<?php echo $postid; ?>/Face"></script>

                </div>
            <?php } ?>
                
        </div>

<script>
    (function ($) {
        $(function () {
            CPlase = window.CPlase || {};
            CPlase.evt = CPlase.evt || [];
            CPlase.evt.push(function (event, p, w) {
                var hdl = $('h1,h2,h3,h4,h5,h6', document.getElementById('CPlase_' + p + '_' + w + '_title'));
                hdl.html('<a href="http://p.atcontent.com/' + p + '/">' + hdl.html() + '</a>');
                var o = $(document.getElementById('CPlase_' + p + '_' + w + '_panel'));
                if (!o.prev('.CPlase_publicationLink').size()) {
					o.before('<div style="margin: 1em 0 0" class="CPlase_publicationLink"><a id="acRepostBtn' + p + '" class="likebutton b_orange" href="javascript:repost_post(\'' + p + '\');">Repost to my blog</a></div>');
				}
            });
        });

        function connect_error(p) {
			var btn = document.getElementById('acRepostBtn' + p);
			$(btn).parent().html('<div class="update-nag">Please connect your blog with AtContent</div>');
			gaSend('repost', 'not connected repost clicked', '');
		}
		
        window.repost_post = function(p) {
			if (connected)
			{
				repostClick(p);
				var btn = document.getElementById('acRepostBtn' + p);
				btn.href = "javascript:";
				btn.innerHTML = "Reposting...";
				$(btn).removeClass("b_orange").addClass("b_white");
				$.ajax({url: '<?php echo $ajax_form_action; ?>',
					type: 'post',
					data: {
							action: 'atcontent_repost',
							ac_post: p
						  },
					dataType: "json",
					success: function(d){
						if (d.IsOK) {
							$(btn).parent().html('<div class="b-note success">Great! Post reposted! You are awesome!</div>');
						}
					},
					error: function(d, s, e) {
						btn.innerHTML = "Repost to my blog";
						btn.href = "javascript:repost_post('" + p + "');";
						$(btn).addClass("b_orange").removeClass("b_white");
					}
				});
			}
			else
			{
				connect_error(p);
			}
        }
    })(jQuery);
</script>
        <?php if ( $currentpage > 1 ) { 
            $prevPageUrl = admin_url( "admin.php?page=atcontent/repost.php&category=" . $currentcategory . "&pageNum=" . ( intval( $currentpage ) - 1 ) );
        ?>
            <a href="<?php echo $prevPageUrl; ?>" class="likebutton b_green">&larr; Previous page</a>
        <?php } ?>
        <?php if ( $pageAnswer["Page"]["HasNext"] == true ) { 
            $nextPageUrl = admin_url( "admin.php?page=atcontent/repost.php&category=" . $currentcategory . "&pageNum=" . ( intval( $currentpage ) + 1 ) );
        ?>
            <a href="<?php echo $nextPageUrl; ?>" class="likebutton b_green">Next page &rarr;</a>
        <?php } ?>
        </div>
    </div>

<br><br><br>
<p><a href="http://wordpress.org/extend/plugins/atcontent/" target="_blank">AtContent plugin page</a> &nbsp; 
    <a href="http://atcontent.com/Support/" target="_blank">Support</a> &nbsp; 
    <a href="http://atcontent.com/About/" target="_blank">About AtContent</a> &nbsp; 
    <a href="http://atcontent.com/Privacy/" target="_blank">Privacy Policy</a> &nbsp; 
    <a href="http://atcontent.com/Terms/" target="_blank">Terms and Conditions</a> &nbsp; 
</p>

</div>

<?php atcontent_ga("RepostTab", "Repost page"); ?>