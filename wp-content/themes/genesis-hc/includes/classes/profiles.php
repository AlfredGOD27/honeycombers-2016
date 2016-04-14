<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HC_Profiles {
	public function __construct() {

		$this->base_url  = 'profile';
		$this->endpoints = array(
			'edit',
			'add-folder',
			'edit-folder',
			'view-folder',
		);

		add_action( 'init', array($this, 'rewrites'), 1 );
		add_action( 'wp', array($this, 'init'), 99 );

	}

	public function rewrites() {

		$page_id = get_option( 'options__hc_profile_page_id' );

		add_rewrite_tag( '%hc_profile_endpoint%', '([^&]+)' );

		add_rewrite_rule(
			'^' . $this->base_url . '/?$',
			'index.php?p=' . $page_id . '&hc_profile_endpoint=base',
			'top'
		);

		foreach( $this->endpoints as $endpoint ) {
			add_rewrite_rule(
				'^' . $this->base_url . '/' . $endpoint . '/?$',
				'index.php?p=' . $page_id . '&hc_profile_endpoint=' . $endpoint,
				'top'
			);
		}

	}

	public function init() {

		global $wp_query;

		$this->endpoint = get_query_var( 'hc_profile_endpoint' );
		if( empty($this->endpoint) )
			return;

		if( 'base' !== $this->endpoint && !in_array($this->endpoint, $this->endpoints, true) )
			return;

		$wp_query->is_404 = false;
		status_header(200);

		add_action( 'template_include', array($this, 'do_seo') );
		add_filter( 'body_class', array($this, 'body_classes') );
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_loop', array(HC()->messages, 'display') );

		if( !is_user_logged_in() ) {
			HC()->messages->add( 'error', 'You must <a href="#" class="open-popup-link" data-mfp-src="#login-popup">login</a> to edit your profile.' );

			return;
		} else {
			$this->user_id = get_current_user_id();
			$this->user    = get_user_by( 'id', $this->user_id );
		}

		add_action( 'genesis_loop', array($this, 'display_heading') );

		switch( $this->endpoint ) {
			case 'base':
				add_action( 'genesis_loop', array($this, 'display_landing') );
				break;
		}

	}

	public function get_url( $endpoint = false ) {

		$url = get_bloginfo( 'url' );
		$url = trailingslashit($url);
		$url .= $this->base_url . '/';

		if( false !== $endpoint && in_array( $endpoint, $this->endpoints, true ) )
			$url .= $endpoint . '/';

		return $url;

	}

	public function get_first_name( $user_id = false ) {

		if( false === $user_id ) {
			$user = $this->user;
		} else {
			$user = get_user_by( 'id', $user_id );
		}

		if( !empty($user->first_name))
			return $user->first_name;

		if( !empty($user->display_name))
			return $user->display_name;

		return $user->user_login;

	}

	public function get_full_name( $user_id = false ) {

		if( false === $user_id ) {
			$user = $this->user;
		} else {
			$user = get_user_by( 'id', $user_id );
		}

		if( !empty($user->first_name) && !empty($user->last_name) )
			return $user->first_name . ' ' . $user->last_name;

		if( !empty($user->display_name))
			return $user->display_name;

		return $user->user_login;

	}

	public function do_seo() {

		// If WordPress SEO is installed, overwrite everything. Otherwise, just replace the <title>
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if( is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php') ) {
			add_action( 'wpseo_robots', array($this, 'noindex') );
			add_filter( 'wpseo_canonical', array($this, 'seo_canonical') );
			add_filter( 'wpseo_title', array($this, 'seo_title') );
		} else {
			add_filter( 'wp_title', array($this, 'seo_title') );
		}

		return get_query_template( 'index' );

	}

	public function noindex() {

		return 'noindex,nofollow';

	}

	public function seo_canonical( $canonical ) {

		return $this->get_url( $this->endpoint );

	}

	public function seo_title( $title ) {

		return 'XXX';

	}

	public function body_classes( $classes ) {

		$classes[] = 'profile';
		$classes[] = 'profile-' . $this->endpoint;

		return $classes;

	}

	public function display_heading() {

		?>
		<heading class="profile-heading clearfix">
			<div class="left two-thirds first">
				<?php
				echo get_avatar( $this->user_id, 120 );
				?>

				<div class="profile-welcome">
					<span>Welcome</span>
					<h1><?php echo $this->get_full_name(); ?></h1>
					<nav class="profile-nav">
						<a href="<?php echo $this->get_url('edit'); ?>">Edit profile</a>
						<a href="<?php echo $this->get_url('folders'); ?>">Edit folders</a>
					</nav>
				</div>
			</div>

			<?php
			if( 'base' === $this->endpoint ) {
				?>
				<div class="right one-third">
					<div class="profile-favorites-info">
						<p class="orange">When you see the <i class="ico-heart"></i> Just click to save!</p>
						<p class="black">Save, organise and share your favourite posts here</p>
					</div>
				</div>
				<?php
			}
			?>
		</heading>
		<?php

	}

	public function display_landing() {

		$folders = HC()->favorites->get_folders( $this->user_id );

		$boxes = array();

		foreach( $folders as $folder )
			$boxes[] = $folder;

		$boxes[] = array(
			'name'        => 'Create Your Own Folder',
			'description' => 'Ideas for a night out, a dinner date or a quick getaway!',
			'url'         => $this->get_url('add-folder'),
			'icon'        => 'plus',
		);

		?>
		<div class="profile-boxes clearfix">
			<?php
			$i = 1;
			foreach( $boxes as $box ) {
				echo 1 === $i % 3 ? '<div class="one-third first box">' : '<div class="one-third box">';
					?>
					<a href="<?php echo $box['url']; ?>">
						<div class="top">
							<?php
							if( !empty($box['image_id']) )	{
								echo wp_get_attachment_image( $box['image_id'], 'archive' );
							} elseif( !empty($box['icon']) ) {
								echo '<i class="ico-' . $box['icon'] . '"></i>';
							}
							?>
						</div>

						<div class="bottom">
							<h3><?php echo $box['name']; ?></h3>

							<?php
							if( !empty($box['description']) ) {
								?>
								<p><?php echo $box['description']; ?></p>
								<?php
							}
							?>
						</div>
					</a>
					<?php
				echo '</div>';
				++$i;
			}
			?>
		</div>
		<?php

	}

}

return new HC_Profiles();
