<?php
/**
 * ⚠️  WARNING: THIS FILE CONTAINS TEMPORARY DEVELOPMENT CODE  ⚠️
 * 
 * 🚨  IMPORTANT: Lines 218-339 contain temporary code that MUST be removed! 🚨
 * 
 * Scroll down to see the TEMPORARY DEVELOPMENT CODE section with removal instructions.
 * 
 * ================================================
 * 
 * Astra functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define Constants
 */
define( 'ASTRA_THEME_VERSION', '4.11.3' );
define( 'ASTRA_THEME_SETTINGS', 'astra-settings' );
define( 'ASTRA_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'ASTRA_THEME_URI', trailingslashit( esc_url( get_template_directory_uri() ) ) );
define( 'ASTRA_THEME_ORG_VERSION', file_exists( ASTRA_THEME_DIR . 'inc/w-org-version.php' ) );

/**
 * Minimum Version requirement of the Astra Pro addon.
 * This constant will be used to display the notice asking user to update the Astra addon to the version defined below.
 */
define( 'ASTRA_EXT_MIN_VER', '4.11.1' );

/**
 * Load in-house compatibility.
 */
if ( ASTRA_THEME_ORG_VERSION ) {
	require_once ASTRA_THEME_DIR . 'inc/w-org-version.php';
}

/**
 * Setup helper functions of Astra.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-theme-options.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-theme-strings.php';
require_once ASTRA_THEME_DIR . 'inc/core/common-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-icons.php';

define( 'ASTRA_WEBSITE_BASE_URL', 'https://wpastra.com' );

/**
 * ToDo: Deprecate constants in future versions as they are no longer used in the codebase.
 */
define( 'ASTRA_PRO_UPGRADE_URL', ASTRA_THEME_ORG_VERSION ? astra_get_pro_url( '/pricing/', 'free-theme', 'dashboard', 'upgrade' ) : 'https://woocommerce.com/products/astra-pro/' );
define( 'ASTRA_PRO_CUSTOMIZER_UPGRADE_URL', ASTRA_THEME_ORG_VERSION ? astra_get_pro_url( '/pricing/', 'free-theme', 'customizer', 'upgrade' ) : 'https://woocommerce.com/products/astra-pro/' );

/**
 * Update theme
 */
require_once ASTRA_THEME_DIR . 'inc/theme-update/astra-update-functions.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-theme-background-updater.php';

/**
 * Fonts Files
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-font-families.php';
if ( is_admin() ) {
	require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts-data.php';
}

require_once ASTRA_THEME_DIR . 'inc/lib/webfont/class-astra-webfont-loader.php';
require_once ASTRA_THEME_DIR . 'inc/lib/docs/class-astra-docs-loader.php';
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts.php';

require_once ASTRA_THEME_DIR . 'inc/dynamic-css/custom-menu-old-header.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/container-layouts.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/astra-icons.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-walker-page.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-enqueue-scripts.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-gutenberg-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-wp-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/block-editor-compatibility.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/inline-on-mobile.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/content-background.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/dark-mode.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-dynamic-css.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-global-palette.php';

// Enable NPS Survey only if the starter templates version is < 4.3.7 or > 4.4.4 to prevent fatal error.
if ( ! defined( 'ASTRA_SITES_VER' ) || version_compare( ASTRA_SITES_VER, '4.3.7', '<' ) || version_compare( ASTRA_SITES_VER, '4.4.4', '>' ) ) {
	// NPS Survey Integration
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-notice.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-survey.php';
}

/**
 * Custom template tags for this theme.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-attr.php';
require_once ASTRA_THEME_DIR . 'inc/template-tags.php';

require_once ASTRA_THEME_DIR . 'inc/widgets.php';
require_once ASTRA_THEME_DIR . 'inc/class-at-dynamic-menu-widget.php';
require_once ASTRA_THEME_DIR . 'inc/core/theme-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/admin-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/sidebar-manager.php';

/**
 * Markup Functions
 */
require_once ASTRA_THEME_DIR . 'inc/markup-extras.php';
require_once ASTRA_THEME_DIR . 'inc/extras.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog-config.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog.php';
require_once ASTRA_THEME_DIR . 'inc/blog/single-blog.php';

/**
 * Markup Files
 */
require_once ASTRA_THEME_DIR . 'inc/template-parts.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-loop.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-mobile-header.php';

/**
 * Functions and definitions.
 */
require_once ASTRA_THEME_DIR . 'inc/class-astra-after-setup-theme.php';

// Required files.
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-helper.php';

require_once ASTRA_THEME_DIR . 'inc/schema/class-astra-schema.php';

/* Setup API */
require_once ASTRA_THEME_DIR . 'admin/includes/class-astra-api-init.php';

if ( is_admin() ) {
	/**
	 * Admin Menu Settings
	 */
	require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-settings.php';
	require_once ASTRA_THEME_DIR . 'admin/class-astra-admin-loader.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/astra-notices/class-astra-notices.php';
}

/**
 * Metabox additions.
 */
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-boxes.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-box-operations.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-elementor-editor-settings.php';

/**
 * Customizer additions.
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-customizer.php';

/**
 * Astra Modules.
 */
require_once ASTRA_THEME_DIR . 'inc/modules/posts-structures/class-astra-post-structures.php';
require_once ASTRA_THEME_DIR . 'inc/modules/related-posts/class-astra-related-posts.php';

/**
 * Compatibility
 */
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gutenberg.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-jetpack.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/woocommerce/class-astra-woocommerce.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/edd/class-astra-edd.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/lifterlms/class-astra-lifterlms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/learndash/class-astra-learndash.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bb-ultimate-addon.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-contact-form-7.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-visual-composer.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-site-origin.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gravity-forms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bne-flyout.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-ubermeu.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-divi-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-amp.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-yoast-seo.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/surecart/class-astra-surecart.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-starter-content.php';
require_once ASTRA_THEME_DIR . 'inc/addons/transparent-header/class-astra-ext-transparent-header.php';
require_once ASTRA_THEME_DIR . 'inc/addons/breadcrumbs/class-astra-breadcrumbs.php';
require_once ASTRA_THEME_DIR . 'inc/addons/scroll-to-top/class-astra-scroll-to-top.php';
require_once ASTRA_THEME_DIR . 'inc/addons/heading-colors/class-astra-heading-colors.php';
require_once ASTRA_THEME_DIR . 'inc/builder/class-astra-builder-loader.php';

// Elementor Compatibility requires PHP 5.4 for namespaces.
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor-pro.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-web-stories.php';
}

// Beaver Themer compatibility requires PHP 5.3 for anonymous functions.
if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-themer.php';
}

require_once ASTRA_THEME_DIR . 'inc/core/markup/class-astra-markup.php';

/**
 * Load deprecated functions
 */
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-filters.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-functions.php';

/**
 * ⚠️  WARNING: TEMPORARY DEVELOPMENT CODE  ⚠️
 * ================================================
 * 
 * 🚨  ALL CODE BELOW THIS LINE IS TEMPORARY AND MUST BE REMOVED  🚨
 * 
 * This includes:
 * - disable_gutenberg_editor() function
 * - Classic editor filters
 * - astra_page_dates() function  
 * - astra_enqueue_fontawesome() function
 * - astra_display_page_dates_after_title() function
 * - astra_enqueue_main_styles() function
 * 
 * REMOVAL INSTRUCTIONS:
 * 1. Delete everything from this comment block down to "END OF TEMPORARY DEVELOPMENT CODE"
 * 2. Delete page-tracking.js and page-tracking.css files
 * 3. Remove tracking code from class-at-dynamic-menu-widget.php
 * 4. Delete page-tracking.json file from uploads directory
 * 
 * ================================================
 */

/**
 * Disable Gutenberg Editor and use Classic Editor
 * TEMPORARY: Remove this function when development is complete
 */
function disable_gutenberg_editor() {
	// Disable Gutenberg for all post types
	add_filter('use_block_editor_for_post', '__return_false');
	add_filter('use_block_editor_for_post_type', '__return_false');
	
	// Disable Gutenberg for widgets
	add_filter('use_widgets_block_editor', '__return_false');
	
	// Remove Gutenberg-related scripts and styles
	add_action('wp_enqueue_scripts', function() {
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');
	});
	
	// Remove Gutenberg from admin
	add_action('admin_enqueue_scripts', function() {
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');
	});
}
add_action('init', 'disable_gutenberg_editor');

// Force classic editor for all post types
// TEMPORARY: Remove these filters when development is complete
add_filter('classic_editor_enabled_editors_for_post', function($editors, $post) {
	return array('classic');
}, 10, 2);

add_filter('classic_editor_plugin_settings', function($settings) {
	$settings['editor'] = 'classic';
	$settings['allow-users'] = false;
	return $settings;
});

/**
 * Custom function to display page creation and update dates
 * TEMPORARY: Remove this function when development is complete
 */
if ( ! function_exists( 'astra_page_dates' ) ) {
    function astra_page_dates() {
        // Set Italian locale for date formatting
        setlocale(LC_TIME, 'it_IT.UTF-8', 'it_IT', 'italian');
        
        // Get dates in Italian format with time
        $created_date = get_the_date('j F Y, H:i');
        $modified_date = get_the_modified_date('j F Y, H:i');
        
        // Italian month names
        $italian_months = array(
            'January' => 'Gennaio',
            'February' => 'Febbraio', 
            'March' => 'Marzo',
            'April' => 'Aprile',
            'May' => 'Maggio',
            'June' => 'Giugno',
            'July' => 'Luglio',
            'August' => 'Agosto',
            'September' => 'Settembre',
            'October' => 'Ottobre',
            'November' => 'Novembre',
            'December' => 'Dicembre'
        );
        
        // Convert English month names to Italian
        foreach ($italian_months as $english => $italian) {
            $created_date = str_replace($english, $italian, $created_date);
            $modified_date = str_replace($english, $italian, $modified_date);
        }
        
        $output = '<div class="page-meta">';
        $output .= '<div class="page-dates">';
        
        // Created date
        $output .= '<span class="page-date-item">';
        $output .= '<i class="fas fa-calendar-plus"></i>';
        $output .= '<strong>Creato:</strong>';
        $output .= '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">';
        $output .= esc_html( $created_date );
        $output .= '</time>';
        $output .= '</span>';
        
        // Modified date (only show if different from created date)
        if ( $modified_date !== $created_date ) {
            $output .= '<span class="page-date-item">';
            $output .= '<i class="fas fa-calendar-check"></i>';
            $output .= '<strong>Ultimo Aggiornamento:</strong>';
            $output .= '<time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">';
            $output .= esc_html( $modified_date );
            $output .= '</time>';
            $output .= '</span>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
}

/**
 * Enqueue Font Awesome for page date icons
 * TEMPORARY: Remove this function when development is complete
 */
function astra_enqueue_fontawesome() {
    if ( is_page() && ! is_front_page() ) {
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0' );
    }
}
add_action( 'wp_enqueue_scripts', 'astra_enqueue_fontawesome' );

/**
 * Hook to automatically display page dates after the title
 * TEMPORARY: Remove this function when development is complete
 */
function astra_display_page_dates_after_title() {
    if ( is_page() && ! is_front_page() ) {
        echo astra_page_dates();
    }
}
add_action( 'astra_after_page_title', 'astra_display_page_dates_after_title' );

/**
 * Enqueue main style.css file
 * TEMPORARY: Remove this function when development is complete
 */
function astra_enqueue_main_styles() {
	wp_enqueue_style(
		'astra-main-style',
		get_stylesheet_uri(),
		array(),
		ASTRA_THEME_VERSION
	);
	
	// Load bootstrap grid system first
	wp_enqueue_style('astra-bootstrap-grid', get_template_directory_uri() . '/css-modules/bootstrap-grid.css', array('astra-main-style'), ASTRA_THEME_VERSION);
	
	// Load modular CSS files
	wp_enqueue_style('astra-footer-styles', get_template_directory_uri() . '/css-modules/footer.css', array(), ASTRA_THEME_VERSION);
	wp_enqueue_style('astra-hero-styles', get_template_directory_uri() . '/css-modules/hero.css', array(), ASTRA_THEME_VERSION);
	wp_enqueue_style('astra-homepage', get_template_directory_uri() . '/css-modules/homepage.css', array(), ASTRA_THEME_VERSION);
	wp_enqueue_style('astra-homepage-polished', get_template_directory_uri() . '/css-modules/homepage-polished.css', array(), ASTRA_THEME_VERSION);
	wp_enqueue_style('astra-services-styles', get_template_directory_uri() . '/css-modules/services.css', array(), ASTRA_THEME_VERSION);
	wp_enqueue_style('astra-contact-styles', get_template_directory_uri() . '/css-modules/contact.css', array(), ASTRA_THEME_VERSION);
	
	// Load dynamic menu minimal CSS AFTER bootstrap-grid to override it
	wp_enqueue_style('astra-dynamic-menu-minimal', get_template_directory_uri() . '/css-modules/dynamic-menu-minimal.css', array('astra-bootstrap-grid', 'astra-main-style'), ASTRA_THEME_VERSION);
	
	// Load homepage carousel JavaScript
	wp_enqueue_script('astra-homepage-carousel', get_template_directory_uri() . '/assets/js/homepage-carousel.js', array(), ASTRA_THEME_VERSION, true);
	
	// Load weather widget on homepage
	if (is_front_page()) {
		wp_enqueue_script('astra-weather-widget', get_template_directory_uri() . '/assets/js/weather-widget.js', array(), ASTRA_THEME_VERSION, true);
	}
	
	// Dynamically load all custom CSS files
	astra_load_custom_css_files();
}

/**
 * OpenStreetMap shortcode for Borgo Valsugana location
 */
function apsp_osm_map_shortcode($atts) {
	$atts = shortcode_atts(array(
		'lat' => '46.0537',
		'lon' => '11.4611',
		'height' => '400'
	), $atts);
	
	$lat = floatval($atts['lat']);
	$lon = floatval($atts['lon']);
	$height = intval($atts['height']);
	
	// Calculate bounding box for OpenStreetMap
	$bbox_offset = 0.01;
	$bbox = sprintf('%f%%2C%f%%2C%f%%2C%f', 
		$lon - $bbox_offset, 
		$lat - $bbox_offset, 
		$lon + $bbox_offset, 
		$lat + $bbox_offset
	);
	
	$iframe_url = sprintf(
		'https://www.openstreetmap.org/export/embed.html?bbox=%s&layer=mapnik&marker=%f%%2C%f',
		$bbox,
		$lat,
		$lon
	);
	
	$output = '<div class="map-widget" style="margin: 2rem 0;">';
	$output .= sprintf(
		'<iframe width="100%%" height="%d" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="%s" style="border:0;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);"></iframe>',
		$height,
		$iframe_url
	);
	$output .= '<div style="text-align:center;margin-top:12px;"><small><a href="https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lon . '#map=16/' . $lat . '/' . $lon . '" target="_blank" rel="noopener" style="color:#5a6c7d;text-decoration:none;">📍 Visualizza mappa più grande</a></small></div>';
	$output .= '</div>';
	
	return $output;
}
add_shortcode('osm_map', 'apsp_osm_map_shortcode');
add_action( 'wp_enqueue_scripts', 'astra_enqueue_main_styles', 20 );

/**
 * Dynamically load all CSS files from the custom folder
 */
function astra_load_custom_css_files() {
	$custom_css_dir = get_template_directory() . '/css-modules/custom/';
	$custom_css_url = get_template_directory_uri() . '/css-modules/custom/';
	
	if (is_dir($custom_css_dir)) {
		$css_files = glob($custom_css_dir . '*.css');
		
		foreach ($css_files as $css_file) {
			$filename = basename($css_file);
			$handle = 'astra-custom-' . str_replace('.css', '', $filename);
			$file_url = $custom_css_url . $filename;
			$file_version = filemtime($css_file);
			
			wp_enqueue_style($handle, $file_url, array(), $file_version);
		}
	}
}

/**
 * Remove automatic paragraph formatting from content
 */
remove_filter( 'the_content', 'wpautop' );

/**
 * Remove [at_dynamic_menu] shortcode from page content if it's embedded
 * We already add it in the template, so remove any instances in content to prevent nesting
 */
function apsp_remove_menu_shortcode_from_content($content) {
    // Only for pages that should show the menu (check if we're in the two-column layout)
    global $post;
    if (!is_page() || !$post) {
        return $content;
    }
    
    // Check if this page should show the dynamic menu (same logic as page.php)
    $should_show_menu = false;
    $current_slug = $post->post_name;
    
    // List of Amministrazione Trasparente section slugs
    $at_sections = array(
        'amministrazione-trasparente',
        'disposizioni-generali',
        'organizzazione',
        'consulenti-e-collaboratori',
        'personale',
        'bandi-di-concorso',
        'performance',
        'enti-controllati',
        'attivita-e-procedimenti',
        'provvedimenti',
        'bandi-di-gara-e-contratti',
        'sovvenzioni-contributi-sussidi-vantaggi-economici',
        'bilanci',
        'beni-immobili-e-gestione-patrimonio',
        'controlli-e-rilievi-sullamministrazione',
        'servizi-erogati',
        'pagamenti-dellamministrazione',
        'opere-pubbliche',
        'pianificazione-e-governo-del-territorio',
        'informazioni-ambientali',
        'strutture-sanitarie-private-accreditate',
        'interventi-straordinari-e-di-emergenza',
        'altri-contenuti'
    );
    
    if (in_array($current_slug, $at_sections)) {
        $should_show_menu = true;
    } else {
        $current_ancestors = get_post_ancestors($post);
        foreach ($current_ancestors as $ancestor_id) {
            $ancestor = get_post($ancestor_id);
            if ($ancestor && ($ancestor->post_name === 'amministrazione-trasparente' || in_array($ancestor->post_name, $at_sections))) {
                $should_show_menu = true;
                break;
            }
        }
        
        // Also check URL
        if (!$should_show_menu) {
            $page_url = get_permalink($post->ID);
            if ($page_url && stripos($page_url, 'amministrazione-trasparente') !== false) {
                $should_show_menu = true;
            }
        }
    }
    
    // If menu should be shown in template, remove any shortcode instances from content
    if ($should_show_menu) {
        // Remove the shortcode and any wrapper divs that might be around it
        $content = preg_replace('/\[at_dynamic_menu[^\]]*\]/i', '', $content);
        // Also remove any divs that might have been created around it
        $content = preg_replace('/<div[^>]*class="[^"]*dynamic-menu[^"]*"[^>]*>.*?<\/div>/is', '', $content);
        $content = preg_replace('/<div[^>]*class="[^"]*sidebar-column[^"]*"[^>]*>.*?<\/div>/is', '', $content);
    }
    
    return $content;
}
add_filter('the_content', 'apsp_remove_menu_shortcode_from_content', 5); // Run early, before shortcodes are processed

/**
 * Enqueue header override styles
 * TEMPORARY: Remove this function when development is complete
 */
function astra_enqueue_header_override_styles() {
	wp_enqueue_style(
		'astra-header-override',
		get_template_directory_uri() . '/style-header-override.css',
		array(),
		ASTRA_THEME_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'astra_enqueue_header_override_styles', 25 );

/**
 * Enqueue Development URL Fixer Script
 * REMOVE THIS ENTIRE FUNCTION WHEN MIGRATING TO PRODUCTION!
 */
function enqueue_dev_url_fixer() {
    // Only load on development server
    if (strpos(home_url(), 'shcl-09573.serverlet.com') !== false) {
        wp_enqueue_script(
            'dev-url-fixer',
            get_template_directory_uri() . '/js/url-fixer.js',
            array(),
            '1.0.0',
            true
        );
        
        // Add admin notice
        if (is_admin()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p><strong>🚨 DEVELOPMENT MODE:</strong> URL Fixer script is active. Remember to remove it when migrating to production!</p>';
                echo '</div>';
            });
        }
    }
}
add_action('wp_enqueue_scripts', 'enqueue_dev_url_fixer');
add_action('admin_enqueue_scripts', 'enqueue_dev_url_fixer');

/**
 * ================================================
 * 🚨  END OF TEMPORARY DEVELOPMENT CODE  🚨
 * ================================================
 * 
 * ⚠️  DELETE EVERYTHING ABOVE THIS LINE WHEN DEVELOPMENT IS COMPLETE  ⚠️
 * 
 * Files to also delete:
 * - assets/js/page-tracking.js
 * - assets/css/page-tracking.css  
 * - uploads/page-tracking.json
 * 
 * Code to remove from:
 * - inc/class-at-dynamic-menu-widget.php (tracking functions and checkbox HTML)
 * 
 * ================================================
 */


// Add smooth scrolling for anchor links
function enqueue_smooth_scrolling() {
    wp_add_inline_script("jquery", "
jQuery(document).ready(function($) {
    // Smooth scrolling for anchor links
    $(\"a[href*=\'#\']\").on(\"click\", function(e) {
        var target = $(this).attr(\"href\");
        
        // Only handle internal anchor links
        if (target.indexOf(\"#\") !== -1) {
            var hash = target.substring(target.indexOf(\"#\"));
            var targetElement = $(hash);
            
            if (targetElement.length) {
                e.preventDefault();
                
                // Smooth scroll to the target element
                $(\"html, body\").animate({
                    scrollTop: targetElement.offset().top - 100 // Offset for fixed headers
                }, 800, \"swing\");
                
                // Update URL hash without jumping
                if (history.pushState) {
                    history.pushState(null, null, hash);
                } else {
                    location.hash = hash;
                }
            }
        }
    });
    
    // Handle direct URL access with hash (page refresh)
    if (window.location.hash) {
        var targetElement = $(window.location.hash);
        if (targetElement.length) {
            setTimeout(function() {
                $(\"html, body\").animate({
                    scrollTop: targetElement.offset().top - 100
                }, 800, \"swing\");
            }, 500); // Small delay to ensure page is loaded
        }
    }
});
");
}
add_action("wp_enqueue_scripts", "enqueue_smooth_scrolling");


/**
 * Generate breadcrumb data based on dynamic menu hierarchy
 */
function apsp_generate_breadcrumb_data() {
    if (!is_page()) {
        return array();
    }
    
    $breadcrumbs = array();
    $current_page = get_queried_object();
    
    // Start with Home
    $breadcrumbs[] = array(
        'title' => 'Home',
        'url' => home_url('/')
    );
    
    // Get the menu structure
    $menu_structure = get_option('at_dynamic_menu_structure', array());
    
    if (!empty($menu_structure)) {
        // Find the current page in the menu structure and build path
        $path = apsp_find_page_in_menu($menu_structure, $current_page->ID);
        
        if (!empty($path)) {
            // Add each item in the path to breadcrumbs
            foreach ($path as $item) {
                $breadcrumbs[] = array(
                    'title' => $item['label'],
                    'url' => get_permalink($item['page_id']),
                    'current' => ($item['page_id'] == $current_page->ID)
                );
            }
        } else {
            // Fallback: use WordPress page hierarchy if not in menu
            $ancestors = get_post_ancestors($current_page->ID);
            $ancestors = array_reverse($ancestors);
            
            foreach ($ancestors as $ancestor_id) {
                $breadcrumbs[] = array(
                    'title' => get_the_title($ancestor_id),
                    'url' => get_permalink($ancestor_id)
                );
            }
            
            // Add current page
            $breadcrumbs[] = array(
                'title' => get_the_title($current_page->ID),
                'url' => get_permalink($current_page->ID),
                'current' => true
            );
        }
    } else {
        // No menu structure, use WordPress hierarchy
        $ancestors = get_post_ancestors($current_page->ID);
        $ancestors = array_reverse($ancestors);
        
        foreach ($ancestors as $ancestor_id) {
            $breadcrumbs[] = array(
                'title' => get_the_title($ancestor_id),
                'url' => get_permalink($ancestor_id)
            );
        }
        
        $breadcrumbs[] = array(
            'title' => get_the_title($current_page->ID),
            'url' => get_permalink($current_page->ID),
            'current' => true
        );
    }
    
    return $breadcrumbs;
}

/**
 * Find a page in the menu structure and return its path
 */
function apsp_find_page_in_menu($items, $target_page_id, $path = array()) {
    foreach ($items as $item) {
        // Add current item to path
        $current_path = $path;
        $current_path[] = array(
            'label' => $item['label'],
            'page_id' => $item['page_id']
        );
        
        // Check if this is the target page
        if ($item['page_id'] == $target_page_id) {
            return $current_path;
        }
        
        // Check children recursively
        if (!empty($item['children'])) {
            $result = apsp_find_page_in_menu($item['children'], $target_page_id, $current_path);
            if ($result !== null) {
                return $result;
            }
        }
    }
    
    return null;
}

/**
 * Enqueue entry header CSS and JS
 */
function enqueue_entry_header_assets() {
    // Enqueue CSS
    wp_enqueue_style(
        'entry-header-css',
        get_template_directory_uri() . '/assets/css/entry-header.css',
        array(),
        '1.0.0'
    );
    
    // Enqueue JS
    wp_enqueue_script(
        'entry-header-js',
        get_template_directory_uri() . '/assets/js/entry-header.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Pass breadcrumb data to JavaScript
    $breadcrumb_data = apsp_generate_breadcrumb_data();
    wp_localize_script('entry-header-js', 'apspBreadcrumbData', array(
        'breadcrumbs' => $breadcrumb_data,
        'homeUrl' => home_url('/')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_entry_header_assets');

// Enqueue minimal dynamic menu styles
function enqueue_dynamic_menu_minimal_styles() {
    wp_enqueue_style(
        'dynamic-menu-minimal',
        get_template_directory_uri() . '/css-modules/dynamic-menu-minimal.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_dynamic_menu_minimal_styles');

/**
 * Check if current page is a child of "Amministrazione Trasparente" in the dynamic menu
 */
function is_amministrazione_trasparente_page() {
    if (!is_page() || is_front_page()) {
        return false;
    }
    
    $current_page_id = get_queried_object_id();
    $menu_structure = get_option('at_dynamic_menu_structure', array());
    
    // Find "Amministrazione Trasparente" in the menu and check if current page is a child
    foreach ($menu_structure as $item) {
        // Check if this is the "Amministrazione Trasparente" section
        $label = isset($item['label']) ? trim(str_replace(array('"', '&quot;', '\"'), '', $item['label'])) : '';
        
        if (stripos($label, 'Amministrazione Trasparente') !== false) {
            // Found the AT section, now check if current page is in its children
            if (is_page_in_menu_branch($item, $current_page_id)) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Recursively check if a page ID exists in a menu branch
 */
function is_page_in_menu_branch($menu_item, $page_id) {
    // Check if this item matches
    if (isset($menu_item['page_id']) && (int)$menu_item['page_id'] === $page_id) {
        return true;
    }
    
    // Check children recursively
    if (!empty($menu_item['children']) && is_array($menu_item['children'])) {
        foreach ($menu_item['children'] as $child) {
            if (is_page_in_menu_branch($child, $page_id)) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Enqueue Amministrazione Trasparente styles for legislative pages
 * Applies custom styling to pages with legislative content (D.Lgs. 33/2013)
 * Only loads on pages that are children of "Amministrazione Trasparente" in the dynamic menu
 */
function enqueue_amministrazione_trasparente_styles() {
    // Only load on pages that are children of "Amministrazione Trasparente"
    if (is_amministrazione_trasparente_page()) {
        wp_enqueue_style(
            'amministrazione-trasparente',
            get_template_directory_uri() . '/assets/css/amministrazione-trasparente.css',
            array(),
            filemtime(get_template_directory() . '/assets/css/amministrazione-trasparente.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_amministrazione_trasparente_styles');

/**
 * Make parent menu items act as grouping placeholders (non-clickable)
 * 
 * This function makes the four main parent menu items ("Chi siamo", "Servizi", 
 * "Albo pretorio", "Attività") act as grouping titles by setting their href to "#"
 * so they don't navigate to pages when clicked.
 * 
 * @param array $atts The HTML attributes for the menu item's anchor element
 * @param WP_Post $menu_item The current menu item object
 * @param stdClass $args An object of wp_nav_menu() arguments
 * @param int $depth Depth of menu item
 * @return array Modified attributes array
 */
function apsp_make_parent_menu_items_placeholders($atts, $menu_item, $args, $depth) {
	// Only affect top-level menu items (depth 0)
	if ($depth !== 0) {
		return $atts;
	}
	
	// Define the four parent menu item titles that should act as placeholders
	$parent_titles = array(
		'Chi siamo',
		'Servizi', 
		'Albo pretorio',
		'Attività'
	);
	
	// Check if the current menu item title matches one of the parent titles
	$item_title = isset($menu_item->title) ? trim($menu_item->title) : '';
	
	if (in_array($item_title, $parent_titles, true)) {
		// Set href to "#" to prevent navigation
		$atts['href'] = '#';
		
		// Add a class to identify this as a placeholder menu item
		if (!empty($atts['class'])) {
			$atts['class'] .= ' menu-item-placeholder';
		} else {
			$atts['class'] = 'menu-item-placeholder';
		}
	}
	
	return $atts;
}
add_filter('nav_menu_link_attributes', 'apsp_make_parent_menu_items_placeholders', 20, 4);

/**
 * Prevent navigation for placeholder menu items
 * 
 * Adds JavaScript to prevent the default link behavior (scroll to top) 
 * when clicking on placeholder menu items that have href="#"
 */
function apsp_prevent_placeholder_menu_navigation() {
	wp_add_inline_script('jquery', "
	jQuery(document).ready(function($) {
		// Prevent navigation for placeholder menu items
		$('.menu-item-placeholder').on('click', function(e) {
			// Only prevent if the href is exactly '#'
			if ($(this).attr('href') === '#') {
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		});
	});
	");
}
add_action('wp_enqueue_scripts', 'apsp_prevent_placeholder_menu_navigation');

/**
 * Fix old image paths from migration
 * 
 * Replaces old /var/avio/storage/images/ paths with correct WordPress uploads paths
 * Also fixes WordPress upload paths that are missing the subdirectory prefix
 * This fixes images that were imported from the old system
 */
function apsp_fix_old_image_paths($content) {
	// Static cache to avoid searching for the same file multiple times
	static $path_cache = array();
	
	$upload_dir = wp_upload_dir();
	$upload_base_url = $upload_dir['baseurl'];
	$upload_base_dir = $upload_dir['basedir'];
	$upload_path = parse_url($upload_base_url, PHP_URL_PATH);
	
	// First, fix WordPress upload paths that are missing the subdirectory
	// Pattern: /wp-content/uploads/... (missing subdirectory like /apsp-avio/)
	if ($upload_path && $upload_path !== '/wp-content/uploads') {
		// Extract the subdirectory part (e.g., /apsp-avio from /apsp-avio/wp-content/uploads)
		$subdir = str_replace('/wp-content/uploads', '', $upload_path);
		if ($subdir && $subdir !== '/') {
			// Replace /wp-content/uploads/ with /apsp-avio/wp-content/uploads/
			$content = preg_replace('/(src|href)=["\']\/wp-content\/uploads\//', '$1="' . esc_attr($upload_path) . '/', $content);
		}
	}
	
	// Pattern to match old image paths: /var/avio/storage/images/.../filename.jpg
	if (preg_match_all('/\/var\/avio\/storage\/images\/[^"\']+?\/([^\/"\']+\.(jpg|jpeg|png|gif|webp|svg))/i', $content, $matches, PREG_SET_ORDER)) {
		
		foreach ($matches as $match) {
			$old_path = $match[0];
			$filename = $match[1];
			
			// Check cache first
			if (isset($path_cache[$filename])) {
				$new_path = $path_cache[$filename];
			} else {
				// Try to find the file in uploads directory
				// First, try searching in common locations (2026/01, 2025, etc.)
				$possible_paths = array(
					$upload_base_dir . '/2026/01/' . $filename,
					$upload_base_dir . '/2025/' . $filename,
					$upload_base_dir . '/2024/' . $filename,
					$upload_base_dir . '/' . $filename,
				);
				
				$found = false;
				$new_path = '';
				
				foreach ($possible_paths as $test_path) {
					if (file_exists($test_path)) {
						$relative_path = str_replace($upload_base_dir, '', $test_path);
						$new_path = $upload_base_url . $relative_path;
						$found = true;
						break;
					}
				}
				
				// If not found, try to search recursively (but limit depth for performance)
				if (!$found) {
					$found_file = apsp_find_image_in_uploads($filename, $upload_base_dir);
					if ($found_file) {
						$relative_path = str_replace($upload_base_dir, '', $found_file);
						$new_path = $upload_base_url . $relative_path;
						$found = true;
					}
				}
				
				// Cache the result (even if not found, to avoid repeated searches)
				$path_cache[$filename] = $new_path;
			}
			
			// Replace the path if we found the file
			if (!empty($new_path)) {
				// Escape the old path for use in regex
				$old_path_escaped = preg_quote($old_path, '/');
				// Replace in src attributes
				$content = preg_replace('/src=["\']' . $old_path_escaped . '["\']/', 'src="' . esc_attr($new_path) . '"', $content);
				// Replace in href attributes (for linked images)
				$content = preg_replace('/href=["\']' . $old_path_escaped . '["\']/', 'href="' . esc_attr($new_path) . '"', $content);
				// Replace in background-image URLs and other contexts
				$content = str_replace($old_path, $new_path, $content);
			}
		}
	}
	
	return $content;
}
add_filter('the_content', 'apsp_fix_old_image_paths', 20);

/**
 * Helper function to find image file in uploads directory
 * 
 * @param string $filename The filename to search for
 * @param string $directory The directory to search in
 * @param int $max_depth Maximum recursion depth
 * @return string|false Full path if found, false otherwise
 */
function apsp_find_image_in_uploads($filename, $directory, $max_depth = 3) {
	if ($max_depth <= 0) {
		return false;
	}
	
	if (!is_dir($directory)) {
		return false;
	}
	
	$files = scandir($directory);
	foreach ($files as $file) {
		if ($file === '.' || $file === '..') {
			continue;
		}
		
		$path = $directory . '/' . $file;
		
		if (is_file($path) && $file === $filename) {
			return $path;
		}
		
		if (is_dir($path)) {
			$found = apsp_find_image_in_uploads($filename, $path, $max_depth - 1);
			if ($found) {
				return $found;
			}
		}
	}
	
	return false;
}
