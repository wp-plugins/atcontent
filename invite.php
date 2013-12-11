<?php 
    $ajax_action = admin_url( 'admin-ajax.php' );
    $currentuser = wp_get_current_user();
    $form_action = admin_url( 'admin.php?page=atcontent/connect.php' );
?>
<form id="connect_form" method="post" action="<?php echo $form_action; ?>">
    <input type="hidden" name="atcontent_invite" value="Y">
<div class="atcontent_invite">
    <div class="invite_left">
        <h1>Congrats!<br>You have activated AtContent plugin!</h1>
        <p style="color: #7D7D7D; font-size: 1.3em; line-height: normal;">The connection requires an AtContent account which is used <br>to  
promote your posts on other sites.<br>
On average, bloggers increase their audinece by 146% <br>in 3 months of using AtContent. </p>
        <!--AtContent is the easiest way to increase readership and SEO, get backlinks and copy-paste protection!-->
    </div>
    <div class="invite_right">
        <p><?php echo get_avatar( $currentuser->ID, 16 ) . " " . $currentuser->display_name; ?> will be connected with AtContent.</p>
<?php 
    $users = get_users("orderby=ID");
    $additionalUsersCount = 0;
    foreach ( $users as $user ) {
        if ( $user->ID != $currentuser->ID && user_can( $user->ID, "edit_posts" ) ) $additionalUsersCount += 1;
    }
    if ( $additionalUsersCount > 1 && user_can( $currentuser->ID, "manage_options" ) ) {
?>
        <p class="small_text">As an administrator of this blog you can also connect following authors with your AtContent account:</p>
        <div class="checkbox_group" id="usersList">
<?php
        foreach ( $users as $user ) {
            if ( $user->ID != $currentuser->ID && user_can( $user->ID, "edit_posts" ) ) {
                echo "<label><input type=\"checkbox\" name=\"connectuser[]\" value=\"{$user->ID}\"> " . get_avatar( $user->ID, 16 ) . " <span class=\"checkbox_group_text\">" . $user->display_name . "</span></label>";
            }
        }
?>
        </div>
<?php
        if ( $additionalUsersCount > 1 ) {
?>
        <p class="small_text checkbox_manage">
            Select:
            <a href="#" class="dashed" id="selectAll">All</a>
            <a href="#" class="dashed" id="selectNone">None</a>
        </p>
<?php
        }
    }
?>
    <iframe id="ac_connect" src="http://atcontent.com/Auth/WordPressConnect/?ping_back=<?php echo $ajax_action ?>" style="width:302px;height:50px;" frameborder="0" scrolling="no"></iframe>
        <div id="ac_connect_result"></div>
<script type="text/javascript">
    (function ($) {
        window.ac_connect_res = function (d) {
            if (d) document.getElementById("connect_form").submit();
            else $("#ac_connect_result").html( 
                    'Something is wrong. <a href="javascript:window.location.reload();">Reload page</a> and try again, please.');
        }
        var $usersList = $('#usersList').find('input[type=checkbox]');
        $('#selectAll').on('click', function (e) {
            e.preventDefault();
            $usersList.each(function () {
                this.checked = true;
            });
        });
        $('#selectNone').on('click', function (e) {
            e.preventDefault();
            $usersList.each(function () {
                this.checked = false;
            });
        });
    })(jQuery);
</script>
    </div>
</div>
</form>
<div class="atcontent_invite footer">
    <hr>
        
    <div class="discl">
        
    </div>
    <div class="addit">
        1,250,000 posts processed by AtContent to date.
    </div>
</div>