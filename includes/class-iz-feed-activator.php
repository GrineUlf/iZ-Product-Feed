<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class iz_feed_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function iz_set_cron() {
		if ( !wp_next_scheduled( 'iz_create_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'iz_create_cron');
		}
	}
	
	public function iz_cron(){
		global $wpdb;
		$table_name = $wpdb->prefix . "iz_feed_settings";
		foreach( $wpdb->get_results("SELECT * FROM ".$table_name." WHERE active = 1;") as $key => $row) {
			$create = create_xml_feed($row->filename);
		}
	}

	public static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . "iz_feed_settings";
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE ".$table_name." (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  user_id int(11),
		  active tinyint(1),
		  name varchar(255),
		  type varchar(255),
		  location varchar(255),
		  filename varchar(255),
		  date_created timestamp  DEFAULT CURRENT_TIMESTAMP,
		  date_edited timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  PRIMARY KEY  (id)
		) ". $charset_collate.";";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		if(!file_exists(ABSPATH . "iz-feed")){
			mkdir(ABSPATH ."iz-feed", 0777, true);
		}
		add_action('iz_create_cron', array('iz_feed_Activator','iz_cron'));
		add_action('wp', array('iz_feed_Activator','iz_set_cron'));
		
		
	}

}


