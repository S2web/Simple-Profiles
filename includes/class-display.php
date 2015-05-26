<?php
/**
 * Plugin Name.
 *
 * @package   S2_Profiles_Common
 * @author    Steven Slack <steven@s2webpress.com>
 * @license   GPL-2.0+
 * @link      http://s2webpress.com
 * @copyright 2014 S2 Web LLC
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-s2-profiles-admin.php`
 *
 * 
 *
 * @package S2_Profiles_Common
 * @author  Steven Slack <steven@s2webpress.com>
 */
class S2_Profiles_Common {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_filter( 'the_content', array( $this, 'profile_single_content_filter' ) );

		// add the profiles shortcode
	    add_shortcode( 'simple-profiles', array( $this, 'profiles_shortcode' ) );

	    //add_action( 'pre_get_posts' array( $this, 'profiles_num_display' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
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
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $post;

		if ( is_post_type_archive( 's2_profiles' ) || has_shortcode( $post->post_content, 'simple-profiles' ) || is_singular( 's2_profiles' )  ) {
			wp_enqueue_style( 's2-profiles-styles', S2_PROFILES . '/assets/css/profiles.css', array(), S2_VERSION );
		}

	}


	/**
	 * Display for the Title, Phone, and email custom fields
	 * @return string
	 */
	public function display_profile_info() {

		global $post;

		// get the post meta and sanitize it.
		$title = esc_html__( get_post_meta( $post->ID, 'profile-title', true ), 's2-profiles' );
		$phone = esc_html( get_post_meta( $post->ID, 'profile-phone', true ) );
		$email = get_post_meta( $post->ID, 'profile-email', true );

		$info_string = '<div class="profiles-info">%1$s%2$s%3$s</div><!--/.profiles-info -->';
	
		if ( ! empty( $title ) ) {
			$title = sprintf( '<span class="job-title"><strong>%s</strong></span><!--/.job-title -->', $title );
		}
		if ( ! empty( $phone ) ) {
			$phone = sprintf( '<div class="s2-phone">%s<span class="tel">%s</span></div><!--/.s2-phone -->', __( 'tel: ',  's2-profiles' ), $phone );		 
		}
		if ( ! empty( $email ) && is_email( $email ) ) {
			$email = sprintf( '<a class="s2-email" href="mailto:%1$s">%1$s</a><!--/.s2-email -->', antispambot( $email ) );
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
	 * Display URL Links
	 * 
	 * @return string
	 */
	public function display_profile_links() {

		global $post;

		$profile_links   = $this->profile_links();
		$profiles_string = '<div class="profiles-links">%1$s%2$s</div><!--/.profiles-links -->';
		$custom_link     = '';
		$website         = '';

		// return early if there are no profile links
		if ( ! $profile_links ) {
			return;
		}

		// if a custom website link is filled out display it
		if ( $profile_links['custom'] ) {
			// get the custom text for the website link
			$custom_text = esc_html__( get_post_meta( $post->ID, 'profile-custom-text', true ), 's2-profiles' );

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
		}

		// If there is a website entered display it
		if ( $profile_links['website'] ) {
			$website = sprintf( '<span class="profile-website">%1$s: <a href="%2$s" rel="bookmark">%2$s</a></span><!--/.profile-website -->', 
				esc_html__( 'Website', 's2-profiles' ), 
				$profile_links['website'] 
			);
		}

		return sprintf( $profiles_string, $custom_link, $website );

	}


	public function display_social_links() {

		global $post;

		$social_links = $this->social_links();
		$social_string = '<div class="profiles-social">%1$s</div><!--/.profiles-social -->';
		$links = '';

		if ( ! $social_links ) {
			return;
		}

		foreach ( $social_links as $key => $value ) {
			if ( $value ) {
			 	$links .= sprintf( '<a href="%1$s" rel="me" class="profilecon-%2$s"><span>%2$s</span></a>', $value, $key );
			}
		}

		return sprintf( $social_string, $links );

	}


	/**
	 * Display the social media icons
	 * @return string
	 */
	public function display_profile_meta() {

		$social = '';
		
		global $post;

		// define meta variable
		$profile_meta = get_post_meta( $post->ID );

		$social_stuff  = $this->social_links();
		$profile_links = $this->profile_links();

		if ( array_filter( $profile_links ) || array_filter( $social_stuff ) ) {

		$social = '<div class="profiles-footer">';

		if ( $profile_links ) {
			$social .= $this->display_profile_links();
		}

		if ( $social_stuff ) {
			$social .= $this->display_social_links();
		}

		$social .= '</div>'; // end .profiles-social-social

		} // end if any footer data is available

		return $social;
	}




	/**
	 * The Query arguments for the profiles
	 * 
	 * @param  string $profile_cat The profile category to run the query for
	 * @return array
	 */
	public function taxonomy_query( $profile_cat = '' ) {

		// Checks if the user has entered a profiles category attribute
		if ( term_exists( $profile_cat, 's2_profiles_cat') ) {

			$args = array( 
				'post_type' => 's2_profiles',
				'orderby'   => 'menu_order',
				'order'     => 'ASC',
				'tax_query' => array(                     
				    'relation' 	=> 'AND',                   
					    array(
					        'taxonomy' 			=> 's2_profiles_cat',               
					        'field' 			=> 'slug',                    
					        'terms' 			=> $profile_cat,
					        'include_children' 	=> false,
					        'operator' 			=> 'IN'
					    ),
				) // end tax query

			);	// end $args array

		// if no attribute is set return all profiles
		} else {

			$args = array( 
				'post_type' => 's2_profiles',
				'orderby' 	=> 'menu_order',
				'order' 	=> 'ASC'
			);

		}

		return $args;
	}



	/**
	 * [profiles_list_display description]
	 * @param  string $profile_cat taxonomy parameter for query
	 * @param  string $layout      left, right, photo-grid
	 * @param  string $display     name-under-photo, photo-name-title, photo-name
	 * @return string              [description]
	 */
	public function profiles_list_display( $profile_cat = '', $layout = '', $display = '' ) {
		global $post;

		$args = $this->taxonomy_query( $profile_cat );

		$profile_query = new WP_Query( $args );

		// define variable
		$output = '';

		// The Loop
		if ( $profile_query->have_posts() ) :

			while ( $profile_query->have_posts() ) : $profile_query->the_post();

			$output .= '<div class="s2-profile">';

			if ( has_post_thumbnail() ) {
				
				$output .= '<div class="s2-profile-avatar"><a href="' . get_the_permalink() . '" rel="bookmark">' . get_the_post_thumbnail( $post->ID,'profiles-thumb' ) . '</a></div>';
				
			} // endif

				$output .= '<div class="s2-profile-info">';
					$output .= '<h3 class="profile-name"><a href="' . get_the_permalink() . '" rel="bookmark">' . get_the_title() . '</a></h3>';

					$output .= $this->display_profile_info();
					if ( get_the_content() ) {
						$output .= '<p class="profiles-bio">' . get_the_content() . '</p>';
					}
					$output .= $this->display_profile_meta();

				$output .= '</div>'; // end s2-profile-info
			$output .= '</div>'; // end s2-profile

			endwhile;

		endif;

		// Reset Post Data
		wp_reset_postdata();

		return $output;

	}


	public function profiles_shortcode( $atts ) {

		extract( shortcode_atts(
			array(
				'category' =>  '',			
				'layout'   =>  '',
	            'display'  =>  '',
			), $atts )
		);

		$profiles = $this->profiles_list_display( $category, $layout, $display );

		return $profiles;
	}


	/**
	 * Content filter for single profile posts
	 * @param $content
	 */
	public function profile_single_content_filter( $content ) {
		
		$post_type = get_post_type( get_the_ID() );

		// checks whether the post type is the profiles post type
		if ( $post_type === 's2_profiles' && is_single() || is_post_type_archive( 's2_profiles' ) || is_page_template( 'page-staff.php' ) ) {

			if ( has_post_thumbnail() ) { 
				$content = $this->display_profile_info() . the_post_thumbnail( 'profiles-thumb' ) .  $content . $this->display_profile_meta();
			} else {
				$content = $this->display_profile_info() .  $content . $this->display_profile_meta();
			}		

		}
		
		return $content;
	}



	/**
	 * Use pre_get_posts to change number of posts_per_page for resource archive
	 */
	
	 
	// public function profiles_num_display( $query ) {

	// 	$options = get_option( $this->csr_slug );

	// 	if ( ! is_admin() && ! is_search() ) {

	// 	    if ( $query->query_vars['post_type'] === 's2_profiles' ) {
        
	// 	        $query->set( 'posts_per_page', 1 );
	// 	        return;
	// 	    }

	// 	}
	 
	// } // end profiles_num_display	

}


if ( ! function_exists( 'simple_profiles' ) ) {

	function simple_profiles( $profile_cat = '', $layout = '', $display = '' ) {
		$profiles = S2_Profiles_Common::get_instance()->profiles_list_display( $profile_cat, $layout, $display );
		echo $profiles;
	}

}