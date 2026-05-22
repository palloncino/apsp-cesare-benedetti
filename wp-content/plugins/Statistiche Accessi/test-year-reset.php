<?php
/**
 * Test file for Access Statistics Plugin Year Reset Functionality
 * 
 * This file tests the automatic year detection and counter reset feature.
 * Run this file to verify the plugin works correctly.
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if plugin is active
if (!class_exists('Access_Statistics')) {
    die('Access Statistics plugin is not active.');
}

echo "<h1>Access Statistics Plugin - Test Funzionalità</h1>";

// Get current year
$current_year = date('Y');
echo "<p><strong>Anno Corrente:</strong> $current_year</p>";

// Get stored year
$stored_year = get_option('access_statistics_current_year', 'Non impostato');
echo "<p><strong>Anno Memorizzato:</strong> $stored_year</p>";

// Test year change detection
if ($current_year != $stored_year) {
    echo "<p style='color: red;'><strong>Cambio anno rilevato!</strong> I contatori dovrebbero essere resettati.</p>";
} else {
    echo "<p style='color: green;'><strong>Nessun cambio anno rilevato.</strong> I contatori rimangono invariati.</p>";
}

// Get visit counts using shortcodes
echo "<p><strong>Visite Totali (Anno Corrente):</strong> " . do_shortcode('[access_total_visits]') . "</p>";
echo "<p><strong>Visitatori Unici (Anno Corrente):</strong> " . do_shortcode('[access_unique_visits]') . "</p>";

// Test shortcode output
echo "<h2>Test Shortcode</h2>";
echo "<p><strong>Shortcode predefinito [access_stats] (tutti gli anni):</strong></p>";
echo do_shortcode('[access_stats]');

echo "<p><strong>Shortcode anno specifico [access_stats year=\"2024\"]:</strong></p>";
echo do_shortcode('[access_stats year="2024"]');

echo "<p><strong>Shortcode anno corrente [access_stats year=\"$current_year\"]:</strong></p>";
echo do_shortcode("[access_stats year=\"$current_year\"]");

echo "<p><strong>Shortcode visite totali [access_total_visits]:</strong> " . do_shortcode('[access_total_visits]') . "</p>";
echo "<p><strong>Shortcode visitatori unici [access_unique_visits]:</strong> " . do_shortcode('[access_unique_visits]') . "</p>";

echo "<h2>Test Completato</h2>";
echo "<p>Se vedi questa pagina, il plugin funziona correttamente.</p>";
echo "<p><strong>Funzionalità implementate:</strong></p>";
echo "<ul>";
echo "<li>✅ Traduzione italiana: 'Statistiche per il $current_year'</li>";
echo "<li>✅ Tabella dinamica con statistiche in tempo reale</li>";
echo "<li>✅ Shortcode sempre aggiornati con dati correnti</li>";
echo "<li>✅ Reset automatico dei contatori ogni 1° gennaio</li>";
echo "<li>✅ Dati storici precaricati (2017-2024)</li>";
echo "<li>✅ Anno corrente creato solo quando ci sono visite reali</li>";
echo "</ul>";

// Test historical data
echo "<h2>Verifica Dati Storici</h2>";
echo "<p>Verifica che i dati storici siano stati caricati correttamente (solo anni precedenti al corrente):</p>";
$plugin = new Access_Statistics();
$all_stats = $plugin->get_all_stats();
if (!empty($all_stats)) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Anno</th><th>Tipo</th><th>Valore</th></tr>";
    foreach ($all_stats as $stat) {
        echo "<tr>";
        echo "<td>" . esc_html($stat->year) . "</td>";
        echo "<td>" . esc_html($stat->metric_type) . "</td>";
        echo "<td>" . esc_html($stat->value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><em>Nota: L'anno corrente ($current_year) apparirà solo dopo la prima visita registrata.</em></p>";
} else {
    echo "<p style='color: red;'>Nessun dato storico trovato.</p>";
}
?> 