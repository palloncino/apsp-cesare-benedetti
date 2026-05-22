<?php
/**
 * Local media URLs for Cesare Benedetti blueprint (avoids cross-origin staging redirects).
 *
 * @package APSP_Cesare_Benedetti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve upload path for blueprint images (2024/09).
 *
 * @param string $filename Basename only.
 * @return string Public URL on this site.
 */
function cesare_benedetti_media_url( $filename ) {
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
 * Internal link: prefer local page permalink when the slug exists.
 *
 * @param string $path Path after home, e.g. chi-siamo/.
 * @return string
 */
function cesare_benedetti_page_url( $path ) {
	$slug = trim( (string) $path, '/' );
	$page = $slug ? get_page_by_path( $slug ) : null;

	if ( $page instanceof WP_Post ) {
		return get_permalink( $page );
	}

	return home_url( '/' . $slug . '/' );
}
