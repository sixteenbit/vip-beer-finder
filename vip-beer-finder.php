<?php
/**
 * Plugin Name: VIP Beer Finder
 * Plugin URI:  https://github.com/sixteenbit/vip-beer-finder
 * Description: Integrate your VIP beer finder with a WordPress shortcode.
 * Version:     1.0.3
 * Author: Sixteenbit
 * Author URI: https://sixteenbit.com
 * License:     GPLv2+
 * Text Domain: vip-beer-finder
 * Domain Path: /languages
 */

// Useful global constants
define( 'VIP_Beer_Finder_Version', '1.0.2' );
define( 'VIP_Beer_Finder_URL', plugin_dir_url( __FILE__ ) );

/**
 * Initialize iframeResizer
 */
function vip_beer_finder_iframeResizer_init() { ?>
	<script>
		jQuery(function ($) {
			$('iframe').iFrameResize({
				log: true, scrolling: 'auto', resizeFrom: 'parent',
				bodyMargin: '20px 0'
			});
		});
	</script>
	<?php
}

/**
 * Adds beer-finder shortcode
 *
 * @param $atts
 *
 * @return string
 */
function vip_beer_finder( $atts ) {
	$specs = shortcode_atts(
		array(
			'height'    => '560',
			'width'     => '100%',
			'id'        => 'VIP',
			// Required: your VIP company code; 3 or 5 characters
			'territory' => false,
			// Territory code to pre-filter brands
			'zip'       => false,
			// Zip code to pre-fill (when search by address not enabled)
			'address'   => false,
			// Address to pre-fill (when search by address enabled)
			'miles'     => false,
			// Miles to default to
			'brand'     => false,
			// Brand description to pre-fill the brand combo
			//          'category'  => '',          // Category 1-12 code to pre-filter data. One value per category is OK.
			'theme'     => 'bs-paper',
			// Base theme override; see Theme section for details
			'pagesize'  => false,
			// 50; max of 150
			'geolocate' => false,
			// Set to Y to trigger browser geolocation prompt.
			// If allowed, will automatically search around the users location. Requires HTTPS in modern browsers.
			// iFrame attribute required: ​allow="geolocation;"

		),
		$atts
	);

	$iframe_width  = ! empty( $specs['width'] ) ? ' width="' . esc_attr( $specs['width'] ) . '" ' : '';
	$iframe_height = ! empty( $specs['height'] ) ? ' height="' . esc_attr( $specs['height'] ) . '" ' : '';

	$param = array();

	if ( $specs['id'] != 'VIP' ) {
		$param['custID'] = esc_attr( $specs['id'] );
	}

	if ( $specs['territory'] != false ) {
		$param['terr'] = esc_attr( $specs['territory'] );
	}

	if ( $specs['zip'] != false ) {
		$param['z'] = esc_attr( $specs['zip'] );
	}

	if ( $specs['miles'] != false ) {
		$param['m'] = esc_attr( $specs['miles'] );
	}

	if ( $specs['brand'] != false ) {
		$param['b'] = esc_attr( $specs['brand'] );
	}

	if ( $specs['id'] != 'bs-paper' ) {
		$param['theme'] = esc_attr( $specs['theme'] );
	}

	if ( $specs['pagesize'] != false ) {
		$param['pagesize'] = esc_attr( $specs['pagesize'] );
	}

	if ( $specs['geolocate'] == true ) {
		$param['geolocate'] = 'Y';
		$allow_geo          = ' allow="geolocation;"';
	}

	wp_enqueue_script( 'vip-beer-finder-iframeResizer', VIP_Beer_Finder_URL . 'js/iframeResizer.js', array(), VIP_Beer_Finder_Version, true );
	add_action( 'wp_print_footer_scripts', 'vip_beer_finder_iframeResizer_init' );

	return '<iframe src="https://www.vtinfo.com/PF/product_finder.asp?' . build_query( $param ) . '" ' . $iframe_width . $iframe_height . ' frameborder="0" scrolling="no" ' . esc_html( $allow_geo ) . '></iframe>';
}

add_shortcode( 'beer-finder', 'vip_beer_finder' );

/**
 * Disables wp texturize on registered shortcodes
 *
 * @param $shortcodes
 *
 * @return array
 */
function vip_beer_finder_shortcode_exclude( $shortcodes ) {
	$shortcodes[] = 'beer-finder';

	return $shortcodes;
}

add_filter( 'no_texturize_shortcodes', 'vip_beer_finder_shortcode_exclude' );

/**
 * Filters shortcode to remove auto p and br tags
 *
 * @param $pee
 *
 * @return mixed
 */
function vip_beer_finder_shortcode_unautop( $pee ) {
	global $shortcode_tags;
	if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
		return $pee;
	}
	$tagregexp = join( '|', array_map( 'preg_quote', array_keys( $shortcode_tags ) ) );
	$pattern   =
		'/'
		. '<p>'                             // Opening paragraph
		. '\\s*+'                           // Optional leading whitespace
		. '('                               // 1: The shortcode
		. '\\[\\/?'                         // Opening bracket for opening or closing shortcode tag
		. "($tagregexp)"                    // 2: Shortcode name
		. '(?![\\w-])'                      // Not followed by word character or hyphen
		// Unroll the loop: Inside the opening shortcode tag
		. '[^\\]\\/]*'                      // Not a closing bracket or forward slash
		. '(?:'
		. '\\/(?!\\])'                      // A forward slash not followed by a closing bracket
		. '[^\\]\\/]*'                      // Not a closing bracket or forward slash
		. ')*?'
		. '[\\w\\s="\']*'                   // Shortcode attributes
		. '(?:'
		. '\\s*+'                           // Optional leading whitespace, supports [footag /]
		. '\\/\\]'                          // Self closing tag and closing bracket
		. '|'
		. '\\]'                             // Closing bracket
		. '(?:'                             // Unroll the loop: Optionally, anything between the opening and closing shortcode tags
		. '(?!<\/p>)'                       // Not followed by closing paragraph
		. '[^\\[]*+'                        // Not an opening bracket, matches all content between closing bracket and closing shortcode tag
		. '(?:'
		. '\\[(?!\\/\\2\\])'                // An opening bracket not followed by the closing shortcode tag
		. '[^\\[]*+'                        // Not an opening bracket
		. ')*+'
		. '\\[\\/\\2\\]'                    // Closing shortcode tag
		. ')?'
		. ')'
		. ')'
		. '\\s*+'                           // optional trailing whitespace
		. '<\\/p>'                          // closing paragraph
		. '/s';

	return preg_replace( $pattern, '$1', $pee );
}

foreach ( array( 'beer-finder' ) as $filter ) {
	remove_filter( $filter, 'shortcode_unautop' );
	add_filter( $filter, 'vip_beer_finder_shortcode_unautop' );
}

remove_filter( 'the_content', 'shortcode_unautop' );
add_filter( 'the_content', 'vip_beer_finder_shortcode_unautop' );

remove_filter( 'the_excerpt', 'shortcode_unautop' );
add_filter( 'the_excerpt', 'vip_beer_finder_shortcode_unautop' );

require 'plugin-update-checker/plugin-update-checker.php';
$VBFUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/sixteenbit/vip-beer-finder/',
	__FILE__,
	'vip-beer-finder'
);
