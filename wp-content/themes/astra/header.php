<?php
/**
 * The header for Astra Theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?><!DOCTYPE html>
<?php astra_html_before(); ?>
<html <?php language_attributes(); ?>>
<head>
<?php astra_head_top(); ?>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
if ( apply_filters( 'astra_header_profile_gmpg_link', true ) ) {
	?>
	<link rel="profile" href="https://gmpg.org/xfn/11"> 
	<?php
}
?>
<?php wp_head(); ?>
<?php astra_head_bottom(); ?>
	
	<!-- TEMPORARY: Development URL Fixer - Remove when migrating to production -->
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		// ========================================
		// 🚨 MIGRATION WARNING - REMOVE THIS CODE 🚨
		// ========================================
		// This entire script block must be removed when migrating to production
		// It's only needed for subdirectory setups
		// ========================================
		
		// Get WordPress path dynamically from PHP
		var wpPath = '<?php echo esc_js( parse_url( home_url(), PHP_URL_PATH ) ); ?>';
		
		// If no path, try to detect from current location
		if (!wpPath || wpPath === '/') {
			var currentPath = window.location.pathname;
			// Extract subdirectory if present (e.g., /apsp-avio/ from /apsp-avio/chi-siamo/...)
			var pathMatch = currentPath.match(/^\/([^\/]+)\//);
			if (pathMatch && pathMatch[1] !== 'wp-admin' && pathMatch[1] !== 'wp-content') {
				wpPath = '/' + pathMatch[1];
			}
		}
		
		// Only run if we have a subdirectory path
		if (!wpPath || wpPath === '/') {
			console.log('%c⚠️  URL Fixer: No subdirectory detected, skipping URL fixes', 'background: #ff6600; color: #ffffff; font-size: 14px; padding: 8px;');
			return;
		}
		
		console.log('%c🚨 DEVELOPMENT MODE 🚨', 'background: #ff0000; color: #ffffff; font-size: 24px; font-weight: bold; padding: 20px; border: 5px solid #ff0000;');
		console.log('%cURL Fixer: Initializing for path:', 'background: #ff6600; color: #ffffff; font-size: 18px; font-weight: bold; padding: 10px;', wpPath);
		console.log('%c⚠️  REMOVE THIS ENTIRE SCRIPT BLOCK WHEN MIGRATING TO PRODUCTION ⚠️', 'background: #ff0000; color: #ffffff; font-size: 16px; font-weight: bold; padding: 15px; border: 3px solid #ff0000;');
		
		// Function to fix relative links and images
		function fixRelativeLinks() {
			var links = document.querySelectorAll('a[href^="/"]');
			var images = document.querySelectorAll('img[src^="/"]');
			var fixedCount = 0;
			
			// Fix links
			links.forEach(function(link) {
				var href = link.getAttribute('href');
				
				// Skip WordPress system URLs
				if (href.startsWith('/wp-admin') || 
					href.startsWith('/wp-content') || 
					href.startsWith('/wp-includes') ||
					href.startsWith('/wp-json') ||
					href.startsWith('/xmlrpc.php') ||
					href.startsWith('/wp-cron.php') ||
					href.startsWith('/wp-login.php') ||
					href.startsWith('/wp-signup.php') ||
					href.startsWith('/wp-trackback.php') ||
					href.startsWith('/wp-comments-post.php') ||
					href.startsWith('/wp-links-opml.php') ||
					href.startsWith('/wp-mail.php') ||
					href.startsWith('/wp-activate.php') ||
					href.startsWith('/wp-blog-header.php') ||
					href.startsWith('/wp-load.php') ||
					href.startsWith('/wp-settings.php')) {
					return;
				}
				
				// Add WordPress path prefix if not already present
				if (!href.startsWith(wpPath + '/') && !href.startsWith(wpPath + '?')) {
					var newHref = wpPath + href;
					link.setAttribute('href', newHref);
					console.log('%c🔗 Fixed link:', 'background: #0066ff; color: #ffffff; font-weight: bold;', href, '→', newHref);
					fixedCount++;
				}
			});
			
			// Fix images - wp-content paths should NOT get the subdirectory prefix
			// They're already correct as-is, so we don't modify them
			
			if (fixedCount > 0) {
				console.log('%c✅ URL Fixer: Fixed', 'background: #00aa00; color: #ffffff; font-weight: bold;', fixedCount, 'links');
			}
		}
		
		// Fix links immediately
		fixRelativeLinks();
		
		// Also fix links after any dynamic content loads
		setTimeout(fixRelativeLinks, 1000);
		setTimeout(fixRelativeLinks, 3000);
		
		// Watch for dynamically added content
		var observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.type === 'childList') {
					fixRelativeLinks();
				}
			});
		});
		
		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	});
	</script>
</head>

<body <?php astra_schema_body(); ?> <?php body_class(); ?>>
<?php astra_body_top(); ?>
<?php wp_body_open(); ?>

<a
	class="skip-link screen-reader-text"
	href="#content"
	title="<?php echo esc_attr( astra_default_strings( 'string-header-skip-link', false ) ); ?>">
		<?php echo esc_html( astra_default_strings( 'string-header-skip-link', false ) ); ?>
</a>

<div
<?php
	echo wp_kses_post(
		astra_attr(
			'site',
			array(
				'id'    => 'page',
				'class' => 'hfeed site',
			)
		)
	);
	?>
>
	<?php
	astra_header_before();

	astra_header();

	astra_header_after();

	astra_content_before();
	?>
	<div id="content" class="site-content">
		<div class="ast-container">
		<?php astra_content_top(); ?>
