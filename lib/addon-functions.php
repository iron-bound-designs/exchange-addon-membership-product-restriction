<?php
/**
 *
 * @package Exchange Addon Membership Product Restriction
 * @subpackage Lib
 * @since 1.0
 */

/**
 * Determine if a user has access to the required membership product.
 *
 * This includes the membership product, as well as all of its parents.
 *
 * @param $user_id int
 * @param $required_product_id int
 *
 * @return bool
 */
function it_exchange_mpr_addon_user_has_access_to_required_membership_product( $user_id, $required_product_id ) {
	if ( $user_id == 0 )
		return apply_filters( 'it_exchange_mpr_addon_user_has_access_to_required_membership_product', false, $user_id, $required_product_id );

	$all_parents = wp_cache_get( 'it_exchange_mpr_all_parents' );

	if ( false === $all_parents || !isset( $all_parents[$required_product_id] ) ) {
		if ( !is_array( $all_parents ) ) {
			$all_parents = array();
		}

		$all_parents[$required_product_id] = it_exchange_membership_addon_get_all_the_parents( $required_product_id );

		wp_cache_set( 'it_exchange_mpr_all_parents', $all_parents, '', 60 );
	}

	$parents = $all_parents[$required_product_id];

	if ( !is_array( $parents ) )
		$parents = array();

	array_unshift( $parents, (int) $required_product_id );

	$all_customer_products = wp_cache_get( 'it_exchange_mpr_all_customer_products' );

	if ( false === $all_customer_products || !isset( $all_customer_products[$user_id] ) ) {

		if ( !is_array( $all_customer_products ) ) {
			$all_customer_products = array();
		}

		$customer_memberships = it_exchange_membership_addon_get_customer_memberships( $user_id );

		if ( ! is_array( $customer_memberships ) ) {
			$customer_products = array();
		} else {

			$customer_products = array();

			foreach ( $customer_memberships as $transaction => $products ) {
				foreach ( $products as $product_id ) {
					$customer_products[] = array(
						'product_id' => $product_id
					);
				}
			}
		}

		$all_customer_products[$user_id] = $customer_products;

		wp_cache_set( 'it_exchange_mpr_all_customer_products', $all_customer_products, '', 60 );
	}

	$customer_products = $all_customer_products[$user_id];

	foreach ( $customer_products as $customer_product ) {
		if ( in_array( $customer_product['product_id'], $parents ) ) {
			return apply_filters( 'it_exchange_mpr_addon_user_has_access_to_required_membership_product', true, $user_id, $required_product_id );
		}
	}

	/**
	 * Filter whether the user has access to the required membership product.
	 *
	 * @param $has_access bool
	 * @param $user_id int
	 * @param $required_product_id int
	 */
	return apply_filters( 'it_exchange_mpr_addon_user_has_access_to_required_membership_product', false, $user_id, $required_product_id );
}

/**
 * Getting customer products during an AJAX request doesn't work.
 *
 * So we have a custom function to overcome that.
 *
 * Basically we are just removing the check to it_exchange_is_page('confirmation')
 *
 * @deprecated
 *
 * @param $user_id int
 *
 * @return array|mixed|void
 */
function it_exchange_mpr_addon_ajax_get_customer_products( $user_id ) {
	$args = array(
	  'numberposts' => - 1,
	  'customer_id' => $user_id,
	);

	$defaults = array(
	  'post_type' => 'it_exchange_tran',
	);

	$args = wp_parse_args( $args, $defaults );
	$args['meta_query'] = empty( $args['meta_query'] ) ? array() : $args['meta_query'];

	// Fold in transaction_method
	if ( !empty( $args['transaction_method'] ) ) {
		$meta_query = array(
		  'key'   => '_it_exchange_transaction_method',
		  'value' => $args['transaction_method'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in transaction_status
	if ( !empty( $args['transaction_status'] ) ) {
		$meta_query = array(
		  'key'   => '_it_exchange_transaction_status',
		  'value' => $args['transaction_status'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in customer
	if ( !empty( $args['customer_id'] ) ) {
		$meta_query = array(
		  'key'   => '_it_exchange_customer_id',
		  'value' => $args['customer_id'],
		  'type'  => 'NUMERIC',
		);
		$args['meta_query'][] = $meta_query;
	}

	$args = apply_filters( 'it_exchange_get_transactions_get_posts_args', $args );

	if ( $transactions = get_posts( $args ) ) {
		foreach ( $transactions as $key => $transaction ) {
			$transactions[$key] = it_exchange_get_transaction( $transaction );
		}
	}

	$transactions = apply_filters( 'it_exchange_get_transactions', $transactions, $args );

	$products = array();
	foreach ( $transactions as $transaction ) {

		// strip array values from each product to prevent ovewriting multiple purchases of same product
		$transaction_products = (array) array_values( it_exchange_get_transaction_products( $transaction ) );

		// Add transaction ID to each products array
		foreach ( $transaction_products as $key => $data ) {
			$transaction_products[$key]['transaction_id'] = $transaction->ID;
		}

		// Merge with previously queried
		$products = array_merge( $products, $transaction_products );
	}

	// Return
	return apply_filters( 'it_exchange_get_customer_products', $products, $user_id );
}

/**
 * Retrieve the purchase requirement message.
 *
 * @since 1.3
 *
 * @param $required_product_id int
 *
 * @return string
 */
function it_exchange_mpr_addon_get_purchase_requirement_message( $required_product_id ) {
	$product = it_exchange_get_product( $required_product_id );
	$title = $product->post_title;
	$url = get_permalink( $product->ID );

	$addon_settings = it_exchange_get_option( 'addon_mpr' );
	$open_new_tab = $addon_settings['product-link-new-tab'];
	$target = '';

	if ( ! empty( $open_new_tab ) ) {
		$target = ' target="_blank"';
	}

	$attr = '';
	/**
	 * Allow for additional attributes to be set in the a tag.
	 *
	 * @param $attr string
	 * @param $product IT_Exchange_Product
	 */
	$attr = apply_filters( 'it_exchange_mpr_addon_purchase_requirement_link_attr', $attr, $product );

	$link = "<a href=\"$url\" $target $attr>$title</a>";

	$default = sprintf( __( "Sorry, you need to have purchased the %s product to purchase this item.",
			IT_Exchange_Membership_Product_Restriction::SLUG ), $link );

	$message = $addon_settings['cannot-purchase-message'];

	if ( empty( $message ) ) {
		$message = $default;
	} else {
		$message = str_replace( "%product%", $link, $message );
	}

	/**
	 * Filters the purchase message that is outputted,
	 * if the customer does not have the required product.
	 *
	 * @param $message string The purchase message.
	 * @param $product IT_Exchange_Product The required product.
	 */
	return apply_filters( 'it_exchange_mpr_addon_purchase_requirement_notification_text', $message,  $product );
}