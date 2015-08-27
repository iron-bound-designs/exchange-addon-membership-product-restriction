<?php
/**
 *
 * @package Membership Product Restriction
 * @subpackage Lib
 * @since 1.0
 */

/**
 * Set the price of the membership product to 0 if
 * the membership product restriction is set free-for-member
 *
 * @param $incoming string Existing price
 * @param $product_id int
 * @param $options array
 *
 * @return string
 */
function it_exchange_mpr_addon_make_product_free_for_members( $incoming, $product_id, $options ) {
	if ( is_admin() || ! it_exchange_product_has_feature( $product_id, 'membership-product-restriction' ) )
		return $incoming;

	$feature = it_exchange_get_product_feature( $product_id, 'membership-product-restriction' );

	if ( $feature['action'] != 'free-for-member' )
		return $incoming;

	if ( ! it_exchange_mpr_addon_user_has_access_to_required_membership_product( get_current_user_id(), $feature['membership_product'] ) ) {
		if ( ! $feature['non_members_free_after_date'] )
			return $incoming;

		if ( $feature['non_members_free_after_date'] < time() )
			return 0;

		return $incoming;
	}

	if ( ! $feature['free_after_date'] ) // if this is a regular free product, and not free after a certain date
		return 0;

	if ( $feature['free_after_date'] < time() ) // if we are past the free after date
		return 0;

	return $incoming;
}

add_filter( 'it_exchange_get_product_feature_base-price', 'it_exchange_mpr_addon_make_product_free_for_members', 10, 3 );

/**
 * Add the additional fee if the membership product restriction
 * is set to additional-fee
 *
 * @param $incoming string Existing price
 * @param $product_id int
 * @param $options array
 *
 * @return string
 */
function it_exchange_mpr_addon_add_additional_fee_for_non_members( $incoming, $product_id, $options ) {
	if ( is_admin() || ! it_exchange_product_has_feature( $product_id, 'membership-product-restriction' ) )
		return $incoming;

	$feature = it_exchange_get_product_feature( $product_id, 'membership-product-restriction' );

	if ( $feature['action'] != 'additional-fee' )
		return $incoming;

	if ( ! it_exchange_mpr_addon_user_has_access_to_required_membership_product( get_current_user_id(), $feature['membership_product'] ) )
		$incoming += $feature['additional_fee'];

	return $incoming;
}

add_filter( 'it_exchange_get_product_feature_base-price', 'it_exchange_mpr_addon_add_additional_fee_for_non_members', 10, 3 );

/**
 * If selected, hide products for non-members.
 *
 * @param $query WP_Query
 */
function it_exchange_mpr_addon_hide_product_for_non_members( $query ) {
	if ( $query->get( 'posts_per_page' ) != -1 )
		return;

	if ( ! it_exchange_is_page( 'store' ) )
		return;

	if ( ! isset( $query->query['post_type'] ) || $query->query['post_type'] != 'it_exchange_prod' )
		return;

	if ( isset( $query->query['meta_key'] ) && $query->query['meta_key'] == '_mpr_addon_hide_from_store' )
		return;

	$args = array(
	  'numberposts' => - 1,
	  'post_type'   => 'it_exchange_prod',
	  'meta_key'    => '_mpr_addon_hide_from_store',
	  'meta_value'  => true,
	);
	$product_query = new WP_Query( $args );

	$product_posts = $product_query->get_posts();

	foreach ( $product_posts as $post ) {
		if ( ! it_exchange_mpr_addon_user_has_access_to_required_membership_product( get_current_user_id(), it_exchange_get_product_feature( $post->ID, 'membership-product-restriction', array( 'field' => 'membership_product' ) ) ) ) {
			$query->query_vars['post__not_in'][] = $post->ID;
		}
	}
}

add_action( 'pre_get_posts', 'it_exchange_mpr_addon_hide_product_for_non_members' );

/**
 * Register our purchase requirement for general checkout
 *
 * @since 1.0
 *
 * @return void
 */
function it_exchange_mpr_addon_register_checkout_purchase_requirements() {
	if ( is_admin() )
		return;

	if ( ! it_exchange_is_page( 'checkout' ) || it_exchange_in_superwidget() )
		return;

	$products = it_exchange_get_cart_products();

	error_log('here');

	foreach ( $products as $product ) {
		if ( ! is_array( $product ) )
			$product = array( 'product_id' => $product->ID );

		if ( ! it_exchange_product_has_feature( $product['product_id'], 'membership-product-restriction' ) )
			continue;

		$feature = it_exchange_get_product_feature( $product['product_id'], 'membership-product-restriction' );

		if ( $feature['action'] != 'members-only' )
			continue;

		$target_membership_product = it_exchange_get_product( $feature['membership_product'] );

		if ( ! $target_membership_product )
			continue;

		$purchased = it_exchange_mpr_addon_user_has_access_to_required_membership_product( get_current_user_id(), $target_membership_product->ID );

		if ( $purchased === true ) {
			continue;
		}
		else {
			unset( $GLOBALS['it_exchange']['purchase-requirements']['membership-product-restriction-sw'] );

			$args = array(
			  'priority'         => 5,
			  'requirement-met'  => '__return_false',
			  'sw-template-part' => 'membership-product-restriction',
			  'notification'     => it_exchange_mpr_addon_get_purchase_requirement_message( $target_membership_product->ID )
			);

			/**
			 * Filter the args that are passed to the purchase requirement for checkout.
			 *
			 * @param $args array
			 */
			$args = apply_filters( 'it_exchange_mpr_addon_checkout_purchase_requirements', $args );

			it_exchange_register_purchase_requirement( 'membership-product-restriction', $args );
		}
	}
}

add_action( 'template_redirect', 'it_exchange_mpr_addon_register_checkout_purchase_requirements', 1 );

/**
 * Register our purchase requirement for Super Widget
 *
 * @since 1.0
 *
 * @return void
 */
function it_exchange_mpr_addon_register_sw_purchase_requirements() {
	$args = array(
	  'priority'         => 5,
	  'requirement-met'  => 'it_exchange_mpr_addon_sw_purchase_requirements_callback',
	  'sw-template-part' => 'membership-product-restriction',
	  'notification'     => __( "Sorry, you need to have purchased another product in order to purchase this item.", IT_Exchange_Membership_Product_Restriction::SLUG ) // this should never be displayed
	);

	/**
	 * Filter the args that are passed to the purchase requirement for super widget.
	 *
	 * @param $args array
	 */
	$args = apply_filters( 'it_exchange_mpr_addon_sw_purchase_requirements', $args );

	it_exchange_register_purchase_requirement( 'membership-product-restriction-sw', $args );
}

add_action( 'init', 'it_exchange_mpr_addon_register_sw_purchase_requirements', 1 );

/**
 * Determine whether or not the current product fulfils the purchase requirement
 *
 * @since 1.0
 *
 * @return boolean
 */
function it_exchange_mpr_addon_sw_purchase_requirements_callback() {

	if ( isset($_GET['sw-product'])) {
		$post_id = $_GET['sw-product'];
	} elseif (isset($GLOBALS['it_exchange']['product'])) {
		$post_id = $GLOBALS['it_exchange']['product']->ID;
	} elseif ( $GLOBALS['post'] instanceof WP_Post ) {
		$post_id = $GLOBALS['post']->ID;
	} else {
		error_log( 'Error determining current product.' );

		return true;
	}

	if ( ! it_exchange_product_has_feature( $post_id, 'membership-product-restriction' ) )
		return true;

	$feature = it_exchange_get_product_feature( $post_id, 'membership-product-restriction' );

	if ( $feature['action'] != 'members-only' )
		return true;

	$target_membership_product = it_exchange_get_product( $feature['membership_product'] );

	if ( ! $target_membership_product )
		return true;

	$purchased = it_exchange_mpr_addon_user_has_access_to_required_membership_product( get_current_user_id(), $target_membership_product->ID );

	if ( $purchased === true )
		return true;

	return false;
}

/**
 * For some reason, our SW state isn't always added to the white list.
 * This forces our SW state into the white list.
 *
 * @param $existing array
 *
 * @return array
 */
function it_exchange_mpr_addon_register_sw_valid_state( $existing ) {
	if ( ! in_array( 'membership-product-restriction', $existing ) ) {
		$existing[] = 'membership-product-restriction';
	}

	return $existing;
}

add_filter( 'it_exchange_super_widget_valid_states', 'it_exchange_mpr_addon_register_sw_valid_state' );

/**
 * Register our template paths
 *
 * @param array $paths existing template paths
 *
 * @return array
 */
function it_exchange_mpr_addon_add_template_paths( $paths = array() ) {
	$paths[] = IT_Exchange_Membership_Product_Restriction::$dir . "lib/templates";

	return $paths;
}

add_filter( 'it_exchange_possible_template_paths', 'it_exchange_mpr_addon_add_template_paths' );