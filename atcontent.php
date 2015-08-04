<?php
    /*
    Plugin Name: AtContent
    Plugin URI: http://atcontent.com/
    Description: Dramatically increase audience and drive more traffic to your blog by connecting with relevant bloggers. It’s free to join!
    Version: 7.12.9.78
    Author: AtContent, IFFace, Inc.
    Author URI: http://atcontent.com/
    */

    define( 'AC_VERSION', '7.12' );
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
        add_action( 'wp_ajax_atcontent_settings_val', 'atcontent_ajax_settings_val' );
        add_action( 'wp_ajax_atcontent_highlighted_hide', 'atcontent_ajax_highlighted_hide' );
        add_action( 'wp_ajax_atcontent_invitefollowup', 'atcontent_ajax_invitefollowup' );
        add_action( 'wp_ajax_atcontent_blogactivate', 'atcontent_ajax_blogactivate' );
        add_action( 'wp_ajax_atcontent_set_envato_purchase', 'atcontent_ajax_set_envato_purchase' );
        add_action( 'wp_ajax_atcontent_renewinfo', 'atcontent_ajax_renewinfo' );
        add_action( 'wp_ajax_atcontent_wipe', 'atcontent_ajax_wipe' );
        add_filter( 'manage_edit-post_columns', 'atcontent_promote_posts_column' );
        add_action( 'manage_posts_custom_column', 'atcontent_promote_posts_row' );
    }
    add_filter( 'wp_default_editor', 'atcontent_disable_rich_editor', 1);
    add_filter( 'the_content', 'atcontent_the_content', 1 );
    add_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
    add_action( 'wp_ajax_nopriv_atcontent_gate', 'atcontent_ajax_gate' );
    add_action( 'wp_ajax_atcontent_gate', 'atcontent_ajax_gate' );
    
    register_activation_hook( __FILE__, 'atcontent_activate' );
    register_deactivation_hook( __FILE__, 'atcontent_deactivate' );
    register_uninstall_hook( __FILE__, 'atcontent_uninstall' );
    
    function atcontent_promote_posts_column( $columns ) {
        $date = $columns['date'];
        $columns['acpromoting'] = 'AtContent';
        unset( $columns['date']);
        $columns['date'] = $date;
        return $columns;
    }

    function atcontent_disable_rich_editor($r) {
        global $post;
        if ($post != NULL ) {
            if ( preg_match_all( '/<script[^<]+src="(https?:\/\/w\.atcontent\.com\/[^\"]+)\"/', $post->post_content, $matches ) ) {
                return 'html';
            }
        }        
        return $r;
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
        wp_register_style( 'atcontentAdminStylesheet', plugins_url( 'assets/atcontent.css?v=0h', __FILE__ ) );
        wp_enqueue_style( 'atcontentAdminStylesheet' );
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        if ( get_option( 'atcontent_inited' ) != 'true' )
        {
            update_option( 'atcontent_inited', 'true' );
            wp_redirect( admin_url( 'admin.php?page=atcontent' ) );
            exit;
        }
        $ac_blog_api_key = get_option( 'ac_blog_api_key' );
        if ( strlen( $ac_blog_api_key ) == 0 ) {
            $ac_blog_api_key = md5( time() . mt_rand() );
            update_option( 'ac_blog_api_key', $ac_blog_api_key );
            update_option( 'ac_main_userid', wp_get_current_user()->ID );
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
        global $wp_version;
        $atcontent_dashboard_key = atcontent_get_menu_key( 2.0 );
        $ac_logo = plugins_url( 'assets/logo.png', __FILE__ );
        if ( version_compare ( $wp_version, "3.8" ) >= 0 ) {
            $ac_logo = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiPg0KICAgIDxnIHRyYW5zZm9ybT0ibWF0cml4KDEuMjUsMCwwLC0xLjI1LC00My40NzYwODQsMTU4LjcxNTg2KSI+DQogICAgICAgIDxnIHRyYW5zZm9ybT0ibWF0cml4KDEuMDgyMDc1NCwwLDAsMS4wODIwNzU0LDc0Ljc3ODcwMiw0Ni45NzI2OTMpIj4NCiAgICAgICAgICAgIDxwYXRoIGZpbGw9IiMxMzY2OWQiIGQ9Im0wLDBjOS4xODYsMCwxNy41ODYsMy4zNTEsMjQuMDUxLDguODk1bC0xLjUyNSwzLjMwMS0xLjc1NiwzLjgwOC0xOC4yMjIsMzkuNDg1aC0yLjc0Ni0yLjQwMi0wLjA3OWwtMTUuMzI0LTMzLjIwNi0yLjg1Ni02LjE4OWMtNS4zNDQsNS4zNDEtOC42NSwxMi43MTktOC42NSwyMC44NzIsMCwxNi4zLDEzLjIwOSwyOS41MTEsMjkuNTA5LDI5LjUxMSwyLjk1NSwwLDUuODAzLTAuNDM3LDguNDk2LTEuMjQ0bDIuNzg3LDYuOTQyYy0zLjU1NSwxLjEzOC03LjM0NywxLjc1Ny0xMS4yODMsMS43NTctMjAuNDE3LDAtMzYuOTY0LTE2LjU0OC0zNi45NjQtMzYuOTY2LDAtMjAuNDE3LDE2LjU0Ny0zNi45NjYsMzYuOTY0LTM2Ljk2Nm0wLDIwLjMzM2MzLjUzNiwwLDYuODE1LDEuMTAzLDkuNTExLDIuOTgzbDAuMTkxLTAuNDE1LDAuMDE0LDAuMDExLDAuMjM0LTAuNTA2LDIuMzEzLTUuMTg1LDIuNTg1LTUuNzYyYy00LjM2Mi0yLjU0NC05LjQzNC00LjAwMi0xNC44NDgtNC4wMDItNS40ODEsMC0xMC42MTEsMS40OTYtMTUuMDA3LDQuMDk3bDQuNTUsOS44NjIsMC44OTUsMS45MzhjMi43MDUtMS45MDMsNi4wMDItMy4wMjEsOS41NjItMy4wMjFtNi40MTQsOS43M2MtMS42OC0xLjU2NC0zLjkzNS0yLjUyMS02LjQxNC0yLjUyMS0yLjQ5NiwwLTQuNzY1LDAuOTctNi40NTEsMi41NTRsMC4xOCwwLjM4OSwwLjEzNywwLjI5OSw2LjA2OSwxNC4wNjgsMi4zNjMtNS4yOTYsMy43NjktOC43NCwwLjM0Ny0wLjc1M3oiLz4NCiAgICAgICAgPC9nPg0KICAgIDwvZz4NCjwvc3ZnPg0KDQo=';
        }
        $repost_title = "Content Feed";
        add_submenu_page( 'atcontent', $repost_title, $repost_title, 'publish_posts', 'atcontent_reposts', 'atcontent_reposts_callback' );
        add_submenu_page( 'atcontent', 'AtContent Settings', 'Settings', 'edit_posts', 'atcontent', 'atcontent_settings_callback' );
        add_menu_page( 'AtContent', 'AtContent', 'edit_posts', 'atcontent', 'atcontent_settings_callback', $ac_logo, $atcontent_dashboard_key );
        //add_submenu_page( 'atcontent', 'Monetize Blog', 'Monetize Blog<span class="ac-dollar" title="Monetize your blog"><span class="ac-dollar__val">$</span></span>', 'publish_posts', 'atcontent_monetize', 'atcontent_monetize_callback' );
        atcontent_fix_menu( 'atcontent_reposts', 'atcontent', 'atcontent_reposts_callback' );
                
        add_action( 'admin_print_styles', 'atcontent_admin_styles' );
        add_action( 'admin_print_footer_scripts', 'atcontent_footer_scripts' );
    }
    
    function atcontent_fix_menu( $slug, $parentslug, $function ){
        global $_registered_pages;
        $hookname = get_plugin_page_hookname( $slug, $parentslug );
        if (!empty ( $hookname ))
            add_action( $hookname, $function );
        $_registered_pages[$hookname] = true;
    }
    
    function atcontent_plugin_callback(){
        include_once( 'dashboard.php' );
    }
    
    function atcontent_settings_callback(){
        include_once( 'dashboard.php' );
    }
    
    function atcontent_reposts_callback(){
        include_once( 'repost.php' );
    }
    
    function atcontent_monetize_callback(){
        include_once( 'getpaid.php' );
    }
    

    function atcontent_admin_styles(){
        wp_enqueue_style( 'atcontentAdminStylesheet' );
    }

    function atcontent_admin_head() {
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
        $ac_syncid = get_user_meta( $userid, "ac_syncid", true );

        $connect_url = admin_url( "admin.php?page=atcontent" );
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
                    $('#toplevel_page_atcontent .wp-menu-name').append('<span class="update-plugins count-' + r.Count + '"><span class="plugin-count">' + cnt + '</span></span>');
                }
            }, 'json');
        });
    })(jQuery);
</script>
<?php
    }

?>