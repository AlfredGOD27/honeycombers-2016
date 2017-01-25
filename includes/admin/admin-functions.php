<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// add_action( 'admin_enqueue_scripts', 'hc_load_admin_assets' );
/**
 * Enqueue admin CSS and JS files.
 *
 * @since 2.3.2
 */
function hc_load_admin_assets() {

	$stylesheet_dir        = get_stylesheet_directory_uri();
	$use_production_assets = genesis_get_option('hc_production_on');
	$use_production_assets = !empty($use_production_assets);

	$src = $use_production_assets ? '/build/css/admin.min.css' : '/build/css/admin.css';
	wp_enqueue_style( 'hc-admin', $stylesheet_dir . $src, array(), null );

	$src = $use_production_assets ? '/build/js/admin.min.js' : '/build/js/admin.js';
	wp_enqueue_script( 'hc-admin', $stylesheet_dir . $src, array('jquery'), null, true );

}

add_action( 'pre_ping', 'hc_disable_self_pings' );
/**
 * Prevent the child theme from being overwritten by a WordPress.org theme with the same name.
 *
 * See: http://wp-snippets.com/disable-self-trackbacks/
 *
 * @since 2.0.0
 */
function hc_disable_self_pings( &$links ) {

	foreach ( $links as $l => $link )
		if ( 0 === strpos( $link, home_url() ) )
			unset($links[$l]);

}

/*
 * Change WP JPEG compression (WP default is 90%).
 *
 * See: http://wpmu.org/how-to-change-jpeg-compression-in-wordpress/
 *
 * @since 2.0.14
 */
// add_filter( 'jpeg_quality', create_function( '', 'return 80;' ) );

/*
 * Add new image sizes.
 *
 * See: http://wptheming.com/2014/04/features-wordpress-3-9/
 *
 * @since 2.0.0
 */
add_image_size( 'slide', 850, 400, true );
add_image_size( 'slide-thumbnail', 420, 100, true );

add_image_size( 'archive-small', 300, 250, true );
add_image_size( 'archive', 460, 315, true );
add_image_size( 'archive-large', 620, 375, true );

add_image_size( 'featured', 930, 550, true );

add_image_size( 'avatar', 120, 120, true );

add_filter( 'jpeg_quality', 'hc_jpeg_quality' );
function hc_jpeg_quality() {

	return 90;

}

// add_filter( 'image_size_names_choose', 'hc_image_size_names_choose' );
/**
 * Add new image sizes to media size selection menu.
 *
 * See: http://wpdaily.co/top-10-snippets/
 *
 * @since 2.0.0
 */
function hc_image_size_names_choose( $sizes ) {

	$sizes['desktop-size'] = __( 'Desktop', CHILD_THEME_TEXT_DOMAIN );

	return $sizes;

}

/**
 * List available image sizes with width and height.
 *
 * See: http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
 *
 * @since 2.2.24
 */
function hc_get_image_sizes( $size = '' ) {

	global $_wp_additional_image_sizes;

	$sizes = array();

	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach( $get_intermediate_image_sizes as $_size ) {
		if( in_array( $_size, array('thumbnail', 'medium', 'large'), true ) ) {
			$sizes[$_size]['width']  = get_option( $_size . '_size_w' );
			$sizes[$_size]['height'] = get_option( $_size . '_size_h' );
			$sizes[$_size]['crop']   = (bool) get_option( $_size . '_crop' );
		} elseif ( isset( $_wp_additional_image_sizes[$_size] ) ) {
			$sizes[$_size] = array(
				'width'  => $_wp_additional_image_sizes[$_size]['width'],
				'height' => $_wp_additional_image_sizes[$_size]['height'],
				'crop'   => $_wp_additional_image_sizes[$_size]['crop'],
			);
		}
	}

	// Get only 1 size if found
	if( $size )
		return isset($sizes[$size]) ? $sizes[$size] : false;

	return $sizes;

}

/*
 * Downsize the original uploaded image if it's too large
 *
 * See: https://wordpress.stackexchange.com/questions/63707/automatically-replace-original-uploaded-image-with-large-image-size
 *
 * @since 2.3.6
 */
// add_filter( 'wp_generate_attachment_metadata', 'hc_downsize_uploaded_image', 99 );
function hc_downsize_uploaded_image( $image_data ) {

	$max_image_size_name = 'large';

	// Abort if no max image
	if( !isset($image_data['sizes'][$max_image_size_name]) )
		return $image_data;

	// paths to the uploaded image and the max image
	$upload_dir              = wp_upload_dir();
	$uploaded_image_location = $upload_dir['basedir'] . '/' . $image_data['file'];
	$max_image_location      = $upload_dir['path'] . '/' . $image_data['sizes'][$max_image_size_name]['file'];

	// Delete original image
	unlink($uploaded_image_location);

	// Rename max image to original image
	rename( $max_image_location, $uploaded_image_location );

	// Update and return image metadata
	$image_data['width']  = $image_data['sizes'][$max_image_size_name]['width'];
	$image_data['height'] = $image_data['sizes'][$max_image_size_name]['height'];
	unset($image_data['sizes'][$max_image_size_name]);

	return $image_data;

}

/*
 * Activate the Link Manager
 *
 * See: http://wordpressexperts.net/how-to-activate-link-manager-in-wordpress-3-5/
 *
 * @since 2.0.1
 */
// add_filter( 'pre_option_link_manager_enabled', '__return_true' );		// Activate

/*
 * Disable pingbacks
 *
 * See: http://wptavern.com/how-to-prevent-wordpress-from-participating-in-pingback-denial-of-service-attacks
 *
 * Still having pingback/trackback issues? This post might help: https://wordpress.org/support/topic/disabling-pingbackstrackbacks-on-pages#post-4046256
 *
 * @since 2.2.3
 */
add_filter( 'xmlrpc_methods', 'hc_remove_xmlrpc_pingback_ping' );
function hc_remove_xmlrpc_pingback_ping( $methods ) {

	unset($methods['pingback.ping']);

	return $methods;

};

/*
 * Disable XML-RPC
 *
 * See: https://wordpress.stackexchange.com/questions/78780/xmlrpc-enabled-filter-not-called
 *
 * @since 2.2.12
 */
if( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) exit;

/*
 * Automatically remove readme.html (and optionally xmlrpc.php) after a WP core update
 *
 * @since 2.2.26
 */
add_action( '_core_updated_successfully', 'hc_remove_files_on_upgrade' );
function hc_remove_files_on_upgrade() {

	if( file_exists(ABSPATH . 'readme.html') )
		unlink(ABSPATH . 'readme.html');

	if( file_exists(ABSPATH . 'xmlrpc.php') )
		unlink(ABSPATH . 'xmlrpc.php');

	if( file_exists(ABSPATH . 'wp-login.php') )
		unlink(ABSPATH . 'wp-login.php');

}

//Remove youtube suggestions
add_filter('oembed_result', 'modify_YT_embed_url');
function modify_YT_embed_url($html) {
    return str_replace('?enablejsapi=1', '?enablejsapi=1&rel=0', $html);
}

//Add body class Advertorial or Editorial
add_filter( 'body_class', function( $classes ) {
	global $post;
	$article_type = get_field('_hc_post_is_sponsored',$post->ID);
	if (is_single()) {
		if( get_field('_hc_post_is_sponsored',$post->ID) )
			{
				$article = 'article-advertorial';
			}
			else
			{
				$article = 'article-editorial';
			}
	}
    return array_merge( $classes, array( $article ) );
} );

add_action( 'amp_post_template_head', 'custom_fonts' );

function custom_fonts( $amp_template ) {
    $post_id = $amp_template->get( 'post_id' );
    ?>
    <link rel="canonical" href="<?php echo get_permalink($post_id); ?>" />
    <link href="https://fonts.googleapis.com/css?family=PT+Serif" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Suranna" rel="stylesheet">
    <?php
}

//Add font awesome
function prefix_add_footer_styles() {
    wp_enqueue_style( 'fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
};
add_action( 'get_footer', 'prefix_add_footer_styles' );

//AMP styling
add_action( 'amp_post_template_css', 'ad_amp_styles' );

function ad_amp_styles( $amp_template ) {
    // only CSS here please...
    ?>
    html {
    	background: none;
    }
    .amp-wp-title {
    	font-family: 'Suranna', serif;
    }
    body p, body ul {
    	font-family: 'PT Serif', serif;
    }
    .amp-wp-header > div, .amp-wp-header {
    	background: #F79433;
    }
    .amp-wp-header > div > a {
    	color: #fff;
    }
    a {
    	color: #F79433;
    }
    .amp-wp-byline amp-img {
    	display: none;
    }
    .amp-wp-header > div > a {
		background-image: url(<?php echo get_stylesheet_directory_uri() . '/build/images/logo.svg'; ?>);
        background-repeat: no-repeat;
        background-size: contain;
        display: block;
        text-indent: -9999px;
    }
    .amp-wp-article-footer {
    	margin-top: 2em;
    }
    .amp-wp-footer > div > h2, .amp-wp-footer > div > p {
    	display: none;
    }
    .amp-related-posts ul {
    	margin-left: 0;
        list-style: none;
    }
    .amp-related-posts ul li {
    	width: 49%;
        display: inline-block;
        text-align: center;
    }
    .amp-related-posts ul li a {
    	text-decoration: none;
        margin-top: 10px;
    }
    .amp-related-posts ul li p {
    	height: 35px;
        min-height: 35px;
        overflow: hidden;
        padding-left: 10px;
    	padding-right: 10px;
    }
    .wp-caption .wp-caption-text, .amp-wp-meta {
    	font-size: 11px;
    }
    figure {
		width: calc(100% + 30px);
        margin-left: -15px;
        margin-right: -15px;
    }
    .amp-wp-article-featured-image {
    	width: calc(100% + 30px);
        margin-left: -15px;
        margin-right: -15px;
    }
    <?php
}

add_filter( 'amp_post_template_file', 'xyz_amp_set_custom_tax_meta_template', 10, 3 );

function xyz_amp_set_custom_tax_meta_template( $file, $type, $post ) {
    if ( 'meta-taxonomy' === $type ) {
        $file = dirname( __FILE__ ) . '/t/meta-custom-tax.php';
    }
    return $file;
}

/**
 * Add related posts to AMP amp_post_article_footer_meta
 */
function my_amp_post_article_footer_meta( $parts ) {

	$index = 1;
	
	array_splice( $parts, $index, 0, array( 'my-related-posts' ) );

	return $parts;
}
add_filter( 'amp_post_article_footer_meta', 'my_amp_post_article_footer_meta' );

/**
 * Designate the template file for related posts
 */
function my_amp_related_posts_path( $file, $template_type, $post ) {

	if ( 'my-related-posts' === $template_type ) {
		$file = get_stylesheet_directory() . '/amp/related-posts.php';
	}
	return $file;
}
add_filter( 'amp_post_template_file', 'my_amp_related_posts_path', 10, 3 );

add_filter( 'amp_post_template_analytics', 'xyz_amp_add_custom_analytics' );
function xyz_amp_add_custom_analytics( $analytics ) {
    if ( ! is_array( $analytics ) ) {
        $analytics = array();
    }

    // https://developers.google.com/analytics/devguides/collection/amp-analytics/
    $analytics['xyz-googleanalytics'] = array(
        'type' => 'googleanalytics',
        'attributes' => array(
			'config' => 'https://www.googletagmanager.com/amp.json?id=GTM-TLR97FQ&gtm.url=SOURCE_URL',
            'data-credentials' => 'include',
        ),
        'config_data' => array(
            'vars' => array(
                'gaTrackingId' => "UA-38721717-4",
				'gtm_css_2_0' => 'a:not([href*=\"thehoneycombers.com\"]), a:not([href*=\"thehoneycombers.com\"]) *',
            ),
            'triggers' => array(
                'trackPageview' => array(
                    'on' => 'visible',
                    'request' => 'pageview',
                ),
				"11" => array (
					"request" => "2",
					"selector" => ":not(*)${gtm_css_2_0}",
					"vars" => array(
						"gtm.event" => "gtm.click"
					),
					"on" => "click"
				),
				"3" => array(
					"request" => "2",
					"scrollSpec" => array(
						"verticalBoundaries" => [25, 50, 75, 100],
						"horizontalBoundaries" => [100]
					),
					"vars" => array(
						"gtm.event" => "gtm.scroll"
					),
					"on" => "scroll"
				)
            ),
        ),
    );

    return $analytics;
}

