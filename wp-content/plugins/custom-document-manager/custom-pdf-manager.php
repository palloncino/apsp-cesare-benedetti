<?php
/**
 * Plugin Name: Gestore Documenti MOD
 * Description: Gestione e caricamento di documenti (PDF, XLS, DOC) in wp-content/uploads/files/images/
 * Version: 1.0.1
 * Author: A. Guiotto & Giollins
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Custom_Document_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_filter('upload_dir', array($this, 'custom_upload_dir'));
        add_action('wp_ajax_upload_document', array($this, 'handle_document_upload'));
        add_action('wp_ajax_nopriv_upload_document', array($this, 'handle_document_upload'));
        add_action('wp_ajax_delete_document', array($this, 'handle_document_delete'));
        add_action('wp_ajax_nopriv_delete_document', array($this, 'handle_document_delete'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function init() {
        // Create the directory structure if it doesn't exist
        $this->create_document_directory();
    }
    
    /**
     * Create the document directory structure
     */
    private function create_document_directory() {
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/files/images';
        
        if (!file_exists($files_dir)) {
            wp_mkdir_p($files_dir);
        }
        
        // Create .htaccess to protect the directory
        $htaccess_file = $files_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "Order allow,deny\n";
            $htaccess_content .= "Allow from all\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }
    
    /**
     * Custom upload directory for documents
     */
    public function custom_upload_dir($upload_dir) {
        // Only modify for document files
        if (isset($_POST['document_upload']) && $_POST['document_upload'] === 'true') {
            $upload_dir['path'] = $upload_dir['basedir'] . '/files/images';
            $upload_dir['url'] = $upload_dir['baseurl'] . '/files/images';
            $upload_dir['subdir'] = '/files/images';
        }
        return $upload_dir;
    }
    
    /**
     * Handle document upload via AJAX
     */
    public function handle_document_upload() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'document_upload_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['document_file'])) {
            wp_die('No file uploaded');
        }
        
        $file = $_FILES['document_file'];
        
        // Validate file type
        $allowed_types = array(
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );
        $file_type = wp_check_filetype($file['name']);
        
        if (!in_array($file_type['type'], $allowed_types)) {
            wp_die('Invalid file type. Only PDF, XLS, XLSX, DOC, and DOCX files are allowed.');
        }
        
        // Get category if provided
        $category = isset($_POST['document_category']) ? sanitize_text_field($_POST['document_category']) : '';
        
        // Create category directory if needed
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/files/images';
        
        if (!empty($category)) {
            $category_dir = $files_dir . '/' . $category;
            if (!is_dir($category_dir)) {
                wp_mkdir_p($category_dir);
            }
            $files_dir = $category_dir;
        }
        
        // Move uploaded file to the correct directory
        $filename = sanitize_file_name($file['name']);
        $destination = $files_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $file_url = $upload_dir['baseurl'] . '/files/images/' . 
                       (empty($category) ? '' : $category . '/') . $filename;
            
            wp_send_json_success(array(
                'url' => $file_url,
                'file' => $destination,
                'filename' => $filename,
                'category' => $category
            ));
        } else {
            wp_die('Upload failed: Could not move uploaded file.');
        }
    }
    
    /**
     * Handle document delete via AJAX
     */
    public function handle_document_delete() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'document_delete_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check if file was provided
        if (!isset($_POST['filename']) || !isset($_POST['category'])) {
            wp_die('No file or category provided');
        }
        
        $filename = $_POST['filename'];
        $category = $_POST['category'];
        
        // Get the full path to the file
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/files/images';
        
        if (!empty($category)) {
            $files_dir .= '/' . $category;
        }
        
        $file_path = $files_dir . '/' . $filename;
        
        // Delete the file
        if (unlink($file_path)) {
            wp_send_json_success('Documento eliminato con successo');
        } else {
            wp_die('Eliminazione fallita: Impossibile eliminare il file');
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Gestore Documenti',
            'Gestore Documenti',
            'manage_options',
            'custom-document-manager',
            array($this, 'admin_page'),
            'dashicons-media-document',
            30
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/files/images';
        $document_files = $this->scan_document_directory($files_dir);
        
        ?>
        <style>
        .pdf-manager-wrap { max-width: none !important; }
        .pdf-manager-wrap .card { margin-bottom: 20px; max-width: none !important; width: 100% !important; }
        .pdf-filters { display: flex; gap: 10px; align-items: center; margin-bottom: 15px; flex-wrap: wrap; }
        .pdf-filters input, .pdf-filters select { min-width: 200px; }
        .pdf-table { font-size: 13px; }
        .pdf-table th, .pdf-table td { padding: 8px 12px; }
        .pdf-table .filename { font-weight: 500; }
        .pdf-table .category { color: #666; font-size: 12px; }
        .pdf-table .size { color: #888; font-size: 12px; text-align: center; }
        .pdf-table .date { color: #888; font-size: 12px; text-align: center; }
        .pdf-table .actions { text-align: center; }
        .pdf-table .actions .button { margin: 0 2px; }
        .file-type-pdf { color: #e74c3c; }
        .file-type-xls { color: #27ae60; }
        .file-type-doc { color: #3498db; }
        .file-url-container { margin-top: 5px; }
        .file-url { font-size: 11px; color: #666; font-family: monospace; word-break: break-all; }
        .copy-link-btn { 
            font-size: 10px; 
            padding: 2px 6px; 
            margin-left: 8px; 
            background: #f0f0f0; 
            border: 1px solid #ccc; 
            border-radius: 3px; 
            cursor: pointer; 
            color: #333;
        }
        .copy-link-btn:hover { 
            background: #e0e0e0; 
            border-color: #999; 
        }
        .copy-link-btn.copied { 
            background: #27ae60; 
            color: white; 
            border-color: #27ae60; 
        }
        .sortable { 
            cursor: pointer; 
            user-select: none; 
            position: relative; 
        }
        .sortable:hover { 
            background-color: #f0f0f0; 
        }
        .sort-indicator { 
            font-size: 10px; 
            margin-left: 5px; 
            color: #666; 
        }
        .sortable.asc .sort-indicator::after { 
            content: "▲"; 
            color: #0073aa; 
        }
        .sortable.desc .sort-indicator::after { 
            content: "▼"; 
            color: #0073aa; 
        }
        .sortable:focus { 
            outline: 2px solid #0073aa; 
            outline-offset: -2px; 
        }
        </style>
        
        <div class="wrap pdf-manager-wrap">
            <h1>Gestore Documenti</h1>
            
            <div class="card">
                <h2>Carica Documento</h2>
                <form id="document-upload-form" enctype="multipart/form-data">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="document_file">Seleziona File</label>
                            </th>
                            <td>
                                <input type="file" name="document_file" id="document_file" accept=".pdf,.xls,.xlsx,.doc,.docx" required>
                                <p class="description">Sono consentiti file PDF, XLS, XLSX, DOC, DOCX.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="document_category">Categoria (Opzionale)</label>
                            </th>
                            <td>
                                <input type="text" name="document_category" id="document_category" placeholder="es. trasparenza/Atti_Generali">
                                <p class="description">Crea sottocartelle per organizzare i documenti (es. trasparenza/Atti_Generali)</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary">Carica Documento</button>
                    </p>
                </form>
            </div>
            
            <div class="card">
                <h2>File Documenti (<?php echo count($document_files); ?> totali)</h2>
                <p>I documenti sono memorizzati in: <code><?php echo esc_html($files_dir); ?></code></p>
                <p><small>💡 <strong>Suggerimento:</strong> Clicca sulle intestazioni delle colonne per ordinare la tabella. I documenti sono ordinati per data (più recenti prima) di default.</small></p>
                
                <?php if (!empty($document_files)): ?>
                    <div class="pdf-filters">
                        <input type="text" id="text-filter" placeholder="Cerca per nome file..." style="flex: 1; min-width: 250px;">
                        <select id="category-filter">
                            <option value="">Tutte le Categorie</option>
                            <?php
                            $categories = array();
                            foreach ($document_files as $file) {
                                if (!empty($file['category'])) {
                                    $categories[$file['category']] = $file['category'];
                                }
                            }
                            foreach ($categories as $category) {
                                echo '<option value="' . esc_attr($category) . '">' . esc_html($category) . '</option>';
                            }
                            ?>
                        </select>
                        <button type="button" class="button" onclick="filterDocuments()">Filtra</button>
                        <button type="button" class="button" onclick="clearFilters()">Pulisci</button>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped pdf-table" id="document-table">
                        <thead>
                            <tr>
                                <th style="width: 35%;" class="sortable" data-sort="name" tabindex="0" role="button" aria-label="Ordina per nome file">
                                    Nome File <span class="sort-indicator"></span>
                                </th>
                                <th style="width: 15%;" class="sortable" data-sort="type" tabindex="0" role="button" aria-label="Ordina per tipo file">
                                    Tipo <span class="sort-indicator"></span>
                                </th>
                                <th style="width: 20%;" class="sortable" data-sort="category" tabindex="0" role="button" aria-label="Ordina per categoria">
                                    Categoria <span class="sort-indicator"></span>
                                </th>
                                <th style="width: 10%; text-align: center;" class="sortable" data-sort="size" tabindex="0" role="button" aria-label="Ordina per dimensione">
                                    Dimensione <span class="sort-indicator"></span>
                                </th>
                                <th style="width: 10%; text-align: center;" class="sortable desc" data-sort="date" tabindex="0" role="button" aria-label="Ordina per data (attualmente ordinato per data decrescente)">
                                    Data <span class="sort-indicator"></span>
                                </th>
                                <th style="width: 10%; text-align: center;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($document_files as $file): ?>
                                <?php 
                                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                $file_type_class = 'file-type-' . $file_extension;
                                ?>
                                <tr data-category="<?php echo esc_attr($file['category']); ?>" 
                                    data-filename="<?php echo esc_attr(strtolower($file['name'])); ?>" 
                                    data-type="<?php echo esc_attr($file['type']); ?>"
                                    data-name="<?php echo esc_attr(strtolower($file['name'])); ?>"
                                    data-size="<?php echo esc_attr($file['size_bytes']); ?>"
                                    data-timestamp="<?php echo esc_attr($file['timestamp']); ?>">
                                    <td class="filename">
                                        <?php echo esc_html($file['name']); ?>
                                        <div class="file-url-container">
                                            <span class="file-url"><?php echo esc_html($file['url']); ?></span>
                                            <button type="button" class="copy-link-btn" data-url="<?php echo esc_attr($file['url']); ?>">Copia</button>
                                        </div>
                                    </td>
                                    <td class="file-type <?php echo esc_attr($file_type_class); ?>"><?php echo esc_html($file['type']); ?></td>
                                    <td class="category"><?php echo esc_html($file['category'] ?: 'Principale'); ?></td>
                                    <td class="size"><?php echo esc_html($file['size']); ?></td>
                                    <td class="date"><?php echo esc_html($file['date']); ?></td>
                                    <td class="actions">
                                        <a href="<?php echo esc_url($file['url']); ?>" 
                                           target="_blank" class="button button-small">Visualizza</a>
                                        <button class="button button-small delete-document" 
                                                data-filename="<?php echo esc_attr($file['name']); ?>"
                                                data-category="<?php echo esc_attr($file['category']); ?>">Elimina</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nessun documento trovato. Carica il primo documento utilizzando il form sopra.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        var currentSort = { column: 'date', direction: 'desc' };
        
        function sortTable(column, direction) {
            var table = document.getElementById('document-table');
            var tbody = table.querySelector('tbody');
            var rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Sort the rows
            rows.sort(function(a, b) {
                var aValue, bValue;
                
                switch(column) {
                    case 'name':
                        aValue = a.getAttribute('data-name');
                        bValue = b.getAttribute('data-name');
                        break;
                    case 'type':
                        aValue = a.getAttribute('data-type');
                        bValue = b.getAttribute('data-type');
                        break;
                    case 'category':
                        aValue = a.getAttribute('data-category') || '';
                        bValue = b.getAttribute('data-category') || '';
                        break;
                    case 'size':
                        aValue = parseInt(a.getAttribute('data-size')) || 0;
                        bValue = parseInt(b.getAttribute('data-size')) || 0;
                        break;
                    case 'date':
                        aValue = parseInt(a.getAttribute('data-timestamp')) || 0;
                        bValue = parseInt(b.getAttribute('data-timestamp')) || 0;
                        break;
                    default:
                        return 0;
                }
                
                // Handle string comparison
                if (typeof aValue === 'string' && typeof bValue === 'string') {
                    aValue = aValue.toLowerCase();
                    bValue = bValue.toLowerCase();
                    if (direction === 'asc') {
                        return aValue.localeCompare(bValue);
                    } else {
                        return bValue.localeCompare(aValue);
                    }
                }
                
                // Handle numeric comparison
                if (direction === 'asc') {
                    return aValue - bValue;
                } else {
                    return bValue - aValue;
                }
            });
            
            // Re-append rows in sorted order
            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        }
        
        function filterDocuments() {
            var category = document.getElementById('category-filter').value;
            var textFilter = document.getElementById('text-filter').value.toLowerCase();
            var rows = document.querySelectorAll('#document-table tbody tr');
            var visibleCount = 0;
            
            rows.forEach(function(row) {
                var rowCategory = row.getAttribute('data-category');
                var rowFilename = row.getAttribute('data-filename');
                var categoryMatch = category === '' || rowCategory === category;
                var textMatch = textFilter === '' || rowFilename.includes(textFilter);
                
                if (categoryMatch && textMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update the count display
            var countElement = document.querySelector('.card h2');
            if (countElement) {
                var totalCount = rows.length;
                countElement.textContent = 'File Documenti (' + visibleCount + ' di ' + totalCount + ' totali)';
            }
        }
        
        function clearFilters() {
            document.getElementById('text-filter').value = '';
            document.getElementById('category-filter').value = '';
            
            // Reset sorting to default (date descending)
            $('.sortable').removeClass('asc desc');
            $('[data-sort="date"]').addClass('desc');
            currentSort = { column: 'date', direction: 'desc' };
            
            filterDocuments();
            sortTable('date', 'desc');
        }
        
        jQuery(document).ready(function($) {
            // Real-time text filtering
            $('#text-filter').on('input', function() {
                filterDocuments();
            });
            
            // Category filter change
            $('#category-filter').on('change', function() {
                filterDocuments();
            });
            
            // Column header sorting
            $('.sortable').on('click keydown', function(e) {
                // Handle both click and Enter/Space key
                if (e.type === 'keydown' && e.keyCode !== 13 && e.keyCode !== 32) {
                    return;
                }
                
                if (e.type === 'keydown') {
                    e.preventDefault();
                }
                
                var column = $(this).data('sort');
                var direction = 'asc';
                
                // If clicking the same column, toggle direction
                if (currentSort.column === column) {
                    direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                }
                
                // Update current sort
                currentSort = { column: column, direction: direction };
                
                // Update visual indicators
                $('.sortable').removeClass('asc desc');
                $(this).addClass(direction);
                
                // Sort the table
                sortTable(column, direction);
            });
            
            $('#document-upload-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData();
                formData.append('action', 'upload_document');
                formData.append('nonce', '<?php echo wp_create_nonce("document_upload_nonce"); ?>');
                formData.append('document_file', $('#document_file')[0].files[0]);
                formData.append('document_category', $('#document_category').val());
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('Documento caricato con successo!');
                            location.reload();
                        } else {
                            alert('Caricamento fallito: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Caricamento fallito. Riprova.');
                    }
                });
            });
            
            $('.delete-document').on('click', function() {
                if (confirm('Sei sicuro di voler eliminare questo documento?')) {
                    var filename = $(this).data('filename');
                    var category = $(this).data('category');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'delete_document',
                            nonce: '<?php echo wp_create_nonce("document_delete_nonce"); ?>',
                            filename: filename,
                            category: category
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Documento eliminato con successo!');
                                location.reload();
                            } else {
                                alert('Eliminazione fallita: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('Eliminazione fallita. Riprova.');
                        }
                    });
                }
            });
            
            // Copy link functionality
            $('.copy-link-btn').on('click', function() {
                var url = $(this).data('url');
                var button = $(this);
                
                // Use modern clipboard API if available
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(function() {
                        button.text('Copiato!').addClass('copied');
                        setTimeout(function() {
                            button.text('Copia').removeClass('copied');
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Failed to copy: ', err);
                        fallbackCopyTextToClipboard(url, button);
                    });
                } else {
                    fallbackCopyTextToClipboard(url, button);
                }
            });
            
            // Fallback copy function for older browsers
            function fallbackCopyTextToClipboard(text, button) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    var successful = document.execCommand('copy');
                    if (successful) {
                        button.text('Copiato!').addClass('copied');
                        setTimeout(function() {
                            button.text('Copia').removeClass('copied');
                        }, 2000);
                    } else {
                        alert('Impossibile copiare il link. Riprova.');
                    }
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                    alert('Impossibile copiare il link. Riprova.');
                }
                
                document.body.removeChild(textArea);
            }
        });
        </script>
        <?php
    }
    
    /**
     * Scan document directory recursively
     */
    private function scan_document_directory($base_dir) {
        $document_files = array();
        
        if (!is_dir($base_dir)) {
            return $document_files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            $extension = strtolower($file->getExtension());
            if ($file->isFile() && in_array($extension, array('pdf', 'xls', 'xlsx', 'doc', 'docx'))) {
                $relative_path = str_replace($base_dir . '/', '', $file->getPathname());
                $category = dirname($relative_path);
                if ($category === '.') {
                    $category = '';
                }
                
                $file_mtime = $file->getMTime();
                
                $document_files[] = array(
                    'name' => basename($file->getPathname()),
                    'category' => $category,
                    'url' => wp_upload_dir()['baseurl'] . '/files/images/' . $relative_path,
                    'path' => $file->getPathname(),
                    'size' => size_format($file->getSize()),
                    'size_bytes' => $file->getSize(), // Raw size for sorting
                    'date' => date('d/m/Y', $file_mtime),
                    'timestamp' => $file_mtime, // Unix timestamp for sorting
                    'type' => strtoupper($extension)
                );
            }
        }
        
        // Sort by date (newest first) by default
        usort($document_files, function($a, $b) {
            return $b['timestamp'] - $a['timestamp']; // Descending order (newest first)
        });
        
        return $document_files;
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
    }
    
    /**
     * Get document files from the directory
     */
    public static function get_document_files() {
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/files/images';
        $document_files = array();
        
        if (is_dir($files_dir)) {
            $files = scandir($files_dir);
            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, array('pdf', 'xls', 'xlsx', 'doc', 'docx'))) {
                    $document_files[] = array(
                        'name' => $file,
                        'url' => $upload_dir['baseurl'] . '/files/images/' . $file,
                        'path' => $files_dir . '/' . $file
                    );
                }
            }
        }
        
        return $document_files;
    }
    
    /**
     * Upload document file
     */
    public static function upload_document($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $files_dir = $upload_dir['basedir'] . '/files/images';
        
        // Create directory if it doesn't exist
        if (!is_dir($files_dir)) {
            wp_mkdir_p($files_dir);
        }
        
        $filename = sanitize_file_name($file['name']);
        $destination = $files_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return array(
                'name' => $filename,
                'url' => $upload_dir['baseurl'] . '/files/images/' . $filename,
                'path' => $destination
            );
        }
        
        return false;
    }
}

// Include shortcodes
require_once plugin_dir_path(__FILE__) . 'shortcodes.php';

// Initialize the plugin
new Custom_Document_Manager();

// Activation hook
register_activation_hook(__FILE__, function() {
    $upload_dir = wp_upload_dir();
    $files_dir = $upload_dir['basedir'] . '/files/images';
    wp_mkdir_p($files_dir);
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if needed
}); 