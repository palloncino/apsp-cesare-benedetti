<?php
/**
 * Plugin Name: APSP Google Map Shortcode
 * Plugin URI:  https://www.apsp-grigno.it/
 * Description: Adds a customizable Google Map embed via shortcode [apsp_map] for APSP Grigno.
 * Version:     1.0.0
 * Author:      Antonio Guiotto
 * Author URI:  https://www.apsp-grigno.it/
 * License:     GPLv2 or later
 * Text Domain: apsp-map-shortcode
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the [apsp_map] shortcode.
 *
 * Usage:
 *   [apsp_map address="Via Vittorio Emanuele, 131, 38055 Grigno" width="600" height="450" zoom="14"]
 *   [apsp_map] (uses default APSP Grigno address)
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML embed code.
 */
function apsp_map_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'address' => 'Via Vittorio Emanuele, 131, 38055 Grigno',
        'width'   => '600',
        'height'  => '450',
        'zoom'    => '14',
        'maptype' => 'roadmap',
        'title'   => 'APSP Grigno - La nostra sede',
    ), $atts, 'apsp_map' );

    $address_encoded = urlencode( $atts['address'] );
    $src = "https://maps.google.com/maps?q={$address_encoded}&amp;z={$atts['zoom']}&amp;output=embed&amp;t={$atts['maptype']}";

    $output = sprintf(
        '<div class="apsp-map-wrapper" style="margin: 20px 0;">
            <h3 class="apsp-map-title" style="margin-bottom: 15px; color: #2c3e50; font-size: 1.2rem;">%1$s</h3>
            <iframe 
                width="%2$s" 
                height="%3$s" 
                frameborder="0" 
                style="border:0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);" 
                src="%4$s" 
                allowfullscreen
                loading="lazy"
                title="%1$s">
            </iframe>
            <div class="apsp-map-address" style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                <strong>Indirizzo:</strong> %5$s
            </div>
        </div>',
        esc_html( $atts['title'] ),
        esc_attr( $atts['width'] ),
        esc_attr( $atts['height'] ),
        esc_url( $src ),
        esc_html( $atts['address'] )
    );

    return $output;
}
add_shortcode( 'apsp_map', 'apsp_map_shortcode' );

/**
 * Enqueue plugin styles.
 */
function apsp_map_shortcode_styles() {
    wp_enqueue_style( 'apsp-map-shortcode', plugin_dir_url( __FILE__ ) . 'css/apsp-map.css', array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'apsp_map_shortcode_styles' );

/**
 * Add admin menu for map settings (optional enhancement).
 */
function apsp_map_admin_menu() {
    add_options_page(
        'APSP Map Settings',
        'APSP Map',
        'manage_options',
        'apsp-map-settings',
        'apsp_map_settings_page'
    );
}
add_action( 'admin_menu', 'apsp_map_admin_menu' );

/**
 * Admin settings page.
 */
function apsp_map_settings_page() {
    ?>
    <div class="wrap">
        <h1>APSP Google Map Settings</h1>
        <div class="card">
            <h2>Shortcode Usage</h2>
            <p>Use the shortcode <code>[apsp_map]</code> in your posts and pages to display a Google Map.</p>
            
            <h3>Default Usage:</h3>
            <code>[apsp_map]</code>
            
            <h3>Custom Usage:</h3>
            <code>[apsp_map address="Via Vittorio Emanuele, 131, 38055 Grigno" width="800" height="400" zoom="15" title="APSP Grigno"]</code>
            
            <h3>Available Parameters:</h3>
            <ul>
                <li><strong>address</strong> - The address to display (default: APSP Grigno address)</li>
                <li><strong>width</strong> - Map width in pixels (default: 600)</li>
                <li><strong>height</strong> - Map height in pixels (default: 450)</li>
                <li><strong>zoom</strong> - Zoom level 1-20 (default: 14)</li>
                <li><strong>maptype</strong> - Map type: roadmap, satellite, hybrid, terrain (default: roadmap)</li>
                <li><strong>title</strong> - Title displayed above the map (default: "APSP Grigno - La nostra sede")</li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Add shortcode button to TinyMCE editor.
 */
function apsp_map_add_tinymce_button() {
    if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
        return;
    }
    
    if ( get_user_option( 'rich_editing' ) == 'true' ) {
        add_filter( 'mce_external_plugins', 'apsp_map_add_tinymce_plugin' );
        add_filter( 'mce_buttons', 'apsp_map_register_tinymce_button' );
    }
}
add_action( 'admin_init', 'apsp_map_add_tinymce_button' );

function apsp_map_add_tinymce_plugin( $plugin_array ) {
    $plugin_array['apsp_map'] = plugin_dir_url( __FILE__ ) . 'js/tinymce-button.js';
    return $plugin_array;
}

function apsp_map_register_tinymce_button( $buttons ) {
    array_push( $buttons, 'apsp_map' );
    return $buttons;
}

/**
 * Plugin activation hook.
 */
function apsp_map_activate() {
    // Add default options if needed
    add_option( 'apsp_map_default_address', 'Via Vittorio Emanuele, 131, 38055 Grigno' );
    add_option( 'apsp_map_default_title', 'APSP Grigno - La nostra sede' );
}
register_activation_hook( __FILE__, 'apsp_map_activate' );

/**
 * Plugin deactivation hook.
 */
function apsp_map_deactivate() {
    // Clean up if needed
}
register_deactivation_hook( __FILE__, 'apsp_map_deactivate' ); 