<?php
/**
 * Main Access Statistics Plugin Class
 */
class Access_Statistics {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'access_statistics';
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add shortcodes
        add_shortcode('access_stats', array($this, 'shortcode_handler'));
        add_shortcode('access_total_visits', array($this, 'total_visits_shortcode'));
        add_shortcode('access_unique_visits', array($this, 'unique_visits_shortcode'));
        
        // Add admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Add AJAX handlers
        add_action('wp_ajax_add_access_stat', array($this, 'ajax_add_stat'));
        add_action('wp_ajax_edit_access_stat', array($this, 'ajax_edit_stat'));
        add_action('wp_ajax_delete_access_stat', array($this, 'ajax_delete_stat'));
        add_action('wp_ajax_update_auto_stats', array($this, 'ajax_update_auto_stats'));
        add_action('wp_ajax_toggle_unique_visits_display', array($this, 'ajax_toggle_unique_visits_display'));
        
        // Track all visits (not just Amministrazione Trasparente)
        add_action('wp_head', array($this, 'track_visit'));
        
        // Add daily cron job to aggregate statistics
        add_action('access_stats_daily_cron', array($this, 'aggregate_daily_stats'));
        if (!wp_next_scheduled('access_stats_daily_cron')) {
            wp_schedule_event(time(), 'daily', 'access_stats_daily_cron');
        }
        
        // Check for year change and reset counters if needed
        add_action('init', array($this, 'check_year_change'));
    }
    
    /**
     * Check if year has changed and reset counters if needed
     */
    public function check_year_change() {
        $current_year = date('Y');
        $last_recorded_year = get_option('access_statistics_current_year', $current_year);
        
        // If year has changed, reset counters
        if ($current_year != $last_recorded_year) {
            $this->reset_counters_for_new_year($current_year);
            update_option('access_statistics_current_year', $current_year);
        }
    }
    
    /**
     * Reset all visit counters for new year
     */
    private function reset_counters_for_new_year($new_year) {
        global $wpdb;
        $visits_table = $wpdb->prefix . 'access_statistics_visits';
        
        // Clear all visits data (simple reset, no historical archives)
        if ($this->visits_table_exists()) {
            $wpdb->query("TRUNCATE TABLE $visits_table");
        }
        
        // Clear manual statistics for the new year
        $stats_table = $wpdb->prefix . 'access_statistics';
        $wpdb->delete(
            $stats_table,
            array('year' => $new_year),
            array('%d')
        );
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Access Statistics: Year changed to ' . $new_year . ' - Counters reset');
        }
    }
    
    /**
     * Initialize current year statistics with 0 values
     * Note: This method is kept for compatibility but should not be used
     * Current year entries should only be created when actual visits occur
     */
    public function initialize_current_year_stats() {
        // Do nothing - current year entries should only be created when visits occur
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Access Statistics: initialize_current_year_stats called but ignored - entries created only on actual visits');
        }
    }
    
    /**
     * Ensure current year statistics exist in the database
     * Note: This method is kept for compatibility but does nothing
     * Current year entries should only be created when actual visits occur
     */
    private function ensure_current_year_stats_exist() {
        // Do nothing - current year entries should only be created when visits occur
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Access Statistics: ensure_current_year_stats_exist called but ignored - entries created only on actual visits');
        }
    }
    
    /**
     * Update current year statistics for real-time display
     * Only creates entries when actual visits are recorded
     */
    private function update_current_year_stats() {
        global $wpdb;
        $stats_table = $wpdb->prefix . 'access_statistics';
        $current_year = date('Y');
        
        // Get current totals
        $total_visits = $this->get_total_visits();
        $unique_visits = $this->get_unique_visits();
        
        // Only create/update total visits if there are actual visits
        if ($total_visits > 0) {
            $wpdb->replace(
                $stats_table,
                array(
                    'year' => $current_year,
                    'metric_type' => 'visualizzazioni',
                    'value' => $total_visits
                ),
                array('%d', '%s', '%d')
            );
        }
        
        // Only create/update unique visits if there are actual unique visitors
        if ($unique_visits > 0) {
            $wpdb->replace(
                $stats_table,
                array(
                    'year' => $current_year,
                    'metric_type' => 'visitatori_unici',
                    'value' => $unique_visits
                ),
                array('%d', '%s', '%d')
            );
        }
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Access Statistics: Updated current year stats - Total: ' . $total_visits . ', Unique: ' . $unique_visits);
        }
    }
    
    /**
     * Create the database table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            year int(4) NOT NULL,
            metric_type varchar(20) NOT NULL,
            value int(11) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_year_metric (year, metric_type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Insert sample data
     */
    public static function insert_sample_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        
        // Preload historical data (2017-2024 only, exclude current year)
        $current_year = date('Y');
        $historical_data = array(
            array('year' => 2024, 'metric_type' => 'utenti', 'value' => 11134),
            array('year' => 2023, 'metric_type' => 'utenti', 'value' => 13346),
            array('year' => 2022, 'metric_type' => 'utenti', 'value' => 12030),
            array('year' => 2021, 'metric_type' => 'utenti', 'value' => 4283),
            array('year' => 2020, 'metric_type' => 'utenti', 'value' => 2828),
            array('year' => 2019, 'metric_type' => 'utenti', 'value' => 4176),
            array('year' => 2018, 'metric_type' => 'utenti', 'value' => 2907),
            array('year' => 2017, 'metric_type' => 'visualizzazioni', 'value' => 250),
        );
        
        // Only insert data for years before current year
        foreach ($historical_data as $data) {
            if ($data['year'] < $current_year) {
                $wpdb->replace(
                    $table_name,
                    array(
                        'year' => $data['year'],
                        'metric_type' => $data['metric_type'],
                        'value' => $data['value']
                    ),
                    array('%d', '%s', '%d')
                );
            }
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Statistiche Accessi',
            'Statistiche Accessi',
            'manage_options',
            'access-statistics',
            array($this, 'admin_page'),
            'dashicons-chart-bar',
            30
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        // Check if table exists, if not create it
        if (!$this->table_exists()) {
            self::create_table();
            self::insert_sample_data();
        }
        
        $current_year = date('Y');
        
        $stats = $this->get_all_stats();
        $current_total_visits = $this->get_total_visits();
        $current_unique_visits = $this->get_unique_visits();
        ?>
        <div class="wrap access-statistics-admin" style="max-width: none !important; width: 100% !important;">
            <h1>Gestione Statistiche Accessi</h1>
            
            <div class="notice notice-info">
                <p><strong>Tracciamento Automatico:</strong> Questo plugin traccia automaticamente tutte le visite del sito. Le statistiche vengono aggiornate quotidianamente.</p>
            </div>
            
            <!-- Current Statistics Dashboard -->
            <div class="card access-statistics-card" style="max-width: none !important; width: 100% !important; box-sizing: border-box;">
                <h2>
                    <span class="dashicons dashicons-chart-bar" style="margin-right: 8px;"></span>
                    Dashboard Statistiche Correnti
                </h2>
                <div style="margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #0073aa; font-size: 1.2em;">Statistiche per il <?php echo esc_html($current_year); ?></h3>
                </div>
                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1; padding: 20px; background: #e7f5ff; border-radius: 4px; text-align: center;">
                        <h3 style="margin: 0 0 10px 0; color: #0066cc;">Visite Totali</h3>
                        <div style="font-size: 2em; font-weight: bold; color: #0066cc;"><?php echo number_format($current_total_visits); ?></div>
                        <small style="color: #666;">Tutte le visite registrate</small>
                    </div>
                    <div style="flex: 1; padding: 20px; background: #f0f8ff; border-radius: 4px; text-align: center;">
                        <h3 style="margin: 0 0 10px 0; color: #0066cc;">Visitatori Unici</h3>
                        <div style="font-size: 2em; font-weight: bold; color: #0066cc;"><?php echo number_format($current_unique_visits); ?></div>
                        <small style="color: #666;">IP unici (una volta al giorno)</small>
                    </div>
                </div>
            </div>
            
            <div class="card access-statistics-card" style="max-width: none !important; width: 100% !important; box-sizing: border-box;">
                <h2>Gestione Statistiche</h2>
                <p><strong>Tracciamento Automatico:</strong> Il plugin traccia automaticamente tutte le visite del sito. Ogni visitatore viene contato una sola volta al giorno.</p>
                
                <h3>Aggiungi Statistica Manuale</h3>
                <form id="add-stat-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="year">Anno:</label></th>
                            <td><input type="number" id="year" name="year" min="2000" max="2030" value="<?php echo esc_attr($current_year); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="metric_type">Tipo di Metrica:</label></th>
                            <td>
                                <select id="metric_type" name="metric_type" required>
                                    <option value="">Seleziona Tipo</option>
                                    <option value="utenti">Utenti (Annuo)</option>
                                    <option value="utenti_mensili">Utenti (Mensile)</option>
                                    <option value="visualizzazioni">Visualizzazioni</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="value">Valore:</label></th>
                            <td><input type="number" id="value" name="value" min="0" required></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary">Aggiungi Statistica</button>
                    </p>
                </form>
                
                <h3>Aggiorna Statistiche Automatiche</h3>
                <p>Forza l'aggiornamento delle statistiche automatiche (normalmente eseguito quotidianamente):</p>
                <button type="button" class="button button-secondary" id="update-auto-stats">Aggiorna Ora</button>
                
                <h3 style="margin-top: 30px;">Impostazioni Visualizzazione Frontend</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="show_unique_visits">Visualizza Visitatori Unici</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="show_unique_visits" name="show_unique_visits" value="1" <?php checked(get_option('access_statistics_show_unique_visits', true), true); ?>>
                                Mostra "visitatori_unici" nel frontend (shortcode [access_stats])
                            </label>
                            <p class="description">
                                <strong>Nota:</strong> "visualizzazioni" è sempre visibile nel frontend. Questa opzione controlla solo la visualizzazione di "visitatori_unici".
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" class="button button-primary" id="save-display-settings">Salva Impostazioni</button>
                </p>
            </div>
            
            <div class="card access-statistics-card" style="max-width: none !important; width: 100% !important; box-sizing: border-box;">
                <h2>Statistiche Attuali</h2>
                <?php 
                // Get current year statistics
                $current_year_stats = $this->get_stats_by_year($current_year);
                
                // Get current totals for display
                $current_total_visits = $this->get_total_visits();
                $current_unique_visits = $this->get_unique_visits();
                
                // Create a comprehensive stats array
                $display_stats = array();
                
                // Add current year automatic statistics only if they exist in database
                // Only show visualizzazioni and visitatori_unici for current year
                if ($current_total_visits > 0) {
                    $display_stats[] = (object) array(
                        'id' => 'auto_total',
                        'year' => $current_year,
                        'metric_type' => 'visualizzazioni',
                        'value' => $current_total_visits,
                        'is_auto' => true,
                        'is_current_year' => true
                    );
                }
                
                if ($current_unique_visits > 0) {
                    $display_stats[] = (object) array(
                        'id' => 'auto_unique',
                        'year' => $current_year,
                        'metric_type' => 'visitatori_unici',
                        'value' => $current_unique_visits,
                        'is_auto' => true,
                        'is_current_year' => true
                    );
                }
                
                // For current year, do not add any manual statistics
                // Only automatic visualizzazioni and visitatori_unici are shown
                
                // Get all historical statistics (excluding current year)
                $all_stats = $this->get_all_stats();
                foreach ($all_stats as $stat) {
                    if ($stat->year != $current_year) {
                        // For past years, show all metrics with full editing capabilities
                        $display_stats[] = $stat;
                    }
                }
                
                // Sort by year (descending) and then by metric type
                usort($display_stats, function($a, $b) {
                    if ($a->year != $b->year) {
                        return $b->year - $a->year; // Descending year order
                    }
                    return strcmp($a->metric_type, $b->metric_type);
                });
                
                if (empty($display_stats)): ?>
                    <div class="notice notice-warning">
                        <p><strong>Nessuna statistica presente.</strong> Le statistiche automatiche verranno mostrate non appena inizieranno le visite.</p>
                    </div>
                <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                                <th>Anno</th>
                                <th>Tipo di Metrica</th>
                                <th>Valore</th>
                                <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($display_stats as $stat): ?>
                        <tr>
                            <td><?php echo esc_html($stat->year); ?></td>
                            <td>
                                <?php echo esc_html($stat->metric_type); ?>
                                <?php if ($stat->year == $current_year): ?>
                                    <span style="color: #0073aa; font-size: 0.8em;">(automatico)</span>
                                <?php elseif (isset($stat->is_auto) && $stat->is_auto): ?>
                                    <span style="color: #0073aa; font-size: 0.8em;">(automatico)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($stat->value); ?></td>
                            <td>
                                <?php if ($stat->year == $current_year): ?>
                                    <span style="color: #666; font-size: 0.8em;">Aggiornamento automatico</span>
                                <?php elseif (!isset($stat->is_auto) || !$stat->is_auto): ?>
                                    <button class="button button-small edit-stat" data-id="<?php echo esc_attr($stat->id); ?>" data-year="<?php echo esc_attr($stat->year); ?>" data-metric="<?php echo esc_attr($stat->metric_type); ?>" data-value="<?php echo esc_attr($stat->value); ?>">Modifica</button>
                                    <button class="button button-small delete-stat" data-id="<?php echo esc_attr($stat->id); ?>">Elimina</button>
                                <?php else: ?>
                                    <span style="color: #666; font-size: 0.8em;">Aggiornamento automatico</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <!-- Shortcode Legend Section - Moved to bottom -->
            <div class="card access-statistics-card" style="max-width: none !important; width: 100% !important; box-sizing: border-box; margin-top: 20px; border-top: 3px solid #f0f0f0;">
                <h2 style="color: #666; font-size: 1.1em;">
                    <span class="dashicons dashicons-editor-code" style="margin-right: 8px;"></span>
                    Legenda Shortcode
                    <button type="button" class="button button-small" id="toggle-shortcode-legend" style="margin-left: 10px;">
                        <span class="dashicons dashicons-arrow-down-alt2"></span> Mostra/Nascondi
                    </button>
                </h2>
                <div id="shortcode-legend-content" style="display: none;">
                    <p><strong>Utilizza questi shortcode per visualizzare le statistiche nelle tue pagine:</strong></p>
                    
                    <table class="widefat" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Shortcode</th>
                                <th>Descrizione</th>
                                <th style="width: 300px;">Esempio di Utilizzo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>[access_total_visits]</code></td>
                                <td>Mostra il numero totale di visite al sito</td>
                                <td><code>[access_total_visits]</code> → <strong><?php echo number_format($current_total_visits); ?></strong></td>
                            </tr>
                            <tr>
                                <td><code>[access_unique_visits]</code></td>
                                <td>Mostra il numero di visitatori unici (basato su IP)</td>
                                <td><code>[access_unique_visits]</code> → <strong><?php echo number_format($current_unique_visits); ?></strong></td>
                            </tr>
                            <tr>
                                <td><code>[access_stats]</code></td>
                                <td>Mostra tutte le statistiche in formato tabella</td>
                                <td><code>[access_stats]</code> → Tabella completa</td>
                            </tr>
                            <tr>
                                <td><code>[access_stats year="<?php echo esc_attr($current_year); ?>"]</code></td>
                                <td>Mostra statistiche per anno specifico</td>
                                <td><code>[access_stats year="<?php echo esc_attr($current_year); ?>"]</code> → Solo <?php echo esc_html($current_year); ?></td>
                            </tr>
                            <tr>
                                <td><code>[access_stats]</code></td>
                                <td>Mostra tutte le statistiche disponibili (tutti gli anni)</td>
                                <td><code>[access_stats]</code> → Tutti gli anni</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-left: 4px solid #0073aa;">
                        <h4 style="margin-top: 0;">💡 Suggerimenti:</h4>
                        <ul style="margin-bottom: 0;">
                            <li>Gli shortcode funzionano in pagine, post e widget</li>
                            <li>Le statistiche si aggiornano automaticamente in tempo reale</li>
                            <li>I visitatori unici sono basati sull'indirizzo IP (un conteggio per IP al giorno)</li>
                            <li>I contatori si resettano automaticamente ogni 1° gennaio</li>
                            <li>Gli shortcode mostrano sempre i dati dell'anno corrente</li>
                            <li>Puoi forzare l'aggiornamento delle statistiche usando il pulsante "Aggiorna Ora" qui sopra</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Check if table exists
     */
    private function table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    }
    
    /**
     * Get all statistics
     */
    public function get_all_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY year DESC, metric_type ASC");
    }
    
    /**
     * Get statistics by year
     */
    private function get_stats_by_year($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE year = %d ORDER BY metric_type ASC", $year));
    }
    
    /**
     * Shortcode handler
     */
    public function shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'year' => null
        ), $atts);
        
        $current_year = date('Y');
        
        // If specific year is requested, show only that year
        if ($atts['year']) {
            $stats = $this->get_stats_by_year($atts['year']);
            $title = 'Contatore degli accessi alla sezione Amministrazione Trasparente - Anno ' . esc_html($atts['year']);
        } else {
            // Show all available years
            $stats = $this->get_all_stats();
            $title = 'Contatore degli accessi alla sezione Amministrazione Trasparente - Tutti gli anni';
        }
        
        // For current year, add dynamic statistics if they exist but are not in database yet
        $show_unique_visits = get_option('access_statistics_show_unique_visits', true);
        $current_year_has_stats = false;
        
        if ($atts['year'] == $current_year || !$atts['year']) {
            $current_total_visits = $this->get_total_visits();
            $current_unique_visits = $this->get_unique_visits();
            
            // Check if current year stats are already in the array
            foreach ($stats as $stat) {
                if ($stat->year == $current_year) {
                    $current_year_has_stats = true;
                    break;
                }
            }
            
            // If current year stats don't exist in database but we have visits, add them dynamically
            if (!$current_year_has_stats && ($current_total_visits > 0 || $current_unique_visits > 0)) {
                if ($current_total_visits > 0) {
                    $stats[] = (object) array(
                        'year' => $current_year,
                        'metric_type' => 'visualizzazioni',
                        'value' => $current_total_visits
                    );
                }
                if ($current_unique_visits > 0) {
                    $stats[] = (object) array(
                        'year' => $current_year,
                        'metric_type' => 'visitatori_unici',
                        'value' => $current_unique_visits
                    );
                }
            }
        }
        
        if (empty($stats)) {
            return '<p>Nessuna statistica disponibile.</p>';
        }
        
        // Filter current year to show only automatic metrics based on settings
        $filtered_stats = array();
        
        foreach ($stats as $stat) {
            if ($stat->year == $current_year) {
                // For current year, always show visualizzazioni
                if ($stat->metric_type === 'visualizzazioni') {
                    $filtered_stats[] = $stat;
                }
                // Show visitatori_unici only if enabled in settings
                elseif ($stat->metric_type === 'visitatori_unici' && $show_unique_visits) {
                    $filtered_stats[] = $stat;
                }
            } else {
                // For past years, show all metrics
                $filtered_stats[] = $stat;
            }
        }
        
        $output = '<div class="access-statistics">';
        $output .= '<h3>' . $title . '</h3>';
        $output .= '<table class="access-stats-table">';
        $output .= '<thead><tr><th>Anno</th><th>Tipo di Metrica</th><th>Valore</th></tr></thead>';
        $output .= '<tbody>';
        
        // Sort by year (descending) and then by metric type
        usort($filtered_stats, function($a, $b) {
            if ($a->year != $b->year) {
                return $b->year - $a->year; // Descending year order
            }
            return strcmp($a->metric_type, $b->metric_type);
        });
        
        foreach ($filtered_stats as $stat) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($stat->year) . '</td>';
            $output .= '<td>' . esc_html($stat->metric_type);
            if ($stat->year == $current_year) {
                $output .= ' <span style="color: #0073aa; font-size: 0.8em;">(automatico)</span>';
            }
            $output .= '</td>';
            $output .= '<td>' . esc_html($stat->value) . '</td>';
            $output .= '</tr>';
        }
        
        $output .= '</tbody></table></div>';
        
        return $output;
    }
    
    /**
     * Total visits shortcode - always returns current year data
     */
    public function total_visits_shortcode($atts) {
        // Force refresh of current year data
        $total_visits = $this->get_total_visits();
        return '<span class="access-total-visits">' . number_format($total_visits) . '</span>';
    }
    
    /**
     * Unique visits shortcode - always returns current year data
     */
    public function unique_visits_shortcode($atts) {
        // Force refresh of current year data
        $unique_visits = $this->get_unique_visits();
        return '<span class="access-unique-visits">' . number_format($unique_visits) . '</span>';
    }
    
    /**
     * Get total visits count for current year
     */
    private function get_total_visits() {
        global $wpdb;
        $visits_table = $wpdb->prefix . 'access_statistics_visits';
        $current_year = date('Y');
        
        if (!$this->visits_table_exists()) {
            return 0;
        }
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $visits_table WHERE YEAR(visit_date) = %d",
            $current_year
        ));
    }
    
    /**
     * Get unique visits count for current year
     */
    private function get_unique_visits() {
        global $wpdb;
        $visits_table = $wpdb->prefix . 'access_statistics_visits';
        $current_year = date('Y');
        
        if (!$this->visits_table_exists()) {
            return 0;
        }
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_ip) FROM $visits_table WHERE YEAR(visit_date) = %d",
            $current_year
        ));
    }
    
    /**
     * Admin scripts
     */
    public function admin_scripts($hook) {
        if ($hook != 'toplevel_page_access-statistics') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'access_stats_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('access_stats_nonce')
        ));
        
        wp_add_inline_style('access-statistics-admin', '
            .inline-edit-form {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .inline-edit-form input {
                border: 1px solid #0073aa;
                border-radius: 3px;
                padding: 2px 5px;
            }
            .inline-edit-form button {
                margin: 0;
            }
        ');
        
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Shortcode legend toggle
                $("#toggle-shortcode-legend").on("click", function() {
                    var content = $("#shortcode-legend-content");
                    var icon = $(this).find(".dashicons");
                    
                    if (content.is(":visible")) {
                        content.slideUp();
                        icon.removeClass("dashicons-arrow-up-alt2").addClass("dashicons-arrow-down-alt2");
                    } else {
                        content.slideDown();
                        icon.removeClass("dashicons-arrow-down-alt2").addClass("dashicons-arrow-up-alt2");
                    }
                });
                
                $("#add-stat-form").on("submit", function(e) {
                    e.preventDefault();
                    var formData = {
                        action: "add_access_stat",
                        nonce: access_stats_ajax.nonce,
                        year: $("#year").val(),
                        metric_type: $("#metric_type").val(),
                        value: $("#value").val()
                    };
                    
                    $.post(access_stats_ajax.ajax_url, formData, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert("Errore: " + response.data);
                        }
                    });
                });
                
                $(".edit-stat").on("click", function() {
                    var button = $(this);
                    var id = button.data("id");
                    var value = button.data("value");
                    var valueCell = button.closest("tr").find("td:eq(2)");
                    var originalValue = valueCell.text();
                    var editForm = `<div class=\"inline-edit-form\">`
                        + `<input type=\"number\" class=\"edit-value\" value=\"${value}\" min=\"0\" style=\"width: 80px; margin-right: 5px;\">`
                        + `<button class=\"button button-small save-edit\" data-id=\"${id}\">Salva</button>`
                        + `<button class=\"button button-small cancel-edit\">Annulla</button>`
                        + `</div>`;
                    valueCell.html(editForm);
                    button.closest("td").find("button").hide();
                    valueCell.find(".edit-value").focus();
                    valueCell.find(".save-edit").on("click", function() {
                        var newValue = valueCell.find(".edit-value").val();
                        if (newValue < 0) { alert("Il valore non può essere negativo"); return; }
                        var formData = {
                            action: "edit_access_stat",
                            nonce: access_stats_ajax.nonce,
                            id: id,
                            value: newValue
                        };
                        $.post(access_stats_ajax.ajax_url, formData, function(response) {
                            if (response.success) {
                                valueCell.text(newValue);
                                button.data("value", newValue);
                                button.closest("td").find("button").show();
                            } else {
                                alert("Errore: " + response.data);
                                valueCell.text(originalValue);
                                button.closest("td").find("button").show();
                            }
                        });
                    });
                    valueCell.find(".cancel-edit").on("click", function() {
                        valueCell.text(originalValue);
                        button.closest("td").find("button").show();
                    });
                    valueCell.find(".edit-value").on("keypress", function(e) {
                        if (e.which === 13) { valueCell.find(".save-edit").click(); }
                    });
                    valueCell.find(".edit-value").on("keydown", function(e) {
                        if (e.which === 27) { valueCell.find(".cancel-edit").click(); }
                    });
                });
                
                $(".delete-stat").on("click", function() {
                    if (confirm("Sei sicuro di voler eliminare questa statistica?")) {
                        var id = $(this).data("id");
                        var formData = {
                            action: "delete_access_stat",
                            nonce: access_stats_ajax.nonce,
                            id: id
                        };
                        
                        $.post(access_stats_ajax.ajax_url, formData, function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert("Errore: " + response.data);
                            }
                        });
                    }
                });
                
                $("#update-auto-stats").on("click", function() {
                    var button = $(this);
                    button.prop("disabled", true).text("Aggiornamento in corso...");
                    
                    var formData = {
                        action: "update_auto_stats",
                        nonce: access_stats_ajax.nonce
                    };
                    
                    $.post(access_stats_ajax.ajax_url, formData, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert("Errore: " + response.data);
                        }
                        button.prop("disabled", false).text("Aggiorna Ora");
                    });
                });
                
                $("#save-display-settings").on("click", function() {
                    var button = $(this);
                    var showUnique = $("#show_unique_visits").is(":checked") ? 1 : 0;
                    button.prop("disabled", true).text("Salvataggio...");
                    
                    var formData = {
                        action: "toggle_unique_visits_display",
                        nonce: access_stats_ajax.nonce,
                        show_unique_visits: showUnique
                    };
                    
                    $.post(access_stats_ajax.ajax_url, formData, function(response) {
                        if (response.success) {
                            alert("Impostazioni salvate con successo!");
                            location.reload();
                        } else {
                            alert("Errore: " + response.data);
                            button.prop("disabled", false).text("Salva Impostazioni");
                        }
                    });
                });
            });
        ');
    }
    
    /**
     * AJAX add statistic
     */
    public function ajax_add_stat() {
        check_ajax_referer('access_stats_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $year = intval($_POST['year']);
        $metric_type = sanitize_text_field($_POST['metric_type']);
        $value = intval($_POST['value']);
        
        if ($year < 2000 || $year > 2030 || $value < 0) {
            wp_send_json_error('Dati non validi');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        
        $result = $wpdb->replace(
            $table_name,
            array(
                'year' => $year,
                'metric_type' => $metric_type,
                'value' => $value
            ),
            array('%d', '%s', '%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Errore del database');
        }
        
        wp_send_json_success('Statistica aggiunta con successo');
    }
    
    /**
     * AJAX edit statistic
     */
    public function ajax_edit_stat() {
        check_ajax_referer('access_stats_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $value = intval($_POST['value']);
        
        if ($value < 0) {
            wp_send_json_error('Il valore non può essere negativo');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        
        // Get the current statistic to verify it exists
        $current_stat = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
        
        if (!$current_stat) {
            wp_send_json_error('Statistica non trovata');
        }
        
        // Update the value
        $result = $wpdb->update(
            $table_name,
            array('value' => $value),
            array('id' => $id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Errore del database');
        }
        
        wp_send_json_success(array(
            'message' => 'Statistica aggiornata con successo',
            'new_value' => $value
        ));
    }
    
    /**
     * Track visit to all pages
     */
    public function track_visit() {
        // Only track on frontend, not admin
        if (is_admin()) {
            return;
        }
        
        // Skip tracking for bots and crawlers
        if ($this->is_bot()) {
            return;
        }
        
        // Store visit in session/cookie to avoid counting same user multiple times per day
        $visit_key = 'site_visit_' . date('Y-m-d');
        if (isset($_COOKIE[$visit_key])) {
            return; // Already counted today
        }
        
        // Set cookie for 24 hours
        setcookie($visit_key, '1', time() + 86400, '/');
        
        // Store visit in database
        $this->store_visit();
        
        // Update current year statistics immediately for real-time updates
        $this->update_current_year_stats();
        
        // Debug logging (only if WP_DEBUG is enabled)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Access Statistics: Visit tracked - IP: ' . $this->get_user_ip() . ', Page: ' . get_the_title() . ' (ID: ' . get_the_ID() . ')');
        }
    }
    
    /**
     * Check if current user is a bot/crawler
     */
    private function is_bot() {
        $bot_patterns = array(
            'bot', 'crawler', 'spider', 'scraper', 'slurp', 'mediapartners',
            'googlebot', 'bingbot', 'yandex', 'baiduspider', 'facebookexternalhit'
        );
        
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $user_agent = strtolower($user_agent);
        
        foreach ($bot_patterns as $pattern) {
            if (strpos($user_agent, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if current page is part of Amministrazione Trasparente section
     */
    private function is_amministrazione_trasparente_page() {
        // Get current page
        $current_page = get_post();
        if (!$current_page) {
            return false;
        }
        
        // Define Amministrazione Trasparente root pages
        $at_roots = array(
            'amministrazione-trasparente',
            'disposizioni-generali',
            'organizzazione',
            'consulenti-e-collaboratori',
            'personale',
            'bandi-di-concorso',
            'performance',
            'enti-controllati',
            'attivita-e-procedimenti',
            'provvedimenti',
            'controlli-sulle-imprese',
            'bandi-di-gara-e-contatti',
            'sovvenzioni-contributi-sussidi-vantaggi-economici',
            'bilanci',
            'beni-immobili-e-gestione-patrimonio',
            'controlli-rilievi-sull-amministrazione',
            'servizi-erogati',
            'pagamenti-dell-amministrazione',
            'opere-pubbliche',
            'pianificazione-e-governo-del-territorio',
            'informazioni-ambientali',
            'strutture-sanitarie-e-private-accreditate',
            'interventi-straordinari-di-emergenza',
            'altri-contenuti-4',
            'contatore-accessi'
        );
        
        // Check if current page is a root page
        $current_slug = $current_page->post_name;
        if (in_array($current_slug, $at_roots)) {
            return true;
        }
        
        // Check if current page is a child of any root page
        $current_ancestors = get_post_ancestors($current_page);
        foreach ($current_ancestors as $ancestor_id) {
            $ancestor = get_post($ancestor_id);
            if ($ancestor && in_array($ancestor->post_name, $at_roots)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Store visit in database
     */
    private function store_visit() {
        global $wpdb;
        $visits_table = $wpdb->prefix . 'access_statistics_visits';
        
        // Create visits table if it doesn't exist
        if (!$this->visits_table_exists()) {
            $this->create_visits_table();
        }
        
        $current_date = current_time('Y-m-d');
        $user_ip = $this->get_user_ip();
        $page_id = get_the_ID();
        $page_title = get_the_title();
        
        // If we can't get page info, use a fallback
        if (!$page_id) {
            $page_id = 0;
            $page_title = 'Unknown Page';
        }
        
        // Check if this IP already visited today
        $existing_visit = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $visits_table WHERE visit_date = %s AND user_ip = %s",
            $current_date,
            $user_ip
        ));
        
        if (!$existing_visit) {
            $result = $wpdb->insert(
                $visits_table,
                array(
                    'visit_date' => $current_date,
                    'user_ip' => $user_ip,
                    'page_id' => $page_id,
                    'page_title' => $page_title,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%d', '%s', '%s')
            );
            
            if ($result === false) {
                // Log error if debug is enabled
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Access Statistics: Failed to insert visit - ' . $wpdb->last_error);
                }
            }
        }
    }
    
    /**
     * Create visits table
     */
    private function create_visits_table() {
        global $wpdb;
        $visits_table = $wpdb->prefix . 'access_statistics_visits';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $visits_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            visit_date date NOT NULL,
            user_ip varchar(45) NOT NULL,
            page_id bigint(20) NOT NULL,
            page_title varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY visit_date (visit_date),
            KEY user_ip (user_ip)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check if visits table exists
     */
    private function visits_table_exists() {
        global $wpdb;
        $visits_table = $wpdb->prefix . 'access_statistics_visits';
        return $wpdb->get_var("SHOW TABLES LIKE '$visits_table'") == $visits_table;
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /**
     * Aggregate daily statistics
     */
    public function aggregate_daily_stats() {
        global $wpdb;
        $visits_table = $wpdb->prefix . 'access_statistics_visits';
        $stats_table = $wpdb->prefix . 'access_statistics';
        
        if (!$this->visits_table_exists()) {
            return;
        }
        
        $current_year = date('Y');
        $current_month = date('m');
        
        // Get total visits for current year
        $total_visits = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $visits_table WHERE YEAR(visit_date) = %d",
            $current_year
        ));
        
        // Get unique visitors for current month
        $monthly_visitors = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_ip) FROM $visits_table 
             WHERE YEAR(visit_date) = %d AND MONTH(visit_date) = %d",
            $current_year,
            $current_month
        ));
        
        // Get unique visitors for current year
        $yearly_visitors = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_ip) FROM $visits_table 
             WHERE YEAR(visit_date) = %d",
            $current_year
        ));
        
        // Only create/update total visits if there are actual visits
        if ($total_visits > 0) {
            $wpdb->replace(
                $stats_table,
                array(
                    'year' => $current_year,
                    'metric_type' => 'visualizzazioni',
                    'value' => $total_visits
                ),
                array('%d', '%s', '%d')
            );
        }
        
        // Only create/update unique visitors if there are actual visitors
        if ($yearly_visitors > 0) {
            $wpdb->replace(
                $stats_table,
                array(
                    'year' => $current_year,
                    'metric_type' => 'visitatori_unici',
                    'value' => $yearly_visitors
                ),
                array('%d', '%s', '%d')
            );
        }
        
        // Update or insert monthly statistics
        $wpdb->replace(
            $stats_table,
            array(
                'year' => $current_year,
                'metric_type' => 'utenti_mensili',
                'value' => $monthly_visitors
            ),
            array('%d', '%s', '%d')
        );
        
        // Update or insert yearly statistics
        $wpdb->replace(
            $stats_table,
            array(
                'year' => $current_year,
                'metric_type' => 'utenti',
                'value' => $yearly_visitors
            ),
            array('%d', '%s', '%d')
        );
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Access Statistics: Daily aggregation completed - Total: ' . $total_visits . ', Monthly Unique: ' . $monthly_visitors . ', Yearly Unique: ' . $yearly_visitors);
        }
    }
    
    /**
     * AJAX update auto statistics
     */
    public function ajax_update_auto_stats() {
        check_ajax_referer('access_stats_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->aggregate_daily_stats();
        $this->update_current_year_stats();
        wp_send_json_success('Statistiche automatiche aggiornate con successo');
    }
    
    /**
     * AJAX delete statistic
     */
    public function ajax_delete_stat() {
        check_ajax_referer('access_stats_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'access_statistics';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Errore del database');
        }
        
        wp_send_json_success('Statistica eliminata con successo');
    }
    
    /**
     * AJAX toggle unique visits display setting
     */
    public function ajax_toggle_unique_visits_display() {
        check_ajax_referer('access_stats_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $show_unique_visits = isset($_POST['show_unique_visits']) && intval($_POST['show_unique_visits']) === 1;
        
        update_option('access_statistics_show_unique_visits', $show_unique_visits);
        
        wp_send_json_success('Impostazioni salvate con successo');
    }
} 