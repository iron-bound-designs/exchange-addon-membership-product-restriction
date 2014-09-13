<?php
/**
 *
 * @package Membership Product Restriction
 * @subpackage Init
 * @since 1.0
 */

/**
 * Load the necessary product features
 */
require_once( IT_Exchange_Membership_Product_Restriction::$dir . "lib/productfeature/load.php" );

/**
 * Load our addon functions
 */
require_once( IT_Exchange_Membership_Product_Restriction::$dir . "lib/addon-functions.php" );

/**
 * Load our required hooks
 */
require_once( IT_Exchange_Membership_Product_Restriction::$dir . "lib/required-hooks.php" );

/**
 * Load the settings page.
 */
require_once( IT_Exchange_Membership_Product_Restriction::$dir . "lib/settings.php" );