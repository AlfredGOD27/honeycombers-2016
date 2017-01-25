<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function hc_do_social() {

	if( !function_exists('get_field') )
		return;

	$sites = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
	);
	?>

	<div class="social">
		<?php
		foreach( $sites as $slug => $name ) {
			$url = get_field( '_hc_' . $slug . '_url', 'option' );
			if( empty($url) )
				continue;

			?>
			<a href="<?php echo esc_url($url); ?>" title="<?php echo $name; ?>"><i class="fa fa-<?php echo $slug; ?>" aria-hidden="true"></i></a>
			<?php
		}
		?>
	</div>
	<?php

}
