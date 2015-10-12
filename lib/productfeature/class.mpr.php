<?php

/**
 *
 * @package Exchange Addon Membership Product Restriction
 * @subpackage Product Feature
 * @since 1.0
 */
class IT_Exchange_MPR_ProductFeature_MPR extends IT_Exchange_Product_Feature_Abstract {

	/**
	 * @var array Of product feature data
	 */
	protected $feature_data = array();

	/**
	 * @var int
	 */
	protected $post_id = - 1;

	/**
	 * Constructor.
	 */
	function __construct() {
		if ( isset( $_GET['post'] ) )
			$this->post_id = $_GET['post'];

		$args = array(
		  'slug'          => 'membership-product-restriction',
		  'metabox_title' => __( 'Membership Product Restriction', IT_Exchange_Membership_Product_Restriction::SLUG )
		);

		$this->metabox_title = $args['metabox_title'];

		parent::__construct( $args );

		$this->feature_data = it_exchange_get_product_feature( $this->post_id, $this->slug );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.7.27
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	function print_metabox( $post ) {
		$membership_products = it_exchange_get_products( array( 'product_type' => 'membership-product-type', 'numberposts' => - 1, 'show_hidden' => true ) );
		wp_enqueue_script( 'it-exchange-membership-product-restriction-single-edit', IT_Exchange_Membership_Product_Restriction::$url . "assets/js/single-edit.js", array( 'jquery' ) );

		if ( $this->feature_data['free_after_date'] )
			$date = date( get_option( 'date_format', 'm/d/Y' ), $this->feature_data['free_after_date'] );
		else
			$date = '';

		if ( $this->feature_data['non_members_free_after_date'] )
			$nm_date = date( get_option( 'date_format', 'm/d/Y' ), $this->feature_data['non_members_free_after_date'] );
		else
			$nm_date = '';

		?>
		<p><?php _e( "Use these options to restrict the purchase of this product to users who have purchased a desired product.", IT_Exchange_Membership_Product_Restriction::SLUG ); ?></p>
		<div>
		    <label>
			    <input type="checkbox" id="mpr-addon-options-enable" name="mpr_addon[enable]" <?php checked( $this->feature_data['enable'] ); ?>>
			    <?php _e( "Enable membership product restrictions for this product?", IT_Exchange_Membership_Product_Restriction::SLUG ); ?>
		    </label>
	    </div>

		<div id="mpr-addon-options-pane" class="<?php if ( ! $this->feature_data['enable'] ) echo "hide-if-js"; ?>">
			<label for="mpr-addon-select-membership"><?php _e( "Select membership product", IT_Exchange_Membership_Product_Restriction::SLUG ); ?></label>
			<select name="mpr_addon[membership_product]" id="mpr-addon-select-membership">
				<option value=""><?php _e( 'Select a Membership', 'it-l10n-exchange-addon-membership' ); ?></option>
				<?php foreach ( $membership_products as $membership ) : ?>
					<option value="<?php echo $membership->ID ?>" <?php selected( $this->feature_data['membership_product'], $membership->ID ); ?>><?php echo $membership->post_title; ?></option>
				<?php endforeach; ?>
			</select>

			<p><?php _e( "How would you like to handle the restriction?", IT_Exchange_Membership_Product_Restriction::SLUG ); ?></p>

			<label>
				<input type="radio" name="mpr_addon[action]" class="mpr-addon-action" id="mpr-addon-free-for-member-select" <?php checked( $this->feature_data['action'], 'free-for-member' ); ?> value="free-for-member">
				<?php _e( "Make this product free for members", IT_Exchange_Membership_Product_Restriction::SLUG ); ?>
			</label>

			<label>
				<input type="radio" name="mpr_addon[action]" class="mpr-addon-action" id="mpr-addon-hide-from-store-select" <?php checked( $this->feature_data['action'], 'members-only' ); ?> value="members-only">
				<?php _e( "Only let members purchase this product", IT_Exchange_Membership_Product_Restriction::SLUG ); ?>
			</label>

			<label>
				<input type="radio" name="mpr_addon[action]" class="mpr-addon-action" id="mpr-addon-additional-fee-select" <?php checked( $this->feature_data['action'], 'additional-fee' ); ?> value="additional-fee">
				<?php _e( "Additional fee for non-members", IT_Exchange_Membership_Product_Restriction::SLUG ); ?>
			</label>

			<div id="mpr-addon-additional-fee-container" class="<?php if ( $this->feature_data['action'] != 'additional-fee' ) echo "hide-if-js"; ?>">
				<label for="mpr-addon-additional-fee"><?php _e( "Additional fee for non-members", IT_Exchange_Membership_Product_Restriction::SLUG ); ?></label>
				<input type="text" id="mpr-addon-additional-fee" name="mpr_addon[additional_fee]" value="<?php echo $this->feature_data['additional_fee']; ?>">
			</div>

			<div id="mpr-addon-hide-from-store-container" class="<?php if ( $this->feature_data['action'] != 'members-only' ) echo "hide-if-js"; ?>">
				<label>
					<input type="checkbox" name="mpr_addon[hide_from_store]" <?php checked( $this->feature_data['hide_from_store'], true ); ?>>
					<?php _e( "Hide this product from the store for non-members?", IT_Exchange_Membership_Product_Restriction::SLUG ); ?>
				</label>
			</div>

			<div id="mpr-addon-free-for-member-container" class="<?php if ( $this->feature_data['action'] != 'free-for-member' ) echo "hide-if-js"; ?>">
				<label for="mpr-addon-free-after-date"><?php _e( "Only make the product free for members after a certain date", IT_Exchange_Membership_Product_Restriction::SLUG ); ?></label>
				<input type="text" class="" name="mpr_addon[free_after_date]" id="mpr-addon-free-after-date" value="<?php echo $date; ?>">
			</div>

			<div id="mpr-addon-free-for-non-members-container" class="<?php if ( $this->feature_data['action'] != 'free-for-member' ) echo "hide-if-js"; ?>">
				<label for="mpr-addon-non-members-free-after-date"><?php _e( "Make the product free for non-members too after a certain date", IT_Exchange_Membership_Product_Restriction::SLUG ); ?></label>
				<input type="text" class="" name="mpr_addon[non_members_free_after_date]" id="mpr-addon-non-members-free-after-date" value="<?php echo $nm_date; ?>">
			</div>
		</div>

	<?php
	}

	/**
	 * This saves the value
	 *
	 * @since 1.7.27
	 *
	 * @return void
	 */
	function save_feature_on_product_save() {
		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		$data = $_POST['mpr_addon'];
		$new_values = array();

		if ( it_exchange_str_true( $data['enable'] ) )
			$new_values['enable'] = true;
		else
			$new_values['enable'] = false;

		$membership_product = it_exchange_get_product( $data['membership_product'] );

		if ( false === $membership_product || $membership_product->product_type != 'membership-product-type' )
			$new_values['membership_product'] = false;
		else
			$new_values['membership_product'] = $data['membership_product'];

		if ( in_array( $data['action'], array( 'free-for-member', 'members-only', 'additional-fee' ) ) )
			$new_values['action'] = $data['action'];
		else
			$new_values['action'] = 'members-only';

		$new_values['additional_fee'] = doubleval( $data['additional_fee'] );

		if ( it_exchange_str_true( $data['hide_from_store'] ) )
			$new_values['hide_from_store'] = true;
		else
			$new_values['hide_from_store'] = false;

		/*
		 * Start date parsing
		 */

		// Get the user's option set in WP General Settings
		$wp_date_format = get_option( 'date_format', 'm/d/Y' );

		$date_val = $data['free_after_date'];

		// strtotime requires formats starting with day to be separated by - and month separated by /
		if ( 'd' == substr( $wp_date_format, 0, 1 ) )
			$date_val = str_replace( '/', '-', $date_val );

		// Transfer to epoch
		if ( $epoch = strtotime( $date_val ) ) {

			// Returns an array with values of each date segment
			$date = date_parse( $date_val );

			// Confirms we have a legitimate date
			if ( checkdate( $date['month'], $date['day'], $date['year'] ) )
				$new_values['free_after_date'] = $epoch;
		}

		if ( ! isset( $new_values['free_after_date'] ) )
			$new_values['free_after_date'] = false;

		// ------- free after for non-members -------

		$date_val_nm = $data['non_members_free_after_date'];

		// strtotime requires formats starting with day to be separated by - and month separated by /
		if ( 'd' == substr( $wp_date_format, 0, 1 ) )
			$date_val_nm = str_replace( '/', '-', $date_val_nm );

		// Transfer to epoch
		if ( $epoch = strtotime( $date_val_nm ) ) {

			// Returns an array with values of each date segment
			$date_nm = date_parse( $date_val_nm );

			// Confirms we have a legitimate date
			if ( checkdate( $date_nm['month'], $date_nm['day'], $date_nm['year'] ) )
				$new_values['non_members_free_after_date'] = $epoch;
		}

		if ( ! isset( $new_values['non_members_free_after_date'] ) )
			$new_values['non_members_free_after_date'] = false;

		/*
		 * End date parsing
		 */

		update_post_meta( $product_id, '_mpr_addon_hide_from_store', ( $new_values['enable'] && $new_values['action'] == 'members-only' ) ? $new_values['hide_from_store'] : false );

		it_exchange_update_product_feature( $product_id, $this->slug, $new_values );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.7.27
	 *
	 * @param integer $product_id the product id
	 * @param array $new_values the new value
	 * @param array $options
	 *
	 * @return boolean
	 */
	function save_feature( $product_id, $new_values, $options = array() ) {
		if ( ! it_exchange_get_product( $product_id ) ) {
			return false;
		}

		$existing_data = get_post_meta( $product_id, '_it-exchange-mpr-data', true );
		$data = ITUtility::merge_defaults( $new_values, $existing_data );

		return update_post_meta( $product_id, '_it-exchange-mpr-data', $data );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.7.27
	 *
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array $options
	 *
	 * @return string product feature
	 */
	function get_feature( $existing, $product_id, $options = array() ) {
		$raw_meta = get_post_meta( $product_id, '_it-exchange-mpr-data', true );

		if ( ! isset( $raw_meta['enable'] ) )
			$raw_meta['enable'] = false;

		if ( ! isset( $raw_meta['membership_product'] ) )
			$raw_meta['membership_product'] = false;

		if ( ! isset( $raw_meta['action'] ) )
			$raw_meta['action'] = 'members-only';

		if ( ! isset( $raw_meta['additional_fee'] ) )
			$raw_meta['additional_fee'] = "";

		if ( ! isset( $raw_meta['hide_from_store'] ) )
			$raw_meta['hide_from_store'] = false;

		if ( ! isset( $raw_meta['free_after_date'] ) )
			$raw_meta['free_after_date'] = false;

		if ( ! isset( $raw_meta['non_members_free_after_date'] ) )
			$raw_meta['non_members_free_after_date'] = false;

		$raw_meta['free_after_date'] = apply_filters( 'it_exchange_mpr_addon_free_after_date', $raw_meta['free_after_date'], $product_id );
		$raw_meta['non_members_free_after_date'] = apply_filters( 'it_exchange_mpr_addon_non_members_free_after_date', $raw_meta['non_members_free_after_date'], $product_id );

		if ( ! isset( $options['field'] ) ) // if we aren't looking for a particular field
			return $raw_meta;

		$field = $options['field'];

		if ( isset( $raw_meta[$field] ) ) { // if the field exists with that name just return it
			return $raw_meta[$field];
		}
		else if ( strpos( $field, "." ) !== false ) { // if the field name was passed using array dot notation
			$pieces = explode( '.', $field );
			$context = $raw_meta;
			foreach ( $pieces as $piece ) {
				if ( ! is_array( $context ) || ! array_key_exists( $piece, $context ) ) {
					// error occurred
					return null;
				}
				$context = & $context[$piece];
			}

			return $context;
		}
		else {
			return null; // we didn't find the data specified
		}
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.7.27
	 *
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @param array $options
	 *
	 * @return boolean
	 */
	function product_has_feature( $result, $product_id, $options = array() ) {
		if ( false === it_exchange_product_supports_feature( $product_id, $this->slug ) )
			return false;

		return (boolean) it_exchange_get_product_feature( $product_id, $this->slug, array( 'field' => 'enable' ) );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.7.27
	 *
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @param array $options
	 *
	 * @return boolean
	 */
	function product_supports_feature( $result, $product_id, $options = array() ) { // Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, $this->slug ) )
			return false;

		return true;
	}
}