<?php
    /*
    Plugin Name: AtContent
    Plugin URI: http://atcontent.com/
    Description: Dramatically increase audience and drive more traffic to your blog by connecting with relevant bloggers. It’s free to join!
    Version: 7.9.6
    Author: AtContent, IFFace, Inc.
    Author URI: http://atcontent.com/
    */

    define( 'AC_VERSION', "7.9.6" );
    define( 'AC_NO_PROCESS_EXCERPT_DEFAULT', "1" );
    define( 'AC_NO_COMMENTS_DEFAULT', "1" );

    require_once( "include/atcontent_service.php" );
    require_once( "include/atcontent_api.php" );
    require_once( "include/atcontent_pingback.php" );
    require_once( "include/atcontent_ajax.php" );
    if ( is_admin() ) {
        require_once( "include/atcontent_dashboard.php" );
        require_once( "include/atcontent_lists.php" );
    }
    require_once( "include/atcontent_post.php" );    
    add_action( 'save_post', 'atcontent_save_post' );
    add_action( 'publish_post', 'atcontent_publish_publication' );
    require_once( "include/atcontent_shortcodes.php" );
    require_once( "include/atcontent_content_processing.php" );
    if ( is_admin() ) {
        require_once( "include/atcontent_editor.php" );
        require_once( "include/atcontent_coexistense.php" );
        add_action( 'admin_init', 'atcontent_admin_init' );
        add_action( 'admin_menu', 'atcontent_add_tools_menu' );
        add_action( 'add_meta_boxes', 'atcontent_add_meta_boxes' );
        add_action( 'admin_head', 'atcontent_admin_head' );
        add_action( 'wp_dashboard_setup', 'atcontent_add_dashboard_widgets' );
        add_action( 'wp_ajax_atcontent_syncqueue', 'atcontent_ajax_syncqueue' );
        add_action( 'wp_ajax_atcontent_readership', 'atcontent_readership' );
        add_action( 'wp_ajax_atcontent_api_key', 'atcontent_api_key' );
        add_action( 'wp_ajax_atcontent_pingback', 'atcontent_pingback' );
        add_action( 'wp_ajax_atcontent_repost', 'atcontent_ajax_repost' );
        add_action( 'wp_ajax_atcontent_get_sync_stat', 'atcontent_ajax_get_sync_stat' );
        add_action( 'wp_ajax_atcontent_save_credentials', 'atcontent_save_credentials' );
        add_action( 'wp_ajax_atcontent_connect_blog', 'atcontent_connect_blog' );
        add_action( 'wp_ajax_atcontent_save_tags', 'atcontent_save_tags' );
        add_action( 'wp_ajax_atcontent_save_country', 'atcontent_save_country' );
        add_action( 'wp_ajax_atcontent_disconnect', 'atcontent_disconnect' );
        add_action( 'wp_ajax_atcontent_save_settings', 'atcontent_save_settings' );
        add_action( 'wp_ajax_atcontent_connect', 'atcontent_connect' );
        add_action( 'wp_ajax_atcontent_send_invites', 'atcontent_send_invites' );
        add_action( 'wp_ajax_atcontent_feed_count', 'atcontent_ajax_feed_count' );
        add_action( 'wp_ajax_atcontent_settings_tab', 'atcontent_ajax_settings_tab' );
        add_filter( 'manage_edit-post_columns', 'atcontent_promote_posts_column' );
        add_action( 'manage_posts_custom_column', 'atcontent_promote_posts_row' );
    }
    add_filter( 'the_content', 'atcontent_the_content', 1 );
    add_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
    add_action( 'wp_ajax_nopriv_atcontent_gate', 'atcontent_ajax_gate' );
    add_action( 'wp_ajax_atcontent_gate', 'atcontent_ajax_gate' );
    
    register_activation_hook( __FILE__, 'atcontent_activate' );
    register_deactivation_hook( __FILE__, 'atcontent_deactivate' );
    register_uninstall_hook( __FILE__, 'atcontent_uninstall' );
    
    function atcontent_promote_posts_column( $columns ) {
        $date = $columns['date'];
        $columns['acpromoting'] = 'AtContent NativeAd';
        unset( $columns['date']);
        $columns['date'] = $date;
        return $columns;
    }

    function atcontent_promote_posts_row ( $colname ){
        if ( $colname == 'acpromoting' ) {
            global $post;
            $ac_postid = get_post_meta( $post -> ID, "ac_postid", true ); 
            if ( strlen( $ac_postid ) == 0 ) {
                $ac_postid = get_post_meta( $post -> ID, "ac_repost_postid", true ); 
            }           
            if ( strlen( $ac_postid ) > 0 ) {
?>
<a class="button-primary ac-button-promote" target="_blank" href="https://atcontent.com/campaigns/create/<?php echo($ac_postid)?>/">
    <span class="ac-logo-promote"></span>
    Promote
</a>
<?php
            }
        }
    }

    function atcontent_admin_init(){
        wp_register_style( 'atcontentAdminStylesheet', plugins_url( 'assets/atcontent.css?v=0b', __FILE__ ) );
        wp_enqueue_style( 'atcontentAdminStylesheet' );
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        global $wp_version;
        if ( version_compare ( $wp_version, "3.8" ) >= 0 ) {
            wp_register_style( 'atcontentAdminStylesheet38', plugins_url( 'assets/atcontent38.css?v=8', __FILE__ ) );
            wp_enqueue_style( 'atcontentAdminStylesheet38' );
            wp_register_script( 'atcontentAdminScript38',  plugins_url( 'assets/atcontent38.js?v=1', __FILE__ ), array(), true );
            wp_enqueue_script( 'atcontentAdminScript38' );
        }
        if ( !get_option('atcontent_inited') )
	    {
		    update_option('atcontent_inited', 'true');
		    wp_redirect( admin_url( 'admin.php?page=atcontent/dashboard.php' ) );
            exit;
	    }
    }

    function atcontent_get_menu_key( $desired ) {
        global $menu;
        $menukey = $desired;
        while ( array_key_exists((string) $menukey,$menu) ) {
          $menukey += 0.0000000001;
        }
        $menukey = (string) $menukey;  //If it's not a string it gets rounded to an int!
        return $menukey;
    }

    function atcontent_add_tools_menu() {
        $atcontent_dashboard_key = atcontent_get_menu_key( 2.0 );
        $unread_settings_tab_count = atcontent_get_settings_unread_count( intval( wp_get_current_user()->ID ) );
        add_menu_page( 'AtContent', 'AtContent' . 
            '<span class="update-plugins count-' . $unread_settings_tab_count . '"><span class="plugin-count">' . $unread_settings_tab_count . '</span></span>', 'edit_posts', 'atcontent/dashboard.php', '',
            plugins_url( 'assets/logo.png', __FILE__ ), $atcontent_dashboard_key );
        $repost_title = "Content Feed";
        $repost_key = atcontent_get_menu_key( 5.0 );
        add_menu_page( $repost_title, $repost_title, 'publish_posts', 'atcontent/repost.php', '', 
            plugins_url( 'assets/logo.png', __FILE__ ), $repost_key );
        $getpaid_key = atcontent_get_menu_key( 5.0 );
        add_menu_page( 'Monetize Blog', 'Monetize Blog<span class="ac-dollar" title="Monetize your blog"><span class="ac-dollar__val">$</span></span>', 'publish_posts', 'atcontent/getpaid.php', '', 
            plugins_url( 'assets/logo.png', __FILE__ ), $getpaid_key );
        add_action( 'admin_print_styles', 'atcontent_admin_styles' );
        add_action( 'admin_print_footer_scripts', 'atcontent_footer_scripts' );
    }

    function atcontent_admin_styles(){
        wp_enqueue_style( 'atcontentAdminStylesheet' );
    }

    function atcontent_admin_head() {
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        $ac_syncid = get_user_meta($userid, "ac_syncid", true );

        $connect_url = admin_url( "admin.php?page=atcontent/dashboard.php" );
        $img_url = plugins_url( 'assets/logo.png', dirname( __FILE__ ) );
        if ( ( strlen( $ac_api_key ) == 0 || strlen( $ac_syncid ) == 0 ) && user_can( $userid, "edit_posts" ) ) {
        ?>
<script type="text/javascript">
$j = jQuery;
$j().ready(function(){
	$j('.wrap > h2').parent().prev().after('<div class="update-nag"><table><tr><td><a class="button button-primary ac-connect-button" href="<?php echo $connect_url; ?>">Connect your account to AtContent</a></td><td>Almost done — connect your account to start growing your audience, increase traffic on your blog and monetize it with sponsored posts!</td></tr></table></div>');
});
</script>
<?php
        }
    }

    function atcontent_footer_scripts() {
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        $ac_syncid = get_user_meta( $userid, "ac_syncid", true );
?>
<script type="text/javascript">
    function ACsetCookie(name, value, expires, path, domain, secure) {
        document.cookie = name + "=" + escape(value) +
            ((expires) ? "; expires=" + expires : "") +
            ((path) ? "; path=" + path : "") +
            ((domain) ? "; domain=" + domain : "") +
            ((secure) ? "; secure" : "");
    }
    function ACgetCookie(name) {
        var results = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        if (results)
            return (unescape(results[2]));
        else
            return null;
    }

    (function( $ ) {
        $(function() {
            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                action: 'atcontent_feed_count'
            }, function(r){
                if (r && r.IsOK) {
                    var cnt = r.Count > 99 ? '99+' : r.Count;
                    $('#toplevel_page_atcontent-repost .wp-menu-name').append('<span class="update-plugins count-' + r.Count + '"><span class="plugin-count">' + cnt + '</span></span>');
                }
            }, 'json');
        });
    })(jQuery);
</script>
<?php
    }

?>