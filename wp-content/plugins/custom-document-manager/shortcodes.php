<?php
/**
 * Shortcodes for Custom PDF Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode to display PDF files
 * Usage: [pdf_list category="trasparenza/Atti_Generali"]
 */
function pdf_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'limit' => 10,
        'orderby' => 'name',
        'order' => 'ASC'
    ), $atts);
    
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/files/images/pdf';
    
    if (!empty($atts['category'])) {
        $pdf_dir .= '/' . sanitize_file_name($atts['category']);
    }
    
    $pdf_files = array();
    
    if (is_dir($pdf_dir)) {
        $files = scandir($pdf_dir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                $pdf_files[] = array(
                    'name' => $file,
                    'url' => $upload_dir['baseurl'] . '/files/images/pdf/' . 
                             (empty($atts['category']) ? '' : $atts['category'] . '/') . $file,
                    'path' => $pdf_dir . '/' . $file,
                    'size' => filesize($pdf_dir . '/' . $file),
                    'modified' => filemtime($pdf_dir . '/' . $file)
                );
            }
        }
    }
    
    // Sort files
    if ($atts['orderby'] === 'name') {
        usort($pdf_files, function($a, $b) use ($atts) {
            return $atts['order'] === 'ASC' ? 
                   strcasecmp($a['name'], $b['name']) : 
                   strcasecmp($b['name'], $a['name']);
        });
    } elseif ($atts['orderby'] === 'date') {
        usort($pdf_files, function($a, $b) use ($atts) {
            return $atts['order'] === 'ASC' ? 
                   $a['modified'] - $b['modified'] : 
                   $b['modified'] - $a['modified'];
        });
    }
    
    // Limit results
    $pdf_files = array_slice($pdf_files, 0, intval($atts['limit']));
    
    if (empty($pdf_files)) {
        return '<p>No PDF files found.</p>';
    }
    
    $output = '<style>';
    $output .= '.pdf-list .file-url-container { margin-top: 5px; }';
    $output .= '.pdf-list .file-url { font-size: 11px; color: #666; font-family: monospace; word-break: break-all; }';
    $output .= '.pdf-list .copy-link-btn { font-size: 10px; padding: 2px 6px; margin-left: 8px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; color: #333; }';
    $output .= '.pdf-list .copy-link-btn:hover { background: #e0e0e0; border-color: #999; }';
    $output .= '.pdf-list .copy-link-btn.copied { background: #27ae60; color: white; border-color: #27ae60; }';
    $output .= '</style>';
    
    $output .= '<div class="pdf-list">';
    $output .= '<table class="pdf-table">';
    $output .= '<thead><tr>';
    $output .= '<th>Documento</th>';
    $output .= '<th>Dimensione</th>';
    $output .= '<th>Data</th>';
    $output .= '<th>Azioni</th>';
    $output .= '</tr></thead>';
    $output .= '<tbody>';
    
    foreach ($pdf_files as $file) {
        $file_size = size_format($file['size']);
        $file_date = date('d/m/Y', $file['modified']);
        $file_name = pathinfo($file['name'], PATHINFO_FILENAME);
        
        $output .= '<tr>';
        $output .= '<td>';
        $output .= esc_html($file_name);
        $output .= '<div class="file-url-container">';
        $output .= '<span class="file-url">' . esc_html($file['url']) . '</span>';
        $output .= '<button type="button" class="copy-link-btn" data-url="' . esc_attr($file['url']) . '">Copia</button>';
        $output .= '</div>';
        $output .= '</td>';
        $output .= '<td>' . esc_html($file_size) . '</td>';
        $output .= '<td>' . esc_html($file_date) . '</td>';
        $output .= '<td>';
        $output .= '<a href="' . esc_url($file['url']) . '" target="_blank" class="pdf-link">';
        $output .= '<span class="dashicons dashicons-media-document"></span> Visualizza';
        $output .= '</a>';
        $output .= '</td>';
        $output .= '</tr>';
    }
    
    $output .= '</tbody></table>';
    $output .= '</div>';
    
    $output .= '<script>';
    $output .= 'jQuery(document).ready(function($) {';
    $output .= '    $(".pdf-list .copy-link-btn").on("click", function() {';
    $output .= '        var url = $(this).data("url");';
    $output .= '        var button = $(this);';
    $output .= '        if (navigator.clipboard && window.isSecureContext) {';
    $output .= '            navigator.clipboard.writeText(url).then(function() {';
    $output .= '                button.text("Copiato!").addClass("copied");';
    $output .= '                setTimeout(function() {';
    $output .= '                    button.text("Copia").removeClass("copied");';
    $output .= '                }, 2000);';
    $output .= '            }).catch(function(err) {';
    $output .= '                fallbackCopyTextToClipboard(url, button);';
    $output .= '            });';
    $output .= '        } else {';
    $output .= '            fallbackCopyTextToClipboard(url, button);';
    $output .= '        }';
    $output .= '    });';
    $output .= '    function fallbackCopyTextToClipboard(text, button) {';
    $output .= '        var textArea = document.createElement("textarea");';
    $output .= '        textArea.value = text;';
    $output .= '        textArea.style.top = "0";';
    $output .= '        textArea.style.left = "0";';
    $output .= '        textArea.style.position = "fixed";';
    $output .= '        document.body.appendChild(textArea);';
    $output .= '        textArea.focus();';
    $output .= '        textArea.select();';
    $output .= '        try {';
    $output .= '            var successful = document.execCommand("copy");';
    $output .= '            if (successful) {';
    $output .= '                button.text("Copiato!").addClass("copied");';
    $output .= '                setTimeout(function() {';
    $output .= '                    button.text("Copia").removeClass("copied");';
    $output .= '                }, 2000);';
    $output .= '            } else {';
    $output .= '                alert("Impossibile copiare il link. Riprova.");';
    $output .= '            }';
    $output .= '        } catch (err) {';
    $output .= '            alert("Impossibile copiare il link. Riprova.");';
    $output .= '        }';
    $output .= '        document.body.removeChild(textArea);';
    $output .= '    }';
    $output .= '});';
    $output .= '</script>';
    
    return $output;
}
add_shortcode('pdf_list', 'pdf_list_shortcode');

/**
 * Shortcode to display PDF files in a grid layout
 * Usage: [pdf_grid category="trasparenza/Atti_Generali"]
 */
function pdf_grid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'limit' => 12,
        'columns' => 3
    ), $atts);
    
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/files/images/pdf';
    
    if (!empty($atts['category'])) {
        $pdf_dir .= '/' . sanitize_file_name($atts['category']);
    }
    
    $pdf_files = array();
    
    if (is_dir($pdf_dir)) {
        $files = scandir($pdf_dir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                $pdf_files[] = array(
                    'name' => $file,
                    'url' => $upload_dir['baseurl'] . '/files/images/pdf/' . 
                             (empty($atts['category']) ? '' : $atts['category'] . '/') . $file,
                    'path' => $pdf_dir . '/' . $file,
                    'size' => filesize($pdf_dir . '/' . $file),
                    'modified' => filemtime($pdf_dir . '/' . $file)
                );
            }
        }
    }
    
    // Sort by name
    usort($pdf_files, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    // Limit results
    $pdf_files = array_slice($pdf_files, 0, intval($atts['limit']));
    
    if (empty($pdf_files)) {
        return '<p>No PDF files found.</p>';
    }
    
    $columns = intval($atts['columns']);
    $column_width = 100 / $columns;
    
    $output = '<style>';
    $output .= '.pdf-grid { display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: 20px; }';
    $output .= '.pdf-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; }';
    $output .= '.pdf-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }';
    $output .= '.pdf-icon { font-size: 48px; color: #e74c3c; margin-bottom: 10px; }';
    $output .= '.pdf-title { font-weight: bold; margin-bottom: 10px; word-break: break-word; }';
    $output .= '.pdf-meta { font-size: 0.9em; color: #666; margin-bottom: 15px; }';
    $output .= '.pdf-link { display: inline-block; padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; }';
    $output .= '.pdf-link:hover { background: #2980b9; }';
    $output .= '.pdf-grid .file-url-container { margin-top: 8px; margin-bottom: 10px; }';
    $output .= '.pdf-grid .file-url { font-size: 10px; color: #666; font-family: monospace; word-break: break-all; display: block; margin-bottom: 5px; }';
    $output .= '.pdf-grid .copy-link-btn { font-size: 9px; padding: 2px 6px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; color: #333; }';
    $output .= '.pdf-grid .copy-link-btn:hover { background: #e0e0e0; border-color: #999; }';
    $output .= '.pdf-grid .copy-link-btn.copied { background: #27ae60; color: white; border-color: #27ae60; }';
    $output .= '@media (max-width: 768px) { .pdf-grid { grid-template-columns: repeat(2, 1fr); } }';
    $output .= '@media (max-width: 480px) { .pdf-grid { grid-template-columns: 1fr; } }';
    $output .= '</style>';
    
    $output .= '<div class="pdf-grid">';
    
    foreach ($pdf_files as $file) {
        $file_size = size_format($file['size']);
        $file_date = date('d/m/Y', $file['modified']);
        $file_name = pathinfo($file['name'], PATHINFO_FILENAME);
        
        $output .= '<div class="pdf-card">';
        $output .= '<div class="pdf-icon">📄</div>';
        $output .= '<div class="pdf-title">' . esc_html($file_name) . '</div>';
        $output .= '<div class="pdf-meta">';
        $output .= esc_html($file_size) . ' • ' . esc_html($file_date);
        $output .= '</div>';
        $output .= '<div class="file-url-container">';
        $output .= '<span class="file-url">' . esc_html($file['url']) . '</span>';
        $output .= '<button type="button" class="copy-link-btn" data-url="' . esc_attr($file['url']) . '">Copia</button>';
        $output .= '</div>';
        $output .= '<a href="' . esc_url($file['url']) . '" target="_blank" class="pdf-link">';
        $output .= 'Visualizza PDF';
        $output .= '</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    $output .= '<script>';
    $output .= 'jQuery(document).ready(function($) {';
    $output .= '    $(".pdf-grid .copy-link-btn").on("click", function() {';
    $output .= '        var url = $(this).data("url");';
    $output .= '        var button = $(this);';
    $output .= '        if (navigator.clipboard && window.isSecureContext) {';
    $output .= '            navigator.clipboard.writeText(url).then(function() {';
    $output .= '                button.text("Copiato!").addClass("copied");';
    $output .= '                setTimeout(function() {';
    $output .= '                    button.text("Copia").removeClass("copied");';
    $output .= '                }, 2000);';
    $output .= '            }).catch(function(err) {';
    $output .= '                fallbackCopyTextToClipboard(url, button);';
    $output .= '            });';
    $output .= '        } else {';
    $output .= '            fallbackCopyTextToClipboard(url, button);';
    $output .= '        }';
    $output .= '    });';
    $output .= '    function fallbackCopyTextToClipboard(text, button) {';
    $output .= '        var textArea = document.createElement("textarea");';
    $output .= '        textArea.value = text;';
    $output .= '        textArea.style.top = "0";';
    $output .= '        textArea.style.left = "0";';
    $output .= '        textArea.style.position = "fixed";';
    $output .= '        document.body.appendChild(textArea);';
    $output .= '        textArea.focus();';
    $output .= '        textArea.select();';
    $output .= '        try {';
    $output .= '            var successful = document.execCommand("copy");';
    $output .= '            if (successful) {';
    $output .= '                button.text("Copiato!").addClass("copied");';
    $output .= '                setTimeout(function() {';
    $output .= '                    button.text("Copia").removeClass("copied");';
    $output .= '                }, 2000);';
    $output .= '            } else {';
    $output .= '                alert("Impossibile copiare il link. Riprova.");';
    $output .= '            }';
    $output .= '        } catch (err) {';
    $output .= '            alert("Impossibile copiare il link. Riprova.");';
    $output .= '        }';
    $output .= '        document.body.removeChild(textArea);';
    $output .= '    }';
    $output .= '});';
    $output .= '</script>';
    
    return $output;
}
add_shortcode('pdf_grid', 'pdf_grid_shortcode');

/**
 * Shortcode to display a single PDF file
 * Usage: [pdf_file filename="document.pdf" category="trasparenza/Atti_Generali"]
 */
function pdf_file_shortcode($atts) {
    $atts = shortcode_atts(array(
        'filename' => '',
        'category' => '',
        'title' => '',
        'show_meta' => 'true'
    ), $atts);
    
    if (empty($atts['filename'])) {
        return '<p>Error: No filename specified.</p>';
    }
    
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/files/images/pdf';
    
    if (!empty($atts['category'])) {
        $pdf_dir .= '/' . sanitize_file_name($atts['category']);
    }
    
    $file_path = $pdf_dir . '/' . sanitize_file_name($atts['filename']);
    $file_url = $upload_dir['baseurl'] . '/files/images/pdf/' . 
                (empty($atts['category']) ? '' : $atts['category'] . '/') . 
                sanitize_file_name($atts['filename']);
    
    if (!file_exists($file_path)) {
        return '<p>Error: PDF file not found.</p>';
    }
    
    $file_size = size_format(filesize($file_path));
    $file_date = date('d/m/Y', filemtime($file_path));
    $file_name = empty($atts['title']) ? pathinfo($atts['filename'], PATHINFO_FILENAME) : $atts['title'];
    
    $output = '<style>';
    $output .= '.pdf-single .file-url-container { margin-top: 10px; margin-bottom: 15px; }';
    $output .= '.pdf-single .file-url { font-size: 11px; color: #666; font-family: monospace; word-break: break-all; display: block; margin-bottom: 8px; }';
    $output .= '.pdf-single .copy-link-btn { font-size: 10px; padding: 3px 8px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 3px; cursor: pointer; color: #333; }';
    $output .= '.pdf-single .copy-link-btn:hover { background: #e0e0e0; border-color: #999; }';
    $output .= '.pdf-single .copy-link-btn.copied { background: #27ae60; color: white; border-color: #27ae60; }';
    $output .= '</style>';
    
    $output .= '<div class="pdf-single">';
    $output .= '<div class="pdf-header">';
    $output .= '<h3>' . esc_html($file_name) . '</h3>';
    
    if ($atts['show_meta'] === 'true') {
        $output .= '<div class="pdf-meta">';
        $output .= '<span>Dimensione: ' . esc_html($file_size) . '</span>';
        $output .= '<span>Data: ' . esc_html($file_date) . '</span>';
        $output .= '</div>';
    }
    
    $output .= '<div class="file-url-container">';
    $output .= '<span class="file-url">' . esc_html($file_url) . '</span>';
    $output .= '<button type="button" class="copy-link-btn" data-url="' . esc_attr($file_url) . '">Copia</button>';
    $output .= '</div>';
    
    $output .= '</div>';
    $output .= '<a href="' . esc_url($file_url) . '" target="_blank" class="pdf-download-btn">';
    $output .= '<span class="dashicons dashicons-download"></span> Scarica PDF';
    $output .= '</a>';
    $output .= '</div>';
    
    $output .= '<script>';
    $output .= 'jQuery(document).ready(function($) {';
    $output .= '    $(".pdf-single .copy-link-btn").on("click", function() {';
    $output .= '        var url = $(this).data("url");';
    $output .= '        var button = $(this);';
    $output .= '        if (navigator.clipboard && window.isSecureContext) {';
    $output .= '            navigator.clipboard.writeText(url).then(function() {';
    $output .= '                button.text("Copiato!").addClass("copied");';
    $output .= '                setTimeout(function() {';
    $output .= '                    button.text("Copia").removeClass("copied");';
    $output .= '                }, 2000);';
    $output .= '            }).catch(function(err) {';
    $output .= '                fallbackCopyTextToClipboard(url, button);';
    $output .= '            });';
    $output .= '        } else {';
    $output .= '            fallbackCopyTextToClipboard(url, button);';
    $output .= '        }';
    $output .= '    });';
    $output .= '    function fallbackCopyTextToClipboard(text, button) {';
    $output .= '        var textArea = document.createElement("textarea");';
    $output .= '        textArea.value = text;';
    $output .= '        textArea.style.top = "0";';
    $output .= '        textArea.style.left = "0";';
    $output .= '        textArea.style.position = "fixed";';
    $output .= '        document.body.appendChild(textArea);';
    $output .= '        textArea.focus();';
    $output .= '        textArea.select();';
    $output .= '        try {';
    $output .= '            var successful = document.execCommand("copy");';
    $output .= '            if (successful) {';
    $output .= '                button.text("Copiato!").addClass("copied");';
    $output .= '                setTimeout(function() {';
    $output .= '                    button.text("Copia").removeClass("copied");';
    $output .= '                }, 2000);';
    $output .= '            } else {';
    $output .= '                alert("Impossibile copiare il link. Riprova.");';
    $output .= '            }';
    $output .= '        } catch (err) {';
    $output .= '            alert("Impossibile copiare il link. Riprova.");';
    $output .= '        }';
    $output .= '        document.body.removeChild(textArea);';
    $output .= '    }';
    $output .= '});';
    $output .= '</script>';
    
    return $output;
}
add_shortcode('pdf_file', 'pdf_file_shortcode'); 