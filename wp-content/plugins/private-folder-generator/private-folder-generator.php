<?php
/*
Plugin Name: Private Folder Generator
Description: Automatically creates a user-specific folder upon registration and displays the user's files on their account page. Use the shortcode [user_files] to display the files in the user's private folder.
Version: 1.3
Author: Antonio Guiotto
*/

// Function to create user-specific folder upon registration
function create_user_folder_on_registration($user_id) {
    // Get user data
    $user_info = get_userdata($user_id);
    $first_name = sanitize_file_name($user_info->first_name); // Sanitize first name
    $last_name = sanitize_file_name($user_info->last_name);   // Sanitize last name
    $fiscal_code = sanitize_file_name(get_user_meta($user_id, 'fiscal_code', true)); // Get and sanitize fiscal code

    // Construct folder name: first name + last name + fiscal code
    $folder_name = strtolower($first_name . '_' . $last_name . '_' . $fiscal_code);

    // Define the path for the user's folder
    $upload_dir = wp_upload_dir(); // Get the upload directory
    $user_folder_path = $upload_dir['basedir'] . '/user-documents/' . $folder_name;

    // Create the directory if it doesn't exist
    if (!file_exists($user_folder_path)) {
        mkdir($user_folder_path, 0755, true); // Create the folder with appropriate permissions
    }

    // Optionally store the folder path in user meta for easy reference
    update_user_meta($user_id, 'user_folder_path', $user_folder_path);
}

// Hook into Ultimate Member's registration completion
add_action('um_registration_complete', 'create_user_folder_on_registration', 10, 1);

// Function to display user files
function display_user_files() {
    // Get current user information
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $display_name = $current_user->display_name;

    // Greet the user in Italian with improved styling
    echo '<div class="account-page">';
    echo '<header class="account-header" style="margin-bottom: 20px; padding: 10px; border-bottom: 2px solid #e0e0e0;">';
    echo '<h1 style="font-size: 24px; margin-bottom: 10px; color: #333;">Benvenuto, ' . esc_html($display_name) . '!</h1>';
    echo '<p style="font-size: 16px; color: #555;">In questa sezione, puoi accedere e scaricare i documenti che ti sono stati condivisi.</p>';
    echo '</header>';

    echo '<section class="account-intro" style="margin: 20px 0;">';
    echo '<h2 style="font-size: 20px; margin-bottom: 10px; color: #333;">I tuoi documenti privati</h2>';
    echo '<p style="font-size: 14px; color: #666;">Qui sotto troverai i file disponibili per il download. Se non vedi nulla, significa che l\'amministratore non ha ancora caricato documenti per te.</p>';
    echo '</section>';

    // Retrieve user-specific folder path from user meta
    $user_folder_path = get_user_meta($user_id, 'user_folder_path', true);
    $upload_dir = wp_upload_dir(); // Get the upload directory
    $user_folder_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $user_folder_path);

    echo '<section class="user-files-list" style="padding: 20px; border: 1px solid #ddd; background: #f9f9f9;">';
    // Check if the folder exists
    if ($user_folder_path && file_exists($user_folder_path)) {
        // Get all files in the folder
        $files = scandir($user_folder_path);

        // Filter out the '.' and '..' entries
        $files = array_diff($files, array('.', '..'));

        if (!empty($files)) {
            echo '<ul class="file-list" style="list-style-type: none; padding: 0; margin: 0;">';
            foreach ($files as $file) {
                $file_url = esc_url($user_folder_url . '/' . $file);
                echo '<li class="file-item" style="margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #e0e0e0; border-radius: 5px;">';
                echo '<a href="' . $file_url . '" download style="font-size: 16px; color: #0073aa; text-decoration: none;">' . esc_html($file) . '</a>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="no-files" style="font-size: 14px; color: #888;">Non ci sono file condivisi con te al momento.</p>';
        }
    } else {
        echo '<p class="no-files" style="font-size: 14px; color: #888;">La tua cartella non è disponibile.</p>';
    }
    echo '</section>';
    echo '</div>';
}

// Shortcode to display user files on a specific page
function user_files_shortcode() {
    ob_start();
    display_user_files();
    return ob_get_clean();
}
add_shortcode('user_files', 'user_files_shortcode');

// Redirect users to the custom account page right after login
function redirect_to_account_page_after_login($user_login, $user) {
    // Get the account page URL
    $account_page_url = get_permalink(63); // ID of the custom account page

    // Redirect to the account page
    wp_safe_redirect($account_page_url);
    exit;
}
add_action('wp_login', 'redirect_to_account_page_after_login', 10, 2);
