<?php
/**
 * APSP shared shell — Cesare Benedetti layout (header hero, hide Astra chrome).
 *
 * @package APSP_Shell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string Site folder under htdocs.
 */
function apsp_shell_site_slug() {
	static $slug = null;
	if ( null !== $slug ) {
		return $slug;
	}
	$slug = basename( dirname( ABSPATH ) );
	return $slug;
}

/**
 * Bootstrap + media helpers (Cesare blueprint).
 */
require_once __DIR__ . '/apsp-bootstrap.php';
require_once __DIR__ . '/apsp-media.php';

/**
 * Hide default Astra header; custom APSP header replaces it.
 */
function apsp_shell_hide_astra_header() {
	$css = '
		#ast-desktop-header,
		.ast-main-header-wrap,
		.site-header,
		#masthead,
		.apsp-header-slim {
			display: none !important;
		}
		.site-content {
			padding-top: 0 !important;
		}
	';
	wp_add_inline_style( 'astra-theme-css', $css );
	wp_add_inline_style( 'astra-main-style', $css );
}
add_action( 'wp_enqueue_scripts', 'apsp_shell_hide_astra_header', 99 );

/**
 * Giacomo had a pre-header band — not part of Cesare blueprint.
 */
function apsp_shell_remove_legacy_preheader() {
	if ( 'apsp-giacomo-cis' !== apsp_shell_site_slug() ) {
		return;
	}
	remove_action( 'astra_header_before', 'apsp_render_header_slim_band', 5 );
}
add_action( 'wp', 'apsp_shell_remove_legacy_preheader', 20 );

/**
 * Whether the current singular post already embeds the header shortcode.
 */
function apsp_shell_page_has_header_shortcode() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	if ( ! $post ) {
		return false;
	}
	return has_shortcode( $post->post_content, 'apsp_header' )
		|| has_shortcode( $post->post_content, 'cesare_benedetti_header' );
}

/**
 * Inject APSP header before main content (no DB changes required).
 */
function apsp_shell_inject_header() {
	if ( is_admin() || apsp_shell_page_has_header_shortcode() ) {
		return;
	}
	echo do_shortcode( '[apsp_header]' );
}
add_action( 'astra_content_top', 'apsp_shell_inject_header', 5 );

/**
 * Footer markup from theme include (site-specific content preserved).
 */
function apsp_shell_render_footer() {
	if ( is_admin() ) {
		return;
	}

	// Cesare uses the dedicated footer plugin markup.
	if ( 'apsp-cesare-benedetti' === apsp_shell_site_slug() ) {
		return;
	}

	$markup_file = get_template_directory() . '/inc/apsp-footer-markup.php';
	if ( ! file_exists( $markup_file ) ) {
		return;
	}

	echo '<footer class="site-footer apsp-footer" id="apsp-site-footer"><div class="ast-container">';
	include $markup_file;
	echo '</div></footer>';
}
add_action( 'wp_footer', 'apsp_shell_render_footer', 10 );

/**
 * Body class for shell styling hooks.
 *
 * @param array $classes Body classes.
 * @return array
 */
function apsp_shell_body_class( $classes ) {
	$classes[] = 'apsp-shell';
	$classes[] = 'apsp-shell--' . sanitize_html_class( apsp_shell_site_slug() );
	return $classes;
}
add_filter( 'body_class', 'apsp_shell_body_class' );
