<?php
/**
 * Plugin Name: News Page
 * Description: A plugin to display and manage posts via a shortcode.
 * Version: 1.0
 * Author: APSP Cesare Benedetti
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue custom styles and scripts
function news_page_scripts() {
    wp_enqueue_style('news-page-style', plugins_url('/assets/css/style.css', __FILE__));
    wp_enqueue_script('news-page-script', plugins_url('/assets/js/script.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'news_page_scripts');

// Add shortcode to display and manage posts
function news_page_shortcode($atts) {
    // Handle the sorting/filtering with shortcode attributes (optional)
    $atts = shortcode_atts(array(
        'category' => '',
        'order'    => 'DESC', // Sorting by date, DESC means newest first
    ), $atts);

    ob_start(); // Start output buffering

    // Fetch posts with query arguments, ordering by date
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 10, // Adjust the number of posts to show
        'orderby'   => 'date', // Order by date
        'order'     => $atts['order'], // Use the 'DESC' or 'ASC' for sorting
        'paged'     => $paged
    );

    $loop = new WP_Query($args);

    if ($loop->have_posts()) :
        echo '<div class="news-page-loop">';
        while ($loop->have_posts()) : $loop->the_post();
            echo '<div class="post-item">';
            echo '<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
            echo '<div class="post-date">' . get_the_date() . '</div>'; // Display the post date
            echo '<div class="post-excerpt">' . get_the_excerpt() . '</div>';
            echo '<a href="' . get_permalink() . '">Read More</a>';
            echo '</div>';
        endwhile;
        echo '</div>';

        // Pagination
        echo '<div class="pagination">';
        echo paginate_links(array(
            'total' => $loop->max_num_pages,
            'current' => $paged,
            'prev_text' => __('« Previous'),
            'next_text' => __('Next »'),
        ));
        echo '</div>';

        wp_reset_postdata(); // Reset post data after the loop
    else :
        echo '<p>No posts found.</p>';
    endif;

    return ob_get_clean();
}
add_shortcode('news_page', 'news_page_shortcode');

// Optionally, handle form submissions (e.g., filtering or sorting)
add_action('init', 'news_page_handle_filters');
function news_page_handle_filters() {
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        add_filter('news_page', function($atts) {
            $atts['category'] = sanitize_text_field($_GET['category']);
            return $atts;
        });
    }
}
