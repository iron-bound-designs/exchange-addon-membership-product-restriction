<?php
/*
Plugin Name: iThemes Exchange Membership Product Restriction Add-on
Plugin URI: http://www.ironbounddesigns.com
Description: Require users to have purchased a certain membership product type to purchase selected products
Version: 0.1
Author: Iron Bound Designs
Author URI: http://www.ironbounddesigns.com
License: GPL v2
Domain: ibd-exchange-addon-membership-product-restriction
*/

/**
 * This registers our plugin as an events add-on
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_register_membership_product_restriction_addon() {
	$options = array(
	  'name'        => __( 'Membership Product Restriction', 'ibd-exchange-addon-membership-product-restriction' ),
	  'description' => __( 'Limit product purchase to certain membership products', 'ibd-exchange-addon-membership-product-restriction' ),
	  'author'      => 'Iron Bound Designs',
	  'author_url'  => 'http://www.ironbounddesigns.com',
	  'file'        => dirname( __FILE__ ) . '/init.php',
	  'category'    => 'product-feature',
	  'basename'    => plugin_basename( __FILE__ ),
	  'labels'      => array(
		'singular_name' => __( 'Membership Product Restriction', 'ibd-exchange-addon-membership-product-restriction' ),
	  )
	);
	it_exchange_register_addon( 'membership-product-restriction-product-type', $options );
}

add_action( 'it_exchange_register_addons', 'it_exchange_register_membership_product_restriction_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses load_plugin_textdomain()
 * @since 1.0.3
 * @return void
 */
function it_exchange_membership_product_restriction_set_textdomain() {
	load_plugin_textdomain( 'ibd-exchange-addon-membership-product-restriction', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}

add_action( 'plugins_loaded', 'it_exchange_membership_product_restriction_set_textdomain' );

/**
 * Class IT_Exchange_Membership_Product_Restriction
 */
class IT_Exchange_Membership_Product_Restriction {
	/**
	 *
	 */
	const SLUG = 'ibd-exchange-addon-membership-product-restriction';

	/**
	 * @var string
	 */
	static $dir;

	/**
	 * @var string
	 */
	static $url;

	/**
	 *
	 */
	public function __construct() {
		self::$dir = plugin_dir_path( __FILE__ );
		self::$url = plugin_dir_url( __FILE__ );
		spl_autoload_register( array( "IT_Exchange_Membership_Product_Restriction", "autoload" ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
	}

	/**
	 *
	 */
	public function scripts_and_styles() {
	}

	/**
	 * Autoloader
	 *
	 * @param $class_name string
	 */
	public static function autoload( $class_name ) {
		if ( substr( $class_name, 0, 15 ) != "IT_Exchange_MPR" ) {
			$path = self::$dir . "lib/classes";
			$class = strtolower( $class_name );

			$name = str_replace( "_", "-", $class );
		}
		else {
			$path = self::$dir . "lib";

			$class = substr( $class_name, 15 );
			$class = strtolower( $class );

			$parts = explode( "_", $class );
			$name = array_pop( $parts );

			$path .= implode( "/", $parts );
		}

		$path .= "/class.$name.php";

		if ( file_exists( $path ) ) {
			require( $path );

			return;
		}

		if ( file_exists( str_replace( "class.", "abstract.", $path ) ) ) {
			require( str_replace( "class.", "abstract.", $path ) );

			return;
		}

		if ( file_exists( str_replace( "class.", "interface.", $path ) ) ) {
			require( str_replace( "class.", "interface.", $path ) );

			return;
		}
	}
}

new IT_Exchange_Membership_Product_Restriction();