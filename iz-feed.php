<?php
require_once(ABSPATH . "wp-content/plugins/Iz-product-feed/public/class-iz-feed-public.php");
if(isset($_GET["id"])){
	$id = $_GET["id"];
	if(iz_feed_Public::iz_check_id($id)){
		$json = iz_feed_Public::create_feed($id);
		echo $json;
	}
}
?>