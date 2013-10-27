<?php
    /*
    Plugin Name: AtContent
    Plugin URI: http://atcontent.com/
    Description: Why 3,500 Sites Have Chosen AtContent? Because it’s the easiest way to Reach new readership & Increase search ranking!
    Version: 5.0.2
    Author: AtContent, IFFace, Inc.
    Author URI: http://atcontent.com/
    */

    define( 'AC_VERSION', "5.0.2.99" );
    define( 'AC_NO_PROCESS_EXCERPT_DEFAULT', "1" );
    define( 'AC_NO_COMMENTS_DEFAULT', "1" );

    require_once( "include/atcontent_api.php" );
    require_once( "include/atcontent_pingback.php" );
    require_once( "include/atcontent_ajax.php" );
    require_once( "include/atcontent_dashboard.php" );
    require_once( "include/atcontent_lists.php" );
    require_once( "include/atcontent_post.php" );
    require_once( "include/atcontent_shortcodes.php" );
    require_once( "include/atcontent_content_processing.php" );
    require_once( "include/atcontent_editor.php" );
    add_action( 'admin_init', 'atcontent_admin_init' );
    add_action( 'admin_menu', 'atcontent_add_tools_menu' );
    add_filter( 'the_content', 'atcontent_the_content', 1 );
    add_filter( 'the_content', 'atcontent_the_content_after', 100);
    add_filter( 'the_excerpt', 'atcontent_the_content_after', 100);
    add_filter( 'the_excerpt', 'atcontent_the_excerpt', 1 );
    add_action( 'save_post', 'atcontent_save_post' );
    add_action( 'publish_post', 'atcontent_publish_publication' );
    add_action( 'comment_post', 'atcontent_comment_post' );
    add_action( 'deleted_comment', 'atcontent_comment_post' );
    add_action( 'trashed_comment', 'atcontent_comment_post' );
    add_action( 'add_meta_boxes', 'atcontent_add_meta_boxes' );
    add_action( 'wp_ajax_atcontent_import', 'atcontent_import_handler' );
    add_action( 'wp_ajax_atcontent_api_key', 'atcontent_api_key' );
    add_action( 'wp_ajax_atcontent_pingback', 'atcontent_pingback' );
    add_action( 'wp_ajax_atcontent_readership', 'atcontent_readership' );
    add_action( 'wp_ajax_nopriv_atcontent_guestpost', 'atcontent_ajax_guestpost' );
    add_action( 'wp_ajax_atcontent_guestpost', 'atcontent_ajax_guestpost' );
    add_action( 'wp_ajax_atcontent_guestpost_check_url', 'atcontent_ajax_guestpost_check_url' );
    add_action( 'admin_head', 'atcontent_admin_head' );
    add_filter( 'manage_posts_columns', 'atcontent_column_head' );
    add_action( 'manage_posts_custom_column', 'atcontent_column_content', 10, 2 );
    add_action( 'wp_dashboard_setup', 'atcontent_add_dashboard_widgets' );


    register_activation_hook( __FILE__, 'atcontent_activate' );
    register_deactivation_hook( __FILE__, 'atcontent_deactivate' );
    register_uninstall_hook( __FILE__, 'atcontent_uninstall' );

    function atcontent_admin_init(){
         wp_register_style( 'atcontentAdminStylesheet', plugins_url( 'assets/atcontent.css?v=4', __FILE__ ) );
         wp_enqueue_style( 'atcontentAdminStylesheet' );
         wp_enqueue_style( 'wp-pointer' );
         wp_enqueue_script( 'wp-pointer' );
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
        add_menu_page( 'AtContent', 'AtContent', 'publish_posts', 'atcontent/dashboard.php', '',
            plugins_url( 'assets/logo.png', __FILE__ ), $atcontent_dashboard_key );

        add_submenu_page( 'atcontent/dashboard.php', 'Connect', 'Connect', 'publish_posts', 'atcontent/connect.php',  '');
        add_submenu_page( 'atcontent/dashboard.php', 'Subscription', 'Subscription', 'publish_posts', 'atcontent/subscription.php',  '');
        add_submenu_page( 'atcontent/dashboard.php', 'Settings', 'Settings', 'publish_posts', 'atcontent/settings.php',  '');
        add_submenu_page( 'atcontent/dashboard.php', 'Sync', 'Sync', 'publish_posts', 'atcontent/sync.php',  '');
        add_submenu_page( 'atcontent/dashboard.php', 'Geek Page', 'Geek Page', 'publish_posts', 'atcontent/knownissues.php',  '');

        $guest_key = atcontent_get_menu_key( 5.0 );
        add_menu_page( 'Guest Posts', 'Guest Posts', 'publish_posts', 'atcontent/guestpost.php', '', 
            plugins_url( 'assets/logo.png', __FILE__ ), $guest_key );
        add_submenu_page( 'atcontent/settings.php', 'Guest Posts', 'Guest Posts', 'publish_posts', 'atcontent/guestpost.php',  '');

        $repost_key = atcontent_get_menu_key( 5.0 );
        add_menu_page( 'Reposting', 'Reposting', 'publish_posts', 'atcontent/repost.php', '', 
            plugins_url( 'assets/logo.png', __FILE__ ), $repost_key );
        
        add_action( 'admin_print_styles', 'atcontent_admin_styles' );
        add_action( 'admin_print_footer_scripts', 'atcontent_footer_scripts' );
    }

    function atcontent_admin_styles(){
        wp_enqueue_style( 'atcontentAdminStylesheet' );
    }

    function atcontent_comment_post( $comment_id, $status = 1 ) {
        $comment = get_comment( $comment_id );
        if ( $comment != NULL ) {
            atcontent_process_comments( $comment->comment_post_ID );
        }
    }

    function atcontent_process_comments( $post_id ) {
        $post = get_post( $post_id );
        if ($post == null) return;
        $ac_api_key = get_user_meta(intval($post->post_author), "ac_api_key", true);
        if (strlen($ac_api_key) > 0) {
            $ac_postid = get_post_meta($post->ID, "ac_postid", true);
            $ac_is_process = get_post_meta($post->ID, "ac_is_process", true);
            $ac_is_import_comments = get_post_meta($post->ID, "ac_is_import_comments", true);
            if ($ac_is_process == "1" && $ac_is_import_comments == "1") {
                $comments_json = "";
                $comments = get_comments( array(
                    'post_id' => $post->ID,
                    'order' => 'ASC',
                    'orderby' => 'comment_date_gmt',
                    'status' => 'approve',
                ) );
                if(!empty($comments)){
                    $comments_json .= json_encode($comments);
                }

                atcontent_api_update_publication_comments( $ac_api_key, $ac_postid, $comments_json );
            }
        }
    }

    function atcontent_get_statistics_link($postID) {
        $ac_postid = get_post_meta( $postID, "ac_postid", true );
        if ( strlen( $ac_postid ) > 0 ) {
            return admin_url( 'admin.php?page=atcontent/statistics.php' ) . "&postid=" . $ac_postid;
        }
        return "";
    }


    function atcontent_column_head($defaults) {
	    $defaults['atcontent_column'] = 'AtContent';
	    return $defaults;
    }


    function atcontent_column_content( $column_name, $post_ID ) {
	    if ( $column_name == 'atcontent_column' ) {
		    $stat_link = atcontent_get_statistics_link( $post_ID );
		    if ( strlen( $stat_link) > 0 ) {
			    echo "<a href=\"{$stat_link}\">Statistics</a>";
		    } else {
		        echo "N/A";
		    }
	    }
    }

    function atcontent_footer_scripts() {
        $userid = wp_get_current_user()->ID;
        $ac_api_key = get_user_meta($userid, "ac_api_key", true );
?>
<script type="text/javascript">
function ACsetCookie (name, value, expires, path, domain, secure) {
      document.cookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "") +
        ((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}
function ACgetCookie(name) {
	var results = document.cookie.match ( '(^|;) ?' + name + '=([^;]*)(;|$)' );
 
  if ( results )
    return ( unescape ( results[2] ) );
  else
    return null;
}
</script>
<?php
        if ( strlen( $ac_api_key ) == 0 ) {
            $connect_url = admin_url( "admin.php?page=atcontent/settings.php" );
            $img_url = plugins_url( 'assets/logo.png', __FILE__ );
            $pointer_content = '<h3>Connect to AtContent</h3>';
            $pointer_content .= '<p><img style="vertical-align:bottom;" src="' . $img_url .
                '" alt=""> To activate AtContent features, please, <a href="' . $connect_url . '">connect</a> your blog to AtContent</p>';
?>
<script type="text/javascript">
jQuery(document).ready( function($) {
    if (window.location.href.indexOf("billbelew.com") != -1) return;
    if (ACgetCookie("ac-connect-dismiss") != "1") {
        $('#toplevel_page_atcontent-settings').pointer({
            content: '<?php echo $pointer_content; ?>',
            position: 'top',
            close: function() {
                ACsetCookie("ac-connect-dismiss", "1", null, "/");
            }
        }).pointer('open');
    }
});
</script>
<?php
        } else {
            $connect_url = admin_url( "admin.php?page=atcontent/settings.php" );
            $ac_country = get_user_meta($userid, "ac_country", true );
            if ( strlen( $ac_country ) == 0 ) {
                $pointer_content = '<h3>Better Interaction with AtContent</h3>';
                $pointer_content .= '<p>Please, <a href="' . $connect_url . '">select location</a> of your blog</p>';
?>
<script type="text/javascript">
jQuery(document).ready( function($) {
    if (window.location.href.indexOf("billbelew.com") != -1) return;
    if (ACgetCookie("ac-location-dismiss") != "1") {
        jQuery('#toplevel_page_atcontent-settings').pointer({
            content: '<?php echo $pointer_content; ?>',
            position: 'top',
            close: function() {
                ACsetCookie("ac-location-dismiss", "1", null, "/");
            }
        }).pointer('open');
    }
   });
</script>
<?php
            }
        }
    }

?>