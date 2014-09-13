<?php
/**
 * Load the admin settings page
 *
 * @author timothybjacobs
 * @since  8/22/14
 */

function it_exchange_mpr_addon_settings() {
	$settings = new IT_Exchange_MPR_Add_On_Settings();
	$settings->print_settings_page();
}

/**
 * Class IT_Exchange_mpr_Add_On_Settings
 */
class IT_Exchange_MPR_Add_On_Settings {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.1.0
	 */
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.1.0
	 */
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 0.1.0
	 */
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.1.0
	 */
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.1.0
	 */
	var $error_message;

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 *
	 * @since 1.0
	 */
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'membership-product-restriction-product-type' == $this->_current_add_on ) {
			add_action( 'it_exchange_save_add_on_settings_mpr', array(
				$this,
				'save_settings'
			) );
			do_action( 'it_exchange_save_add_on_settings_mpr' );
		}
	}

	/**
	 * Prints settings page
	 *
	 * @since 0.4.5
	 * @return void
	 */
	function print_settings_page() {
		$settings     = it_exchange_get_option( 'addon_mpr', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'     => apply_filters( 'it_exchange_add_on_mpr', 'it-exchange-add-on-mpr-settings' ),
			'action' => 'admin.php?page=it-exchange-addons&add-on-settings=membership-product-restriction-product-type',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-mpr' ) );

		if ( ! empty ( $this->status_message ) ) {
			ITUtility::show_status_message( $this->status_message );
		}
		if ( ! empty( $this->error_message ) ) {
			ITUtility::show_error_message( $this->error_message );
		}

		?>
		<div class="wrap">
			<h2><?php _e( 'Members Only Products Settings', IT_Exchange_Membership_Product_Restriction::SLUG ); ?></h2>

			<?php do_action( 'it_exchange_mpr_settings_page_top' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>
			<?php $form->start_form( $form_options, 'it-exchange-mpr-settings' ); ?>
			<?php do_action( 'it_exchange_mpr_settings_form_top' ); ?>
			<?php $this->get_form_table( $form, $form_values ); ?>
			<?php do_action( 'it_exchange_mpr_settings_form_bottom' ); ?>

			<p class="submit">
				<?php $form->add_submit( 'submit', array(
					'value' => __( 'Save Changes', IT_Exchange_Membership_Product_Restriction::SLUG ),
					'class' => 'button button-primary button-large'
				) ); ?>
			</p>

			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_mpr_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>
	<?php
	}

	/**
	 * Render the settings table
	 *
	 * @param ITForm $form
	 * @param array $settings
	 *
	 * @return void
	 */
	function get_form_table( $form, $settings = array() ) {

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $key => $var ) {
				$form->set_option( $key, $var );
			}
		}
		?>

		<div class="it-exchange-addon-settings it-exchange-mpr-addon-settings">
			<div>
				<label for="cannot-purchase-message">
					<?php _e( 'Purchase Requirement Message', IT_Exchange_Membership_Product_Restriction::SLUG ); ?>
					<span class="tip" title="<?php esc_attr_e( 'This message appears when a user cannot purchase a certain product, because they don\'t have the required membership. ', IT_Exchange_Membership_Product_Restriction::SLUG ); ?>">
						i
					</span>
				</label>
				<?php
				$form->add_text_box( 'cannot-purchase-message', array(
					'style' => 'max-width:600px;width:100%',
					'class' => 'large-text'
				) );
				?>
				<p><em><?php _e( "You can use <code>%product%</code> to substitute the required product title.", IT_Exchange_Membership_Product_Restriction::SLUG ); ?></em></p>
				<p><em>
						<?php printf( __( "Default Message: %s", IT_Exchange_Membership_Product_Restriction::SLUG ),
						__( 'Sorry, you need to have purchased the %product% product to purchase this item.', IT_Exchange_Membership_Product_Restriction::SLUG ) ); ?>
					</em>
				</p>
			</div>

			<div>
				<label for="product-link-new-tab">
					<?php _e( "Open Required Product Link in New Tab", IT_Exchange_Membership_Product_Restriction::SLUG ); ?>
				</label>

				<p><?php $form->add_check_box( 'product-link-new-tab' ); ?></p>
			</div>
		</div>
	<?php
	}

	/**
	 * Save settings
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function save_settings() {
		$defaults   = it_exchange_get_option( 'addon_mpr' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-mpr-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', IT_Exchange_Membership_Product_Restriction::SLUG );

			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_mpr_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_mpr', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', IT_Exchange_Membership_Product_Restriction::SLUG ) );
		} else if ( $errors ) {
			$errors              = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', IT_Exchange_Membership_Product_Restriction::SLUG );
		}
	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_form_errors( $values ) {

		$errors = array();

		return $errors;
	}
}