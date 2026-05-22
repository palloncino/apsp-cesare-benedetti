<?php
/**
 * APSP Cesare Benedetti blueprint — theme additions without replacing Astra functions.php.
 *
 * @package APSP_Cesare_Benedetti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrap 4 assets used by custom header/footer shortcodes.
 */
function cesare_benedetti_enqueue_bootstrap_assets() {
	wp_enqueue_style(
		'bootstrap-css',
		'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
		array(),
		'4.5.2'
	);
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script(
		'popper-js',
		'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js',
		array(),
		'2.9.2',
		true
	);
	wp_enqueue_script(
		'bootstrap-js',
		'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js',
		array( 'jquery', 'popper-js' ),
		'4.5.2',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'cesare_benedetti_enqueue_bootstrap_assets' );
