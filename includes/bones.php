<?php
function bfg_scripts_and_styles() {
    wp_register_style( 'bfg-css', get_stylesheet_directory_uri() . '/css/style.css', false, null );
    wp_register_style( 'bfg-ie-only', get_stylesheet_directory_uri() . '/css/ie.css', false, null );
    wp_register_style( 'bfg-ie-universal', 'http://universal-ie6-css.googlecode.com/files/ie6.1.1.css', false, null );
    if ( is_singular() && comments_open() & get_option( 'thread_comments' ) == 1 ) {
		wp_enqueue_script( 'comment-reply' );
    } else {
		wp_dequeue_script( 'comment-reply' );
    }

    wp_register_script( 'bfg-js', get_stylesheet_directory_uri() . '/js/scripts-ck.js', array( 'jquery' ), null, true );

    wp_dequeue_script( 'superfish' );
    wp_dequeue_script( 'superfish-args' );

    if( !is_admin() ) {
		wp_deregister_script( 'jquery' );
		wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', false, null);

		wp_enqueue_style( 'bfg-css' );
		wp_enqueue_style( 'bfg-ie-only' );
		wp_enqueue_style( 'bfg-ie-universal' );
	    wp_enqueue_script( 'bfg-js' );
    }
}


function bfg_ie_conditionals( $tag, $handle ) {
	if( 'bfg-css' == $handle ) {
        $output = '<!--[if !IE]> -->' . "\n" . $tag . '<!-- <![endif]-->' . "\n";
        $output .= '<!--[if gte IE 8]>' . "\n" . $tag . '<![endif]-->' . "\n";
	} elseif( 'bfg-ie-only' == $handle ) {
        $output = '<!--[if gte IE 8]>' . "\n" . $tag . '<![endif]-->' . "\n";
	} elseif( 'bfg-ie-universal' == $handle ) {
        $output = '<!--[if lt IE 8]>' . "\n" . $tag . '<![endif]-->' . "\n";
	}

    return $output;
}


function bfg_viewport_meta() {
	echo '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />';
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';
}


function bfg_dont_update( $r, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/themes/update-check' ) )
		return $r; // Not a theme update request. Bail immediately.
	$themes = unserialize( $r['body']['themes'] );
	unset( $themes[ get_option( 'template' ) ] );
	unset( $themes[ get_option( 'stylesheet' ) ] );
	$r['body']['themes'] = serialize( $themes );
	return $r;
}


function bfg_filter_ptags_on_images($content){
	return preg_replace( '/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}


function bfg_remove_wp_widget_recent_comments_style() {
	if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) ) {
		remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
	}
}


function bfg_remove_recent_comments_style() {
	global $wp_widget_factory;
	if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
		remove_action( 'wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ));
	}
}


function bfg_gallery_style($css) {
	return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
}


function bfg_load_favicon( $favicon_url ) {
	return get_stylesheet_directory_uri() . '/images/favicon.ico';
}


function bfg_no_js_body_class( $classes ) {
	$classes[] = 'no-js';
	return $classes;
}


/* http://wpdaily.co/top-10-snippets/ */
function bfg_custom_image_size_choices($sizes) {
        $sizes['new-size'] = 'Test Size';
        return $sizes;
}