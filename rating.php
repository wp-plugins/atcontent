<div class="atcontent_wrap">
    <div class="wrap">
        <div class="icon32" id="icon-index"><br></div><h2>AtContent Blogs Rating</h2>
    </div>
<?php

if ( strlen( $_GET["category"] ) == 0 ) {

?>
    <div style="font-size: 15pt; line-height: normal;">
        <p>Choose a category</p>
    <?php

    foreach ($atcontent_categories as $category => $description) {
        if ( strlen( $category ) == 0 ) continue;
        $category_url = admin_url("admin.php?page=atcontent/rating.php") . "&category=" . $category;
        echo <<<END
<a href="{$category_url}">{$description}</a><br>
END;
    }
?>
    </div>
<?php
} else {
    $current_category = $_GET["category"];
    $current_category_desctipion = $atcontent_categories[$current_category];
?>
    <h3><?php echo $current_category_desctipion; ?></h3>
    <p><a href="<?php echo admin_url("admin.php?page=atcontent/rating.php")?>">Back to category list</a></p>
<?php
    $category_rating = atcontent_api_category_sites_rating( $current_category );
    if ( $category_rating["IsOK"] != true ) {
        ?>
    <p>Something gets wrong</p>
        <?php
    } else {
        ?>
<table cellspacing="0" class="wp-list-table widefat fixed posts">
    <thead>
        <tr>
            <th style="width:50px;">#</th>
            <th style="" class="manage-column" scope="col">Url</th>
            <th style="width:100px;" class="manage-column" scope="col">On Blog Views</th>
            <th style="width:100px;" class="manage-column" scope="col">Reposts Views</th>
            <th style="width:100px;" class="manage-column" scope="col">Increase Rate</th>
        </tr>
	</thead>

	<tfoot>
        <tr>
            <th style="width:50px;">#</th>
            <th style="" class="manage-column" scope="col">Url</th>
            <th style="width:100px;" class="manage-column" scope="col">On Blog Views</th>
            <th style="width:100px;" class="manage-column" scope="col">Reposts Views</th>
            <th style="width:100px;" class="manage-column" scope="col">Increase Rate</th>
        </tr>
    </tfoot>
    <tbody id="the-list">
        <?php
            $i = 0;
            foreach ( $category_rating["Results"] as $item ) {
                $i++;
                if ( $item["IncreaseRate"] == "Infinity" ) {
                    $item["IncreaseRate"] = "&infin;";
                } else {
                    $item["IncreaseRate"] = number_format_i18n( $item["IncreaseRate"], 1) . "%";
                }
                ?>
        <tr>
            <td><?php echo $i; ?></td>
            <td><a href="<?php echo $item["Site"];?>"><?php echo $item["Site"]; ?></a></td>
            <td><?php echo number_format_i18n( $item["OriginalViews"] );?></td>
            <td><?php echo number_format_i18n( $item["RepostViews"] );?></td>
            <td><?php echo $item["IncreaseRate"];?></td>
            </tr>
                <?php
            }
        ?>
    </tbody>
</table>
        <?php
    }
?>
<?php
    }
?>
</div>