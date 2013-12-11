<?php
include_once( "include/galvanize.php" );
$GA = new AC_Galvanize("UA-42908730-2");
$GA->trackPageView("/" . $_GET["page"], $_GET["title"]);
?>