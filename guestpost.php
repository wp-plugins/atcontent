<?php
    $userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta($userid, "ac_api_key", true );
    

    function stripslashes_array($array)
    {
        return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
    }

    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);
    $_COOKIE = stripslashes_array($_COOKIE);


?>

<div class="atcontent_wrap">
    <div class="wrap">
        <div class="icon32" id="icon-link"><br></div><h2>AtContent&nbsp;Guest&nbsp;Posts <?php 
                if ( strlen ( $ac_api_key ) > 0 ) { ?><a class="add-new-h2" href="<?php echo admin_url("admin.php?page=atcontent/guestpost.php&postid=new")?>">Add New</a><?php } ?> </h2>
    </div>
    <br>

    <?php
        if ( ! atcontent_pingback_inline() ) {
             echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
        }
        
        if ( strlen( $ac_api_key ) == 0 ) {
            include("invite.php");
        } else {

            $ac_is_pro = atcontent_api_is_pro( $ac_api_key );

             if ( $ac_is_pro["IsOK"] == true && $ac_is_pro["IsPro"] == true ) {
                $ac_pro_end_date = date("F d, Y", strtotime( $ac_is_pro["Ended"] ) );
                echo <<<END
<script type="text/javascript">
$$j = jQuery;
$$j(function(){
	$$j('#wpbody-content').prepend('<div class="update-nag">Congradulations!!! You can use AtContent Pro Account features for free till {$ac_pro_end_date}. <a href="https://atcontent.com/Blog/41Za4W4VL0s.text">Read more</a></div>');
});
</script>
END;
             }

            $guestpostid = $_GET["postid"];
            if ( strlen( $guestpostid ) == 0 ) {
                $incoming_request = atcontent_api_guestposts_incoming( site_url(), $ac_api_key );
                if ( $incoming_request["IsOK"] == true && count($incoming_request["List"]) > 0 ) {
    ?>

    <h3>Incoming</h3>

    <table cellspacing="0" class="wp-list-table widefat fixed posts">
	<thead>
	<tr>
		<th style="" class="manage-column" scope="col">Title</th>
        <th style="" class="manage-column" scope="col">From</th>
        <th style="" class="manage-column" scope="col">Status</th>
    </tr>
	</thead>

	<tfoot>
	<tr>
		<th style="" class="manage-column" scope="col">Title</th>
        <th style="" class="manage-column" scope="col">From</th>
        <th style="" class="manage-column" scope="col">Status</th>
	</tfoot>

	<tbody id="the-list">
        <?php foreach ( $incoming_request["List"] as $gp_item ) { 
                if ( $gp_item["Status"] == "Created" || $gp_item["Status"] == "Declined" ) continue;
     ?>
				<tr valign="top" class="post-<?php echo $gp_item["Id"]; ?> type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self" id="post-<?php echo $gp_item["Id"]; ?>">
			<td class="post-title page-title column-title"><strong><a title="Preview “<?php echo $gp_item["Title"]; ?>”" href="<?php echo site_url( "?ac_guest_post=" . $gp_item["Id"] ); ?>" class="row-title"><?php echo $gp_item["Title"]; ?></a></strong>

<div class="row-actions">
    <span class="edit"><a title="Preview this item" href="<?php echo site_url( "?ac_guest_post=" . $gp_item["Id"] ); ?>">Preview</a></span>
    <?php if ( $gp_item["Status"] == "Accepted" ) { ?>
    | <span class="stat"><a title="Statistics" href="<?php echo admin_url( 'admin.php?page=atcontent/statistics.php' ) . "&postid=" . $gp_item["Post4gId"]; ?>">Statistics</a></span>
    <?php } ?>
</div>

						<td ><a href="<?php echo $gp_item["SourceUri"]; ?>"><?php echo $gp_item["SourceUri"]; ?></a></td>
                        <td ><?php echo $gp_item["Status"];?></td>
					</tr>
        <?php } ?>
		</tbody>
</table>

    <?php                } 
                
                    $outgoing_request = atcontent_api_guestposts_outgoing( $ac_api_key );
                    if ( $outgoing_request["IsOK"] == true && count($outgoing_request["List"]) > 0 ) {
                ?>

    <h3>Outgoing</h3>

    <table cellspacing="0" class="wp-list-table widefat fixed posts">
	<thead>
	<tr>
		<th class="manage-column" scope="col">Title</th>
        <th class="manage-column" scope="col">To</th>
        <th class="manage-column" scope="col">Status</th>
    </tr>
	</thead>

	<tfoot>
	<tr>
		<th class="manage-column" scope="col">Title</th>
        <th class="manage-column" scope="col">To</th>
        <th class="manage-column" scope="col">Status</th>
	</tfoot>

	<tbody id="the-list">
                <?php foreach ( $outgoing_request["List"] as $gp_item )  { 
                    $gp_item["edit_url"] = admin_url( "admin.php?page=atcontent/guestpost.php&postid=" . $gp_item["Id"] );
                    $gp_item["submit_url"] = admin_url( "admin.php?page=atcontent/guestpost.php&postid=" . $gp_item["Id"] . "&action=submit" );
                ?>
				<tr valign="top" class="post-<?php echo $gp_item["Id"]; ?> type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self" id="post-<?php echo $gp_item["Id"]; ?>">
			<td class="post-title page-title column-title"><strong><a title="Edit “<?php echo $gp_item["Title"]; ?>”" href="<?php echo $gp_item["edit_url"]; ?>" class="row-title"><?php echo $gp_item["Title"]; ?></a></strong>

<div class="row-actions">
    <span class="edit"><a title="Edit this item" href="<?php echo $gp_item["edit_url"]; ?>">Edit</a></span>
    <?php if ( $gp_item["Status"] != "Accepted" && strlen( $gp_item["TargetUri"] ) > 0 ) { ?>
    | <span class="submit"><a title="Submit this item" href="<?php echo $gp_item["submit_url"]; ?>">Submit</a></span>
    <?php }?>
    <?php if ( $gp_item["Status"] == "Accepted" ) { ?>
    | <span class="stat"><a title="Statistics" href="<?php echo admin_url( 'admin.php?page=atcontent/statistics.php' ) . "&postid=" . $gp_item["Post4gId"]; ?>">Statistics</a></span>
    <?php }?>
</div>
					<td ><a href="<?php echo $gp_item["TargetUri"];?>"><?php echo $gp_item["TargetUri"];?></a></td>
                    <td ><?php echo $gp_item["Status"];?></td>
					</tr>
                <?php   }   ?>
		</tbody>
</table>

    <?php                        }

                    } else { //Create, view and edit guest post

                        $editor_title = '';
                        $gp_content = '';
                        $gp_title = '';
                        $gp_targeturi = '';
                        $action = $_GET["action"];
                        if ( $guestpostid == "new" ) {
                            $editor_title = "Create new guest post";
                            if ( $action == "save" ) {
                                $draft_status = "Created";
                                if ( $_POST["targetaction"] == "save" ) $draft_status = "Created";
                                if ( $_POST["targetaction"] == "submit" ) $draft_status = "Submitted";
                                $create_result = atcontent_api_guestposts_create( $ac_api_key, site_url(), $_POST["targeturi"], $_POST["title"], $_POST["post_content"], $draft_status );
                                if ( $create_result["IsOK"] != true ) {
                                     if ($create_result["ErrorCode"] == 102) {
                                        echo "<div class=\"error\">" . 'Could not save draft to atcontent.com. ' . 
                                            'To submit guest post you should have Pro account.<br>' .
                                            '<a href="https://atcontent.com/Subscribe">Upgrade for Pro account here</a>' .
                                            "</div>";
                                    } else {
                                        echo "<div class=\"error\">" . 'Could not save draft to atcontent.com. ' . $create_result["Reason"] .  "</div>";
                                    }
                                } else {
                                    die(  '<h2>Post was saved!</h2>' . '<script type="text/javascript">window.location="'. admin_url("admin.php?page=atcontent/guestpost.php") . '";</script>' );
                                }
                                $gp_title = $_POST["title"];
                                $gp_content = $_POST["post_content"];
                                $gp_targeturi = $_POST["targeturi"];
                            }
                        } else {
                            $editor_title = "Edit guest post";
                            if ( $action == "save" ) {
                                if ( $_POST["targetaction"] == "save" ) $status = "Created";
                                if ( $_POST["targetaction"] == "submit" ) $status = "Submitted";
                                $post_content = apply_filters( "the_content", $_POST["post_content"] );
                                $update_result = atcontent_api_guestposts_update( $ac_api_key, $guestpostid, site_url(), $_POST["targeturi"], $_POST["title"], $post_content, $status );
                                if ( $update_result["IsOK"] != true ) {
                                    if ($update_result["ErrorCode"] == 102) {
                                        echo "<div class=\"error\">" . 'Could not save draft to atcontent.com.<br>' . 
                                            'You must have a Pro account to submit guest posts.<br>' .
                                            "</div>";
                                        echo( '<p><b><a href="https://atcontent.com/Subscribe">Upgrade for Pro account here</a></b></p>' );
                                    } else {
                                        echo "<div class=\"error\">" . 'Could not save draft to atcontent.com. ' . $create_result["Reason"] .  "</div>";
                                    }
                                    $gp_title = $_POST["title"];
                                    $gp_content = $_POST["post_content"];
                                    $gp_targeturi = $_POST["targeturi"];
                                } else {
                                    die( '<h2>Post was saved!</h2>' . '<script type="text/javascript">window.location="'. admin_url("admin.php?page=atcontent/guestpost.php") . '";</script>' );
                                }
                            } else if ( $action == "submit" ) {
                                $update_result = atcontent_api_guestposts_status_update( $ac_api_key, $guestpostid, "Submitted" );
                                if ( $update_result["IsOK"] != true ) {
                                    if ($update_result["ErrorCode"] == 102) {
                                        echo "<div class=\"error\">" . 'Could not update status.<br>' . 
                                            'You must have a Pro account to submit guest posts.<br>' .
                                            "</div>";
                                            die( '<p><b><a href="https://atcontent.com/Subscribe">Upgrade for Pro account here</a></b></p>' );
                                    } else {
                                        echo "<div class=\"error\">" . 'Could not update status. ' . $create_result["Reason"] .  "</div>";
                                    }
                                } else {
                                    die( '<h2>Post was submited!</h2>' . '<script type="text/javascript">window.location="'. admin_url("admin.php?page=atcontent/guestpost.php") . '";</script>' );
                                }
                            } else if ( $action == "accept") {
                                $preview_result = atcontent_api_guestposts_preview( $ac_api_key, $guestpostid, site_url() );
                                if ( $preview_result["IsOK"] != true ) {
                                    echo "<div class=\"error\">" . 'Could not preview guest post. ' . $create_result["Reason"] .  "</div>";
                                } else {
                                    // Create post object
                                    $new_post = array(
                                      'post_title'    => $preview_result["Title"],
                                      'post_content'  => '[atcontent id="' . $preview_result["Post4gId"] . '"]',
                                      'post_status'   => 'publish',
                                      'post_author'   => $userid,
                                      'post_category' => array()
                                    );

                                    // Insert the post into the database
                                    $new_post_id = wp_insert_post( $new_post );
                                    $original_uri = get_permalink ( $new_post_id );
                                    $update_result = atcontent_api_guestposts_accept( $ac_api_key, $guestpostid, site_url(), $original_uri );
                                    if ( $update_result["IsOK"] != true ) {
                                        if ($update_result["Code"] == 102) {
                                            echo "<div class=\"error\">" . 'Could not accept guest post.<br>' . 
                                            'You must have a Pro account to accept guest posts.<br>' .
                                            "</div>";
                                            die( '<p><b><a href="https://atcontent.com/Subscribe">Subscribe for Pro account here</a></b></p>' );
                                        } else {
                                            echo "<div class=\"error\">" . 'Could not accept guest post. ' . $create_result["Reason"] .  "</div>";
                                        }
                                        
                                    } else {
                                        die( '<h2>Post was accepted and published</h2>' . '<script type="text/javascript">window.location="'. admin_url("admin.php?page=atcontent/guestpost.php") . '";</script>' );
                                    }
                                }
                            } else if ( $action == "decline") {
                                $update_result = atcontent_api_guestposts_decline( $ac_api_key, $guestpostid, site_url(), $original_uri );
                                if ( $update_result["IsOK"] != true ) {
                                    if ($update_result["Code"] == 102) {
                                        echo "<div class=\"error\">" . 'Could not decline guest post. ' . 
                                        'You must have a Pro account to decline guest posts.<br>' .
                                        "</div>";
                                        die( '<b><a href="https://atcontent.com/Subscribe">Subscribe for Pro account here</a></b>' );
                                    } else {
                                        echo "<div class=\"error\">" . 'Could not decline guest post. ' . $create_result["Reason"] .  "</div>";
                                    }
                                } else {
                                    die( '<h2>Post was declined</h2>' . '<script type="text/javascript">window.location="'. admin_url("admin.php?page=atcontent/guestpost.php") . '";</script>' );
                                }
                            } else {
                                $gp_result = atcontent_api_guestposts_get( $ac_api_key, $guestpostid );
                                if ( $gp_result["IsOK"] != true ) {
                                    echo "<div class=\"error\">" . 'Could not load draft from atcontent.com. ' . $create_result["Reason"] .  "</div>";
                                } else {
                                    $gp_content = $gp_result["Item"]["Face"] . ( strlen( $gp_result["Item"]["Body"] ) > 0 ? '<!--more-->' . $gp_result["Item"]["Body"] : '' );
                                    $gp_title = $gp_result["Item"]["Title"];
                                    $gp_targeturi = $gp_result["Item"]["TargetUri"];
                                }
                            }
                        }
                        ?>
    <script type="text/javascript">
        var processed = false;
        function savedraft() {
            jQuery("#targetaction").val("save");
            submitform();
        }
        function submit() {
            jQuery("#targetaction").val("submit");
            submitform();
        }
        function submitform() {
            if (processed) return;
            processed = true;
            jQuery("#gp_form").submit();
        }
var file_frame;

jQuery(function(){
  jQuery('.upload_image_button').live('click', function( event ){

    event.preventDefault();

    // If the media frame already exists, reopen it.
    if ( file_frame ) {
      // Set the post ID to what we want
      file_frame.uploader.uploader.param( 'post_id', 1 );
      // Open frame
      file_frame.open();
      return;
    } else {
      // Set the wp.media post id so the uploader grabs the ID we want when initialised
      wp.media.model.settings.post.id = 1;
    }

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
      title: jQuery( this ).data( 'uploader_title' ),
      button: {
        text: jQuery( this ).data( 'uploader_button_text' ),
      },
      multiple: false
    });

    
    file_frame.on( 'select', function() {
        attachment = file_frame.state().get('selection').first().toJSON();
        var ed = tinyMCE.get('post_editor');
        var range = ed.selection.getRng();
        var newNode = ed.getDoc().createElement("img");
        newNode.src = attachment.url;
        newNode.alt = attachment.title;
        range.insertNode(newNode);
    });

    // Finally, open the modal
    file_frame.open();
  });
});
var checkUrlInProcess = false;
function checkUrl(){
    if (checkUrlInProcess) return;
    checkUrlInProcess = true;
    var jq = jQuery, url = jq("#targeturi").val();
    jq("#check-url-out").html("Loading...");
    jq.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'post',
        data: {
            action: 'atcontent_guestpost_check_url',
            url: url},
        dataType: "json",
        success: function(d){
            checkUrlInProcess = false;
            if (d.IsOK) {
                if (d.IsActive) {
                    jq("#check-url-out").html('Found URL: ' + d.Url + ' <a href="javascript:setUrl(\'' + d.Url + '\');">Set correct URL</a>');
                } else {
                    jq("#check-url-out").html("Found inactive URL: " + d.Url + ' <a href="javascript:setUrl(\'' + d.Url + '\');">Set anyway</a>' );
                }
            } else {
                jq("#check-url-out").html("Incorrect URL");
            }

        }
        });
}
function setUrl(url) {
    jQuery("#targeturi").val(url);
}
    </script>
    <?php
                        wp_enqueue_media();
                        echo '<h3>' . $editor_title . '</h3>';
                        echo '<form id="gp_form" action="' . admin_url("admin.php?page=atcontent/guestpost.php&action=save&postid=" . $guestpostid) . '" method="post">';
                        echo 
                        '<a href="javascript:savedraft();" class="button">Save Draft</a> or ' .
                        '<a href="javascript:submit();" class="button">Submit for Consideration</a><br><br>';
                        echo 
                        'Blog\'s URL<br><input type="text" id="targeturi" name="targeturi" style="width:100%" value="' . $gp_targeturi . '">';
?>
<a id="check-url-link" class="button" href="javascript:checkUrl();">Check URL</a>
<span id="check-url-out"></span>
                        <br><br>
<?php
                        echo 
                        'Title<br><input type="text" name="title" style="width:100%" value="' . $gp_title . '"><br><br>';
                        echo 
                        '<a href="javascript:" class="button upload_image_button">Add image</a><br><br>';
                        $tinymcescript = plugins_url("atcontent/tinymce/tinymce.min.js");
                        $tinymcecss = '';
                        if ( file_exists( get_stylesheet_directory() . '/css/editor-style.css') ) {
                            $tinymcecss .= get_stylesheet_directory_uri() . '/css/editor-style.css';
                        }
                        if ( file_exists( get_stylesheet_directory() . '/editor-style.css') ) {
                            $tinymcecss .= ( strlen( $tinymcecss ) > 0 ? ',' : '') . get_stylesheet_directory_uri() . '/editor-style.css';
                        }
                        echo '<textarea id="post_editor" name="post_content" rows=15>' . $gp_content . '</textarea>' . 
                        <<<END
<script src="{$tinymcescript}"></script>
<script type="text/javascript">
tinymce.init({
    selector: "textarea#post_editor",
    plugins : 'advlist autolink autoresize link image lists pagebreak',
    menubar: false,
    toolbar: "undo redo bold italic underline strikethrough alignleft aligncenter alignright alignjustify bullist numlist link unlink pagebreak styleselect formatselect cut copy paste outdent indent blockquote removeformat subscript superscript image",
    pagebreak_separator: "<!--more-->",
    image_advtab: true,
    skin: "light",
    relative_urls: false,
    remove_script_host: false,
    content_css: '{$tinymcecss}',
});
</script>
END;
                        //wp_editor( $gp_content, 'listingeditor', $settings = array('textarea_name' => post_content) ); 
                        echo '<input type="hidden" id="targetaction" value="save" name="targetaction"><br><br>'. 
                        '<a href="javascript:savedraft();" class="button">Save Draft</a> or ' .
                        '<a href="javascript:submit();" class="button">Submit for Consideration</a>';
                        echo "</form>";
                    }

        } //end of API Key Check
    ?>
    
</div>