<?php
         $userid = wp_get_current_user()->ID;
         $ac_api_key = get_user_meta( $userid, "ac_api_key", true );
         $ac_pen_name = get_user_meta( $userid, "ac_pen_name", true );
         $img_url = plugins_url( 'assets/logo.png', __FILE__ );

         $category1url = admin_url("admin.php?page=atcontent/repost.php&category=1");
         $category2url = admin_url("admin.php?page=atcontent/repost.php&category=2");
         $category3url = admin_url("admin.php?page=atcontent/repost.php&category=3");

         $currentcategory = $_GET["category"];
         if ( strlen( $currentcategory ) == 0 ) $currentcategory = "1";

         $currentpage = $_GET["pageNum"];
         if ( strlen( $currentpage ) == 0 ) $currentpage = "1";

         $pageAnswer = atcontent_api_reposts( $currentcategory, $currentpage );
         if ( $pageAnswer["IsOK"] != true ) {
             wp_die( "Something gets wrong" . var_dump( $pageAnswer ) );
         }

         $atcontent_reposts = $pageAnswer["Page"]["PostIDs"];

         $preview_url = site_url("?ac_repost_post=");

         // PingBack

         if ( ! atcontent_pingback_inline() ) {
             echo "<div class=\"error\">" . 'Could not connect to atcontent.com. Contact your hosting provider.' . "</div>";
         }

         if ( $_GET["postid"] != null && strlen( $_GET["postid"] ) > 0) {
            
            $repost_title_answer = atcontent_api_get_title( $_GET["postid"] );
            $repost_title = "";
            if ( $repost_title_answer["IsOK"] == true ) {
                $repost_title = $repost_title_answer["Title"];
            } else {
                wp_die( "Something gets wrong. Please try again" );
            }
            // Create post object
            $new_post = array(
                'post_title'    => $repost_title,
                'post_content'  => '[atcontent id="' . $_GET["postid"] . '"]',
                'post_status'   => 'publish',
                'post_author'   => $userid,
                'post_category' => array()
            );

            // Insert the post into the database
            $new_post_id = wp_insert_post( $new_post );
            $original_uri = get_permalink ( $new_post_id );

            ?>
<h2>Post accepted</h2>
<script>
    window.location = '<?php echo $original_uri ?>';
</script>
<?php
            wp_die();

         }

         //End PingBack

?>         
<div class="atcontent_wrap">

<?php if ( strlen( $ac_api_key ) == 0 ) { ?>
    <?php include("invite.php"); ?>
    <hr />
    <br>
<?php } ?>
<div class="wrap">
    <div class="icon32" id="icon-link"><br></div><h2>Content&nbsp;for&nbsp;reposting</h2>
</div>
    <br><br>
    <style>
    .contentColumns:after {
        clear: both;
        content: "";
        display: block;
    }
    .contentColumns {
        margin-left: -5px;
    }
    .mainCol {
        overflow: hidden;
        padding-left: 5px;
    }
        
    .asideCol {
        float: left;
        margin-left: 5px;
        margin-right: 35px;
        min-height: 1px;
        position: relative;
        width: 200px;
    }
    .article-inline {
        display: inline-block;
        margin-bottom: 50px;
        min-width: 350px;
        vertical-align: top;
        width: 48%;
    }
    </style>

    <div class="contentColumns">
        <div class="asideCol">
            <h2>Categories</h2>
            <p>
                <?php if ($currentcategory !== "1") { ?>
                    <a href="<?php echo $category1url; ?>">Business & Marketing</a><br>
                <?php } else { ?>
                    <strong>Business & Marketing</strong><br>
                <?php }?>
                <?php if ($currentcategory !== "2") { ?>
                    <a href="<?php echo $category2url; ?>">Fashion</a><br>
                <?php } else { ?>
                    <strong>Fashion</strong><br>
                <?php }?>
                <?php if ($currentcategory !== "3") { ?>
                    <a href="<?php echo $category3url; ?>">Enterpreneurship</a><br>
                <?php } else { ?>
                    <strong>Enterpreneurship</strong><br>
                <?php }?>
            </p>
        </div>

    <div class="mainCol">
        <div class="postList b-publications-columns">
            <?php foreach ( $atcontent_reposts as $postid ) { ?>
                <div class="article-inline">
<script async src="https://w.atcontent.com/CPlase/<?php echo $postid; ?>/Title/h3"></script>
<script async src="https://w.atcontent.com/CPlase/<?php echo $postid; ?>/Face"></script>

                </div>
            <?php } ?>
                
        </div>

<script>
    jQuery(function () {
        CPlase = window.CPlase || {};
        CPlase.evt = CPlase.evt || [];
        CPlase.evt.push(function (event, p, w) {
            var hdl = jQuery('h1,h2,h3,h4,h5,h6', document.getElementById('CPlase_' + p + '_' + w + '_title'));
            hdl.html('<a href="http://p.atcontent.com/' + p + '/">' + hdl.html() + '</a>');
            var o = jQuery(document.getElementById('CPlase_' + p + '_' + w + '_panel'));
            if (!o.prev('.CPlase_publicationLink').size()) {
                o.before('<div style="margin: 1em 0 0" class="CPlase_publicationLink"><a class="likebutton b_orange" href="<?php echo $preview_url; ?>' + p + '">Repost to my blog</a></div>');
            }
        })
    })
</script>
        <?php if ( $currentpage > 1 ) { 
            $prevPageUrl = admin_url( "admin.php?page=atcontent/repost.php&category=" . $currentcategory . "&pageNum=" . ( intval( $currentpage ) - 1 ) );
        ?>
            <a href="<?php echo $prevPageUrl; ?>" class="likebutton b_green">&larr; Previous page</a>
        <?php } ?>
        <?php if ( $pageAnswer["Page"]["HasNext"] == true ) { 
            $nextPageUrl = admin_url( "admin.php?page=atcontent/repost.php&category=" . $currentcategory . "&pageNum=" . ( intval( $currentpage ) + 1 ) );
        ?>
            <a href="<?php echo $nextPageUrl; ?>" class="likebutton b_green">Next page &rarr;</a>
        <?php } ?>
        </div>
    </div>

<br><br><br>
<p><a href="http://wordpress.org/extend/plugins/atcontent/" target="_blank">AtCotnent plugin page</a> &nbsp; 
    <a href="http://atcontent.com/Support/" target="_blank">Support</a> &nbsp; 
    <a href="http://atcontent.com/About/" target="_blank">About AtContent</a> &nbsp; 
    <a href="http://atcontent.com/Privacy/" target="_blank">Privacy Policy</a> &nbsp; 
    <a href="http://atcontent.com/Terms/" target="_blank">Terms and Conditions</a> &nbsp; 
</p>

</div>