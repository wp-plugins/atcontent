<?php
    wp_register_script( 'atcontentAdminHints',  plugins_url( 'assets/hints.js?v=1', __FILE__ ), array(), true );
    wp_enqueue_script( 'atcontentAdminHints' );
    wp_register_script( 'atcontentAdminMarketplace',  plugins_url( 'assets/marketplace.js?v=1', __FILE__ ), array(), true );
    wp_enqueue_script( 'atcontentAdminMarketplace' );
    wp_register_script( 'atcontentAdminTmpl',  plugins_url( 'assets/tmpl.js?v=1', __FILE__ ), array(), true );
    wp_enqueue_script( 'atcontentAdminTmpl' );
    wp_register_script( 'atcontentAdminCustomSelect',  plugins_url( 'assets/customSelect.js?v=1', __FILE__ ), array(), true );    
    wp_enqueue_script( 'atcontentAdminCustomSelect' );
    wp_register_style( 'atcontentAdminIcons',  plugins_url( 'assets/icons.css?v=1', __FILE__ ), array(), true );
    wp_enqueue_style( 'atcontentAdminIcons' );
    require_once( "include/atcontent_userinit.php" );
    if (ac_isjsonly()) {
?>
<script>
    window.location = 'http://atcontent.com/marketplace/';
</script>
<?php
    } else {
        $ac_marketplace = atcontent_api_marketplace( $ac_api_key );
        $connect_url = admin_url( "admin.php?page=atcontent&marketplace=1" );
        $ajax_form_action = admin_url( 'admin-ajax.php' );
        include( 'include/atcontent_analytics.php' );
        if ( isset( $ac_marketplace["IsOK"] ) && $ac_marketplace["IsOK"] == TRUE ) {
?>
<div class="atcontent_wrap">
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
    <div class="wrap">
        <div class="icon32" id="icon-link"><br></div><h2>NativeAd Sponsored Posts</h2>
    </div>
    <br/>
    <br/>
    <div class="contentColumns">
        <div class="asideCol">
            <section class="b-hints b-hints_no-border b-hints_aside" id="tips">
            <div class="b-hints__todo font-light">
                <ul class="b-hints__todo-list" id="todoList">
                    <li class="b-hints__todo-item b-hints__todo-item_undone" id="hintTodo_join">
                        <span class="b-hints__todo-icon icon-ok"></span>
                        <span class="b-hints__todo-icon icon-right-arrow"></span>
                        <span class="b-hints__todo-item-title" data-step="join">Connect AtContent</span>
                    </li>
                    <li class="b-hints__todo-item b-hints__todo-item_undone" id="hintTodo_tags">
                        <span class="b-hints__todo-icon icon-ok"></span>
                        <span class="b-hints__todo-icon icon-right-arrow"></span>
                        <span class="b-hints__todo-item-title" data-step="tags">Set profile tags</span>
                    </li>
                    <li class="b-hints__todo-item b-hints__todo-item_undone" id="hintTodo_country">
                        <span class="b-hints__todo-icon icon-ok"></span>
                        <span class="b-hints__todo-icon icon-right-arrow"></span>
                        <span class="b-hints__todo-item-title" data-step="country">Set country</span>
                    </li>
                    <li class="b-hints__todo-item b-hints__todo-item_undone" id="hintTodo_earnings">
                        <span class="b-hints__todo-icon icon-ok"></span>
                        <span class="b-hints__todo-icon icon-right-arrow"></span>
                        <span class="b-hints__todo-item-title" data-step="earnings">Start earning!</span>
                    </li>
                </ul>
            </div>
            <div class="b-hints__list" id="hintsList">
                <div class="b-hints__item centerAlgn" id="hint_join">
                        <h3>Welcome to AtContent NativeAd!</h3>
                        <p>
                            Here you can get sponsored stories and post them on your blog.<br>
                            <?php if ( strlen( $ac_marketplace["Nickname"] ) == 0 ) { ?>
                            To start please <a href="<?php echo $connect_url; ?>">connect plugin</a> to AtContent.
                            <?php } ?>
                        </p>
                        <p>It’s only 3 simple steps to go!</p>
                        <?php if ( strlen( $ac_marketplace["Nickname"] ) == 0 ) { ?>
                        <p style="margin-top: 1em">
						<a href="<?php echo $connect_url; ?>" class="likebutton b_big b_green">Connect</a>
						</p>
                        <?php } ?>
                </div>
                <div class="b-hints__item" id="hint_tags">
                    <h3>Choose tags for your profile</h3>
                    <p>
                        These tags define your blog topic and help us provide relevant content for you.
                        Please choose 1 to 3 tags.
                    </p>
                    <form action="./" method="post" id="tagsHint">
                        <select name="tags" multiple data-editable="true" data-max="3">
<?php 
                            foreach( $ac_marketplace["Tags"] as $key => $value ) {
                                $selected = ( $key == $ac_marketplace["UserTag1"] || $key == $ac_marketplace["UserTag2"] || $key == $ac_marketplace["UserTag3"] ) ? "selected" : "";
                                echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
                            }
?>
                        </select>
                        <br>
                        <button type="submit" class="button-color-green" id="tagsSubmit">Set tags</button>
                        <div class="fieldsetNote" id="tagsSaved" style="display: none">Saved!</div>
                    </form>
                </div>
                <div class="b-hints__item" id="hint_country">
                    <h3>Well done! Now, where are you from?</h3>
                    <p>Please specify your country. This will help us show geographically relevant posts.</p>
                    <form action="./" method="post" class="" id="countryHint">
                        <select name="country">
                            <option value="0">[Not selected]</option>
<?php 
                            foreach( $ac_marketplace["CountryList"] as $key => $value ) {
                                $selected = ( $key == $ac_marketplace["Country"] ) ? "selected" : "";
                                echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
                            }
?>
                        </select>
                        <br>
                        <button type="submit" class="button-color-green" id="countrySubmit">Set country</button>
                        <div class="fieldsetNote" id="countrySaved" style="display: none">Saved!</div>
                    </form>
                </div>
                <div class="b-hints__item" id="hint_earnings">
                    <h4>How to use it?</h4>
                    <p>Click “Repost” button under any article you like.</p>
                    <div class="toolsInfo">Please be sure you follow <a href="http://atcontent.com/nativead-rules/" target="_blank">the rules of AtContent NativeAd</a>.</div>
                </div>
                <div class="b-hints__list-arrow" id="hintsArrow"></div>
            </div>
        </section>
        </div>
<script>
    var costPerMille = {};
    ac_ga_s('getpaidTab', 'view');
</script>
        <div class="mainCol">
        <div class="postList b-publications-columns blocked" id="postsList">
            <?php foreach ( $ac_marketplace["Campaigns"] as $post ) { ?>
                <div class="article-inline" data-options="hide_shares hide_author hide_date" >
<script src="https://w.atcontent.com/CPlase/<?php echo $post["PostId"]; ?>/Title/h2"></script>
<script data-ac-src="https://w.atcontent.com/CPlase/<?php echo $post["PostId"]; ?>/Face"></script>
<script>
    costPerMille['<?php echo $post["PostId"]; ?>'] = '<?php echo $post["CostPerMille"]; ?>';
</script>
                </div>
            <?php } ?>
        </div>

<script>
    var ac_allow_repost = false;
    (function ($) {
        $(function () {
            CPlase = window.CPlase || {};
            CPlase.evt = CPlase.evt || [];
            CPlase.evt.push(function (event, p, w) {
                var o = $(document.getElementById('CPlase_' + p + '_' + w + '_panel'));
                if (!o.prev('.CPlase_publicationLink').size()) {
					o.before('<div class="CPlase_publicationLink"><div class="ac-rpst-b"><a id="acRepostBtn' + p + '" class="ac-rpst-b__b" href="javascript:repost_post(\'' + p + 
                    '\');"><div class="ac-rpst-b__l"></div><div id="acRepostBtnCaption' + p + '" class="ac-rpst-b__t">Repost</div></a></div>' + 
                    ' Earn <big class="b-green-text">$' + costPerMille[p] + '</big> per 1,000 views</div>');
				}
            });
        });

        function connect_error(p) {
          var btn = document.getElementById('acRepostBtn' + p);
          $(btn).parent().html('<div class="update-nag">Please complete all steps</div>');
        }
		
        window.repost_post = function(p) {
            if (ac_allow_repost)
            {
                var btn = document.getElementById('acRepostBtn' + p);
                btn.href = "javascript:";
                var btnCaption = document.getElementById('acRepostBtnCaption' + p);
                btnCaption.innerHTML = "Reposting...";
                $.ajax({url: '<?php echo $ajax_form_action; ?>',
                    type: 'post',
                    data: {
                        action: 'atcontent_repost',
                        ac_post: p
                    },
                    dataType: "json",
                    success: function(d) {
                        if (d.IsOK) {
                            $(btn).parent().parent().html('<div class="b-note success">Great! Story reposted! You are awesome!</div>');
                        }
                    },
                    error: function(d, s, e) {
                        btnCaption.innerHTML = "Repost";
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
        </div>
    </div>
</div>
<input type="hidden" id="_nickname" value="<?php echo $ac_marketplace["Nickname"]; ?>">
<script type="text/template" id="tmpl_repost">
    <div class="CPlase_publicationLink">
        <div class="ac-rpst-b">
            <div class="ac-rpst-b__b">
                <div class="ac-rpst-b__l"></div><div class="ac-rpst-b__t">Repost</div>
            </div>
        </div>
        Earn <big class="b-green-text">$<%=cost%></big> per 1,000 views
    </div>
</script>
<script type="text/template" id="tmpl_congrats">
    <h3>How to publish sponsored stories on your blog</h3>
    <ol class="p-usageTips">
        <li>Click “Repost” button under any article you like.</li>
    </ol>
    <div class="toolsInfo">Please be sure you follow <a href="http://atcontent.com/nativead-rules/" target="_blank">the rules of AtContent NativeAd</a>.</div>
    <p class="centerAlgn">
        <button type="button" id="startBtn" class="button-color-orange button-size-big">Start earning!</button>
    </p>
</script>
<?php    } else { ?>
<p>Something went wrong. Please, reload page.</p>
<?php    }
    }?>
<script>
    var ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
</script>