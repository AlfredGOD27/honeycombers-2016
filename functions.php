<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists( 'HC' ) ) :

final class HC {
	private static $_instance = null;

	private static $classes_init = array(
		'archives',
		'components',
		'entry',
		'events',
		'favorites',
		'formatting',
		'schemas',
		'share',
		'users',
	);

	public static function instance() {

		if( is_null(self::$_instance ) )
			self::$_instance = new self();

		return self::$_instance;

	}

	/**
	 * HC Constructor.
	 */
	public function __construct() {

		$this->define_constants();
		$this->includes();

	}

	/**
	 * Define HC Constants.
	 */
	private function define_constants() {

		define( 'CHILD_THEME_NAME', 'The Honeycombers' );
		define( 'CHILD_THEME_TEXT_DOMAIN', 'hc' );

	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes() {

		// Initialize Genesis
		require_once get_template_directory() . '/lib/init.php';

		// Libraries
		// require_once CHILD_DIR . '/vendor/autoload.php';

		foreach( self::$classes_init as $key )
			$this->{$key} = require_once CHILD_DIR . '/includes/classes/' . $key . '.php';

		// Developer Tools
		require_once CHILD_DIR . '/includes/developer-tools.php';		// DO NOT USE THESE ON A LIVE SITE

		// Genesis
		require_once CHILD_DIR . '/includes/genesis.php';				// Customizations to Genesis-specific functions

		// Admin
		require_once CHILD_DIR . '/includes/admin/admin-functions.php';	// Customization to admin functionality
		require_once CHILD_DIR . '/includes/admin/admin-views.php';		// Customizations to the admin area display
		require_once CHILD_DIR . '/includes/admin/admin-branding.php';	// Admin view customizations that specifically involve branding
		require_once CHILD_DIR . '/includes/admin/admin-options.php';	// For adding/editing theme options to Genesis

		// Structure (corresponds to Genesis's lib/structure)
		require_once CHILD_DIR . '/includes/structure/archive.php';
		require_once CHILD_DIR . '/includes/structure/comments.php';
		require_once CHILD_DIR . '/includes/structure/footer.php';
		require_once CHILD_DIR . '/includes/structure/header.php';
		require_once CHILD_DIR . '/includes/structure/layout.php';
		require_once CHILD_DIR . '/includes/structure/loops.php';
		require_once CHILD_DIR . '/includes/structure/menu.php';
		require_once CHILD_DIR . '/includes/structure/post.php';
		require_once CHILD_DIR . '/includes/structure/search.php';
		require_once CHILD_DIR . '/includes/structure/sidebar.php';
		require_once CHILD_DIR . '/includes/structure/social.php';

		// Shame
		require_once CHILD_DIR . '/includes/shame.php';					// For new code snippets that haven't been sorted and commented yet

	}

}

endif;

function HC() {

	return HC::instance();

}

// Global for backwards compatibility.
$GLOBALS['hc'] = HC();
