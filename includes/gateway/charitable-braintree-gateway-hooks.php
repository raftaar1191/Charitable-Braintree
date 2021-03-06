<?php
/**
 * Charitable Braintree Gateway Hooks.
 *
 * @package   Charitable Braintree/Hooks/Gateway
 * @copyright Copyright (c) 2019, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 * @version   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register our new gateway.
 *
 * @see Charitable_Gateway_Braintree::register_gateway()
 */
add_filter( 'charitable_payment_gateways', array( 'Charitable_Gateway_Braintree', 'register_gateway' ) );

/**
 * Set up Braintree in the donation form.
 *
 * @see Charitable_Gateway_Braintree::setup_scripts()
 */
add_action( 'charitable_form_after_fields', array( 'Charitable_Gateway_Braintree', 'maybe_setup_scripts_in_donation_form' ) );

/**
 * Maybe enqueue the Braintree scripts after a campaign loop, if modal donations are in use.
 *
 * @see Charitable_Gateway_Braintree::maybe_setup_scripts_in_campaign_loop()
 */
add_action( 'charitable_campaign_loop_after', array( 'Charitable_Gateway_Braintree', 'maybe_setup_scripts_in_campaign_loop' ) );

/**
 * Set up Braintree payment fields.
 *
 * @see Charitable_Gateway_Braintree::setup_braintree_payment_fields()
 */
add_action( 'charitable_donation_form_gateway_fields', array( 'Charitable_Gateway_Braintree', 'setup_braintree_payment_fields' ), 10, 2 );

/**
 * Validate the donation form submission before processing.
 *
 * @see Charitable_Gateway_Braintree::validate_donation()
 */
add_filter( 'charitable_validate_donation_form_submission_gateway', array( 'Charitable_Gateway_Braintree', 'validate_donation' ), 10, 3 );

/**
 * Also make sure that the Braintree token is picked up in the values array.
 *
 * @see Charitable_Gateway_Braintree::set_submitted_braintree_token()
 */
add_filter( 'charitable_donation_form_submission_values', array( 'Charitable_Gateway_Braintree', 'set_submitted_braintree_token' ), 10, 2 );

/**
 * Process the donation.
 *
 * @see Charitable_Gateway_Braintree::process_donation()
 */
add_filter( 'charitable_process_donation_braintree', array( 'Charitable_Gateway_Braintree', 'process_donation' ), 10, 3 );

/**
 * Set up webhook handling.
 *
 * @see Charitable_Braintree_Webhook_Processor::process()
 */
add_action( 'charitable_process_ipn_braintree', array( 'Charitable_Braintree_Webhook_Processor', 'process' ) );

/**
 * Refund a donation from the dashboard.
 *
 * @see Charitable_Gateway_Braintree::refund_donation_from_dashboard()
 */
add_action( 'charitable_process_refund_braintree', [ 'Charitable_Gateway_Braintree', 'refund_donation_from_dashboard' ] );

/**
 * Returns whether a subscription can be cancelled.
 *
 * @see Charitable_Gateway_Braintree::is_subscription_cancellable()
 */
add_filter( 'charitable_recurring_can_cancel_braintree', [ 'Charitable_Gateway_Braintree', 'is_subscription_cancellable' ], 10, 2 );

/**
 * Cancel a subscription from the dashboard.
 *
 * @see Charitable_Gateway_Braintree::cancel_subscription()
 */
add_action( 'charitable_process_cancellation_braintree', [ 'Charitable_Gateway_Braintree', 'cancel_subscription' ], 10, 2 );
