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
		wp_enqueue_style( 's2-profiles-styles', plugins_url( 'assets/css/profiles.css', __FILE__ ), array(), S2_VERSION );
	}



	public function display_profile_info( $title_only = true ) {

		global $post;

		$email = '';

		// define variables
		$profile_info = get_post_meta( $post->ID );

		$email = is_email( get_post_meta( $post->ID, 'profile-email', true ) );
		$phone = esc_html( get_post_meta( $post->ID, 'profile-phone', true ) );
		$title = esc_html( get_post_meta( $post->ID, 'profile-title', true ) );
	
		$info  = '<div class="profiles-info">';
					if ( ! empty( $title ) ) {
						$info .= sprintf( '<span class="job-title"><strong>%s</strong></span>', $title );
					}
					if ( $title_only != false ) {
						if ( ! empty( $phone ) ) {
							$info .= sprintf( '<div class="s2-phone">%s<span class="tel">%s</span></div>', __( 'tel: ',  's2-profiles' ), $phone );		 
						}
						if ( ! empty( $email ) ){
							$info .= '<a class="s2-email" href="mailto:' . antispambot( $email ) . '">' . antispambot( $email ) . '</a>';
						}
					}
		$info .= '</div>';

		return $info;
	}


	/** 
	 * Store social links in an array
	 * @return array social links
	 */
	public function social_array() {
		global $post;

		// Get all the social media groups key 
		$social_media_groups = array( 'facebook', 'twitter', 'linkedin', 'pinterest', 'instagram' );
		// set the social media meta array
		$social = array();
		// get the value for each social media group key
		foreach ( $social_media_groups as $social_media ) {
			$social[] = get_post_meta( $post->ID, 'profile-' . $social_media, true );
		}

		return $social;

	}

	/**
	 * Custom fields meta values
	 * @return array of meta values
	 */
	public function profile_links() {
		global $post;

		$custom_keys = array( 'custom', 'website' );
		$values = array();

		foreach ( $custom_keys as $key ) {
			$values[] = get_post_meta( $post->ID, 'profile-' . $key, true );
		}

		return $values;
	}


	/**
	 * Display the social media icons
	 * @return string
	 */
	public function display_social_meta() {

		$social = '';
		
		global $post;

		// define meta variable
		$profile_meta = get_post_meta( $post->ID );

		$social_stuff  = $this->social_array();
		$profile_links = $this->profile_links();

		if ( array_filter( $profile_links ) || array_filter( $social_stuff ) ) {

		$social = '<div class="profiles-footer">';

		if ( array_filter( $profile_links ) ) {

		$social .= '<div class="profiles-links">';

			if( ! empty( $profile_meta['profile-custom'][0] ) ) {
				if( ! empty( $profile_meta['profile-custom-text'][0] ) ) {
					$social .= '<a href="' . esc_url( $profile_meta['profile-custom'][0] ) . '" class="profile-custom">' . $profile_meta['profile-custom-text'][0] . '</a>';
				} else {
					$social .= '<a href="' . esc_url( $profile_meta['profile-custom'][0] ) . '" class="profile-custom">' . esc_url( $profile_meta['profile-custom'][0] ) . '</a>';
				}
				if( $profile_meta['profile-website'][0] || array_filter( $social_stuff ) ) {
					$social .= '<span class="s2-sep"> | </span>';
				}
			}
			if( ! empty( $profile_meta['profile-website'][0] ) ) 
			{
				$social .= '<a href="' . esc_url( $profile_meta['profile-website'][0] ) . '" class="profile-website">' . esc_url( $profile_meta['profile-website'][0] ) . '</a>';
				if ( array_filter( $social_stuff ) ) {
					$social .= '<span class="s2-sep"> | </span>';
				}
			}

		$social .= '</div>';

		} // end if profile links

		if ( array_filter( $social_stuff ) ) {

			$social .= '<div class="profiles-social">'; // end profiles-links

				if( ! empty( $profile_meta['profile-facebook'][0]  ) )
					$social .= '<a href="'. esc_url( $profile_meta['profile-facebook'][0]  ) .'" rel="me" class="profilecon-facebook"><span>'   . __( 'Facebook',  's2-profiles' ) . '</span></a>';
				if( ! empty( $profile_meta['profile-twitter'][0]   ) )
					$social .= '<a href="'. esc_url( $profile_meta['profile-twitter'][0]   ) .'" rel="me" class="profilecon-twitter"><span>'    . __( 'Twitter',   's2-profiles' ) . '</span></a>';
				if( ! empty( $profile_meta['profile-linkedin'][0]  ) )
					$social .= '<a href="'. esc_url( $profile_meta['profile-linkedin'][0]  ) .'" rel="me" class="profilecon-linkedin"><span>'   . __( 'LinkedIn',  's2-profiles' ) . '</span></a>';
				if( ! empty( $profile_meta['profile-google+'][0]   ) )
					$social .= '<a href="'. esc_url( $profile_meta['profile-google+'][0]   ) .'" rel="me" class="profilecon-googleplus"><span>' . __( 'Google+',   's2-profiles' ) . '</span></a>';
				if( ! empty( $profile_meta['profile-pinterest'][0] ) )
					$social .= '<a href="'. esc_url( $profile_meta['profile-pinterest'][0] ) .'" rel="me" class="profilecon-pinterest"><span>'  . __( 'Pinterest', 's2-profiles' ) . '</span></a>';
				if( ! empty( $profile_meta['profile-instagram'][0] ) )
					$social .= '<a href="'. esc_url( $profile_meta['profile-instagram'][0] ) .'" rel="me" class="profilecon-instagram"><span>'  . __( 'Instagram', 's2-profiles' ) . '</span></a>';

			$social .= '</div>';

		} // end if social links

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
					$output .= $this->display_social_meta();

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
				$content = $this->display_profile_info() . the_post_thumbnail( 'profiles-thumb' ) .  $content . $this->display_social_meta();
			} else {
				$content = $this->display_profile_info() .  $content . $this->display_social_meta();
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