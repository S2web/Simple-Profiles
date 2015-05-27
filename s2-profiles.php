<?php
/**
 * Simple Profiles
 *
 * @package   S2_Profiles
 * @author    Steven Slack <steven@s2webpress.com>
 * @license   GPL-2.0+
 * @link      http://stevenslack.com
 * @copyright 2015 S2 Web LLC
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Profiles
 * Plugin URI:        https://github.com/S2web/Simple-Profiles
 * Description:       Simple Profiles for your business or organization. Each profile features fields with name, bio, profile picture, email, phone number, website, job/position title, custom links, social media links
 * Version:           2.0.1
 * Author:            Steven Slack
 * Author URI:        http://stevenslack.com
 * Text Domain:       simple-profiles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The initial plugin class
 */
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
	 */
	private function __construct() {

		$this->setup_constants();
		// require all dependent files
		$this->load_dependencies();

		// registers the Custom Post Type
		new S2_Profiles_CPT();

		// get front facing common class
		S2_Profiles_Display::get_instance();

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

		$domain = 'simple-profiles';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}


	/*
	 * Sets up constants used throughout the plugin
	 */
	public function setup_constants() {

		if ( ! defined( 'PROFILES_VERSION' ) ) {
			define( 'PROFILES_VERSION', '2.0.1' );
		}

		if ( ! defined( 'PROFILES_PATH' ) ) {
			define( 'PROFILES_PATH', plugins_url( 's2-profiles' ) );
		}
		
	}


	/**
	 * Load Plugin Files
	 */
	public function load_dependencies() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-post-type.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-display.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-featured-image-metabox-customizer.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-profiles-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/template-tags.php';

	}


	/**
	 * Return an instance of this class.
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


	/**
	 * Change the Featured Image Metabox to more appropriately assign profile pcitures
	 */
	public function profile_featured_image_metabox() {
		new Featured_Image_Metabox_Customizer( array(
			'post_type'     => 's2_profiles',
			'metabox_title' => __( 'Profile Picture',        'simple-profiles' ),
			'set_text'      => __( 'Set Profile Picture',    'simple-profiles' ),
			'remove_text'   => __( 'Remove Profile Picture', 'simple-profiles' )
		));
	}

} // end S2_Profiles class


// Get the instance on plugins_loaded
add_action( 'plugins_loaded', array( 'S2_Profiles', 'get_instance' ) );

register_activation_hook( __FILE__, array( 'S2_Profiles', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
