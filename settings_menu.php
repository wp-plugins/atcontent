<nav class="b-settings-tabs">
    <ul>
        <?php if ( $atcontent_menu_section == "connect" ) { ?>
        <li><span><i>1</i>Connect</a></span></li>
        <?php } else { ?>
        <li><a href="<?php echo admin_url("admin.php?page=atcontent/connect.php"); ?>"><i>1</i>Connect</a></li>
        <?php } ?>
        <?php if ( $atcontent_menu_section == "settings" ) { ?>
        <li><span><i>2</i>Settings</a></span></li>
        <?php } else { ?>
        <li><a href="<?php echo admin_url("admin.php?page=atcontent/settings.php"); ?>"><i>2</i>Settings</a></li>
        <?php } ?>
        <?php if ( $atcontent_menu_section == "sync" ) { ?>
        <li><span><i>3</i>Sync</a></span></li>
        <?php } else { ?>
        <li><a href="<?php echo admin_url("admin.php?page=atcontent/sync.php"); ?>"><i>3</i>Sync</a></li>
        <?php } ?>
        <?php if ( $atcontent_menu_section == "subscription" ) { ?>
        <li><span><i>4</i>Subscription</a></span></li>
        <?php } else { ?>
        <li><a href="<?php echo admin_url("admin.php?page=atcontent/subscription.php"); ?>"><i>4</i>Subscription</a></li>
        <?php } ?>
    </ul>
</nav>