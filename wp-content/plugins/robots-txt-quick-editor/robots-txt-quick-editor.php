<?php

/**
 * Plugin Name: Robots.txt Quick Editor
 * Description: Plugin to edit robots.txt file
 * Version: 0.4
 * Author: Davide Airaghi
 * Author URI: http://www.airaghi.net
 * Text Domain: robots-txt-quick-editor
 * Domain Path: /languages
 * License: GPLv2 or later
 */

defined('ABSPATH') or die("No script kiddies please!");

define('ROBOTS_TXT_QUICK_EDITOR_NONCE' ,'_wpnonce');
define('ROBOTS_TXT_QUICK_EDITOR_ACTION','robots-txt-quick-editor-page-options');
define('ROBOTS_TXT_QUICK_EDITOR_OPTION_CONTENT','robots-txt-quick-editor-content');
define('ROBOTS_TXT_QUICK_EDITOR_OPTION_OVERWRITE','robots-txt-quick-editor-overwrite');

add_action('admin_menu', 'airaghi_robots_txt_quick_editor_admin_menu');
add_action('init','airaghi_robots_txt_quick_editor_init');

add_filter('robots_txt','airaghi_robots_txt_quick_editor_filter',PHP_INT_MAX,2);

function airaghi_robots_txt_quick_editor_init() {
    load_plugin_textdomain( 'robots-txt-quick-editor', false, 'robots-txt-quick-editor/languages' );
}

function airaghi_robots_txt_quick_editor_capabilities_list() {
    return [ 
        /* WP    */ 'edit_files','manage_options','unfiltered_upload','upload_files',
        /* YOAST */ 'wpseo_manage_options','aioseo_general_settings',
        /* AIOS  */ 'aioseo_tools_settings','aioseo_feature_manager_settings','aioseo_local_seo_settings'
      ];
}

function airaghi_robots_txt_quick_editor_check_capabilities() {
    $list = airaghi_robots_txt_quick_editor_capabilities_list();
    foreach ($list as $cap) {
        if (current_user_can($cap)) {
            return true;
        }
    }
    return false;
}

function airaghi_robots_txt_quick_editor_admin_menu() {
    $list = airaghi_robots_txt_quick_editor_capabilities_list();
    foreach ($list as $cap) {
        if (current_user_can($cap)) {
            add_submenu_page('options-general.php','Robots.txt','Robots.txt',$cap,'robots-txt-quick-editor-page','airaghi_robots_txt_quick_editor_admin_page');
            break;
        }
    }
    $robots = get_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_CONTENT);
    if ($robots === false) {
        add_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_CONTENT,'');
    }
    $overwrite = get_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_OVERWRITE);
    if ($overwrite === false) {
        add_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_OVERWRITE,'0');
    }
}

function airaghi_robots_txt_quick_editor_filter($output,$public) {
    $extra     = '';
    $extra     = get_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_CONTENT,'');
    $overwrite = get_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_OVERWRITE,0);
    if ($extra === '' && $overwrite) {
        $overwrite = 0;
    }
    if ($overwrite) {
        $output = $extra;
    } else {
        $output = $output . ( $extra !== '' ? "\n" . $extra : '' );
    }
    return $output;
}

function airaghi_robots_txt_quick_editor_admin_page() {
    if (!airaghi_robots_txt_quick_editor_check_capabilities()) { wp_die('Unauthorized'); }
    $is_save  = isset($_POST['robots']) ? true : false;
    $nonce    = sanitize_text_field($_REQUEST[ROBOTS_TXT_QUICK_EDITOR_NONCE] ?? '');
    $ok_nonce = $is_save && wp_verify_nonce( $nonce , ROBOTS_TXT_QUICK_EDITOR_ACTION );
    $msg      = '';
    $ok       = true;
    if ($is_save && !$ok_nonce) {
        $msg = __('You are not authorized','robots-txt-quick-editor');
        $ok  = false;
    } elseif ($is_save && $ok_nonce) {
        $robots    = sanitize_textarea_field($_POST['robots'] ?? '');
        $robots    = str_replace("\r","\n",$robots);
        $robots    = preg_replace('#\n+#',"\n",$robots);
        $robots    = trim($robots);
        $overwrite = intval(sanitize_text_field($_POST['overwrite'] ?? ''));
        update_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_CONTENT,$robots,true);
        update_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_OVERWRITE,$overwrite,true);
        $msg = __('Robots.txt saved','robots-txt-quick-editor');
        $ok  = true;
    }
    $robots    = strval(get_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_CONTENT,''));
    $overwrite = intval(get_option(ROBOTS_TXT_QUICK_EDITOR_OPTION_OVERWRITE,'0'));
    $color     = $ok ? '#008000' : '#ff0000';
    ?>
    <div class="wrap">
        <h2>Robots.txt</h2>
        <?php if ($msg) { ?><p><strong style="color:<?php echo esc_attr($color); ?>"><?php echo esc_html($msg); ?></strong></p><?php } ?>
        <p>
            <script>
                function checkRobots() {
                    var contenuto = document.airaghi_robots_txt_quick_editor_form.robots.value.trim();
                    if (contenuto != '') { return true; }
                    return confirm("<?php echo esc_js(__('Do you really want to save an empty file?','robots-txt-quick-editor')); ?>");
                }
            </script>
            <form  method="post" action="options-general.php?page=robots-txt-quick-editor-page" name="airaghi_robots_txt_quick_editor_form" onsubmit="return checkRobots();">
               <?php settings_fields( 'robots-txt-quick-editor-page' ); ?>
               <?php do_settings_sections( 'robots-txt-quick-editor-page' ); ?>
               <div>
                   <?php echo esc_html__('Content will be appended to Wordpress generated one, if you want to fully overwrite it check the following option','robots-txt-quick-editor'); ?>
               </div>
               <div>
                   <?php echo esc_html__('Overwrite Wordpress generated robots.txt','robots-txt-quick-editor'); ?>
                   <input type="checkbox" name="overwrite" value="1" <?php if ($overwrite) { echo "checked"; } ?> >
               </div>
               <div>
                   <textarea rows="50" cols="100" name="robots"><?php echo esc_textarea($robots); ?></textarea>
               </div>
               <?php submit_button(); ?>
            </form>
        </p>
    </div>
    <?php
}

