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
		return false;

	$parents = it_exchange_membership_addon_get_all_the_parents( $required_product_id );
	array_unshift( $parents, (int) $required_product_id );

	if ( ! empty( $_GET['it-exchange-sw-ajax'] ) )
		$customer_products = it_exchange_mpr_addon_ajax_get_customer_products( $user_id );
	else
		$customer_products = it_exchange_get_customer_products( $user_id );

	foreach ( $customer_products as $customer_product ) {
		if ( in_array( $customer_product['product_id'], $parents ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Getting customer products during an AJAX request doesn't work.
 *
 * So we have a custom function to overcome that.
 *
 * Basically we are just removing the check to it_exchange_is_page('confirmation')
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
	if ( ! empty( $args['transaction_method'] ) ) {
		$meta_query = array(
		  'key'   => '_it_exchange_transaction_method',
		  'value' => $args['transaction_method'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in transaction_status
	if ( ! empty( $args['transaction_status'] ) ) {
		$meta_query = array(
		  'key'   => '_it_exchange_transaction_status',
		  'value' => $args['transaction_status'],
		);
		$args['meta_query'][] = $meta_query;
	}

	// Fold in customer
	if ( ! empty( $args['customer_id'] ) ) {
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