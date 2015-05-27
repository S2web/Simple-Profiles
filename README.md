# Simple Profiles

**Authors**	           [S2web](https://github.com/S2web)  
**License:**           GPLv2 or later  
**License URI:**       [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html) 

## Description

Simple Profiles allows you to create profiles for staff, people, members, etc in WordPress. 

## Features

Each profile has fields which assign meta information to the profile. The current fields include:

* Name
* Position / Title
* Email Address
* Phone Number
* Website
* Custom Link
* Custom Link Text
* Profile Bio
* Profile Picture
* Profile Category
* Social Network Links
 * Facebook
 * Twitter
 * LinkedIn
 * Google+
 * Pinterest
 * Instagram

## Installation & Usage

1. Upload the `s2-profiles` directory to the `wp-content/plugins` directory.
2. Activate Simple Profiles through the plugins menu in WordPress.
3. Add or edit profiles in the `Profiles` menu found in the WordPress admin menu.

To view the profiles you can go to the archive page, use the shortcode, or use a template function. 

The archive page can be found at `yourdomain.com/profiles` if you have pretty permalinks or `yourdomain.com/s2-profiles` if you have the default permalink settings.

Use the shortcode `[simple-profiles]` to display all the profiles. To display profiles in a particular profile category, use the shortcode attribute to filter the profiles. 

Shortcode Example: `[simple-profiles category="staff"]`

You can also use a template function to show profiles. Use the function like the following example in your theme:

```
if ( function_exists( 'simple_profiles' ) ) {
	simple_profiles();
}
```
This function accepts one parameter similar to the shortcode. You can use this parameter to filter profile categories as shown in this example: `simple_profiles( 'staff' );`

### Changes and Enhancements

Future plans include:

* creating more filters for plugin
* Adding more options for shortcode & template tag
