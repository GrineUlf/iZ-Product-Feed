<?php
require_once("wp-load.php");
require_once("wp-content/plugins/Iz-product-feed/public/class-iz-feed-public.php");

if(isset($_GET["id"])){
	$id = $_GET["id"];
	if(iz_feed_Public::iz_check_id($id)){
		$json = iz_feed_Public::iz_create_feed($id);
		echo $json;
	}else{
		echo "Invalid access token.";
	}
}else{
	echo "Invalid access token.";
}
?>