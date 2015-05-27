<?php
/**
 * Simple Profiles
 *
 * @package   S2_Profiles_Display
 */

/**
 * The front end display side of the plugin.
 */
class S2_Profiles_Display {


	/**
	 * Instance of this class.
	 * @var      object
	 */
	protected static $instance = null;


	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	private function __construct() {

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_filter( 'the_content', array( $this, 'profile_single_content_filter' ) );

		// add the profiles shortcode
	    add_shortcode( 'simple-profiles', array( $this, 'profiles_shortcode' ) );

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
	 * Register and enqueue public-facing style sheet.
	 *
	 */
	public function enqueue_styles() {

		global $post;

		if ( is_post_type_archive( 's2_profiles' ) || has_shortcode( $post->post_content, 'simple-profiles' ) || is_singular( 's2_profiles' )  ) {
			wp_enqueue_style( 'simple-profiles-styles', PROFILES_PATH . '/assets/css/profiles.css', array(), PROFILES_VERSION );
		}

	}


	/**
	 * Slugify a string
	 * 
	 * Convert the name / title to a class name or URL friendly string
	 * @return string
	 */
	public function slug_name() {

		$string = get_the_title();

		// Lower case everything
		$string = strtolower( $string ) ;
		// Make alphanumeric (removes all other characters)
		$string = preg_replace( "/[^a-z0-9_\s-]/", "", $string );
		// Clean up multiple dashes or whitespaces
		$string = preg_replace( "/[\s-]+/", " ", $string );
		// Convert whitespaces and underscore to dash
		$string = preg_replace( "/[\s_]/", "-", $string );

		return $string;
	}


	/**
	 * Display for the Title, Phone, and email custom fields
	 * @return string
	 */
	public function display_profile_info() {

		global $post;

		// get the post meta and sanitize it.
		$title = esc_html__( get_post_meta( $post->ID, 'profile-title', true ), 'simple-profiles' );
		$phone = esc_html( get_post_meta( $post->ID, 'profile-phone', true ) );
		$email = get_post_meta( $post->ID, 'profile-email', true );

		$info_string = '<div class="profile-meta">%1$s%2$s%3$s</div><!--/.profile-meta -->';
	
		if ( ! empty( $title ) ) {
			$title = sprintf( '<span class="job-title"><strong>%s</strong></span><!--/.job-title -->', $title );
		}
		if ( ! empty( $phone ) ) {
			$phone = sprintf( '<div class="profile-phone">%s<span class="tel">%s</span></div><!--/.profile-phone -->', __( 'tel: ',  'simple-profiles' ), $phone );		 
		}
		if ( ! empty( $email ) && is_email( $email ) ) {
			$email = sprintf( '<a class="profile-email" href="mailto:%1$s">%1$s</a><!--/.profile-email -->', antispambot( $email ) );
		}

		if ( $title || $phone || $mail ) {
			return sprintf( $info_string, $title, $phone, $email );
		}

	}


	/** 
	 * Store social links in an array
	 * 
	 * @return array social links
	 */
	public function social_links() {

		global $post;

		// Get all the social media groups key 
		$social_media_groups = array( 'facebook', 'twitter', 'linkedin', 'pinterest', 'instagram' );
		// set the social media meta array
		$social_meta = array();
		// get the value for each social media group key
		foreach ( $social_media_groups as $social_media ) {
			$social_meta[$social_media] = esc_url( get_post_meta( $post->ID, 'profile-' . $social_media, true ) );
		}

		if ( $social_meta ) {
			return $social_meta;
		}

	}

	/**
	 * Custom fields meta values
	 * 
	 * @return array of meta values
	 */
	public function profile_links() {
		
		global $post;

		$custom_keys = array( 'custom', 'website' );
		$custom = array();

		foreach ( $custom_keys as $key ) {
			$custom[$key] = esc_url( get_post_meta( $post->ID, 'profile-' . $key, true ) );
		}

		if ( $custom ) {
			return $custom;
		}
	}


	/**
	 * Get the custom links with custom text 
	 * 
	 * @param  array $profile_links the custom field keys and values for the profile links
	 * @return string
	 */
	public function get_custom_links( $profile_links ) {

		global $post;

		$custom_link = ''; // initiate custom link variable

		// if a custom website link is empty return early
		if ( ! $profile_links['custom'] ) {
			return;
		}

		// get the custom text for the website link
		$custom_text = esc_html__( get_post_meta( $post->ID, 'profile-custom-text', true ), 'simple-profiles' );

		// if there is custom text display it 
		// if not just show the actual URL instead
		if ( $custom_text ) {
			$custom_link = sprintf( '<a href="%1$s" class="profile-custom">%2$s</a><!--/.profile-custom -->', 
				$profile_links['custom'], 
				$custom_text 
			);
		} else {
			$custom_link = sprintf( '<a href="%1$s" class="profile-custom">%1$s</a><!--/.profile-custom -->', 
				$profile_links['custom'] 
			);
		}	

		return $custom_link;

	}


	/**
	 * Get the display for the website
	 * 
	 * @param  array $profile_links the custom field keys and values for the profile links
	 * @return string
	 */
	public function get_profile_website( $profile_links ) {

		$website = ''; // initiate custom website variable

		// If there is no website link return early
		if ( ! $profile_links['website'] ) {
			return;
		}

		$website = sprintf( '<span class="profile-website">%1$s: <a href="%2$s" rel="bookmark">%2$s</a></span><!--/.profile-website -->', 
			esc_html__( 'Website', 'simple-profiles' ), 
			$profile_links['website'] 
		);

		return $website;

	}


	/**
	 * Display URL Links
	 * 
	 * @return string
	 */
	public function display_profile_links() {

		$profile_links   = $this->profile_links();
		$profiles_string = '<div class="profiles-links">%1$s%2$s</div><!--/.profiles-links -->';

		// return early if there are no profile links
		if ( ! $profile_links ) {
			return;
		}

		$custom_link = $this->get_custom_links( $profile_links );
		$website     = $this->get_profile_website( $profile_links );

		return sprintf( $profiles_string, $custom_link, $website );

	}

	/**
	 * The social media links
	 * 
	 * Display for the custom fields to display social media links
	 * @return string
	 */
	public function display_social_links() {

		$social_links  = $this->social_links(); // get the custom meta data
		$output_string = '<div class="profiles-social">%1$s</div><!--/.profiles-social -->';
		$links         = ''; // set the links variable to empty

		// return early if there is no custom meta set
		if ( ! $social_links ) {
			return;
		}

		// the display for each social icon
		// example: <a href="http://facebook.com" rel="me" class="profilecon-facebook"><span>facebook</span></a>
		foreach ( $social_links as $key => $value ) {
			if ( $value ) {
			 	$links .= sprintf( '<a href="%1$s" rel="me" class="profilecon-%2$s"><span>%2$s</span></a>', $value, $key );
			}
		}

		return sprintf( $output_string, $links );

	}


	/**
	 * Display the profile footer 
	 * contains the meta information associated with the profile
	 * 
	 * @return string
	 */
	public function profile_footer() {

		$social        = $this->social_links();  // get the social links display
		$profile_links = $this->profile_links(); // get the custom links display

		if ( $profile_links ) {
			$profile_links = $this->display_profile_links();
		}

		if ( $social ) {
			$social = $this->display_social_links();
		}

		// if either the social or the custom links are set display the footer
		if ( $profile_links || $social ) {
			$output_string = '<div class="profiles-footer">%1$s%2$s</div><!--/.profiles-footer-->';
			return sprintf( $output_string, $profile_links, $social );
		}

	}


	/**
	 * The profile picture of the person
	 * 
	 * Set with the featured image
	 * @return string
	 */
	public function profile_picture_display() {

		if ( ! has_post_thumbnail() ) {
			return;
		}

		$picture = sprintf( '<div class="profile-picture"><a href="%1$s" rel="bookmark">%2$s</a></div>',
			get_the_permalink(),
			get_the_post_thumbnail( get_the_id(), 'profiles-thumb' )
		);

		return $picture;

	}


	/**
	 * The name of the person
	 * 
	 * @return string
	 */
	public function profile_name() {

		if ( get_the_title() === '' )
			return;

		$name = sprintf( '<h3 class="profile-name"><a href="%1$s" rel="bookmark">%2$s</a></h3>',
			get_the_permalink(),
			get_the_title()
		);
		
		return $name;
	}


	/**
	 * The profiles bio
	 * 
	 * Entered with the regular WordPress text editor
	 * @return string
	 */
	public function profile_bio() {
		if ( get_the_content() ) {
			return '<p class="profiles-bio">' . get_the_content() . '</p>';
		}
	}


	/**
	 * The Query arguments for the profiles
	 * 
	 * @param  string $profile_cat The profile category to run the query for
	 * @return array
	 */
	public function profile_query_args( $profile_cat = '' ) {

		// Checks if the user has entered a profiles category attribute
		if ( term_exists( $profile_cat, 's2_profiles_cat' ) ) {

			$args = array(
				'post_type'              => 's2_profiles',
				'orderby'                => 'menu_order',
				'order'                  => 'ASC',
				'posts_per_page'         => 500,
				'posts_per_archive_page' => 500,
				'tax_query' => array(                     
					'relation' => 'AND',                   
					array(
						'taxonomy'         => 's2_profiles_cat',               
						'field'            => 'slug',                    
						'terms'            => $profile_cat,
						'include_children' => false,
						'operator'         => 'IN'
					),
				) // end tax query

			);	// end $args array

		// if no attribute is set return all profiles
		} else {

			$args = array( 
				'post_type'              => 's2_profiles',
				'orderby'                => 'menu_order',
				'order'                  => 'ASC',
				'posts_per_page'         => 500,
				'posts_per_archive_page' => 500,
			);
		}

		return $args;
	}


	/**
	 * The Profiles List
	 * 
	 * Returns a list view of the profiles
	 * @param  string $profile_cat taxonomy parameter for query
	 * @return string
	 */
	public function profiles_list( $profile_cat = '' ) {
		
		global $post;

		// setup the profile query
		$args = $this->profile_query_args( $profile_cat );
		$profile_query = new WP_Query( $args );

		$profile = ''; // set the profile variable
		$count   = 1;  // start the count at 1

		// The Profiles
		if ( $profile_query->have_posts() ) :
			while ( $profile_query->have_posts() ) : $profile_query->the_post();

			$count++;

			// the profile wrapper
			$output_string = '<div id="%1$s-%2$s" class="simple-profile">%3$s<div class="profile-info">%4$s %5$s %6$s %7$s</div><!--/.profile-info --><hr></div><!--/.simple-profile -->';

			$profile .= sprintf( $output_string,
				$this->slug_name(),
				get_the_id(),
				$this->profile_picture_display(),
				$this->profile_name(),
				$this->display_profile_info(),
				$this->profile_bio(),
				$this->profile_footer()
			);

			endwhile;
		endif;

		// Reset Post Data
		wp_reset_postdata();

		return $profile;

	}

	/**
	 * The Profiles Shortcode [simple-profiles]
	 * 
	 * @param  array $atts [description]
	 * @return the profiles output
	 */
	public function profiles_shortcode( $atts ) {

		$shortcode = shortcode_atts(
			array(
				'category' => '',
		), $atts );

		$profiles = $this->profiles_list( $shortcode['category'] );

		return $profiles;
	}


	/**
	 * Content filter for single profile posts
	 * @param $content
	 */
	public function profile_single_content_filter( $content ) {
		
		$post_type = get_post_type( get_the_ID() );

		// checks whether the post type is the profiles post type
		if ( $post_type === 's2_profiles' && is_single() || is_post_type_archive( 's2_profiles' ) ) {

			if ( has_post_thumbnail() ) { 
				$content = $this->display_profile_info() . the_post_thumbnail( 'profiles-thumb' ) .  $content . $this->profile_footer();
			} else {
				$content = $this->display_profile_info() .  $content . $this->profile_footer();
			}		

		}
		
		return $content;
	}

} // end class