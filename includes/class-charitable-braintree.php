<?php
/**
 * The main Charitable Braintree class.
 *
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package   Charitable Braintree
 * @copyright Copyright (c) 2019, Eric Daams
 * @license   http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @version   1.0.0
 * @since     1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Charitable_Braintree' ) ) :

	/**
	 * Charitable_Braintree
	 *
	 * @since 1.0.0
	 */
	class Charitable_Braintree {

		/** Plugin version. */
		const VERSION = '1.0.0-alpha.2';

		/** The extension name. */
		const NAME = 'Charitable Braintree';

		/** The extension author. */
		const AUTHOR = 'Studio 164a';

		/**
		 * Single static instance of this class.
		 *
		 * @since 1.0.0
		 *
		 * @var   Charitable_Braintree
		 */
		private static $instance = null;

		/**
		 * The root file of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		private $plugin_file;

		/**
		 * The root directory of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		private $directory_path;

		/**
		 * The root directory of the plugin as a URL.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		private $directory_url;

		/**
		 * Create class instance.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin_file Absolute path to the main plugin file.
		 */
		public function __construct( $plugin_file ) {
			$this->plugin_file    = $plugin_file;
			$this->directory_path = plugin_dir_path( $plugin_file );
			$this->directory_url  = plugin_dir_url( $plugin_file );

			add_action( 'charitable_start', array( $this, 'start' ), 6 );
		}

		/**
		 * Returns the original instance of this class.
		 *
		 * @since  1.0.0
		 *
		 * @return Charitable
		 */
		public static function get_instance() {
			return self::$instance;
		}

		/**
		 * Run the startup sequence on the charitable_start hook.
		 *
		 * This is only ever executed once.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function start() {
			if ( $this->started() ) {
				return;
			}

			self::$instance = $this;

			$this->load_dependencies();

			$this->maybe_start_admin();

			$this->maybe_start_public();

			$this->setup_licensing();

			$this->setup_i18n();

			$this->attach_hooks_and_filters();

			/**
			 * Init classes that need to be started.
			 */
			new Charitable_Braintree_Fields();

			/**
			 * Do something when the plugin is first started.
			 *
			 * @since 1.0.0
			 *
			 * @param Charitable_Braintree $plugin This class instance.
			 */
			do_action( 'charitable_braintree_start', $this );
		}

		/**
		 * Include necessary files.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function load_dependencies() {
			$includes_dir = $this->get_path( 'includes' );

			/* Vendor autoload */
			require_once( $this->get_path( 'directory' ) . 'vendor/autoload.php' );

			/* Interfaces */
			require_once( $includes_dir . 'interfaces/interface-charitable-braintree-gateway-processor.php' );

			/* Abstracts */
			require_once( $includes_dir . 'abstracts/class-charitable-braintree-gateway-processor.php' );

			/* Core */
			require_once( $includes_dir . 'charitable-braintree-core-functions.php' );

			/* Deprecated */
			require_once( $includes_dir . 'deprecated/class-charitable-braintree-deprecated.php' );

			/* Fields */
			require_once( $includes_dir . 'fields/class-charitable-braintree-fields.php' );

			/* Gateways */
			require_once( $includes_dir . 'gateway/class-charitable-braintree-gateway-processor-one-time.php' );
			require_once( $includes_dir . 'gateway/class-charitable-braintree-gateway-processor-recurring.php' );
			require_once( $includes_dir . 'gateway/class-charitable-braintree-plans.php' );
			require_once( $includes_dir . 'gateway/class-charitable-braintree-webhook-processor.php' );
			require_once( $includes_dir . 'gateway/class-charitable-gateway-braintree.php' );
			require_once( $includes_dir . 'gateway/charitable-braintree-gateway-hooks.php' );

			/* Upgrades */
			require_once( $includes_dir . 'upgrades/class-charitable-braintree-upgrade.php' );
		}

		/**
		 * Load the admin-only functionality.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function maybe_start_admin() {
			if ( ! is_admin() ) {
				return;
			}

			require_once( $this->get_path( 'includes' ) . 'admin/class-charitable-braintree-admin.php' );

			new Charitable_Braintree_Admin();
		}

		/**
		 * Load the public-only functionality.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function maybe_start_public() {
			require_once( $this->get_path( 'includes' ) . 'public/class-charitable-braintree-template.php' );
		}

		/**
		 * Set up licensing for the extension.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function setup_licensing() {
			charitable_get_helper( 'licenses' )->register_licensed_product(
				Charitable_Braintree::NAME,
				Charitable_Braintree::AUTHOR,
				Charitable_Braintree::VERSION,
				$this->plugin_file
			);
		}

		/**
		 * Set up the internationalisation for the plugin.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function setup_i18n() {
			if ( class_exists( 'Charitable_i18n' ) ) {

				require_once( $this->get_path( 'includes' ) . 'i18n/class-charitable-braintree-i18n.php' );

				Charitable_Braintree_i18n::get_instance();
			}
		}

		/**
		 * Set up hooks and filters.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function attach_hooks_and_filters() {
			/**
			 * Set up scripts & styles.
			 */
			add_action( 'wp_enqueue_scripts', array( $this, 'setup_scripts' ), 11 );

			/**
			 * Set up upgrade process.
			 */
			// add_action( 'admin_notices', array( Charitable_Braintree_Upgrade::get_instance(), 'add_upgrade_notice' ) );
			// add_action( 'init', array( Charitable_Braintree_Upgrade::get_instance(), 'do_immediate_upgrades' ), 5 );
		}

		/**
		 * Set up the scripts.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function setup_scripts() {
			if ( is_admin() ) {
				return;
			}

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$version = '';
				$suffix  = '';
			} else {
				$version = $this->get_version();
				$suffix  = '.min';
			}

			$gateway = new Charitable_Gateway_Braintree;
			$keys    = $gateway->get_keys();

			/* Register Braintree dropin scripts. */
			wp_register_script(
				'charitable-braintree-dropin',
				'https://js.braintreegateway.com/web/dropin/1.16.0/js/dropin.min.js',
				[],
				'1.16.0',
				true
			);

			/* Register our Braintree handler. */
			wp_register_script(
				'charitable-braintree-handler',
				$this->get_path( 'directory', false ) . 'assets/js/charitable-braintree-handler' . $suffix . '.js',
				[
					'charitable-braintree-dropin',
					'jquery-core',
				],
				$version,
				true
			);
		}

		/**
		 * Returns whether the plugin has already started.
		 *
		 * @since  1.0.0
		 *
		 * @return boolean
		 */
		public function started() {
			return did_action( 'charitable_braintree_start' ) || current_filter() == 'charitable_braintree_start';
		}

		/**
		 * Returns the plugin's version number.
		 *
		 * @since  1.0.0
		 *
		 * @return string
		 */
		public function get_version() {
			return self::VERSION;
		}

		/**
		 * Returns plugin paths.
		 *
		 * @since   1.0.0
		 *
		 * @param  string  $type          If empty, returns the path to the plugin.
		 * @param  boolean $absolute_path If true, returns the file system path. If false, returns it as a URL.
		 * @return string
		 */
		public function get_path( $type = '', $absolute_path = true ) {
			$base = $absolute_path ? $this->directory_path : $this->directory_url;

			switch ( $type ) {
				case 'includes':
					$path = $base . 'includes/';
					break;

				case 'templates':
					$path = $base . 'templates/';
					break;

				case 'directory':
					$path = $base;
					break;

				default:
					$path = $this->plugin_file;
			}

			return $path;
		}

		/**
		 * Throw error on object clone.
		 *
		 * This class is specifically designed to be instantiated once. You can retrieve the instance using charitable()
		 *
		 * @since   1.0.0
		 *
		 * @return void
		 */
		public function __clone() {
			charitable_braintree_deprecated()->doing_it_wrong(
				__FUNCTION__,
				__( 'Cloning this object is forbidden.', 'charitable-braintree' ),
				'1.0.0'
			);
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since   1.0.0
		 *
		 * @return void
		 */
		public function __wakeup() {
			charitable_braintree_deprecated()->doing_it_wrong(
				__FUNCTION__,
				__( 'Unserializing instances of this class is forbidden.', 'charitable-braintree' ),
				'1.0.0'
			);
		}
	}

endif;
