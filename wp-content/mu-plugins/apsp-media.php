<?php
/**
 * Local media URL helpers (Cesare Benedetti blueprint).
 *
 * @package APSP_Shell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $filename Upload basename.
 * @return string
 */
function apsp_media_url( $filename ) {
	$filename = basename( (string) $filename );
	$subdir   = '2024/09';
	$path     = WP_CONTENT_DIR . '/uploads/' . $subdir . '/' . $filename;

	if ( file_exists( $path ) ) {
		return content_url( 'uploads/' . $subdir . '/' . $filename );
	}

	$bundled_map = array(
		'logo.png'                      => 'logo.svg',
		'fantastic-blue-sky-scaled.jpg' => 'hero-bg.svg',
		'asd.png'                       => 'asd.svg',
		'pagopa.png'                    => 'pagopa.svg',
		'distretto-family-1.png'        => 'distretto-family-1.svg',
	);

	$asset = isset( $bundled_map[ $filename ] ) ? $bundled_map[ $filename ] : $filename;

	return plugins_url( 'assets/' . $asset, __FILE__ );
}

/**
 * @param string $path Page path/slug.
 * @return string
 */
function apsp_page_url( $path ) {
	$slug = trim( (string) $path, '/' );
	$page = $slug ? get_page_by_path( $slug ) : null;

	if ( $page instanceof WP_Post ) {
		return get_permalink( $page );
	}

	return home_url( '/' . $slug . '/' );
}

if ( ! function_exists( 'cesare_benedetti_media_url' ) ) {
	function cesare_benedetti_media_url( $filename ) {
		return apsp_media_url( $filename );
	}
}

if ( ! function_exists( 'cesare_benedetti_page_url' ) ) {
	function cesare_benedetti_page_url( $path ) {
		return apsp_page_url( $path );
	}
}
