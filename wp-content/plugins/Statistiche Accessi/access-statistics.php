<?php
/**
 * Plugin Name: Contatore Accessi
 * Description: Plugin per gestire e visualizzare le statistiche di accesso - Use shortcode [access_stats]
 * Version: 1.0.13
 * Author: Super Anthony & Giollins
 * License: GPL v2 or later
 * Text Domain: access-statistics
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ACCESS_STATS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACCESS_STATS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ACCESS_STATS_VERSION', '1.0.13');

// Include required files
require_once ACCESS_STATS_PLUGIN_PATH . 'includes/class-access-statistics.php';

// Initialize the plugin
function access_statistics_init() {
    new Access_Statistics();
}
add_action('plugins_loaded', 'access_statistics_init');

// Enqueue frontend styles
function access_statistics_enqueue_styles() {
    wp_enqueue_style('access-statistics-style', ACCESS_STATS_PLUGIN_URL . 'assets/style.css', array(), ACCESS_STATS_VERSION);
}
add_action('wp_enqueue_scripts', 'access_statistics_enqueue_styles');

// Activation hook
register_activation_hook(__FILE__, 'access_statistics_activate');
function access_statistics_activate() {
    // Create database table
    Access_Statistics::create_table();
    
    // Insert sample data
    Access_Statistics::insert_sample_data();
    
    // Set initial current year
    update_option('access_statistics_current_year', date('Y'));
    
    // Set default display setting (show unique visits by default)
    if (get_option('access_statistics_show_unique_visits') === false) {
        update_option('access_statistics_show_unique_visits', true);
    }
    
    // Note: Current year statistics will be created only when actual visits occur
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'access_statistics_deactivate');
function access_statistics_deactivate() {
    // Plugin deactivation cleanup if needed
} 