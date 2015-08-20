<?php
    wp_register_style( 'atcontentSettingsPage',  plugins_url( 'assets/settings.css?v=1', __FILE__ ), array(), true );
    wp_enqueue_style( 'atcontentSettingsPage' );
    $ajax_form_action = admin_url( 'admin-ajax.php' );
    require_once( "include/atcontent_userinit.php" );
    if ( strlen( $ac_pen_name ) == 0 ) {
        $ac_pen_name = "AtContent";
    }
    $img_url = plugins_url( 'assets/logo.png', __FILE__ );  
    include( 'include/atcontent_analytics.php' );
?>
<div class="b-ac-page b-ac-page_fluid" id="ac-page">
<?php if ( ac_isjsonly() ) { ?>
<script>
    window.location = 'http://atcontent.com/posts/';
</script>
<?php } else if ( strlen( $ac_api_key ) == 0 ) { ?>
<script>
        window.location = "admin.php?page=atcontent&repost=1";
</script>    
<?php } else {
// PingBack
    ?>
    <div id="ac_pingback_error" style="display:none" class="error">Could not connect to the <a href="http://atcontent.com" target=_blank>AtContent.com</a> server. Please, contact your hosting provider to solve this issue.</div>
    <script>
        (function ($) {
            $(function () {
                $.post("admin-ajax.php", {
                    "action": "atcontent_pingback"
                }, function (d) {
                    if (d.IsOK == false) {
                        $("#ac_pingback_error").show();
                    }
                }, "json");
            });
        })(jQuery);
    </script>
<?php
    //End PingBack
    update_user_meta( $userid, "ac_last_repost_visit", date( "Y-m-d H:i:s" ) );
    $currenttag = "";
    if ( isset( $_GET["tag"] ) ) {
        $currenttag = $_GET["tag"];
    }
    $currentpage = "1";
    if ( isset( $_GET["pageNum"] ) ) {
        $currentpage = $_GET["pageNum"];
    }
    $pageAnswer = atcontent_api_feed( $ac_api_key, $currenttag, $currentpage );
    if ( $pageAnswer["IsOK"] != true ) {
        wp_die( "Something gets wrong" . var_dump( $pageAnswer ) );
    }    
    $atcontent_reposts = $pageAnswer["Page"]["EntityList"];
    $atcontent_second_reposts = $pageAnswer["SecondPage"]["EntityList"];
    $atcontent_highlighted = $pageAnswer["Highlighted"];
?>
<script>
    ac_ga_s('repostTab', 'view');
    ac_ga_s('repostTab', 'tag ' + '<?php echo $currenttag; ?>');
    ac_ga_s('repostTab', 'page ' + '<?php echo $currentpage; ?>');
    var ac_highlighted = <?php echo json_encode( $atcontent_highlighted ); ?>;
    var ac_highlighted_cached = <?php echo json_encode( $atcontent_highlighted ); ?>;
    var ac_posts_page = <?php echo json_encode($atcontent_second_reposts); ?>;
</script>

    <div class="b-ac-mobile-menu">
        <div class="b-ac-mobile-menu__tags-toggle" id="ac-tags-toggle"></div>
        <div class="b-ac-mobile-menu__nav">
            <a href="admin.php?page=atcontent_reposts&tag=feed&" class="b-ac-mobile-menu__link<?php if ( $currenttag == "feed" ) echo " b-ac-mobile-menu__link_current"; ?>">My Feed</a>
            <a href="admin.php?page=atcontent_reposts" class="b-ac-mobile-menu__link<?php if ( $currenttag != "feed" ) echo " b-ac-mobile-menu__link_current"; ?>">AtContent Featued</a>
        </div>
    </div>
    
    <div class="l-ac-grid">

        <div class="l-ac-grid__col-left">
            <ul class="b-ac-main-menu">
                <li class="b-ac-main-menu__item<?php if ( $currenttag == "feed" ) echo " b-ac-main-menu__item_current"; ?>">
                    <a href="admin.php?page=atcontent_reposts&tag=feed&">My Feed</a>
                </li>
                <li class="b-ac-main-menu__item<?php if ( $currenttag != "feed" ) echo " b-ac-main-menu__item_current"; ?>">
                    <a href="admin.php?page=atcontent_reposts">AtContent Featured</a>
                </li>
            </ul>
            
            <?php if ( $currenttag != "feed" ) { ?>
            <p style="line-height: 12px">
                <a href="http://atcontent.com/subscribe/?wp=1" target="_blank" class="button button-nav button-large">Feature My Posts</a>
            </p>
            <br/>
            <nav class="b-tags-list b-tags-list_aside">
                <?php foreach ( $pageAnswer["Tags"] as $tagId => $tagValue ) { ?>
                    <a class="b-tag<?php if ( $currenttag == $tagId ) echo " b-tag_current"; if ( in_array( $tagId, $pageAnswer["UserTags"] )) echo " b-tag_my"; ?>" href="admin.php?page=atcontent_reposts&tag=<?php echo $tagId ?>"><?php echo $tagValue ?></a>
                <?php } ?>
            </nav>
            <?php } else { ?>
            <?php if ( count ( $atcontent_reposts ) > 0 ) { ?>
            <p>
                <a href="http://atcontent.com/following-wp/" class="button button-nav button-large" target="_blank" id="follow_bloggers_button_2">Follow Bloggers</a>
            </p>
            <?php } ?>
            <?php } ?>
            

            

        </div>

        <div class="l-ac-grid__col-right">
            <div style="text-align: right; padding: 10px;">
                <form action="http://atcontent.com/search" method="get" target="_blank">
                    <input type="text" name="q" placeholder="Find posts..."> <input type="submit" value="Search" class="button button-primary">
                </form>
            </div>

            <?php if ( count( $atcontent_reposts ) > 0 ) { ?>
            <h3>Posts below can be published on your blog. Click "Repost" to try it.</h3>
            <?php } ?>
<?php
    $rpst_hint = atcontent_get_user_settings_value( $userid, "rpst_hint" );
    if ( $rpst_hint == 0 ) {
?>
             <div class="b-alert b-alert_hidable b-alert_info" id="rpstValue">
                <figure style="float: left; margin: 0 .8em 0 0">
                    <img src="http://i.imgur.com/VaBiTn4.png" alt="" width="330" style="display: block">
                </figure>
                <p>By reposting some content on your blog you can almost double your page views and social sharing simply because you have more content on your blog.</p>
                <p>Reposts look super-native to your blog. You and your readers will love it!</p>
                <p>Simply click <span class="ac-rpst-b" style="margin: 0;"><span class="ac-rpst-b__b"><span class="ac-rpst-b__l"></span><span class="ac-rpst-b__t">Repost</span></span></span> under any post you like. You can always edit repost title, tags, etc. in your WordPress editor.</p>
                <a href="#" class="b-btn b-btn_plain b-alert__hide" id="rpstHide">&times;</a>
            </div>
<?php    
    }
?>
            
            <div class="postList b-publications-columns">
                <?php 
                    if ( count( $atcontent_highlighted ) > 1 ) {
                        for ( $i = 0; $i < 2; $i++ ) {
                            ?>
                            <div class="article-inline article-highlighted" id="ac-highlighted-<?php echo $i; ?>" data-options="hide_shares" data-num="<?php echo $i ?>">
                                <div class="b-post-highlight">
                                    Hightlighted post
                                    <div class="b-post-highlight__refresh" data-num="<?php echo $i; ?>">&times;</div>
                                </div>
                                <div id="ac-highlighted-post-<?php echo $i; ?>"></div>
                            </div>
                            <?php
                        }
                    } 
                    foreach ( $atcontent_reposts as $post ) { 
                    $postid = $post["PostIdString"];
                ?>
                    <div class="article-inline" data-options="hide_shares" >
    <script src="https://w.atcontent.com/CPlase/<?php echo $postid; ?>/Title/h3"></script>
    <script data-ac-src="https://w.atcontent.com/CPlase/<?php echo $postid; ?>/Face"></script>

                    </div>
                <?php } ?>
            </div>

            <style>
                .CPlase_fixedWidget .CPlase_face .CPlase_fixedWidget_firstImg {
                    height: 0 !important;
                    margin: 0 -1em 1em !important;
                    padding-top: 56.25%;
                    
                    background: 0 33% / cover no-repeat;
                }
                .article-inline {
                    width: 45%;
                    margin-right: 5%;
                    padding: 1em;
                    -moz-box-sizing: border-box;
                    -webkit-box-sizing: border-box;
                    box-sizing: border-box;
                
                    background: white;
                    box-shadow: 0 0 4px rgba(0,0,0,.2);
                    
                    letter-spacing: normal;
                    word-spacing: normal;
                }
                .postList {
                    letter-spacing: -.31em;
                    word-spacing: -.43em;
                }
                .CPlase_fixedWidget {
                    max-width: none;
                    margin: 0 !important;
                    overflow: visible !important;
                }
                .CPlase_panel { display: none; }
            </style>
    <script>
        (function ($) {
            $(function () {
                var $acMobileButton = $('#ac-tags-toggle'),
                    $acPage = $('#ac-page'),
                    $rpstHide = $('#rpstHide'),
                    CLASS_NAME_ASIDE_OPEN = 'b-ac-page_aside-open';
                    
                $acMobileButton.on('click', function () {
                    $acPage.toggleClass(CLASS_NAME_ASIDE_OPEN);
                    $(window).scrollTop(0);
                });
                
                $rpstHide.on('click', function(e) {
                    e.preventDefault();
                    $('#rpstValue').hide();
                    $.post('admin-ajax.php', {
                        'action': 'atcontent_settings_val',
                        'id': 'rpst_hint',
                        'val': '1'
                    }, function(d) {
                        
                    }, "json");
                });
                
                $('.article-highlighted').each(function(){
                    ac_highlighted_show($(this).attr('data-num'));
                });
                
                $('.b-post-highlight__refresh').on('click', function(e) {
                   e.preventDefault();
                   var hNum = $(this).attr('data-num'),
                       postId = $(this).attr('data-id');
                   $('#ac-highlighted-post-' + hNum).html('');
                   ac_highlighted_show(hNum);
                   ac_highlighted_hide(postId);
                });
            });
            
            window.ac_highlighted_hide = function(postId) {
                $.post('admin-ajax.php', {
                       'action': 'atcontent_highlighted_hide',
                       'postId': postId,
                   }, function(d){
                       
                   }, 'json');
            };
            
            window.ac_highlighted_show = function(i) {
                var postNum, postId, $ac_highlighted = $('#ac-highlighted-' + i);
                if (ac_highlighted.length == 0) {
                    postNum = parseInt(Math.floor(Math.random() * ac_posts_page.length));
                    postId = ac_posts_page.splice(postNum, 1)[0].PostIdString;
                    $ac_highlighted.find('.b-post-highlight').remove();
                } else {
                    postNum = parseInt(Math.floor(Math.random() * ac_highlighted.length));
                    postId = ac_highlighted.splice(postNum, 1)[0];
                }
                
                
                $ac_highlighted.find('.b-post-highlight__refresh')
                        .attr('data-id', postId);
                var sc1 = document.createElement("SCRIPT");
                sc1.setAttribute('data-ac-src', 'http://w.atcontent.com/CPlase/' + postId + '/Title/h3');
                var sc2 = document.createElement("SCRIPT");
                sc2.setAttribute('src', 'http://w.atcontent.com/CPlase/' + postId + '/Face');
                var container = document.getElementById('ac-highlighted-post-' + i);
                container.appendChild(sc1);
                container.appendChild(sc2);
                try {
                    CPlase.text.init();
                } catch (e) { }
            };

            CPlase = window.CPlase || {};
            CPlase.evt = CPlase.evt || [];
            CPlase.evt.push(function (event, p, w) {
                var titleTag = CPlase.t[p][w],
                    title = CPlase.id(CPlase.CPID(p, w, 'title')).getElementsByTagName(titleTag)[0],
                    
                    panel = CPlase.id(CPlase.CPID(p, w, 'panel')),
                    linkWrapper, repostWrapper, repostButton, repostsCounter;
                    
                title.innerHTML = CPlase.tmpl('<a href="http://p.atcontent.com/{p}/" target="_blank">{html}</a>', { p: p, html: title.innerHTML });

                if (!CPlase.getByClass(panel.parentNode, 'CPlase_publicationLink').length) {
                    linkWrapper = CPlase.elem('div', {
                        className: 'CPlase_publicationLink'
                    });
                    repostWrapper = CPlase.elem('div', {
                        className: 'ac-rpst-b',
                        id: 'acRepostBtn' + p
                    });
                    repostButton = CPlase.elem('div', {
                        html: '<div class="ac-rpst-b__l" id="acRepostBtn' + p + '"></div><div class="ac-rpst-b__t" id="acRepostBtnCaption' + p + '">Repost</div>',
                        className: 'ac-rpst-b__b'
                    });
                    
                    CPlase.events.add(repostButton, 'click', function () {
                        repost_post(p);
                    });
                    
                    repostWrapper.appendChild(repostButton);
                    linkWrapper.appendChild(repostWrapper);
                    
                    panel.parentNode.insertBefore(linkWrapper, panel);
                }
                    
                var featuredImgContainer = CPlase.getByClass(CPlase.id(CPlase.CPID(p, w, 'face')), 'CPlase_fixedWidget_firstImg')[0],
                    featuredImg = featuredImgContainer ? featuredImgContainer.getElementsByTagName('img')[0] : null;
                    
                    if (featuredImg) {    
                        featuredImgContainer.style.backgroundImage = 'url(' + featuredImg.src + ')';
                        featuredImgContainer.innerHTML = '';
                    }
            });
		
            window.repost_post = function(p) {
                var btn = document.getElementById('acRepostBtn' + p);
                btn.href = "javascript:";
                var btnCaption = document.getElementById('acRepostBtnCaption' + p);
                btnCaption.innerHTML = "Reposting...";
                ac_ga_s('repostTab', 'doRepost');
                if (ac_highlighted_cached.indexOf(p) != -1) {
                    ac_highlighted_hide(p);
                }
                $.ajax({url: '<?php echo $ajax_form_action; ?>',
                    type: 'post',
                    data: {
                        action: 'atcontent_repost',
                        ac_post: p
                    },
                    dataType: "json",
                    success: function(d) {
                        if (d.IsOK) {
                            $(btn).parent().html('<div class="b-note success">Great! Story reposted! You are awesome!</div>');
                        }
                    },
                    error: function(d, s, e) {
                        btnCaption.innerHTML = "Repost";
                        btn.href = "javascript:repost_post('" + p + "');";
                        $(btn).addClass("b_orange").removeClass("b_white");
                    }
                });
            }
        })(jQuery);
    </script>
            <?php if ( count( $atcontent_reposts ) == 0 ) { ?>

    <div class="b-ac-acc">
        <div class="b-ac-acc__pane b-ac-acc__pane_open">
            <div class="b-ac-acc__pane-content">
                <div class="b-ac-settings-section">
                    <br/><br/><br/>
                    <div class="b-ac-following">
                        <p>
                            <a href="http://atcontent.com/following-wp/" class="button button-nav button-hero" target="_blank" id="follow_bloggers_button">Discover and Follow Bloggers</a>
                        </p>
                        <p>
                            Discover and follow bloggers by relevant tags to create your AtContent Feed for reposting.
                        </p>
                        <p>
                            Bloggers will follow you back and repost your content, growing your audience and traffic!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

            <?php } 
            if ( $currentpage > 1 ) { 
                $prevPageUrl = admin_url( "admin.php?page=atcontent_reposts&tag=" . $currenttag . "&pageNum=" . ( intval( $currentpage ) - 1 ) );
            ?>
                <a href="<?php echo $prevPageUrl; ?>" class="likebutton b_green">&larr; Previous page</a>
            <?php }
            if ( $pageAnswer["Page"]["HasNext"] == true ) { 
                $nextPageUrl = admin_url( "admin.php?page=atcontent_reposts&tag=" . $currenttag . "&pageNum=" . ( intval( $currentpage ) + 1 ) );
            ?>
                <a href="<?php echo $nextPageUrl; ?>" class="likebutton b_green">Next page &rarr;</a>
            <?php } else if ( $currenttag == "feed" && count( $atcontent_reposts ) > 0 ) { ?>
                <p>Here are displayed only latest posts from bloggers you follow. Click author's name or avatar to see the full list.</p>
            <?php } ?>
            
        </div>

    </div>
<?php } ?>
</div>
