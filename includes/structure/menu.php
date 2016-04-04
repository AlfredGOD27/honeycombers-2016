<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Remove the primary and secondary menus
 *
 * @since 2.0.9
 */
remove_action( 'genesis_after_header', 'genesis_do_nav' );
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

remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_after_header', 'hc_do_nav' );
function hc_do_nav() {

	printf( '<h2 class="screen-reader-text">%s</h2>', __( 'Main navigation', 'genesis' ) );

	$transient_name = 'hc_main_menu_html';
	$transient      = get_transient( $transient_name );

	if( false === $transient ) {
		$menu = get_field( 'hc_main_menu', 'option' );
		if( empty($menu) )
			return;

		$sanitized_location = 'primary';

		$nav_markup_open = genesis_markup( array(
			'html5'   => '<nav %s>',
			'xhtml'   => '<div id="nav-primary">',
			'context' => 'nav-primary',
			'echo'    => false,
		) );
		$nav_markup_open .= genesis_structural_wrap( 'menu-primary', 'open', 0 );

		$nav_markup_close = genesis_structural_wrap( 'menu-primary', 'close', 0 );
		$nav_markup_close .= '</nav>';

		ob_start();
		?>
		<div class="show-phone mobile-icon-nav">
			<?php $page_id = get_field( '_hc_calendar_page_id', 'option' ); ?>
			<a href="<?php echo get_permalink($page_id); ?>" class="btn btn-icon"><i class="ico-calendar"></i> <span>Calendar</span></a>

			<?php $page_id = get_field( '_hc_directory_page_id', 'option' ); ?>
			<a href="<?php echo get_permalink($page_id); ?>" class="btn btn-icon"><i class="ico-pin"></i> <span>Directory</span></a>

			<?php $page_id = get_field( '_hc_video_page_id', 'option' ); ?>
			<a href="<?php echo get_permalink($page_id); ?>" class="btn btn-icon"><i class="ico-play"></i> <span>Video</span></a>
		</div>

		<ul class="main-menu clearfix">
			<?php
			foreach( $menu as $top_item ) {
				echo !empty($top_item['columns']) ? '<li class="menu-item has-children">' : '<li class="menu-item">';
					$label = sanitize_text_field($top_item['label']);

					if( !empty($top_item['columns']) ) {
						echo '<a href="' . get_permalink($top_item['item_id']) . '" class="menu-item-link">' . $label . '</a>';
						echo '<button type="button" class="inactive-link"><span>' . $label . '</span> <i class="ico-arrow-down"></i> <i class="ico-arrow-up"></i></button>';

						echo '<div class="sub-menu">';
							echo '<ul class="clearfix">';
								$column_count = count($top_item['columns']);

								$i = 1;
								foreach( $top_item['columns'] as $column ) {
									// Column class
									$column_class = '';
									switch( $column_count ) {
										case 2:
											$column_class = 'one-half';
											break;
										case 3:
											$column_class = 'one-third';
											break;
										case 4:
											$column_class = 'one-fourth';
											break;
									}

									// Type
									echo 1 === $i % $column_count ? '<li class="menu-col menu-col-' . $column['type'] . ' clearfix first ' . $column_class . '">' : '<li class="menu-col menu-col-' . $column['type'] . ' clearfix ' . $column_class . '">';
										switch( $column['type'] ) {
											case 'links':
												?>
												<div class="one-half first left">
													<i class="ico-<?php echo $column['icon']; ?>"></i>
												</div>

												<div class="one-half right">
													<ul>
														<?php
														foreach( $column['item_ids'] as $item_id ) {
															?>
															<li>
																<a href="<?php echo get_permalink($item_id); ?>"><?php echo get_the_title($item_id); ?></a>
															</li>
															<?php
														}
														?>
													</ul>
												</div>
												<?php
												break;
											case 'post':
												?>
												<div class="one-third first">
													<?php
													if( !empty($column['label']) )
														echo '<span>' . sanitize_text_field($column['label']) . '</span>';
													?>

													<a href="<?php echo get_permalink($column['item_id']); ?>"><?php echo get_the_title($column['item_id']); ?></a>
												</div>

												<div class="two-thirds">
													<?php
													if( has_post_thumbnail($column['item_id']) )
														echo get_the_post_thumbnail($column['item_id'], 'archive-small', '', array('class' => 'async-load-image skip-image-on-mobile') );
													?>
												</div>
												<?php
												break;
										}
									echo '</li>';
									++$i;
								}
							echo '</ul>';
						echo '</div>';
					} else {
						echo '<a href="' . get_permalink($top_item['item_id']) . '" class="menu-item-link">' . $label . '</a>';
					}
				echo '</li>';
			}
			?>
		</ul>

		<div class="show-phone mobile-social-nav">
			<span><span>Follow Us</span></span>
			<?php hc_do_social(); ?>
		</div>
		<?php
		$nav = ob_get_clean();

		$transient = $nav_markup_open . $nav . $nav_markup_close;
		set_transient( $transient_name, $transient, YEAR_IN_SECONDS );
	}

	echo $transient;

}

add_action( 'acf/save_post', 'hc_clear_menu_transient', 12 );
function hc_clear_menu_transient( $post_id ) {

	if( 'options' !== $post_id )
		return;

	$transient_name = 'hc_main_menu_html';
	delete_transient($transient_name);

}
