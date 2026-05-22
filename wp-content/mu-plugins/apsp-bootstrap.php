<?php
/**
 * Bootstrap 4 assets (Cesare Benedetti blueprint).
 *
 * @package APSP_Shell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function apsp_enqueue_bootstrap_assets() {
	wp_enqueue_style(
		'apsp-bootstrap-css',
		'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
		array(),
		'4.5.2'
	);
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script(
		'apsp-popper-js',
		'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js',
		array(),
		'2.9.2',
		true
	);
	wp_enqueue_script(
		'apsp-bootstrap-js',
		'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js',
		array( 'jquery', 'apsp-popper-js' ),
		'4.5.2',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'apsp_enqueue_bootstrap_assets' );
