<?php
    function atcontent_update_user_meta( $userid, $ac_userinfo ) {
        update_user_meta( $userid, "ac_pen_name", $ac_userinfo["Nickname"] );
        update_user_meta( $userid, "ac_showname", $ac_userinfo["Showname"] );
        update_user_meta( $userid, "ac_avatar_20", $ac_userinfo["Avatar20"] );
        update_user_meta( $userid, "ac_avatar_80", $ac_userinfo["Avatar80"] );
        update_user_meta( $userid, "ac_avatar_200", $ac_userinfo["Avatar200"] );
    }
?>