<?php
/**
 * Installation Test Script for Access Statistics Plugin
 * 
 * This script helps verify that the plugin is working correctly.
 * Run this after activating the plugin to test functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Test function
function test_access_statistics_plugin() {
    echo '<div style="max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">';
    echo '<h2>🔍 Access Statistics Plugin - Installation Test</h2>';
    
    // Test 1: Check if plugin class exists
    echo '<h3>Test 1: Plugin Class</h3>';
    if (class_exists('Access_Statistics')) {
        echo '<p style="color: green;">✅ Access_Statistics class found</p>';
    } else {
        echo '<p style="color: red;">❌ Access_Statistics class not found</p>';
        return;
    }
    
    // Test 2: Check database tables
    echo '<h3>Test 2: Database Tables</h3>';
    global $wpdb;
    
    $stats_table = $wpdb->prefix . 'access_statistics';
    $visits_table = $wpdb->prefix . 'access_statistics_visits';
    
    $stats_exists = $wpdb->get_var("SHOW TABLES LIKE '$stats_table'") == $stats_table;
    $visits_exists = $wpdb->get_var("SHOW TABLES LIKE '$visits_table'") == $visits_table;
    
    if ($stats_exists) {
        echo '<p style="color: green;">✅ Statistics table exists</p>';
    } else {
        echo '<p style="color: red;">❌ Statistics table missing</p>';
    }
    
    if ($visits_exists) {
        echo '<p style="color: green;">✅ Visits table exists</p>';
    } else {
        echo '<p style="color: red;">❌ Visits table missing</p>';
    }
    
    // Test 3: Check shortcodes
    echo '<h3>Test 3: Shortcodes</h3>';
    
    $shortcodes = array(
        'access_stats',
        'access_total_visits',
        'access_unique_visits'
    );
    
    foreach ($shortcodes as $shortcode) {
        if (shortcode_exists($shortcode)) {
            echo '<p style="color: green;">✅ Shortcode ['.$shortcode.'] registered</p>';
        } else {
            echo '<p style="color: red;">❌ Shortcode ['.$shortcode.'] not found</p>';
        }
    }
    
    // Test 4: Test shortcode output
    echo '<h3>Test 4: Shortcode Output</h3>';
    
    echo '<h4>Total Visits:</h4>';
    echo do_shortcode('[access_total_visits]');
    
    echo '<h4>Unique Visits:</h4>';
    echo do_shortcode('[access_unique_visits]');
    
    echo '<h4>All Statistics:</h4>';
    echo do_shortcode('[access_stats]');
    
    // Test 5: Check admin menu
    echo '<h3>Test 5: Admin Menu</h3>';
    if (current_user_can('manage_options')) {
        echo '<p style="color: green;">✅ Admin menu should be visible (check WordPress admin)</p>';
        echo '<p><a href="' . admin_url('admin.php?page=access-statistics') . '" target="_blank">Open Admin Panel</a></p>';
    } else {
        echo '<p style="color: orange;">⚠️ Admin menu test skipped (not admin user)</p>';
    }
    
    // Test 6: Debug information
    echo '<h3>Test 6: Debug Information</h3>';
    echo '<p><strong>WordPress Version:</strong> ' . get_bloginfo('version') . '</p>';
    echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
    echo '<p><strong>Plugin Directory:</strong> ' . plugin_dir_path(__FILE__) . '</p>';
    echo '<p><strong>Debug Mode:</strong> ' . (defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled') . '</p>';
    
    echo '<hr>';
    echo '<h3>🎯 Next Steps</h3>';
    echo '<ol>';
    echo '<li>Visit some pages on your site to generate visit data</li>';
    echo '<li>Check the admin panel to see real-time statistics</li>';
    echo '<li>Use the shortcodes in your pages and posts</li>';
    echo '<li>Enable WP_DEBUG in wp-config.php for detailed logging</li>';
    echo '</ol>';
    
    echo '<p><strong>Note:</strong> If you see any red ❌ errors above, please check your WordPress error logs and ensure the plugin is properly activated.</p>';
    
    echo '</div>';
}

// Run the test if this file is included
if (function_exists('test_access_statistics_plugin')) {
    test_access_statistics_plugin();
}
?> 