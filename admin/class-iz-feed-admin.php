<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class iz_feed_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	public function iz_admin_page(){
		require_once(dirname(__FILE__) . '/partials/iz-feed-admin-display.php');
	}
	public function iz_admin_new_feed(){
		require_once(dirname(__FILE__) . '/partials/iz-feed-admin-new-feed.php');
	}
	
	public function iz_feed_menu(){
		add_menu_page('Iz Product Feed', 'Iz Product Feed', 'export', 'iz-feed', array('iz_feed_Admin','iz_admin_page'));
		add_submenu_page('iz-feed', 'Add new feed','Add new feed', 'export', 'iz-new-feed', array('iz_feed_Admin','iz_admin_new_feed'));
	}
	
	//Function to remove unfriendly XML Characters
	public function removeUnsafeXML($VarVal) {
		$output = str_replace('&', '&amp;', $VarVal);
		$output = preg_replace('/[^(\x20-\x7F)]*/','', $output);
		return $output;
	}
	
	public function get_product_list(){
		$full_product_list = array();
		
		$loop = new WP_Query( array( 'post_status' => 'publish', 'post_type' => array('product', 'product_variation'), 'posts_per_page' => -1, ) );
	 
		while ( $loop->have_posts() ) : $loop->the_post();
			$theid = get_the_ID();
			$product = new WC_Product($theid);
			// its a variable product
			if( get_post_type() == 'product_variation' ){
				$parent_id = wp_get_post_parent_id($theid );
				$sku = get_post_meta($theid, '_sku', true );
				$thetitle = get_the_title( $parent_id);
	 
		// ****** Some error checking for product database *******
				// check if variation sku is set
				if ($sku == '') {
					if ($parent_id == 0) {
						// Remove unexpected orphaned variations.. set to auto-draft
						$false_post = array();
						$false_post['ID'] = $theid;
						$false_post['post_status'] = 'auto-draft';
						wp_update_post( $false_post );
						if (function_exists(add_to_debug)) add_to_debug('false post_type set to auto-draft. id='.$theid);
					} else {
						// there's no sku for this variation > copy parent sku to variation sku
						// & remove the parent sku so the parent check below triggers
						$sku = get_post_meta($parent_id, '_sku', true );
						if (function_exists(add_to_debug)) add_to_debug('empty sku id='.$theid.'parent='.$parent_id.'setting sku to '.$sku);
						update_post_meta($theid, '_sku', $sku );
						update_post_meta($parent_id, '_sku', '' );
						$description = get_post_field('post_content', $parent_id);
					}
				}
		// ****************** end error checking *****************
	 
			// its a simple product
			} else {
				$sku = get_post_meta($theid, '_sku', true );
				$thetitle = get_the_title();
				$description = get_post_field('post_content', $theid);
			}
			
			switch(get_post_meta($theid, '_stock_status', true)){
				case "instock":
					$availability = "in stock";
					break;
				case "outofstock":
					$availability = "out of stock";
					break;
				default:
					$availability = "in stock";
			}
			
			if(strpos($sku, "-")){
				$am = explode("-",$sku);
				$amount = $am[1];
			}else{
				$amount = 0;
			}
			$currency = get_woocommerce_currency();
			$link = get_permalink($theid);
			$ilink = wp_get_attachment_image_src( get_post_thumbnail_id($theid))[0];
			
			// Google doesn't allow & in the feed
			$the_title = "<![CDATA[".iz_feed_Admin::removeUnsafeXML($thetitle)."]]>";
			$description = "<![CDATA[".iz_feed_Admin::removeUnsafeXML($description)."]]>";
			// add product to array but don't add the parent of product variations
			if (!empty($sku)) $full_product_list[] = array(
			"id" => $theid,
			"sku" => $sku, 
			"price" => get_post_meta($theid, '_price', true) . " " . $currency,
			"availability" => $availability,
			"link" => "<![CDATA[".$link."]]>",
			"image_link" => "<![CDATA[".$ilink."]]>",
			"google_product_category" => 209,
			"brand" => "iZ-Sock",
			"condition" => "new",
			"adult" => "no",
			
			"is_bundle" => "no",
			"age_group" => "adult",
			"gender" => "unisex",
			"title" => $the_title,
			"description" => $description,
			);
		endwhile; wp_reset_query();
		// sort into alphabetical order, by title
		sort($full_product_list);
		return $full_product_list;
	}
	
	public function array_to_xml( $data, &$xml_data ) {
		foreach( $data as $key => $value ) {
			if( is_numeric($key) ){
				$key = 'item'.$key; //dealing with <0/>..<n/> issues
			}
			if( is_array($value) ) {
				$subnode = $xml_data->addChild($key);
				iz_feed_Admin::array_to_xml($value, $subnode);
			} else {
				$xml_data->addChild("$key",htmlspecialchars("$value"));
			}
		 }
		 return $xml_data;
	}
	
	public function create_xml_feed($filename = null){
		//based on google merchant settings
		$list = iz_feed_Admin::get_product_list();
		$prettyname = get_bloginfo("name");
		$description = get_bloginfo("description");
		$link = get_bloginfo("url");
		$storename = htmlspecialchars(get_bloginfo("name"));
		if(!$filename){
			$filename = time() . $storename .".xml";
		}
		$xml = fopen(ABSPATH . "/iz-feed/" . $filename, "w") or die("Unable to open file!");
			fwrite($xml, '<?xml version="1.0"?>');
			fwrite($xml, '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">\n'.'\n<channel>\n');
			fwrite($xml, "<title>".$prettyname."</title>\n");
			fwrite($xml, "<link>".$link."</link>\n");
			fwrite($xml, "<description>".$description."</description>\n");
			fwrite($xml, "");
			foreach ($list as $element) {
				fwrite($xml, "  <item>\n");
				foreach ($element as $child => $value) {
					fwrite($xml, "    <g:$child>$value</g:$child>\n");
				}
				fwrite($xml, "  </item>\n");
			}
			fwrite($xml, "</channel>\n");
			fwrite($xml, "</rss>");
			fclose($xml);
			return $filename;
		
	}
	
	public function create_json_feed(){
		//based on clerk settings
		
	}
	
	public function iz_admin_create_feed(){
		$user_id = get_current_user_id();
		$feed_name = $_POST["feed_name"];
		$feed_type = $_POST["feed_type"];
		$feed_active = $_POST["feed_active"];
		//Time to call a function that will create the desired feed
		switch($feed_type){
			case "XML":
				$feed = iz_feed_Admin::create_xml_feed();
			break;
			case "JSON":
				$feed = false;
				//iz_feed_Admin::create_json_feed();
			break;
			case "CSV":
				$feed = false;
			break;
			default:
				$feed = false;
			break;
		}
		if($feed_active == "on"){
			$active = 1;
		}else{
			$active = 0;
		}
		$link = get_bloginfo("url");
		if($feed){
			global $wpdb;
			$table_name = $wpdb->prefix . "iz_feed_settings";
			$wpdb->insert( 
			$table_name, 
				array( 
					'user_id' => $user_id, 
					'active' => $active, 
					'name' => $feed_name,
					'type' => $feed_type,
					'location' => $link."/iz-feed/".$feed,
					'filename' => $feed,
					'date_created' => current_time( 'mysql' ),
					'date_edited' => current_time( 'mysql' )
				) 
			);
		}
		wp_safe_redirect(admin_url('admin.php?page=iz-feed'));
	}
	
	

	

}
add_action('admin_post_iz_add_feed', array('iz_feed_Admin', 'iz_admin_create_feed'));
add_action('admin_menu', array('iz_feed_Admin', 'iz_feed_menu'));
