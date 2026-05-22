<?php
/**
 * Plugin Name: APSP Editor CSS
 * Plugin URI: https://www.apsp-grigno.it/
 * Description: Editor CSS semplice per modificare gli stili del sito web. Include backup automatici e spiegazioni utili per principianti.
 * Version: 1.0.0
 * Author: Antonio Guiotto
 * Author URI: https://www.apsp-grigno.it/
 * License: GPLv2 or later
 * Text Domain: apsp-css-editor
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class APSP_CSS_Editor {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_save_css', array($this, 'save_css'));
        add_action('wp_ajax_backup_css', array($this, 'backup_css'));
        add_action('wp_ajax_restore_css', array($this, 'restore_css'));
        add_action('wp_ajax_create_css_file', array($this, 'create_css_file'));
        add_action('wp_ajax_delete_css_file', array($this, 'delete_css_file'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_button'), 100);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Editor CSS',
            'Editor CSS',
            'edit_theme_options',
            'apsp-css-editor',
            array($this, 'admin_page'),
            'dashicons-editor-code',
            30
        );
    }
    
    /**
     * Add admin bar button
     */
    public function add_admin_bar_button($wp_admin_bar) {
        if (current_user_can('edit_theme_options')) {
            $wp_admin_bar->add_node(array(
                'id' => 'apsp-css-editor',
                'title' => 'Editor CSS',
                'href' => admin_url('admin.php?page=apsp-css-editor'),
                'meta' => array(
                    'class' => 'apsp-css-editor-button'
                )
            ));
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_apsp-css-editor') {
            return;
        }
        
        wp_enqueue_style('apsp-css-editor-admin', plugin_dir_url(__FILE__) . 'css/admin.css', array(), '1.0.0');
        wp_enqueue_script('apsp-css-editor-admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), '1.0.0', true);
        
        wp_localize_script('apsp-css-editor-admin', 'apsp_css_editor', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('apsp_css_editor_nonce'),
            'strings' => array(
                'saving' => '💾 Salvando...',
                'saved' => '✅ CSS salvato con successo!',
                'error' => '❌ Errore nel salvataggio',
                'backup_created' => '📦 Backup creato con successo!',
                'restore_confirm' => '⚠️ Sei sicuro di voler ripristinare il backup? Questo sovrascriverà le modifiche attuali.',
                'restore_success' => '🔄 Backup ripristinato con successo!',
                'file_created' => '✨ File creato con successo!',
                'file_deleted' => '🗑️ File eliminato con successo!',
                'enter_filename' => '📝 Inserisci un nome per il file',
                'delete_confirm' => '⚠️ Sei sicuro di voler eliminare questo file? Questa azione non si può annullare!'
            )
        ));
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        $css_files = array(
            'style' => array(
                'name' => 'Stile Principale',
                'file' => get_stylesheet_directory() . '/style.css',
                'description' => 'Il file principale del tema - come un libro di regole per tutto il sito'
            ),
            'footer' => array(
                'name' => 'Piè di Pagina',
                'file' => get_stylesheet_directory() . '/css-modules/footer.css',
                'description' => 'La parte in fondo alla pagina - come la firma del sito'
            ),
            'hero' => array(
                'name' => 'Sezione Hero',
                'file' => get_stylesheet_directory() . '/css-modules/hero.css',
                'description' => 'La parte grande in alto - come il titolo di un libro'
            ),
            'services' => array(
                'name' => 'Servizi',
                'file' => get_stylesheet_directory() . '/css-modules/services.css',
                'description' => 'Le scatole che mostrano i servizi - come le pagine di un catalogo'
            ),
            'contact' => array(
                'name' => 'Contatti',
                'file' => get_stylesheet_directory() . '/css-modules/contact.css',
                'description' => 'Dove mettere telefono e indirizzo - come un biglietto da visita'
            )
        );
        
        // Add custom CSS files dynamically
        $custom_css_dir = get_stylesheet_directory() . '/css-modules/custom/';
        if (is_dir($custom_css_dir)) {
            $custom_files = glob($custom_css_dir . '*.css');
            foreach ($custom_files as $custom_file) {
                $filename = basename($custom_file);
                $key = 'custom_' . str_replace('.css', '', $filename);
                $css_files[$key] = array(
                    'name' => 'Personalizzato: ' . ucfirst(str_replace('.css', '', $filename)),
                    'file' => $custom_file,
                    'description' => 'Un file che hai creato tu - come un disegno personalizzato'
                );
            }
        }
        
        $current_file = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : 'style';
        $current_file_data = $css_files[$current_file];
        $css_content = '';
        
        if (file_exists($current_file_data['file'])) {
            $css_content = file_get_contents($current_file_data['file']);
        }
        
        $backups = $this->get_backups();
        ?>
        <div class="wrap apsp-css-editor-wrap">
            <h1>🎨 Editor CSS</h1>
            
            <!-- Guida per Principianti -->
            <div class="apsp-css-guide" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3>📖 Come Funziona</h3>
                <p><strong>CSS controlla l'aspetto del tuo sito web:</strong></p>
                <ul style="font-size: 15px; line-height: 1.5;">
                    <li>🎨 <strong>CSS</strong> = Stili e design del sito</li>
                    <li>📝 <strong>Questo editor</strong> = Strumento per modificare i CSS</li>
                    <li>💾 <strong>Salvare</strong> = Applicare le modifiche</li>
                    <li>📦 <strong>Backup</strong> = Salvare una copia prima di modificare</li>
                </ul>
                <p><strong>Esempi di codice:</strong></p>
                <ul style="font-size: 13px; background: white; padding: 15px; border-radius: 5px; border: 1px solid #e9ecef;">
                    <li><code>color: red;</code> = Testo rosso</li>
                    <li><code>background: blue;</code> = Sfondo blu</li>
                    <li><code>font-size: 20px;</code> = Dimensione testo 20px</li>
                </ul>
            </div>
            
            <div class="apsp-css-editor-container">
                <div class="apsp-css-editor-main">
                    <div class="apsp-css-editor-header">
                        <h2>🎯 Modifica: <?php echo esc_html($current_file_data['name']); ?></h2>
                        <div class="apsp-css-editor-actions">
                            <button type="button" class="button button-primary" id="save-css">💾 Salva</button>
                            <button type="button" class="button" id="backup-css">📦 Crea Backup</button>
                            <button type="button" class="button" id="preview-css">👁️ Anteprima</button>
                        </div>
                    </div>
                    
                    <div class="apsp-css-editor-content">
                        <textarea id="css-editor" class="apsp-css-textarea" data-file="<?php echo esc_attr($current_file); ?>"><?php echo esc_textarea($css_content); ?></textarea>
                    </div>
                    
                    <div class="apsp-css-editor-status">
                        <div id="save-status" class="apsp-status-message"></div>
                    </div>
                </div>
                
                <div class="apsp-css-editor-sidebar">
                    <div class="apsp-css-editor-panel">
                        <h3>📁 Scegli il File da Modificare</h3>
                        <select id="file-selector">
                            <?php foreach ($css_files as $key => $file_data): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_file, $key); ?>>
                                    <?php echo esc_html($file_data['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="file-description" style="font-style: italic; color: #666;"><?php echo esc_html($current_file_data['description']); ?></p>
                        
                        <!-- Create New File -->
                        <div class="create-file-section" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                            <h4>🆕 Crea un Nuovo File</h4>
                            <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Crea un nuovo file CSS personalizzato</p>
                            <input type="text" id="new-filename" placeholder="esempio: mio-stile.css" style="width: 100%; margin-bottom: 10px;">
                            <button type="button" class="button" id="create-file">✨ Crea File</button>
                        </div>
                        
                                                    <!-- Delete File (only for user-created custom files, not example/test files) -->
                            <?php if (str_starts_with($current_file, 'custom_') && !in_array($current_file, array('custom_example', 'custom_test-file'))): ?>
                            <div class="delete-file-section" style="margin-top: 15px; padding: 15px; background: #fff5f5; border: 1px solid #ffcdd2; border-radius: 8px;">
                                <h4>🗑️ Elimina File</h4>
                                <p style="font-size: 12px; color: #d32f2f; margin-bottom: 10px;">⚠️ Attenzione: questa azione non si può annullare!</p>
                                <button type="button" class="button button-link-delete" id="delete-file">🗑️ Elimina File Attuale</button>
                            </div>
                            <?php endif; ?>
                    </div>
                    
                    <div class="apsp-css-editor-panel">
                        <h3>📦 Backup</h3>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Salva una copia prima di modificare</p>
                        <?php if (!empty($backups)): ?>
                            <select id="backup-select">
                                <option value="">📋 Scegli un backup...</option>
                                <?php foreach ($backups as $backup): ?>
                                    <option value="<?php echo esc_attr($backup['file']); ?>">
                                        <?php echo esc_html($backup['date']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="button" id="restore-backup">🔄 Ripristina Backup</button>
                        <?php else: ?>
                            <p style="color: #666; font-style: italic;">📭 Nessun backup disponibile</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="apsp-css-editor-panel">
                        <h3>📊 Informazioni File</h3>
                        <p><strong>📁 File:</strong> <?php echo esc_html(str_replace(ABSPATH, '', $current_file_data['file'])); ?></p>
                        <p><strong>📏 Dimensione:</strong> <?php echo size_format(filesize($current_file_data['file'])); ?></p>
                        <p><strong>🕒 Ultima modifica:</strong> <?php echo date('d/m/Y H:i', filemtime($current_file_data['file'])); ?></p>
                    </div>
                    
                    <div class="apsp-css-editor-panel">
                        <h3>⌨️ Scorciatoie Tastiera</h3>
                        <ul class="apsp-shortcuts" style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 8px;"><kbd style="background: #f1f1f1; padding: 2px 6px; border-radius: 3px;">Ctrl+S</kbd> 💾 Salva</li>
                            <li style="margin-bottom: 8px;"><kbd style="background: #f1f1f1; padding: 2px 6px; border-radius: 3px;">Ctrl+Z</kbd> ↩️ Annulla</li>
                            <li style="margin-bottom: 8px;"><kbd style="background: #f1f1f1; padding: 2px 6px; border-radius: 3px;">Ctrl+F</kbd> 🔍 Cerca</li>
                            <li style="margin-bottom: 8px;"><kbd style="background: #f1f1f1; padding: 2px 6px; border-radius: 3px;">Tab</kbd> ➡️ Indenta</li>
                        </ul>
                    </div>
                    
                    <div class="apsp-css-editor-panel">
                        <h3>🎨 Esempi CSS</h3>
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Clicca per inserire esempi di codice</p>
                        <div class="apsp-css-snippets" style="display: grid; gap: 8px;">
                            <button type="button" class="button snippet-btn" data-snippet="hero-section" style="text-align: left;">🎯 Sezione Hero</button>
                            <button type="button" class="button snippet-btn" data-snippet="responsive" style="text-align: left;">📱 Responsive</button>
                            <button type="button" class="button snippet-btn" data-snippet="buttons" style="text-align: left;">🔘 Pulsanti</button>
                            <button type="button" class="button snippet-btn" data-snippet="cards" style="text-align: left;">🃏 Card</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save CSS
     */
    public function save_css() {
        error_log('=== SAVE CSS CALLED ===');
        
        check_ajax_referer('apsp_css_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            error_log('SAVE: Insufficient permissions');
            wp_die('Insufficient permissions');
        }
        
        $css_content = sanitize_textarea_field($_POST['css']);
        $file_type = sanitize_text_field($_POST['file_type']);
        
        error_log('SAVE: file_type = ' . $file_type);
        error_log('SAVE: content length = ' . strlen($css_content));
        
        $css_files = array(
            'style' => get_stylesheet_directory() . '/style.css',
            'footer' => get_stylesheet_directory() . '/css-modules/footer.css',
            'hero' => get_stylesheet_directory() . '/css-modules/hero.css',
            'services' => get_stylesheet_directory() . '/css-modules/services.css',
            'contact' => get_stylesheet_directory() . '/css-modules/contact.css'
        );
        
        // Add custom CSS files dynamically
        $custom_css_dir = get_stylesheet_directory() . '/css-modules/custom/';
        if (is_dir($custom_css_dir)) {
            $custom_files = glob($custom_css_dir . '*.css');
            foreach ($custom_files as $custom_file) {
                $filename = basename($custom_file);
                $key = 'custom_' . str_replace('.css', '', $filename);
                $css_files[$key] = $custom_file;
            }
        }
        
        $css_file = isset($css_files[$file_type]) ? $css_files[$file_type] : $css_files['style'];
        
        error_log('SAVE: target file = ' . $css_file);
        error_log('SAVE: file exists = ' . (file_exists($css_file) ? 'YES' : 'NO'));
        error_log('SAVE: file writable = ' . (is_writable($css_file) ? 'YES' : 'NO'));
        
        // Create backup before saving (only for main style.css)
        if ($file_type === 'style') {
            $this->create_backup();
        }
        
        $result = file_put_contents($css_file, $css_content);
        
        error_log('SAVE: write result = ' . ($result !== false ? 'SUCCESS' : 'FAILED'));
        
        if ($result !== false) {
            error_log('SAVE: SUCCESS - sending success response');
            wp_send_json_success('CSS salvato con successo!');
        } else {
            error_log('SAVE: FAILED - sending error response');
            wp_send_json_error('Errore nel salvataggio del file CSS');
        }
    }
    
    /**
     * Create backup
     */
    public function backup_css() {
        check_ajax_referer('apsp_css_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        $result = $this->create_backup();
        
        if ($result) {
            wp_send_json_success('Backup creato con successo!');
        } else {
            wp_send_json_error('Errore nella creazione del backup');
        }
    }
    
    /**
     * Restore backup
     */
    public function restore_css() {
        check_ajax_referer('apsp_css_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        $backup_file = sanitize_text_field($_POST['backup_file']);
        $backup_dir = $this->get_backup_dir();
        $backup_path = $backup_dir . '/' . basename($backup_file);
        
        if (!file_exists($backup_path)) {
            wp_send_json_error('File di backup non trovato');
        }
        
        $css_file = get_stylesheet_directory() . '/style.css';
        $result = copy($backup_path, $css_file);
        
        if ($result) {
            wp_send_json_success('Backup ripristinato con successo!');
        } else {
            wp_send_json_error('Errore nel ripristino del backup');
        }
    }
    
    /**
     * Create backup
     */
    private function create_backup() {
        $css_file = get_stylesheet_directory() . '/style.css';
        $backup_dir = $this->get_backup_dir();
        
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        $backup_file = $backup_dir . '/style-backup-' . date('Y-m-d-H-i-s') . '.css';
        
        return copy($css_file, $backup_file);
    }
    
    /**
     * Get backup directory
     */
    private function get_backup_dir() {
        return WP_CONTENT_DIR . '/apsp-css-backups';
    }
    
    /**
     * Get backups
     */
    private function get_backups() {
        $backup_dir = $this->get_backup_dir();
        $backups = array();
        
        if (!is_dir($backup_dir)) {
            return $backups;
        }
        
        $files = glob($backup_dir . '/style-backup-*.css');
        
        foreach ($files as $file) {
            $backups[] = array(
                'file' => basename($file),
                'date' => date('d/m/Y H:i', filemtime($file)),
                'size' => size_format(filesize($file))
            );
        }
        
        // Sort by date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Create new CSS file
     */
    public function create_css_file() {
        check_ajax_referer('apsp_css_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        $filename = sanitize_file_name($_POST['filename']);
        
        if (empty($filename)) {
            wp_send_json_error('Filename is required');
        }
        
        // Ensure .css extension
        if (!str_ends_with($filename, '.css')) {
            $filename .= '.css';
        }
        
        $custom_css_dir = get_stylesheet_directory() . '/css-modules/custom/';
        $file_path = $custom_css_dir . $filename;
        
        // Check if file already exists
        if (file_exists($file_path)) {
            wp_send_json_error('File already exists');
        }
        
        // Create directory if it doesn't exist
        if (!is_dir($custom_css_dir)) {
            mkdir($custom_css_dir, 0755, true);
        }
        
        // Default CSS content - Template semplice
        $default_content = "/**
 * File CSS Personalizzato: {$filename}
 * Creato il: " . date('d/m/Y H:i:s') . "
 */

/* ===========================================
   STILI PERSONALIZZATI
   =========================================== */

/* 
COME FUNZIONA:
- Le righe che iniziano con /* sono commenti
- Le righe con {} contengono le regole CSS
- Puoi modificare colori, dimensioni, spazi
*/

/* Esempio: Modificare il colore del testo */
/*
body {
    color: #333333;
    font-family: Arial, sans-serif;
}
*/

/* Esempio: Modificare lo sfondo */
/*
.container {
    background-color: #f0f0f0;
    padding: 20px;
}
*/

/* ===========================================
   DESIGN RESPONSIVE
   =========================================== */

@media (max-width: 768px) {
    /* 
    Regole per schermi piccoli (tablet e telefoni)
    */
    
    /*
    .container {
        padding: 10px;
    }
    */
}
";
        
        $result = file_put_contents($file_path, $default_content);
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'File created successfully',
                'filename' => $filename,
                'file_key' => 'custom_' . str_replace('.css', '', $filename)
            ));
        } else {
            wp_send_json_error('Failed to create file');
        }
    }
    
    /**
     * Delete CSS file
     */
    public function delete_css_file() {
        check_ajax_referer('apsp_css_editor_nonce', 'nonce');
        
        if (!current_user_can('edit_theme_options')) {
            wp_die('Insufficient permissions');
        }
        
        $file_type = sanitize_text_field($_POST['file_type']);
        
        // Only allow deletion of custom files
        if (!str_starts_with($file_type, 'custom_')) {
            wp_send_json_error('Can only delete custom files');
        }
        
        $filename = str_replace('custom_', '', $file_type) . '.css';
        $custom_css_dir = get_stylesheet_directory() . '/css-modules/custom/';
        $file_path = $custom_css_dir . $filename;
        
        if (!file_exists($file_path)) {
            wp_send_json_error('File not found');
        }
        
        $result = unlink($file_path);
        
        if ($result) {
            wp_send_json_success('File deleted successfully');
        } else {
            wp_send_json_error('Failed to delete file');
        }
    }
}

// Initialize the plugin
new APSP_CSS_Editor(); 