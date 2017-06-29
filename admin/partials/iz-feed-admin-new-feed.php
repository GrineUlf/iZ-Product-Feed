<?php
global $wpdb;
$table_name = $wpdb->prefix . "iz_feed_settings";
if(isset($_GET["edit"])){
	$edit = 1;
	$id = $_GET["edit"];
	$form_field = '<input type="hidden" name="action" value="iz_edit_feed">';
	$res = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id = " . $id.";");
	
}else{
	$form_field = '<input type="hidden" name="action" value="iz_add_feed">';
}
?>
<div class="iz-admin-container">
	<form name="iz-feed-add-feed-form" method="POST" enctype="multipart/form-data" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php
	echo $form_field;
	?>
	<table class="iz-admin-table">
		<tr>
			<th class="iz-admin-header">Feed name</th>
			<td class="iz-admin-row"><input type="text" name="feed_name" <?php if($edit){ echo 'value="'.$res->name.'"'; }?>></td>
		</tr>
		<tr>
			<th class="iz-admin-header">Type</th>
			<td class="iz-admin-row">
				<select name="feed_type" <?php if($edit){ echo 'value="'.$res->type.'"'; }?>>
					<option value="XML">XML</option>
					<option value="JSON">JSON</option>
					<option value="CSV">CSV</option>
				</select>
			</td>
		</tr>
		<tr>
			<th class="iz-admin-header">Active</th>
			<td class="iz-admin-row"><input type="checkbox" name="feed_active" <?php if($edit && $res->active == 1){ echo "checked"; } ?>> </td>
		</tr>
	</table>
	<input type="submit" name="add-feed" class="iz-add-feed-button" value="Save Feed">
	</form>
</div>