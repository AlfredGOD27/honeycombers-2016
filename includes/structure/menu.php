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
				<?php
				$sites = array(
					'facebook'  => 'Facebook',
					'youtube'   => 'YouTube',
					'instagram' => 'Instagram',
					'twitter'   => 'Twitter',
				);
				?>

				<div class="social">
					<?php
					foreach( $sites as $slug => $name ) {
						$url = get_field( '_hc_' . $slug . '_url', 'option' );
						if( empty($url) )
							continue;

						?>
						<a href="<?php echo esc_url($url); ?>" title="<?php echo $name; ?>"><i class="ico-<?php echo $slug; ?>"></i></a>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php

}
