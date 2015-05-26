<?php
/**
 * Plugin Name.
 *
 * @package   S2_Profiles_Admin
 * @author    Steven Slack <steven@s2webpress.com>
 * @license   GPL-2.0+
 * @link      http://s2webpress.com
 * @copyright 2014 S2 Web LLC
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-s2-profiles.php`
 *
 * 
 *
 * @package S2_Profiles_Admin
 * @author  Steven Slack <steven@s2webpress.com>
 */
class S2_Profiles_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

		add_action( 'edit_form_after_title', array( $this, 'move_meta_box' ) );
		add_action( 'admin_head',            array( $this, 'remove_media_buttons' ) );

		add_action( 'add_meta_boxes',        array( $this, 's2_profiles_meta_box_callback' ) );
		add_action( 'save_post',             array( $this, 's2_profiles_meta_save' ) );

		add_filter( 'enter_title_here',      array( $this, 'change_title_placeholder' ) );

	}

	/**
	 * Return an instance of this class.
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
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();
		if ( 's2_profiles' == $screen->post_type ) {
			wp_enqueue_style( 's2-profiles-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), S2_VERSION );
		}

	}

	/**
	 * Move the profiles meta box above the post editor
	 */
	public function move_meta_box() {

	    global $post, $wp_meta_boxes;

	    if ( 's2_profiles' == get_post_type() ) {

			// Output the "advanced" meta boxes:
			do_meta_boxes( get_current_screen(), 'advanced', $post );

			// Remove the initial "advanced" meta boxes:
			unset( $wp_meta_boxes['s2_profiles']['advanced'] );

			printf( '<h3 class="profiles-bio-title">%s</h3>', __( 'Profile Bio', 's2-profiles' ) );

	    }
	}

	/**
	 * Remove Media buttons from Profile Post Type
	 */
	public function remove_media_buttons() {

		$screen = get_current_screen();

		if ( 's2_profiles' == $screen->post_type ) {
			// remove all media buttons from the s2_profiles post type
			remove_all_actions( 'media_buttons' );
		}
	}

	/**
	 * Meta Box Callbacks
	 * 
	 * @param  object $post
	 */
	public function s2_profiles_meta_box_callback( $post ) {
		// 
		add_meta_box(
			's2_profiles_meta_info',
			__( 'Profile Information', 's2-profiles' ),
			array( $this, 'profiles_meta_output' ),
			's2_profiles',
			'advanced',
			'high'
		);
		add_meta_box(
			's2_profiles_social_info',
			__( 'Social Networks', 's2-profiles' ),
			array( $this, 'profiles_social_meta_output' ),
			's2_profiles',
			'normal',
			'low'
		);
	}

	/**
	 * Profiles metabox output
	 */
	public function profiles_meta_output( $post ) {

		wp_nonce_field( basename( __FILE__ ), 's2_profile_nonce' );

		$meta = get_post_meta( $post->ID );
		?>
		<p>
		    <label for="profile-title"><?php _e( 'Position / Title:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-title" id="profile-title" value="<?php if ( isset ( $meta['profile-title'] ) ) echo $meta['profile-title'][0]; ?>" />
		</p>
		<p>
		    <label for="profile-email"><?php _e( 'Email Address:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-email" id="profile-email" value="<?php if ( isset ( $meta['profile-email'] ) ) echo is_email( $meta['profile-email'][0] ); ?>" />
		</p>
		<p>
		    <label for="profile-phone"><?php _e( 'Phone Number:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-phone" id="profile-phone" value="<?php if ( isset ( $meta['profile-phone'] ) ) echo $meta['profile-phone'][0]; ?>" />
		</p>
		<p>
		    <label for="profile-website"><?php _e( 'Website:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-website" id="profile-website" value="<?php if ( isset ( $meta['profile-website'] ) ) echo esc_url( $meta['profile-website'][0] ); ?>" />
		</p>
		<div class="custom-link">
			<h3><span><?php _e( 'Custom Link', 's2-profiles' ) ?></span></h3>
			<p class="description"><?php _e( 'You can provide a custom link and text in the fields below.', 's2-profiles' ) ?></p>
			<p>
			    <label for="profile-custom"><?php _e( 'Custom Link:', 's2-profiles' ) ?></label>
			    <input type="text" name="profile-custom" id="profile-custom" value="<?php if ( isset ( $meta['profile-custom'] ) ) echo esc_url( $meta['profile-custom'][0] ); ?>" />
			</p>
			<p>
			    <label for="profile-custom-text"><?php _e( 'Custom Link Text:', 's2-profiles' ) ?></label>
			    <input type="text" name="profile-custom-text" id="profile-custom-text" value="<?php if ( isset ( $meta['profile-custom-text'] ) ) echo $meta['profile-custom-text'][0]; ?>" />
			</p>
		</div>

		<?php
	}


	/**
	 * Social Meta Box Output
	 */
	public function profiles_social_meta_output( $post ) {

		wp_nonce_field( basename( __FILE__ ), 's2_profile_nonce' );

		$meta = get_post_meta( $post->ID );

		?>
		<p class="description"><?php _e( 'Enter the full URL for each of the social networks. (These fields are optional)', 's2-profiles' ); ?></p>
	
		<p>
		    <label for="profile-facebook"><?php _e( 'Facebook URL:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-facebook" id="profile-facebook" value="<?php if ( isset ( $meta['profile-facebook'] ) ) echo esc_url( $meta['profile-facebook'][0] ); ?>" />
		</p>
		<p>
		    <label for="profile-twitter"><?php _e( 'Twitter URL:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-twitter" id="profile-twitter" value="<?php if ( isset ( $meta['profile-twitter'] ) ) echo esc_url( $meta['profile-twitter'][0] ); ?>" />
		</p>
		<p>
		    <label for="profile-linkedin"><?php _e( 'LinkedIn URL:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-linkedin" id="profile-linkedin" value="<?php if ( isset ( $meta['profile-linkedin'] ) ) echo esc_url( $meta['profile-linkedin'][0] ); ?>" />
		</p>
		<p>
		    <label for="profile-google+"><?php _e( 'Google+ URL:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-google+" id="profile-google+" value="<?php if ( isset ( $meta['profile-google+'] ) ) echo esc_url( $meta['profile-google+'][0] ); ?>" />
		</p>
		<p>
		    <label for="profile-pinterest"><?php _e( 'Pinterest URL:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-pinterest" id="profile-pinterest" value="<?php if ( isset ( $meta['profile-pinterest'] ) ) echo esc_url( $meta['profile-pinterest'][0] ); ?>" />
		</p>
		<p>
		    <label for="profile-instagram"><?php _e( 'Instagram URL:', 's2-profiles' ) ?></label>
		    <input type="text" name="profile-instagram" id="profile-instagram" value="<?php if ( isset ( $meta['profile-instagram'] ) ) echo esc_url( $meta['profile-instagram'][0] ); ?>" />
		</p>

		<?php
	}

	/**
	 * Save all the meta values
	 */
	public function s2_profiles_meta_save( $post_id ) {
 
		// Checks save status
		$is_autosave    = wp_is_post_autosave( $post_id );
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 's2_profile_nonce' ] ) && wp_verify_nonce( $_POST[ 's2_profile_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

		// Exits script depending on save status
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		    return;
		}

		// Checks for input and sanitizes/saves if needed
		if( isset( $_POST[ 'profile-title' ] ) ) {
		    update_post_meta( $post_id, 'profile-title', sanitize_text_field( $_POST[ 'profile-title' ] ) );
		}
		if( isset( $_POST[ 'profile-email' ] ) ) {
		    update_post_meta( $post_id, 'profile-email', sanitize_email( $_POST[ 'profile-email' ] ) );
		}
		if( isset( $_POST[ 'profile-phone' ] ) ) {
		    update_post_meta( $post_id, 'profile-phone', sanitize_text_field( $_POST[ 'profile-phone' ] ) );
		}
		if( isset( $_POST[ 'profile-website' ] ) ) {
		    update_post_meta( $post_id, 'profile-website', esc_url_raw( $_POST[ 'profile-website' ] ) );
		}
		if( isset( $_POST[ 'profile-facebook' ] ) ) {
		    update_post_meta( $post_id, 'profile-facebook', esc_url_raw( $_POST[ 'profile-facebook' ] ) );
		}
		if( isset( $_POST[ 'profile-twitter' ] ) ) {
		    update_post_meta( $post_id, 'profile-twitter', esc_url_raw( $_POST[ 'profile-twitter' ] ) );
		}
		if( isset( $_POST[ 'profile-linkedin' ] ) ) {
		    update_post_meta( $post_id, 'profile-linkedin', esc_url_raw( $_POST[ 'profile-linkedin' ] ) );
		}
		if( isset( $_POST[ 'profile-google+' ] ) ) {
		    update_post_meta( $post_id, 'profile-google+', esc_url_raw( $_POST[ 'profile-google+' ] ) );
		}
		if( isset( $_POST[ 'profile-pinterest' ] ) ) {
		    update_post_meta( $post_id, 'profile-pinterest', esc_url_raw( $_POST[ 'profile-pinterest' ] ) );
		}
		if( isset( $_POST[ 'profile-instagram' ] ) ) {
		    update_post_meta( $post_id, 'profile-instagram', esc_url_raw( $_POST[ 'profile-instagram' ] ) );
		}
		if( isset( $_POST[ 'profile-custom' ] ) ) {
		    update_post_meta( $post_id, 'profile-custom', esc_url_raw( $_POST[ 'profile-custom' ] ) );
		}
		if( isset( $_POST[ 'profile-custom-text' ] ) ) {
		    update_post_meta( $post_id, 'profile-custom-text', sanitize_text_field( $_POST[ 'profile-custom-text' ] ) );
		}

	}

	/* 
	 * replacing the default "Enter title here" placeholder text in the title input box
	 *
	 * source: http://flashingcursor.com/wordpress/change-the-enter-title-here-text-in-wordpress-963
	 * 
	*/
	public function change_title_placeholder( $title ){

		$screen = get_current_screen();

		if ( 's2_profiles' == $screen->post_type ){
			$title = __( 'Enter Name Here', 's2-profiles' );
		}

		return $title;
	}

}
