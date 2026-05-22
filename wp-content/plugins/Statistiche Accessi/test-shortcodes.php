<?php
/**
 * Test file for Access Statistics Plugin Shortcodes
 * 
 * This file demonstrates how to use the shortcodes and can be used for testing.
 * You can include this in a WordPress page or post to test the functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Test shortcodes
echo '<h2>Test Access Statistics Shortcodes</h2>';

echo '<h3>Total Visits:</h3>';
echo do_shortcode('[access_total_visits]');

echo '<h3>Unique Visits:</h3>';
echo do_shortcode('[access_unique_visits]');

echo '<h3>All Statistics:</h3>';
echo do_shortcode('[access_stats]');

echo '<h3>Current Year Statistics:</h3>';
echo do_shortcode('[access_stats year="' . date('Y') . '"]');

echo '<hr>';
echo '<p><strong>Note:</strong> These shortcodes will display the actual statistics from your database.</p>';
echo '<p>Make sure to visit some pages to generate visit data.</p>';
?> 