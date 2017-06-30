<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/GrineUlf/iZ-Product-Feed/
 * @since      2.0.0
 *
 * @package    Iz-product-feed
 * @subpackage Iz-product-feed/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    iz-product-feed
 * @subpackage iz-product-feed/public
 * @author     Mike Koopman <mike@blaesbjerg.com>
 */
class iz_feed_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		

	}

	public function iz_check_id($id){
		global $wpdb;
		$table_name = $wpdb->prefix . "iz_feed_settings";
		$row = $wpdb->get_row("SELECT * FROM " . $table_name . " WHERE filename = " . $id . " AND active = 1");
		if($row){
			return true;
		}else{
			return false;
		}
	}
	
	public function iz_get_products(){
		$full_product_list = array();
		
		$loop = new WP_Query( array( 'post_status' => 'publish', 'post_type' => array('product', 'product_variation'), 'posts_per_page' => -1, ) );
		while ( $loop->have_posts() ) : $loop->the_post();
			$theid = get_the_ID();
			$product = wc_get_product($theid);
			// its a variable product
			if( get_post_type() == 'product_variation' ){
				$parent_id = wp_get_post_parent_id($theid );
				$sku = get_post_meta($theid, '_sku', true );
				$thetitle = get_the_title( $parent_id);
				$description = get_post_field('post_content', $parent_id);
				$prod = wc_get_product($parent_id);
				$cats = $prod->get_category_ids();
				$ilink = wp_get_attachment_image_src( get_post_thumbnail_id($parent_id))[0];
	 
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
					}
				}
		// ****************** end error checking *****************
	 
			// its a simple product
			} else {
				$sku = get_post_meta($theid, '_sku', true );
				$thetitle = get_the_title();
				$description = get_post_field('post_content', $theid);
				$cats = $product->get_category_ids();
				$ilink = wp_get_attachment_image_src( get_post_thumbnail_id($theid))[0];
			}
			$link = get_permalink($theid);
			$term =  wp_get_post_terms( $theid, 'product_cat' );
			
			// add product to array but don't add the parent of product variations
			if (!empty($sku)) $full_product_list[] = array(
			"id" => $theid,
			"sku" => $sku, 
			"price" => get_post_meta($theid, '_price', true),
			"link" => $link,
			"image" => $ilink,
			"name" => $thetitle,
			"description" => htmlentities($description),
			"categories" => $cats,
			);
		endwhile; wp_reset_query();
		// sort into alphabetical order, by title
		sort($full_product_list);
		return $full_product_list;
	}
	
	public function iz_get_categories(){
		$categories = array();
		$taxonomy     = 'product_cat';
		$orderby      = 'name';  
		$show_count   = 0;      // 1 for yes, 0 for no
		$pad_counts   = 0;      // 1 for yes, 0 for no
		$hierarchical = 1;      // 1 for yes, 0 for no  
		$title        = '';  
		$empty        = 0;

		$args = array(
			 'taxonomy'     => $taxonomy,
			 'orderby'      => $orderby,
			 'show_count'   => $show_count,
			 'pad_counts'   => $pad_counts,
			 'hierarchical' => $hierarchical,
			 'title_li'     => $title,
			 'hide_empty'   => $empty
		);
		$all_categories = get_categories( $args );
		foreach ($all_categories as $cat) {
			$category_id = $cat->term_id;   
			$link = get_term_link( $category_id, 'product_cat' );			
			

			$args2 = array(
					'taxonomy'     => $taxonomy,
					'child_of'     => 0,
					'parent'       => $category_id,
					'orderby'      => $orderby,
					'show_count'   => $show_count,
					'pad_counts'   => $pad_counts,
					'hierarchical' => $hierarchical,
					'title_li'     => $title,
					'hide_empty'   => $empty
			);
			$sub_cats = get_categories( $args2 );
			$subcats = array();
			if($sub_cats) {				
				foreach($sub_cats as $sub_category) {
					$subcats[] = $sub_category->term_id;
				}   
			}
			$categories[] = array(
				"id" => $category_id,
				"name" => $cat->name,
				"url" => $link,
				"subcategories" => $subcats,
			);
		}
		return $categories;
	}
	
	public function iz_get_sales(){
		global $wpdb;
		$table_name = $wpdb->prefix . "posts";
		$query = "SELECT * FROM " . $table_name . " WHERE post_type = 'shop_order'";
		$orders = $wpdb->get_results($query);
		foreach($orders as $ord){
			$order = wc_get_order( $ord->ID );
			$items = $order->get_items();
			$email = $order->get_billing_email();
			$products = array();
			foreach ( $order->get_items() as $item_id => $item ){
				$products[] = wc_get_order_item_meta($item_id, '_product_id', true);
								
			}
			$customer = $order->get_customer_id();
			if(!$customer){
				$customer = 0;
			}
			$sales[] = array(
			"id" => $ord->ID,
			"products" => $products,
			"time" => strtotime($ord->post_date),
			"email" => $email,
			"customer" => $customer
			);
 		}
		return $sales;
	}
	
	public function iz_get_customers(){
		
	}
	
	public function iz_create_feed($id){
		//Get all the data, based on Clerk.io
		$products = iz_feed_Public::iz_get_products();
		$categories = iz_feed_Public::iz_get_categories();
		$sales = iz_feed_Public::iz_get_sales();
		//Customers is in beta at Clerk, disabled for now
		//$customers = iz_feed_Public::iz_get_customers();
		$created = time();
		$list = array("products" => $products, "categories" => $categories, "sales" => $sales, "created" => $created);
		$stuff = json_encode($list);
		echo $stuff;
	}

}
