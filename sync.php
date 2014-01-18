<script type="text/javascript">
window.qbaka || (function(a,c){a.__qbaka_eh=a.onerror;a.__qbaka_reports=[];a.onerror=function(){a.__qbaka_reports.push(arguments);if(a.__qbaka_eh)try{a.__qbaka_eh.apply(a,arguments)}catch(b){}};a.onerror.qbaka=1;a.qbaka={report:function(){a.__qbaka_reports.push([arguments, new Error()]);},customParams:{},set:function(a,b){qbaka.customParams[a]=b},exec:function(a){try{a()}catch(b){qbaka.reportException(b)}},reportException:function(){}};var b=c.createElement("script"),e=c.getElementsByTagName("script")[0],d=function(){e.parentNode.insertBefore(b,e)};b.type="text/javascript";b.async=!0;b.src="//cdn.qbaka.net/reporting.js";"[object Opera]"==a.opera?c.addEventListener("DOMContentLoaded",d):d();qbaka.key="d8b59ec1eecd7dd2c01096ec6a21e80c"})(window,document);qbaka.options={autoStacktrace:1,trackEvents:1};
</script>
<div class="atcontent_wrap">
<?php
    $currentuser = wp_get_current_user();
    $userinfo = get_userdata($currentuser -> ID);
    $email = $userinfo -> user_email;
    $site = $_SERVER['HTTP_HOST'];

    $atcontent_menu_section = "sync";
    
    require( "include/atcontent_userinit.php" );

    $img_url = plugins_url( 'assets/logo.png', __FILE__ );
    $hidden_field_name = 'ac_submit_hidden';
    $form_action = admin_url( 'admin-ajax.php' );
    $form_message = '';

    if ( strlen( $ac_api_key ) == 0 ) {
        $connect_url = admin_url( "admin.php?page=atcontent/connect.php" );
        ?>
<script>window.location = '<?php echo $connect_url; ?>';</script>
        <?php
    }

    include( "settings_menu.php" );

    // PingBack
    if ( ! atcontent_pingback_inline() ) {
        echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
    }
    //End PingBack

    $posts_id = array();
    $posts_title = array();

    $posts = $wpdb->get_results( 
	    "
	    SELECT ID, post_title, post_author
	    FROM {$wpdb->posts}
	    WHERE post_status = 'publish' 
		    AND post_author = {$userid} AND post_type = 'post'
	    "
    );

    wp_cache_flush();

    foreach ( $posts as $post ) 
    {
        if ($post->post_author == $userid) {
            array_push( $posts_id, $post->ID );
            array_push( $posts_title, $post->post_title );
        }
        wp_cache_flush();
    }

   

    $ac_copyprotect = get_user_meta( $userid, "ac_copyprotect", true );
    if ( strlen( $ac_copyprotect ) == 0 ) $ac_copyprotect = "1";

    $ac_paidrepost = get_user_meta( $userid, "ac_paidrepost", true );
    if ( strlen($ac_paidrepost) == 0 ) $ac_paidrepost = "0";

    $ac_paidrepostcost = get_user_meta($userid, "ac_paidrepostcost", true );
    if (strlen($ac_paidrepostcost) == 0) $ac_paidrepostcost = "2.50";

    $ac_is_import_comments = get_user_meta($userid, "ac_is_import_comments", true );
    if (strlen($ac_is_import_comments) == 0) $ac_is_import_comments = "1";
  
    $postIDs = join( "','" , $posts_id );
    $postTitles = join( "','" , $posts_title );
?>
<script src="/wp-content/plugins/atcontent/interface.js" type="text/javascript"></script>
<script>
    var email = '<?php echo $email?>';    
    var site = '<?php echo $site?>';
    function gaSend(category, action)
    {
        window.CPlase_ga = window.CPlase_ga || [];
                        CPlase_ga.push({
                            category: category + ' <?php echo AC_VERSION?>',
                            action: action,
                            label: site + '      ' + email
                        });
    }
    gaSend('sync', 'sync opened');

    (function ($) {
        var postIDs = ['<?php echo $postIDs; ?>'],
            postTitles = <?php echo json_encode($posts_title); ?>,
            postInfo = [],
            imported = 0;
        
        for (var i in postIDs) {
            if ( postIDs[i].length > 0 ) postInfo[i] = {id: postIDs[i], title: postTitles[i], status: "queued", retry: 0};
        }

        function getStatus() {
            var r = {created:0, updated:0, skipped:0, error:0, queued:0, active:0};
            for (var i in postInfo) {
                if (isNaN(i)) continue;
                if (postInfo[i].status == "created") r.created++;
                else if (postInfo[i].status == "updated") r.updated++; 
                else if (postInfo[i].status == "skipped") r.skipped++; 
                else if (postInfo[i].status == "error") r.error++; 
                else if (postInfo[i].status == "active") r.active++; 
                else r.queued++; 
            }
            return r;
        }
        function doRetry() {
            $("#sync-status").html("Processing");
            for (var i in postInfo) {
                if (postInfo[i].status == "error") { 
                    postInfo[i].status = "queued";
                }
            }
        }
        function doImport(i) {
            postInfo[i].status = "active";
            postInfo[i].started = Date.now();
            $.ajax({url: '<?php echo $form_action; ?>', 
                             type: 'post', 
                             data: {action: 'atcontent_import', 
                                    postID: postIDs[i], 
                                    copyProtection: <?php echo $ac_copyprotect; ?>, 
                                    paidRepost: <?php echo $ac_paidrepost; ?>, 
                                    cost: <?php echo $ac_paidrepostcost; ?>, 
                                    comments: <?php echo $ac_is_import_comments; ?>},
                             dataType: "json",
                             success: function(d){
                                    postInfo[i] = postInfo[i] || {};
                                    postInfo[i].ended = Date.now();
                                    if (d.IsOK) {
                                        postInfo[i].title = postTitles[i];
                                        postInfo[i].status = d.AC_action;
                                        s = getStatus();
                                        imported = s.created + s.updated + s.skipped;
                                        $("#sync-counter").html(imported + " of " + postInfo.length);
                                    } else {
                                        postInfo[i].status = "error";
                                        postInfo[i].error =  "Connection problem occured for \"" + postTitles[i] + "\". Post not synced (" + d.Info + ")";
                                        if (postInfo[i].retry < 3) {
                                            postInfo[i].retry++;
                                            postInfo[i].status = "queued";
                                        }
                                    }
                                    var cumtime = 0;
                                    var processed = 0;
                                    for (var j = 0; j < postInfo.length; j++) {
                                        if (!isNaN(postInfo[j].ended)) {
                                            processed += 1;
                                            cumtime += (postInfo[j].ended - postInfo[j].started);
                                        }
                                    }
                                    if (processed > 0) {
                                        var avgtime = cumtime / processed;
                                        avgtime = avgtime / 60000;
                                        avgtime = avgtime / 2;
                                        var esttime = Math.round( avgtime * (postInfo.length - processed) );
                                        if (esttime > 0) {
                                            $("#sync-estimated-val").html(esttime + " min.");
                                        } else {
                                            $("#sync-estimated-val").html("<1 min.");
                                        }
                                        $("#sync-estimated").show();
                                    }
                                    s = getStatus();
                                    if (s.queued == 0 && s.active == 0) doResult();
                                },
                             error: function(d, s, e) {
                                    postInfo[i] = postInfo[i] || {};
                                    if (e == 'timeout') { postInfo[i].status = "queued"; return; }
                                    var err = "Connection problem occured";
                                    if (e.length > 0) err += ": " + e;                                
                                    postInfo[i].status = "error";
                                    postInfo[i].error = err + " for \"" + postTitles[i] + "\". Post not synced.";
                                    if (postInfo[i].retry < 3) {
                                       postInfo[i].retry++;
                                       postInfo[i].status = "queued";
                                    }
                                    s = getStatus();
                                    if (s.queued == 0 && s.active == 0) doResult();
                                 },
                             });
        }
        function doResult(){
            var s = getStatus(),
                backlinks = s.created + s.updated;
            if (s.created > 0) {
                $("#sync-created-val").html(s.created);
                $("#sync-created").show();
            } else {
                $("#sync-created").hide();
            }
            if (s.updated > 0) {
                $("#sync-updated-val").html(s.updated);
                $("#sync-updated").show();
            } else {
                $("#sync-updated").hide();
            }
            if (s.skipped > 0) {
                $("#sync-skipped-val").html(s.skipped);
                $("#sync-skipped").show();
            } else {
                $("#sync-skipped").hide();
            }
            if (s.error > 0) {
                $("#sync-error-val").html(s.error);
                $("#sync-error").show();
            } else {
                $("#sync-error").hide();
            }
            $("#sync-backlinks-val").html(backlinks);
            $("#sync-backlinks").show();
            if (postInfo.length > 0) $("#sync-details").show();
            $("#sync-status").html("Completed");
            $("#sync-button")[0].disabled = false;
            $("#sync-note").hide();
            $("#sync-estimated").hide();
            $("#sync-whatisnext").show();
        }
        function getDetails(){
             var h = "";         
             for (var i in postInfo) {
                 if (isNaN(i)) continue;
                 h += "\"" + postInfo[i].title + "\" ";
                 if (postInfo[i].status == "created") h += "created";
                 if (postInfo[i].status == "updated") h += "updated";
                 if (postInfo[i].status == "skipped") h += "skipped";
                 if (postInfo[i].status == "error") h += "not synced: " + postInfo[i].error;
                 h += "<br>";
             }
             if (getStatus().error > 0) {
                 h += "<a id=\"retry-link\" href=\"javascript:\">Retry sync for not synced posts</a>";
                 setTimeout(function(){
                     $("#retry-link").on("click", function(){
                        doRetry(); 
                     });
                 }, 200);
             }
             $("#importDetails").html(h);
        }
        function startImport() {
            $("#sync-button")[0].disabled = false;
            $("#sync-process").show();
            setInterval(processQueue, 100);
        }
        function processQueue(){
            s = getStatus();
            if (s.queued == 0 && s.active == 0) doResult();
            if (s.active > 2) return;
            for (var i = postInfo.length - 1; i >= 0; i--) {
                if (postInfo[i].status == "queued") {  
                    doImport(i);
                    return;
                }
            }
        }
        
        $(function() {
            $('#sync-button').on('click', function (e) {
                gaSend('sync', 'sync clicked');
                startImport();
            });
            $("#link-details").on('click', function(e) {
                e.preventDefault();
                getDetails();
            } );
            $('#sync-queue-button').on('click', function(e){
                e.preventDefault();
                $.ajax({url: '<?php echo $form_action; ?>', 
                        type: 'post', 
                        data: {action: 'atcontent_syncqueue'},
                        dataType: "json",
                        success: function(d){
                        },
                        error: function(d, s, e) {
                        }
                });
            });
        });
    })(jQuery);
    
</script>
<?php ?>
<div class="b-column-single">
<?php 
if ( $_GET["afterconnect"] == "1" ) {
    ?>
<div class="b-note success">
Great! Settings applied. The final step is to synchronize your blog!
</div>
    <?php
}
?>
    <p style="text-align: center; margin: 40px 0;">
        <button type="button" class="button-color-orange button-size-large" id="sync-button">Sync me!</button>
    </p>
    <p>
        Synchronization gets your posts signed with your name
        <a href="https://atcontent.com/Profile/<?php echo $ac_pen_name; ?>" target="_blank"><img style="vertical-align: text-top; margin-right: .3em" src="<?php echo $ac_avatar_20; ?>" alt=""><?php echo $ac_show_name; ?></a> and
        presents your blog to the AtContent audience. Additionally, it backups your posts
        in the AtContent cloud and provides you one backlink per each synchronized post.
    </p>
    <div class="b-dashboard-table b-dashboard-table-status" id="sync-process">
        <table>
            <tr><th>Status</th><td id="sync-status">Processing</td></tr>
            <tr><th>Synced</th><td id="sync-counter">0 of <?php echo count( $posts_id ); ?></td></tr>
            <tr id="sync-estimated" style="display: none;"><th>Estimated</th><td id="sync-estimated-val"></td></tr>
            <tr id="sync-created" style="display: none;"><th>Created</th><td id="sync-created-val"></td></tr>
            <tr id="sync-updated" style="display: none;"><th>Updated</th><td id="sync-updated-val"></td></tr>
            <tr id="sync-skipped" style="display: none;"><th>Skipped</th><td id="sync-skipped-val"></td></tr>
            <tr id="sync-error" style="display: none;"><th>Not synced</th><td id="sync-error-val"></td></tr>
            <tr id="sync-backlinks" style="display: none;"><th>Backlinks</th><td id="sync-backlinks-val"></td></tr>
            <tr id="sync-details" style="display: none;"><th></th><td><a href="#" id="link-details">Get details</a></td></tr>
        </table>
        <p id="sync-note">Note: Updating a post takes few seconds, please be patient</p>
        <div id="sync-whatisnext" style="display: none;">
        <h4>What's next?</h4>
        <p>
            — Check your blog traffic next 7 days and see how it grows.<br>
<?php

$email_subject = $_SERVER['HTTP_HOST'] . " would like to be featured";

$email_body = "Hey AtContent team, \n" .
	"I would like to submit my posts from " . $_SERVER['HTTP_HOST'] . " to be on the Featured page.\n\n\n\n" .
	"%% You also can share your feedback right here - so, we'll be able to improve AtContent for you\n\n".
	"Thanks,\n".
	$_SERVER['HTTP_HOST'];

?>
            — <a href="mailto:mail@atcontent.com?subject=<?php
	echo str_replace('+', '%20', urlencode($email_subject)); ?>&body=<?php
	echo str_replace('+', '%20', urlencode($email_body)); ?>">Submit your posts to be featured</a> and increase your readership.<br>
            — <a href="<?php echo admin_url("admin.php?page=atcontent/repost.php"); ?>">Repost relevant posts to your blog</a> to increase readers engagement.
        </p>
 
        <p>Thank you for using AContent!</p>
        <p>In 7 days we'll share with you insights on getting better results in your blog promotion.</p>

        <p>If you have any issues or questions, please <a href="http://atcontent.com/support" target="_blank">contact our support</a>.</p>
        </div>
    </div>

    <div id="importDetails"></div>
</div>

    <button type="button" class="button-color-orange button-size-large" id="sync-queue-button">Sync me!</button>
</div>
<?php atcontent_ga("SyncTab", "Sync page"); ?>