<div class="atcontent_wrap">
<?php 
    $userid = wp_get_current_user()->ID;
    $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
    $ac_postid = $_GET["postid"]; 
    $outsidelist = $_GET["outside"];

    if ( strlen( $ac_postid ) == 0 && strlen ( $outsidelist ) == 0 ) {
        
        $posts_id = array();

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
            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            if ( strlen( $ac_postid ) > 0 ) { 
                array_unshift( $posts_id, $ac_postid );
            }
            wp_cache_flush();
        }

        $onlyoutsidelink = admin_url("admin.php?page=atcontent/statistics.php&outside=1");

?>
       <h2>AtContent Statistics</h2>
<p><a href="<?php echo $onlyoutsidelink; ?>">Show outside reposts</a></p>
<?php
        $stat_result = atcontent_api_extended_readership( $ac_api_key, site_url(), json_encode( $posts_id ), 0 );

        if ( $stat_result["IsOK"] != true ) {
            if ( $stat_result["ErrorCode"] == 102 ) {
                echo  "<div class=\"error\">" . 'Could not get statistics from atcontent.com. ' . 
                'You should have Pro account.<br>' .
                '<a href="https://atcontent.com/Subscribe">Upgrade for Pro account here</a>' .
                "</div>" ;
            } else {
                echo 'Something gets wrong. ' . $stat_result["Reason"] ;
            }
        }

        if ( $stat_result["IsOK"] == true ) {
        ?>
<table cellspacing="0" class="wp-list-table widefat fixed posts">
	<thead>
    <tr>
      <th style="" class="manage-column" scope="col">Title</th>
      <th style="width:100px;" class="manage-column" scope="col">On My Blog Views</th>
      <th style="width:100px;" class="manage-column" scope="col">Repost Views</th>
      <th style="width:100px;" class="manage-column" scope="col">Increase Rate</th>
      <th style="width:100px;" class="manage-column" scope="col">Facebook</th>
      <th style="width:100px;" class="manage-column" scope="col">Twitter</th>
      <th style="width:100px;" class="manage-column" scope="col">LinkedIn</th>
      <th style="width:100px;" class="manage-column" scope="col">Direct</th>
    </tr>
	</thead>

	<tfoot>
    <tr>
      <th style="" class="manage-column" scope="col">Title</th>
      <th style="" class="manage-column" scope="col">On My Blog Views</th>
      <th style="" class="manage-column" scope="col">Repost Views</th>
      <th style="" class="manage-column" scope="col">Increase Rate</th>
      <th style="" class="manage-column" scope="col">Facebook</th>
      <th style="" class="manage-column" scope="col">Twitter</th>
      <th style="" class="manage-column" scope="col">LinkedIn</th>
      <th style="" class="manage-column" scope="col">Direct</th>
     </tr>
  </tfoot>
  <tbody id="the-list">
<?php
            foreach ( $stat_result["PostsStats"] as $poststat ) {
                $poststat["StatUrl"] = admin_url( "admin.php?page=atcontent/statistics.php" ) . '&postid=' . $poststat["PostID"] ; 
?> 
        <tr valign="top" class="post-<?php echo $poststat["PostID"]; ?> type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self" id="post-<?php echo $poststat["PostID"]; ?>">
			<td class="post-title page-title column-title"><strong><a title="Details for “<?php echo $poststat["Title"]; ?>”" href="<?php echo $poststat["StatUrl"]; ?>" class="row-title"><?php echo $poststat["Title"]; ?></a></strong>

<div class="row-actions">
    <span class="details"><a title="Details for this item" href="<?php echo $poststat["StatUrl"]; ?>">Details</a></span>
    <span class="detailsoutside"><a title="Outside reposts for this item" href="<?php echo $poststat["StatUrl"] . "&outside=1"; ?>">Outside reposts</a></span>
</div>

						<td><?php echo $poststat["OriginalViews"] > 0 ? $poststat["OriginalViews"] : "—";?></td>
            <td ><?php echo $poststat["RepostViews"] > 0 ? $poststat["RepostViews"] : "—"; ?></td>
            <td ><?php echo  $poststat["IncreaseRate"] > 0 ? number_format_i18n ( $poststat["IncreaseRate"] ) . '%' : "—"; ?></td>
            <td ><?php echo  $poststat["FacebookHits"] >= 0 ? number_format_i18n ( $poststat["FacebookHits"] ) : "—"; ?></td>
            <td ><?php echo  $poststat["TwitterHits"] >= 0 ? number_format_i18n ( $poststat["TwitterHits"] ) : "—"; ?></td>
            <td ><?php echo  $poststat["LinkedInHits"] >= 0 ? number_format_i18n ( $poststat["LinkedInHits"] ) : "—"; ?></td>
            <td ><?php echo  $poststat["DirectHits"] >= 0 ? number_format_i18n ( $poststat["DirectHits"] ) : "—"; ?></td>
					</tr>
<?php
            }
?>
    </tbody>
</table>
            <?php

        }

    } else if ( strlen ( $ac_postid ) > 0 ) {
        $deatils = 2;

        $repost_title_answer = atcontent_api_get_title( $ac_postid );
        $repost_title = "";
        if ( $repost_title_answer["IsOK"] == true ) {
            $repost_title = $repost_title_answer["Title"];
        } else {
            wp_die( "Something gets wrong. Please try again" );
        }

        $listheader = "Statistics for \"" . $repost_title . "\"";
        $another_stat_link = '<a href="' . admin_url( "admin.php?page=atcontent/statistics.php&postid=" . $ac_postid . "&outside=1" ) . '">View outsite reposts only</a>';

        if ( $outsidelist == "1" ) {
            $deatils = 1;
            $listheader = "Outside reposts for \"" . $repost_title . "\"";
            $another_stat_link = '<a href="' . admin_url( "admin.php?page=atcontent/statistics.php&postid=" . $ac_postid ) . '">View complete statistics for this post</a>';
        }

        ?>
<h2><?php echo $listheader; ?></h2>
<p>
    <a href="<?php echo admin_url( "admin.php?page=atcontent/statistics.php" ); ?>">Back to posts</a> 
    <a href="https://atcontent.com/Studio/Publication/Stat/<?php echo $ac_postid; ?>/">View statistics on AtContent</a> 
<?php echo $another_stat_link ?>
</p>
    <?php
        $stat_result = atcontent_api_extended_readership( $ac_api_key, site_url(), json_encode( array( $ac_postid ) ), $deatils );

        if ( $stat_result["IsOK"] != true ) {
            if ($stat_result["ErrorCode"] == 102) {
                echo  "<div class=\"error\">" . 'Could not get statistics from atcontent.com. ' . 
                'You should have Pro account.<br>' .
                '<a href="https://atcontent.com/Subscribe">Upgrade for Pro account here</a>' .
                "</div>" ;
            } else {
                echo 'Something gets wrong. ' . $stat_result["Reason"] ;
            }
        } else {

            $stat_info = $stat_result["PostsStats"][0];


?>
    <h3>Summary</h3>
    Original views: <strong><?php echo  $stat_info["OriginalViews"] > 0 ? $stat_info["OriginalViews"] : "N/A"; ?></strong><br>
    Repost views: <strong><?php echo $stat_info["RepostViews"] > 0 ? $stat_info["RepostViews"] : "N/A"; ?></strong><br>
    Increate rate: <strong><?php echo $stat_info["IncreaseRate"] > 0 ? number_format_i18n ( $stat_info["IncreaseRate"] ) . '%' : "N/A"; ?></strong><br>
<?php if ( $stat_info["IsAdvancedTracking"] == true ) { ?>
    <h3>Details</h3>
<script type="text/javascript">
    function toggleDetails(domain) {
        var link = document.getElementById("details-link-" + domain), jq = jQuery;
        if (jq(link).html() == "Show details") {
            jq(".child-" + domain.split(".").join("\\.")).show();
            jq(link).html("Hide details");
        } else {
            jq(".child-" + domain.split(".").join("\\.")).hide();
            jq(link).html("Show details");
        }
    }
</script>
<table cellspacing="0" class="wp-list-table widefat fixed posts">
	<thead>
    <tr>
      <th style="" class="manage-column" scope="col">Url</th>
      <th style="width:100px;" class="manage-column" scope="col">Views</th>
      <th style="width:100px;" class="manage-column" scope="col">Facebook</th>
      <th style="width:100px;" class="manage-column" scope="col">Twitter</th>
      <th style="width:100px;" class="manage-column" scope="col">LinkedIn</th>
      <th style="width:100px;" class="manage-column" scope="col">Direct</th>
    </tr>
	</thead>

	<tfoot>
    <tr>
      <th style="" class="manage-column" scope="col">Url</th>
      <th style="width:100px;" class="manage-column" scope="col">Views</th>
      <th style="width:100px;" class="manage-column" scope="col">Facebook</th>
      <th style="width:100px;" class="manage-column" scope="col">Twitter</th>
      <th style="width:100px;" class="manage-column" scope="col">LinkedIn</th>
      <th style="width:100px;" class="manage-column" scope="col">Direct</th>
    </tr>
	</tfoot>
    <tbody id="the-list">
<?php
    foreach ( $stat_info["Details"] as $stat_domain_pair ) {
        $stat_domain = $stat_domain_pair["Key"];
        $stat_domain_info = $stat_domain_pair["Value"];
        $stat_domain_info["DetailsUrl"] = "javascript:toggleDetails('" . $stat_domain . "');";
        ?>
        <tr valign="top" class="domain-<?php echo $stat_domain; ?> type-post hentry" id="domain-<?php echo $stat_domain; ?>">
          <td class="domain-title column-title"><strong>“<?php echo $stat_domain; ?>”</strong>

<div class="row-actions">
    <span class="details"><a title="Details for this item" id="details-link-<?php echo $stat_domain; ?>" href="<?php echo $stat_domain_info["DetailsUrl"]; ?>">Show details</a></span>
    <span class="view"><a title="Visit this site" href="<?php echo 'http://' . $stat_domain; ?>">Visit site</a></span>
</div>

          <td><strong><?php echo $stat_domain_info["Views"]; ?></strong></td>
          <td><strong><?php echo $stat_domain_info["Facebook"] >= 0 ? $stat_domain_info["Facebook"] : "—" ; ?></strong></td>
          <td><strong><?php echo $stat_domain_info["Twitter"] >= 0 ? $stat_domain_info["Twitter"] : "—" ; ?></strong></td>
          <td><strong><?php echo $stat_domain_info["LinkedIn"] >= 0 ? $stat_domain_info["LinkedIn"] : "—" ; ?></strong></td>
          <td><strong><?php echo $stat_domain_info["DirectLink"] >= 0 ? $stat_domain_info["DirectLink"] : "—" ; ?></strong></td>
        </tr>
        <?php
            foreach ( $stat_domain_info["Items"] as $details_pair ) {
                $details_url = $details_pair["Key"];
                $details_info = $details_pair["Value"];
                ?>
        <tr valign="top" class="url-entry ac-url-entry type-post hentry child-<?php echo $stat_domain ?>">
          <td class="url-caption"><a href="<?php echo $details_url; ?>"><?php echo $details_url; ?></a></td>
          <td><?php echo $details_info["Views"]; ?></td>
          <td><?php echo $details_info["Facebook"] >= 0 ? $details_info["Facebook"] : "—" ; ?></td>
          <td><?php echo $details_info["Twitter"] >= 0 ? $details_info["Twitter"] : "—" ; ?></td>
          <td><?php echo $details_info["LinkedIn"] >= 0 ? $details_info["LinkedIn"] : "—" ; ?></td>
          <td><?php echo $details_info["DirectLink"] >= 0 ? $details_info["DirectLink"] : "—" ; ?></td>
        </tr>
        <?php
            }
    } 
    
?>
    </tbody>
</table>
<?php } else {
     ?><h3>Details are not available<br>Turn on Advanced Tracking for this post to view details</h3><?php
 }
        }
    } else if ( $outsidelist == "1" ) {
        $posts_id = array();

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
            $ac_postid = get_post_meta( $post->ID, "ac_postid", true );
            if ( strlen( $ac_postid ) > 0 ) { 
                array_push( $posts_id, $ac_postid );
            }
            wp_cache_flush();
        }

         $allstatlink = admin_url("admin.php?page=atcontent/statistics.php");

?>
       <h2>AtContent Statistics for Outside Reposts</h2>
<p><a href="<?php echo $allstatlink; ?>">Back to statistics</a></p>
<?php
        $stat_result = atcontent_api_extended_readership( $ac_api_key, site_url(), json_encode( $posts_id ), 1 );

         if ( $stat_result["IsOK"] != true ) {
            if ($stat_result["ErrorCode"] == 102) {
                echo  "<div class=\"error\">" . 'Could not get statistics from atcontent.com. ' . 
                'You should have Pro account.<br>' .
                '<a href="https://atcontent.com/Subscribe">Upgrade for Pro account here</a>' .
                "</div>" ;
            } else {
                echo 'Something gets wrong. ' . $stat_result["Reason"] ;
            }
        } else {
            ?> 
<script type="text/javascript">
    function toggleDetails(id) {
        var link = document.getElementById("details-link-" + id), jq = jQuery;
        if (jq(link).html() == "Show details") {
            jq(".child-" + id.split(".").join("\\.")).show();
            jq(link).html("Hide details");
        } else {
            jq(".child-" + id.split(".").join("\\.")).hide();
            jq(link).html("Show details");
        }
    }
</script>
<table cellspacing="0" class="wp-list-table widefat fixed posts">
	<thead>
    <tr>
      <th style="" class="manage-column" scope="col">Url</th>
      <th style="width:100px;" class="manage-column" scope="col">Reposts Views</th>
      <th style="width:100px;" class="manage-column" scope="col">Facebook</th>
      <th style="width:100px;" class="manage-column" scope="col">Twitter</th>
      <th style="width:100px;" class="manage-column" scope="col">LinkedIn</th>
      <th style="width:100px;" class="manage-column" scope="col">Direct</th>
    </tr>
	</thead>

	<tfoot>
    <tr>
      <th style="" class="manage-column" scope="col">Url</th>
      <th style="width:100px;" class="manage-column" scope="col">Reposts Views</th>
      <th style="width:100px;" class="manage-column" scope="col">Facebook</th>
      <th style="width:100px;" class="manage-column" scope="col">Twitter</th>
      <th style="width:100px;" class="manage-column" scope="col">LinkedIn</th>
      <th style="width:100px;" class="manage-column" scope="col">Direct</th>
    </tr>
	</tfoot>
  <tbody id="the-list">
<?php
foreach ( $stat_result["PostsStats"] as $stat_info ) {
?>
        <tr>
            <td>Post <strong style="font-size:12pt;">“<?php echo $stat_info["Title"]; ?>”</strong></td>
            <td><strong><?php echo $stat_info["RepostViews"] > 0 ? $stat_info["RepostViews"] : "—" ; ?></strong></td>
            <td><strong><?php echo $stat_info["FacebookHits"] >= 0 ? $stat_info["FacebookHits"] : "—" ; ?></strong></td>
            <td><strong><?php echo $stat_info["TwitterHits"] >= 0 ? $stat_info["TwitterHits"] : "—" ; ?></strong></td>
            <td><strong><?php echo $stat_info["LinkedInHits"] >= 0 ? $stat_info["LinkedInHits"] : "—" ; ?></strong></td>
            <td><strong><?php echo $stat_info["DirectHits"] >= 0 ? $stat_info["DirectHits"] : "—" ; ?></strong></td>
        </tr>
<?php
    foreach ( $stat_info["Details"] as $stat_domain_pair ) {
        $stat_domain = $stat_domain_pair["Key"];
        $stat_domain_info = $stat_domain_pair["Value"];
        $stat_row_id = $stat_info["PostID"] . "-" . $stat_domain;
        $stat_domain_info["DetailsUrl"] = "javascript:toggleDetails('" . $stat_row_id . "');";
        ?>
        <tr valign="top" class="domain-<?php echo $stat_row_id; ?> type-post hentry" id="domain-<?php echo $stat_row_id; ?>">
			<td class="domain-title column-title"><strong><?php echo $stat_domain; ?></strong>

<div class="row-actions">
    <span class="details"><a title="Details for this item" id="details-link-<?php echo $stat_row_id; ?>" href="<?php echo $stat_domain_info["DetailsUrl"]; ?>">Show details</a></span>
    <span class="view"><a title="Visit this site" href="<?php echo 'http://' . $stat_domain; ?>">Visit site</a></span>
</div>

			<td><strong><?php echo $stat_domain_info["Views"]; ?></strong></td>
			<td><strong><?php echo $stat_domain_info["Facebook"] >= 0 ? $stat_domain_info["Facebook"] : "—" ; ?></strong></td>
      <td><strong><?php echo $stat_domain_info["Twitter"] >= 0 ? $stat_domain_info["Twitter"] : "—" ; ?></strong></td>
      <td><strong><?php echo $stat_domain_info["LinkedIn"] >= 0 ? $stat_domain_info["LinkedIn"] : "—" ; ?></strong></td>
      <td><strong><?php echo $stat_domain_info["DirectLink"] >= 0 ? $stat_domain_info["DirectLink"] : "—" ; ?></strong></td>
		</tr>
        <?php
            foreach ( $stat_domain_info["Items"] as $details_pair ) {
                $details_url = $details_pair["Key"];
                $details_info = $details_pair["Value"];
                ?>
        <tr valign="top" class="url-entry ac-url-entry type-post hentry child-<?php echo $stat_row_id ?>">
			<td class="url-caption"><a href="<?php echo $details_url; ?>"><?php echo $details_url; ?></a></td>
			<td><?php echo $details_info["Views"]; ?></td>
      <td><?php echo $details_info["Facebook"] >= 0 ? $details_info["Facebook"] : "—" ; ?></td>
      <td><?php echo $details_info["Twitter"] >= 0 ? $details_info["Twitter"] : "—" ; ?></td>
      <td><?php echo $details_info["LinkedIn"] >= 0 ? $details_info["LinkedIn"] : "—" ; ?></td>
      <td><?php echo $details_info["DirectLink"] >= 0 ? $details_info["DirectLink"] : "—" ; ?></td>
		</tr>
        <?php
            }
    } 
}
?>
    </tbody>
</table>
<?php
        }
    }
?>
</div>