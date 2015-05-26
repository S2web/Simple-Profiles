<?php
/**
 * Simple Profiles
 *
 * @package   S2_Profiles
 * @author    Steven Slack <steven@s2webpress.com>
 * @license   GPL-2.0+
 * @link      http://s2webpress.com
 * @copyright 2014 S2 Web LLC
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Profiles
 * Plugin URI:        http://s2webpress.com/plugins/simple-profiles
 * Description:       Simple Profiles for your business or organization. Each profile features an 
 * Version:           2.0.0
 * Author:            Steven Slack
 * Author URI:        http://s2webpress.com
 * Text Domain:       s2-profiles-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/s2web/s2-profiles
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


class S2_Profiles {

	/**
	 * Instance of this class
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     2.0.0
	 */
	private function __construct() {

		$this->setup_constants();
		// require all dependent files
		$this->load_dependencies();

		// registers the Custom Post Type
		new S2_Profiles_CPT();

		// get front facing common class
		S2_Profiles_Common::get_instance();

		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			$this->profile_featured_image_metabox();
			S2_Profiles_Admin::get_instance();
    	}

		// Load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'load_profiles_textdomain' ) );

	}

	/**
	 * Define the internationalization functionality
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 */ 
	public function load_profiles_textdomain() {

		$domain = 'sc-catalog';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/*
	 * Sets up constants used throughout the plugin
	 */
	public function setup_constants() {

		if ( ! defined( 'S2_VERSION' ) )
			define( 'S2_VERSION', '2.0.0' );

		// Plugin Folder Path
		if ( ! defined( 'S2_DIR' ) )
			define( 'S2_DIR', dirname( __FILE__ ) );

		// Plugin Folder URL
		if ( ! defined( 'S2_URL' ) )
			define( 'S2_URL', plugins_url( __FILE__ ) );
		
	}

	/**
	 * Load Plugin Files
	 * 
	 */
	public function load_dependencies() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-post-type.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-common.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-featured-image-metabox-customizer.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-s2-profiles-admin.php';

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     2.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * The Activation function. Runs when the plugin is activated
	 * 
	 * @since      2.0.0
	 */
	public static function activate() {

		/** post types are registered on 
		 *  activation and rewrite rules are flushed.
		 */
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-post-type.php';

		$profiles_cpt = new S2_Profiles_CPT();
		$profiles_cpt->register_cpt();

		flush_rewrite_rules();

	}

	public function profile_featured_image_metabox() {

		new Featured_Image_Metabox_Customizer( array(
			'post_type'     => 's2_profiles',
			'metabox_title' => __( 'Profile Picture',        's2-profiles' ),
			'set_text'      => __( 'Set Profile Picture',    's2-profiles' ),
			'remove_text'   => __( 'Remove Profile Picture', 's2-profiles' )
		));

	}

}
add_action( 'plugins_loaded', array( 'S2_Profiles', 'get_instance' ) );

register_activation_hook( __FILE__, array( 'S2_Profiles', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
