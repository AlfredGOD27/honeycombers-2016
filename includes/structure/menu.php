<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Remove the primary and secondary menus
 *
 * @since 2.0.9
 */
// remove_action( 'genesis_after_header', 'genesis_do_nav' );
remove_action( 'genesis_after_header', 'genesis_do_subnav' );

add_action( 'genesis_after_header', 'hc_nav_open', 8 );
function hc_nav_open() {

	?>
	<div class="nav-primary-wrapper">
		<div class="wrap">
			<div class="left">
	<?php

}

add_action( 'genesis_after_header', 'hc_nav_close', 12 );
function hc_nav_close() {

	?>
			</div>
			<div class="right">
				<?php hc_do_social(); ?>
			</div>
		</div>
	</div>
	<?php

}
