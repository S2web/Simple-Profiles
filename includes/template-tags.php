<?php
/**
 * Simple Profiles Template Tags
 * 
 * Template tags to be used in a theme or plugin
 */
 

if ( ! function_exists( 'simple_profiles' ) ) {
	/**
	 * Simple profiles function
	 * Used to display profiles
	 * 
	 * @param  string $profile_cat the profile taxonomy to filter the profiels
	 * @return string the profiles
	 */
	function simple_profiles( $profile_cat = '' ) {
		$profiles = S2_Profiles_Display::get_instance()->profiles_list( $profile_cat );
		echo $profiles;
	}

}