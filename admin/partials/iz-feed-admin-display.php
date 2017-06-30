<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/GrineUlf/iZ-Product-Feed/
 * @since      2.0.0
 *
 * @package    iz-product-feed
 * @subpackage iz-product-feed/admin/partials
 */
global $wpdb;
$table_name = $wpdb->prefix . "iz_feed_settings";
if(isset($_GET["active"])){
	$id = $_GET["active"];
	$res = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id = " . $id.";");
	
	switch($res->active){
		case 1:
			$wpdb->update($table_name, array("active" => 0), array("id" => $id),"%d","%d");
		break;
		case 0:
			$wpdb->update($table_name, array("active" => 1), array("id" => $id),"%d","%d");
		break;
	}
	wp_safe_redirect(admin_url('admin.php?page=iz-feed'));
}
if(isset($_GET["delete"])){
	$id = $_GET["delete"];
	$res = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id = " . $id.";");
	$del_name = $res->name;
	if(isset($_GET["confirm"])){
		$wpdb->delete($table_name, array("id" => $id));
		unlink(ABSPATH . "/iz-feed/" . $res->filename);
		wp_safe_redirect(admin_url('admin.php?page=iz-feed'));
	}
	
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php
if(isset($_GET["delete"]) && !isset($_GET["confirm"])){
?>
<div class="iz-admin-delete">
	<p>Are you sure you want to delete <?php echo $del_name; ?>?<br>
	<a href="admin.php?page=iz-feed&delete=<?php echo $id;?>&confirm=1">Yes</a> - <a href="admin.php?page=iz-feed">No</a></p>
</div>
<?php
}
?>

<div class="iz-admin-container">
	<table class="iz-admin-table">
		<tr>
			<th class="iz-admin-header">Feed name</th>
			<th class="iz-admin-header">Created by</th>
			<th class="iz-admin-header">Active</th>
			<th class="iz-admin-header">Location</th>
			<th class="iz-admin-header">Actions</th>
		</tr>
		<?php
		foreach( $wpdb->get_results("SELECT * FROM ".$table_name.";") as $key => $row) {
			$user = get_user_by('id', $row->user_id);
			if($row->active == 1){
				$active = "Yes";
				$act_name = "Deactivate";
			}else{
				$active = "No";
				$act_name = "Activate";
			}
		?>
		<tr>
			<td class="iz-admin-row"><?php echo $row->name; ?></td>
			<td class="iz-admin-row"><?php echo $user->user_login; ?></td>
			<td class="iz-admin-row"><?php echo $active;?></td>
			<td class="iz-admin-row"><?php echo $row->location; ?></td>
			<td class="iz-admin-row">
			<?php //<a href="admin.php?page=iz-new-feed&edit=<?php echo $row->id;">Edit</a> - ?>
			<a href="admin.php?page=iz-feed&active=<?php echo $row->id;?>"><?php echo $act_name;?></a> - 
			<a href="admin.php?page=iz-feed&delete=<?php echo $row->id;?>">Delete</a></td>
		</tr>
		<?php
		}
		?>
	</table>
	<button name="add-feed" class="iz-add-feed-button" onclick="javascript:location.href='admin.php?page=iz-new-feed'">Add a new feed</button>
</div>