<?php
/**
 * AT Dynamic Menu Widget
 * 
 * A dynamic widget that builds a nested menu structure from WordPress pages
 * for the Amministrazione Trasparente section.
 * 
 * SHORTCODE: [at_dynamic_menu title="Custom Title" search="true"]
 * 
 * @package Astra
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * AT Dynamic Menu Admin Class
 */
class AT_Dynamic_Menu_Admin
{
	/**
	 * Clean up label by removing leading quotes and trimming whitespace.
	 *
	 * (Admin utilities like export/import run in this class, so it needs its own helper.)
	 */
	private function clean_label($label)
	{
		if (!is_string($label)) {
			return '';
		}

		$clean = trim($label);
		// Remove leading quotes (both regular and HTML entity)
		$clean = ltrim($clean, '"');
		$clean = ltrim($clean, '&quot;');
		$clean = trim($clean);
		return $clean;
	}

	/**
	 * Ensure every menu item has a unique 'id' (deduplicate and fill missing).
	 * Required to make export/import mapping safe (1:1).
	 */
	private function regenerate_menu_item_ids()
	{
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		if (empty($menu_structure) || !is_array($menu_structure)) {
			wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=no_menu_to_regen_ids'));
			exit;
		}

		$seen = array();
		$changed = 0;
		$total = 0;

		$this->regenerate_menu_item_ids_recursive($menu_structure, $seen, $changed, $total);

		update_option('at_dynamic_menu_structure', $menu_structure);

		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=menu_ids_regenerated&changed=' . intval($changed) . '&total=' . intval($total)));
		exit;
	}

	private function regenerate_menu_item_ids_recursive(&$items, &$seen, &$changed, &$total)
	{
		if (!is_array($items)) {
			return;
		}

		foreach ($items as &$item) {
			if (!is_array($item)) {
				continue;
			}
			$total++;

			$current_id = isset($item['id']) ? (string) $item['id'] : '';
			if ($current_id === '' || isset($seen[$current_id])) {
				$new_id = $this->generate_menu_item_id();
				while (isset($seen[$new_id])) {
					$new_id = $this->generate_menu_item_id();
				}
				$item['id'] = $new_id;
				$changed++;
				$seen[$new_id] = true;
			} else {
				$seen[$current_id] = true;
			}

			if (!empty($item['children'])) {
				$this->regenerate_menu_item_ids_recursive($item['children'], $seen, $changed, $total);
			}
		}
	}

	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'handle_form_submission'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		
		// AJAX handlers
		add_action('wp_ajax_at_save_menu', array($this, 'ajax_save_menu'));
		add_action('wp_ajax_at_add_root', array($this, 'ajax_add_root'));
		add_action('wp_ajax_at_add_child', array($this, 'ajax_add_child'));
		add_action('wp_ajax_at_update_item', array($this, 'ajax_update_item'));
		add_action('wp_ajax_at_delete_item', array($this, 'ajax_delete_item'));
		add_action('wp_ajax_at_toggle_verified', array($this, 'ajax_toggle_verified'));
	}

	/**
	 * Add admin menu page
	 */
	public function add_admin_menu()
	{
		add_submenu_page(
			'tools.php',
			'Menu Builder',
			'Menu Builder',
			'manage_options',
			'at-dynamic-menu',
			array($this, 'admin_page')
		);
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts($hook)
	{
		if ('tools_page_at-dynamic-menu' !== $hook) {
			return;
		}

		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style('wp-admin');
		wp_enqueue_style('jquery-ui-dialog');
	}

	/**
	 * Handle form submission
	 */
	public function handle_form_submission()
	{
		if (
			!isset($_POST['at_dynamic_menu_nonce']) ||
			!wp_verify_nonce($_POST['at_dynamic_menu_nonce'], 'at_dynamic_menu_action')
		) {
			return;
		}

		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'save_menu':
					$this->save_menu_structure();
					break;
				case 'add_root':
					$this->add_root_item();
					break;
				case 'add_child':
					$this->add_child_item();
					break;
				case 'delete_item':
					$this->delete_menu_item();
					break;
				case 'create_default_menu':
					$this->create_default_menu();
					break;
				case 'clean_menu_labels':
					$this->clean_menu_labels();
					break;
				case 'fix_menu_page_ids':
					$this->fix_menu_page_ids();
					break;
				case 'export_menu_for_matching':
					$this->export_menu_for_matching();
					break;
				case 'import_menu_mapping':
					$this->import_menu_mapping();
					break;
			case 'regenerate_menu_item_ids':
				$this->regenerate_menu_item_ids();
				break;
			case 'update_item':
				$this->update_menu_item();
				break;
			}
		}
	}

	/**
	 * Save menu structure
	 */
	private function save_menu_structure()
	{
		if (!isset($_POST['menu_structure'])) {
			return;
		}

		$menu_structure = json_decode(stripslashes($_POST['menu_structure']), true);
		
		if (is_array($menu_structure)) {
			update_option('at_dynamic_menu_structure', $menu_structure);
			// AJAX response
			if (defined('DOING_AJAX') && DOING_AJAX) {
				wp_send_json_success(array('message' => 'Menu salvato con successo.'));
			}
			wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=saved'));
			exit;
		}
	}

	/**
	 * Add root item
	 */
	private function add_root_item()
	{
		$label = sanitize_text_field($_POST['root_label']);
		$page_id = intval($_POST['root_page_id']);

		if (empty($label) || empty($page_id)) {
			wp_die('Label e pagina sono obbligatori.');
		}

		$menu_structure = get_option('at_dynamic_menu_structure', array());
		
		$new_item = array(
			'id' => 'root_' . time(),
			'type' => 'root',
			'label' => $label,
			'page_id' => $page_id,
			'children' => array()
		);

		$menu_structure[] = $new_item;
		update_option('at_dynamic_menu_structure', $menu_structure);

		// AJAX response
		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_send_json_success(array('message' => 'Elemento radice aggiunto con successo.', 'redirect' => false));
		}
		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=root_added'));
		exit;
	}

	/**
	 * Add child item
	 */
	private function add_child_item()
	{
		$parent_id = sanitize_text_field($_POST['parent_id']);
		$label = sanitize_text_field($_POST['child_label']);
		$type = sanitize_text_field($_POST['child_type']); // 'page' or 'internal_link'
		$page_id = intval($_POST['child_page_id']);
		$anchor = sanitize_title($_POST['child_anchor']);

		if (empty($label) || empty($parent_id)) {
			wp_die('Label e parent sono obbligatori.');
		}

		$menu_structure = get_option('at_dynamic_menu_structure', array());
		
		$new_child = array(
			'id' => 'child_' . time(),
			'type' => 'page', // Always use page type - no more internal links
			'label' => $label,
			'page_id' => $page_id,
			'anchor' => '' // No anchors needed
		);

		// Find parent and add child
		$this->add_child_to_parent($menu_structure, $parent_id, $new_child);
		
		update_option('at_dynamic_menu_structure', $menu_structure);

		// AJAX response
		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_send_json_success(array('message' => 'Sub-pagina aggiunta con successo.', 'redirect' => false));
		}
		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=child_added'));
		exit;
	}

	/**
	 * Recursively add child to parent
	 */
	private function add_child_to_parent(&$menu_structure, $parent_id, $child)
	{
		foreach ($menu_structure as &$item) {
			if ($item['id'] === $parent_id) {
				if (!isset($item['children'])) {
					$item['children'] = array();
				}
				$item['children'][] = $child;
				return true;
			}
			if (isset($item['children'])) {
				if ($this->add_child_to_parent($item['children'], $parent_id, $child)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Delete menu item
	 */
	private function delete_menu_item()
	{
		$item_id = sanitize_text_field($_POST['item_id']);
		
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		$this->delete_item_recursive($menu_structure, $item_id);
		
		update_option('at_dynamic_menu_structure', $menu_structure);

		// AJAX response
		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_send_json_success(array('message' => 'Elemento eliminato con successo.', 'redirect' => false));
		}
		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=item_deleted'));
		exit;
	}

	/**
	 * Update menu item page_id
	 */
	private function update_menu_item()
	{
		$item_id = sanitize_text_field($_POST['item_id']);
		$page_id = intval($_POST['page_id']);
		
		if (empty($item_id) || empty($page_id)) {
			wp_die('Item ID e Page ID sono obbligatori.');
		}
		
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		$updated = $this->update_item_recursive($menu_structure, $item_id, $page_id);
		
		if ($updated) {
			update_option('at_dynamic_menu_structure', $menu_structure);
			// AJAX response
			if (defined('DOING_AJAX') && DOING_AJAX) {
				wp_send_json_success(array('message' => 'Pagina aggiornata con successo.', 'redirect' => false));
			}
			wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=item_updated'));
		} else {
			// AJAX response
			if (defined('DOING_AJAX') && DOING_AJAX) {
				wp_send_json_error(array('message' => 'Elemento non trovato.'));
			}
			wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=item_not_found'));
		}
		exit;
	}

	/**
	 * Recursively update item page_id
	 */
	private function update_item_recursive(&$menu_structure, $item_id, $page_id)
	{
		foreach ($menu_structure as &$item) {
			if ($item['id'] === $item_id) {
				$item['page_id'] = $page_id;
				return true;
			}
			if (isset($item['children'])) {
				if ($this->update_item_recursive($item['children'], $item_id, $page_id)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Clean up menu labels by removing leading quotes
	 */
	private function clean_menu_labels() {
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		
		if (empty($menu_structure)) {
			wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=no_menu_to_clean'));
			exit;
		}
		
		// Recursively clean labels
		$this->clean_labels_recursive($menu_structure);
		
		update_option('at_dynamic_menu_structure', $menu_structure);
		
		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=labels_cleaned'));
		exit;
	}
	
	/**
	 * Recursively clean labels in menu structure
	 */
	private function clean_labels_recursive(&$items) {
		foreach ($items as &$item) {
			if (isset($item['label'])) {
				$item['label'] = trim($item['label']);
				$item['label'] = ltrim($item['label'], '"');
				$item['label'] = ltrim($item['label'], '&quot;');
				$item['label'] = trim($item['label']);
			}
			
			if (!empty($item['children'])) {
				$this->clean_labels_recursive($item['children']);
			}
		}
	}
	
	/**
	 * Fix menu page IDs by trying to match labels to actual pages
	 */
	private function fix_menu_page_ids() {
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		
		if (empty($menu_structure)) {
			wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=no_menu_to_fix'));
			exit;
		}

		// Build deterministic lookup maps from existing pages
		$all_pages = get_pages(array(
			'number' => 0,
			'post_type' => 'page',
			'post_status' => array('publish', 'private', 'draft')
		));

		$page_lookup_by_slug = array();        // slug => page_id
		$page_lookup_by_title = array();       // lowercase title => page_id
		$page_lookup_by_uri = array();         // full uri (parent/child) => page_id

		if (is_array($all_pages)) {
			foreach ($all_pages as $page) {
				if (!is_object($page) || !isset($page->ID)) {
					continue;
				}
				$page_id = (int) $page->ID;
				$slug = isset($page->post_name) ? strtolower(trim($page->post_name)) : '';
				$title = isset($page->post_title) ? strtolower(trim($page->post_title)) : '';
				$uri = strtolower(trim(get_page_uri($page_id)));

				if ($slug !== '') {
					$page_lookup_by_slug[$slug] = $page_id;
				}
				if ($title !== '') {
					$page_lookup_by_title[$title] = $page_id;
				}
				if ($uri !== '') {
					$page_lookup_by_uri[$uri] = $page_id;
				}
			}
		}

		$stats = array(
			'total' => 0,
			'updated' => 0,
			'unchanged' => 0,
			'unresolved' => 0,
			'fixed_from_280' => 0,
		);

		// Deterministic repair: path (uri) -> slug -> title (no fuzzy guessing)
		$this->fix_page_ids_recursive_strict($menu_structure, $page_lookup_by_slug, $page_lookup_by_title, $page_lookup_by_uri, array(), $stats);
		
		update_option('at_dynamic_menu_structure', $menu_structure);
		
		$message = sprintf(
			'page_ids_fixed&updated=%d&unchanged=%d&unresolved=%d&fixed_from_280=%d&total=%d',
			(int) $stats['updated'],
			(int) $stats['unchanged'],
			(int) $stats['unresolved'],
			(int) $stats['fixed_from_280'],
			(int) $stats['total']
		);
		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=' . $message));
		exit;
	}
	
	/**
	 * Strict recursive page_id repair:
	 * - Try full hierarchical URI match (parent/child)
	 * - Fallback to slug match (sanitize_title(label))
	 * - Fallback to exact title match
	 */
	private function fix_page_ids_recursive_strict(
		&$items,
		$page_lookup_by_slug,
		$page_lookup_by_title,
		$page_lookup_by_uri,
		$ancestor_slugs,
		&$stats
	) {
		if (!is_array($items)) {
			return;
		}

		foreach ($items as &$item) {
			$stats['total']++;

			$label = isset($item['label']) ? $item['label'] : '';
			$clean_label = $this->clean_label($label);
			$slug = sanitize_title($clean_label);

			// Build hierarchical URI from menu structure (parent/child)
			$path_slugs = $ancestor_slugs;
			if ($slug !== '') {
				$path_slugs[] = $slug;
			}
			$full_uri = strtolower(implode('/', array_filter($path_slugs)));

			$candidate_page_id = null;

			if ($full_uri !== '' && isset($page_lookup_by_uri[$full_uri])) {
				$candidate_page_id = (int) $page_lookup_by_uri[$full_uri];
			} elseif ($slug !== '' && isset($page_lookup_by_slug[$slug])) {
				$candidate_page_id = (int) $page_lookup_by_slug[$slug];
			} else {
				$title_key = strtolower(trim($clean_label));
				if ($title_key !== '' && isset($page_lookup_by_title[$title_key])) {
					$candidate_page_id = (int) $page_lookup_by_title[$title_key];
				}
			}

			$current_page_id = isset($item['page_id']) ? (int) $item['page_id'] : 0;

			if ($candidate_page_id && $candidate_page_id > 0) {
				if ($current_page_id !== $candidate_page_id) {
					if ($current_page_id === 280 && $candidate_page_id !== 280) {
						$stats['fixed_from_280']++;
					}
					$item['page_id'] = $candidate_page_id;
					$stats['updated']++;
				} else {
					$stats['unchanged']++;
				}
			} else {
				$stats['unresolved']++;
			}

			if (!empty($item['children'])) {
				$this->fix_page_ids_recursive_strict(
					$item['children'],
					$page_lookup_by_slug,
					$page_lookup_by_title,
					$page_lookup_by_uri,
					$path_slugs,
					$stats
				);
			}
		}
	}
	
	/**
	 * Export menu structure and pages for AI matching
	 */
	private function export_menu_for_matching() {
		// Verify nonce
		if (!isset($_POST['at_dynamic_menu_nonce']) || !wp_verify_nonce($_POST['at_dynamic_menu_nonce'], 'at_dynamic_menu_action')) {
			wp_die('Security check failed.');
		}
		
		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die('You do not have permission to perform this action.');
		}
		
		try {
			$menu_structure = get_option('at_dynamic_menu_structure', array());
			$all_pages = get_pages(array('number' => 0));
			
			// Extract menu items with their labels and current page IDs
			$menu_items = array();
			if (!empty($menu_structure) && is_array($menu_structure)) {
				$this->extract_menu_items($menu_structure, $menu_items);
			}
			
			// Extract all pages
			$pages = array();
			if (is_array($all_pages)) {
				foreach ($all_pages as $page) {
					if (is_object($page) && isset($page->ID)) {
						$pages[] = array(
							'id' => (int) $page->ID,
							'title' => isset($page->post_title) ? $page->post_title : '',
							'slug' => isset($page->post_name) ? $page->post_name : '',
							'url' => get_permalink($page->ID)
						);
					}
				}
			}
			
			// Create export data
			$export = array(
				'menu_items' => $menu_items,
				'pages' => $pages,
				'export_date' => date('Y-m-d H:i:s')
			);
			
			// Clear any previous output
			while (ob_get_level()) {
				ob_end_clean();
			}
			
			// Output as JSON
			header('Content-Type: application/json; charset=utf-8');
			header('Content-Disposition: attachment; filename="menu-mapping-export.json"');
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			
			$json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			
			if ($json === false) {
				wp_die('Error encoding JSON: ' . json_last_error_msg());
			}
			
			echo $json;
			exit;
		} catch (Exception $e) {
			wp_die('Error during export: ' . $e->getMessage());
		}
	}
	
	/**
	 * Extract menu items recursively
	 */
	private function extract_menu_items($items, &$result, $path = '') {
		if (!is_array($items)) {
			return;
		}
		
		foreach ($items as $item) {
			if (!is_array($item) || !isset($item['id']) || !isset($item['label'])) {
				continue;
			}
			
			$label = is_string($item['label']) ? $item['label'] : '';
			$current_path = $path ? $path . ' > ' . $label : $label;
			
			$result[] = array(
				'id' => $item['id'],
				'label' => $label,
				'clean_label' => $this->clean_label($label),
				'current_page_id' => isset($item['page_id']) ? (int) $item['page_id'] : null,
				'path' => $current_path
			);
			
			if (!empty($item['children']) && is_array($item['children'])) {
				$this->extract_menu_items($item['children'], $result, $current_path);
			}
		}
	}
	
	/**
	 * Import menu mapping from JSON
	 */
	private function import_menu_mapping() {
		// Verify nonce
		if (!isset($_POST['at_dynamic_menu_nonce']) || !wp_verify_nonce($_POST['at_dynamic_menu_nonce'], 'at_dynamic_menu_action')) {
			wp_die('Security check failed.');
		}
		
		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die('You do not have permission to perform this action.');
		}
		
		if (!isset($_POST['mapping_json']) || empty($_POST['mapping_json'])) {
			wp_die('No mapping data provided.');
		}
		
		$mapping = json_decode(stripslashes($_POST['mapping_json']), true);
		
		if (!$mapping || !is_array($mapping)) {
			wp_die('Invalid mapping data format. Expected JSON array.');
		}
		
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		
		// Create lookup map: menu_item_id => page_id
		$mapping_lookup = array();
		foreach ($mapping as $map) {
			if (isset($map['menu_item_id']) && isset($map['page_id'])) {
				$mapping_lookup[$map['menu_item_id']] = intval($map['page_id']);
			}
		}
		
		$updated = 0;
		$this->apply_menu_mapping($menu_structure, $mapping_lookup, $updated);
		
		update_option('at_dynamic_menu_structure', $menu_structure);
		
		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=mapping_imported&updated=' . $updated));
		exit;
	}
	
	/**
	 * Apply mapping to menu structure recursively
	 */
	private function apply_menu_mapping(&$items, $mapping_lookup, &$updated) {
		foreach ($items as &$item) {
			if (isset($item['id']) && isset($mapping_lookup[$item['id']])) {
				$item['page_id'] = $mapping_lookup[$item['id']];
				$updated++;
			}
			
			if (!empty($item['children'])) {
				$this->apply_menu_mapping($item['children'], $mapping_lookup, $updated);
			}
		}
	}
	
	/**
	 * Recursively fix page IDs in menu structure
	 */
	private function fix_page_ids_recursive(&$items, $page_lookup_by_title, $page_lookup_by_slug, $page_lookup_by_title_normalized, $all_pages, &$stats) {
		foreach ($items as &$item) {
			if (isset($item['label']) && isset($item['page_id'])) {
				$matched_page_id = $this->find_matching_page(
					$item['label'], 
					$page_lookup_by_title, 
					$page_lookup_by_slug, 
					$page_lookup_by_title_normalized, 
					$all_pages
				);
				
				if ($matched_page_id && $matched_page_id != $item['page_id']) {
					$item['page_id'] = $matched_page_id;
					$stats['matched']++;
				} else {
					$stats['not_matched']++;
				}
			}
			
			if (!empty($item['children'])) {
				$this->fix_page_ids_recursive($item['children'], $page_lookup_by_title, $page_lookup_by_slug, $page_lookup_by_title_normalized, $all_pages, $stats);
			}
		}
	}

	/**
	 * Recursively delete item
	 */
	private function delete_item_recursive(&$menu_structure, $item_id)
	{
		foreach ($menu_structure as $key => &$item) {
			if ($item['id'] === $item_id) {
				unset($menu_structure[$key]);
				return true;
			}
			if (isset($item['children'])) {
				if ($this->delete_item_recursive($item['children'], $item_id)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Create default menu structure
	 */
	private function create_default_menu()
	{
		// Get or create the main pages - Complete list of document folders
		$pages = array(
			'disposizioni-generali' => 'Disposizioni generali',
			'organizzazione' => 'Organizzazione',
			'consulenti-e-collaboratori' => 'Consulenti e collaboratori',
			'personale' => 'Personale',
			'bandi-di-concorso' => 'Bandi di concorso',
			'performance' => 'Performance',
			'enti-controllati' => 'Enti controllati',
			'attivita-e-procedimenti' => 'Attività e procedimenti',
			'provvedimenti' => 'Provvedimenti',
			'controlli-sulle-imprese' => 'Controlli sulle imprese',
			'bandi-di-gara-e-contratti' => 'Bandi di gara e contratti',
			'sovvenzioni-contributi-sussidi-vantaggi-economici' => 'Sovvenzioni, contributi, sussidi, vantaggi economici',
			'bilanci' => 'Bilanci',
			'beni-immobili-e-gestione-patrimonio' => 'Beni immobili e gestione patrimonio',
			'canoni-di-locazione-o-affitto' => 'Canoni di locazione o affitto',
			'controlli-e-rilievi-sull-amministrazione' => 'Controlli e rilievi sull\'amministrazione',
			'servizi-erogati' => 'Servizi erogati',
			'pagamenti-dell-amministrazione' => 'Pagamenti dell\'amministrazione',
			'opere-pubbliche' => 'Opere pubbliche',
			'pianificazione-e-governo-del-territorio' => 'Pianificazione e governo del territorio',
			'informazioni-ambientali' => 'Informazioni ambientali',
			'strutture-sanitarie-private-accreditate' => 'Strutture sanitarie private accreditate',
			'interventi-straordinari-e-di-emergenza' => 'Interventi straordinari e di emergenza',
			'altri-contenuti' => 'Altri contenuti'
		);

		$menu_structure = array();
		$counter = 1;

		foreach ($pages as $slug => $label) {
			// Get or create the page
			$page = get_page_by_path($slug);
			if (!$page) {
				$page_data = array(
					'post_title' => $label,
					'post_name' => $slug,
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => "<!-- {$label} -->\n\nContenuto della sezione {$label}.",
				);
				$page_id = wp_insert_post($page_data);
			} else {
				$page_id = $page->ID;
			}

			$menu_item = array(
				'id' => 'root_' . $counter,
				'type' => 'root',
				'label' => $label,
				'page_id' => $page_id,
				'children' => array()
			);

			// Add children based on the structure you provided
			$children = $this->get_default_children($slug);
			foreach ($children as $child_label => $child_slug) {
				// Get or create the child page
				$child_page = get_page_by_path($child_slug);
				if (!$child_page) {
					// Try to find by slug with parent path
					$full_slug = $slug . '/' . $child_slug;
					$child_page = get_page_by_path($full_slug);
					
					if (!$child_page) {
						// Create the child page
						$child_page_data = array(
							'post_title' => $child_label,
							'post_name' => $child_slug,
							'post_status' => 'publish',
							'post_type' => 'page',
							'post_parent' => $page_id, // Set parent page
							'post_content' => "<!-- {$child_label} -->\n\nContenuto della sezione {$child_label}.",
						);
						$child_page_id = wp_insert_post($child_page_data);
						$child_page = get_post($child_page_id);
					}
				}
				
				if ($child_page) {
					$menu_item['children'][] = array(
						'id' => 'child_' . $counter . '_' . time() . rand(1000, 9999),
						'type' => 'page',
						'label' => $child_label,
						'page_id' => $child_page->ID, // Use the child page's ID, not the parent's
						'anchor' => ''
					);
				}
			}

			$menu_structure[] = $menu_item;
			$counter++;
		}

		update_option('at_dynamic_menu_structure', $menu_structure);
		wp_redirect(admin_url('tools.php?page=at-dynamic-menu&message=default_created'));
		exit;
	}

	/**
	 * Get default children for each page
	 */
	private function get_default_children($slug)
	{
		$children = array();

		switch ($slug) {
			case 'disposizioni-generali':
				$children = array(
					'Piano triennale per la prevenzione della corruzione e della trasparenza' => 'piano-triennale-prevenzione-corruzione-trasparenza',
					'Atti generali' => 'atti-generali'
				);
				break;

			case 'organizzazione':
				$children = array(
					'Titolari di incarichi politici, di amministrazione, di direzione o di governo' => 'titolari-incarichi-politici-amministrazione-direzione-governo',
					'Articolazione degli uffici' => 'articolazione-uffici',
					'Telefono e posta elettronica' => 'telefono-posta-elettronica'
				);
				break;

			case 'consulenti-e-collaboratori':
				$children = array(
					'Titolari di incarichi di collaborazione o consulenza' => 'titolari-incarichi-collaborazione-consulenza'
				);
				break;

			case 'personale':
				$children = array(
					'Titolari di incarichi dirigenziali' => 'titolari-incarichi-dirigenziali',
					'Dotazione organica' => 'dotazione-organica',
					'Personale non a tempo indeterminato' => 'personale-non-tempo-indeterminato',
					'Incarichi conferiti e autorizzati ai dipendenti (dirigenti e non dirigenti)' => 'incarichi-conferiti-autorizzati-dipendenti',
					'Tassi di assenza' => 'tassi-assenza',
					'Contrattazione collettiva' => 'contrattazione-collettiva'
				);
				break;

			case 'bandi-di-concorso':
				$children = array(
					'Bandi di concorso pubblici' => 'bandi-concorso-pubblici'
				);
				break;

			case 'performance':
				$children = array(
					'Ammontare complessivo dei premi' => 'ammontare-complessivo-premi',
					'Piano delle Performance' => 'piano-performance'
				);
				break;

			case 'enti-controllati':
				$children = array(
					'Società partecipate' => 'societa-partecipate',
					'Rappresentazione grafica' => 'rappresentazione-grafica'
				);
				break;

			case 'attivita-e-procedimenti':
				$children = array(
					'Attività e procedimenti amministrativi' => 'attivita-procedimenti-amministrativi'
				);
				break;

			case 'provvedimenti':
				$children = array(
					'Provvedimenti organi indirizzo politico' => 'provvedimenti-organi-indirizzo-politico',
					'Provvedimenti dirigenti amministrativi' => 'provvedimenti-dirigenti-amministrativi'
				);
				break;

			case 'controlli-sulle-imprese':
				$children = array(
					'Controlli e verifiche sulle imprese' => 'controlli-verifiche-imprese'
				);
				break;

			case 'bandi-di-gara-e-contratti':
				$children = array(
					'Adempimenti di cui all\'art. 1 comma 32 della legge n. 190/2012' => 'adempimenti-art-1-comma-32-legge-190-2012',
					'BDNCP - Banca Dati Nazionale dei Contratti Pubblici' => 'bdncp-banca-dati-nazionale-contratti-pubblici'
				);
				break;

			case 'sovvenzioni-contributi-sussidi-vantaggi-economici':
				$children = array(
					'Sovvenzioni, contributi, sussidi e vantaggi economici' => 'sovvenzioni-contributi-sussidi-vantaggi-economici'
				);
				break;

			case 'bilanci':
				$children = array(
					'Bilancio preventivo e consuntivo' => 'bilancio-preventivo-consuntivo'
				);
				break;

			case 'beni-immobili-e-gestione-patrimonio':
				$children = array(
					'Patrimonio immobiliare' => 'patrimonio-immobiliare',
					'Gestione del patrimonio' => 'gestione-patrimonio'
				);
				break;

			case 'canoni-di-locazione-o-affitto':
				$children = array(
					'Canoni di locazione e affitto' => 'canoni-locazione-affitto'
				);
				break;

			case 'controlli-e-rilievi-sull-amministrazione':
				$children = array(
					'Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe' => 'organismi-indipendenti-valutazione-nuclei-valutazione',
					'Organi di revisione amministrativa e contabile' => 'organi-revisione-amministrativa-contabile'
				);
				break;

			case 'servizi-erogati':
				$children = array(
					'Carta dei servizi e standard di qualità' => 'carta-servizi-standard-qualita'
				);
				break;

			case 'pagamenti-dell-amministrazione':
				$children = array(
					'Dati sui pagamenti' => 'dati-pagamenti',
					'Indicatore di tempestività dei pagamenti' => 'indicatore-tempestivita-pagamenti',
					'Ammontare complessivo dei debiti' => 'ammontare-complessivo-debiti',
					'IBAN e pagamenti informatici' => 'iban-pagamenti-informatici'
				);
				break;

			case 'opere-pubbliche':
				$children = array(
					'Opere pubbliche e interventi' => 'opere-pubbliche-interventi'
				);
				break;

			case 'pianificazione-e-governo-del-territorio':
				$children = array(
					'Pianificazione territoriale' => 'pianificazione-territoriale',
					'Governo del territorio' => 'governo-territorio'
				);
				break;

			case 'informazioni-ambientali':
				$children = array(
					'Informazioni ambientali e sostenibilità' => 'informazioni-ambientali-sostenibilita'
				);
				break;

			case 'strutture-sanitarie-private-accreditate':
				$children = array(
					'Strutture sanitarie private accreditate' => 'strutture-sanitarie-private-accreditate'
				);
				break;

			case 'interventi-straordinari-e-di-emergenza':
				$children = array(
					'Interventi straordinari' => 'interventi-straordinari',
					'Interventi di emergenza' => 'interventi-emergenza'
				);
				break;

			case 'altri-contenuti':
				// Merged "Altri contenuti" sections
				$children = array(
					'Prevenzione della corruzione' => 'prevenzione-corruzione',
					'Accesso civico' => 'accesso-civico',
					'Accessibilità e catalogo di dati, metadati e banche dati' => 'accessibilita-catalogo-dati-metadati-banche-dati',
					'Dati ulteriori' => 'dati-ulteriori',
					'Whistleblower' => 'whistleblower'
				);
				break;
		}

		return $children;
	}

	/**
	 * Get all pages for dropdown
	 */
	private function get_pages_dropdown()
	{
		$pages = get_pages(array(
			'sort_column' => 'post_title',
			'sort_order' => 'ASC',
			'number' => 0 // Get all pages
		));

		// Detect duplicates by title
		$title_counts = array();
		foreach ($pages as $page) {
			$normalized_title = strtolower(remove_accents($page->post_title));
			if (!isset($title_counts[$normalized_title])) {
				$title_counts[$normalized_title] = 0;
			}
			$title_counts[$normalized_title]++;
		}

		// Sort alphabetically by title (case-insensitive, handling special characters)
		usort($pages, function($a, $b) {
			// Remove accents and convert to lowercase for comparison
			$title_a = strtolower(remove_accents($a->post_title));
			$title_b = strtolower(remove_accents($b->post_title));
			return strcmp($title_a, $title_b);
		});

		$options = '<option value="">Seleziona una pagina...</option>';
		foreach ($pages as $page) {
			$normalized_title = strtolower(remove_accents($page->post_title));
			$is_duplicate = $title_counts[$normalized_title] > 1;
			
			// Get parent info
			$parent_info = '';
			if ($page->post_parent > 0) {
				$parent = get_post($page->post_parent);
				if ($parent) {
					$parent_info = ' (Parent: ' . esc_html($parent->post_title) . ')';
				}
			}
			
			// Get slug
			$slug = $page->post_name;
			
			// Build display text
			$display_text = esc_html($page->post_title);
			if ($is_duplicate) {
				$display_text .= ' ⚠️ [ID:' . $page->ID . ', Slug:' . $slug . ']' . $parent_info;
			} else {
				$display_text .= ' [ID:' . $page->ID . ']';
			}
			
			$options .= sprintf(
				'<option value="%d" data-slug="%s" data-parent="%d" data-is-duplicate="%s">%s</option>',
				$page->ID,
				esc_attr($slug),
				$page->post_parent,
				$is_duplicate ? '1' : '0',
				$display_text
			);
		}
		return $options;
	}

	/**
	 * AJAX: Save menu structure
	 */
	public function ajax_save_menu()
	{
		check_ajax_referer('at_dynamic_menu_nonce', 'nonce');
		
		$menu_structure = json_decode(stripslashes($_POST['menu_structure']), true);
		
		if (is_array($menu_structure)) {
			update_option('at_dynamic_menu_structure', $menu_structure);
			wp_send_json_success(array('message' => 'Menu salvato con successo.'));
		} else {
			wp_send_json_error(array('message' => 'Struttura menu non valida.'));
		}
	}

	/**
	 * AJAX: Add root item
	 */
	public function ajax_add_root()
	{
		check_ajax_referer('at_dynamic_menu_nonce', 'nonce');
		
		$label = sanitize_text_field($_POST['root_label']);
		$page_id = intval($_POST['root_page_id']);

		if (empty($label) || empty($page_id)) {
			wp_send_json_error(array('message' => 'Label e pagina sono obbligatori.'));
		}

		$menu_structure = get_option('at_dynamic_menu_structure', array());
		
		$new_item = array(
			'id' => 'root_' . time(),
			'type' => 'root',
			'label' => $label,
			'page_id' => $page_id,
			'children' => array()
		);

		$menu_structure[] = $new_item;
		update_option('at_dynamic_menu_structure', $menu_structure);
		
		wp_send_json_success(array('message' => 'Elemento radice aggiunto con successo.'));
	}

	/**
	 * AJAX: Add child item
	 */
	public function ajax_add_child()
	{
		check_ajax_referer('at_dynamic_menu_nonce', 'nonce');
		
		$parent_id = sanitize_text_field($_POST['parent_id']);
		$label = sanitize_text_field($_POST['child_label']);
		$page_id = intval($_POST['child_page_id']);

		if (empty($label) || empty($parent_id) || empty($page_id)) {
			wp_send_json_error(array('message' => 'Label, parent e pagina sono obbligatori.'));
		}

		$menu_structure = get_option('at_dynamic_menu_structure', array());
		
		$new_child = array(
			'id' => 'child_' . time(),
			'type' => 'page',
			'label' => $label,
			'page_id' => $page_id,
			'anchor' => ''
		);

		// Find parent and add child
		$added = $this->add_child_to_parent($menu_structure, $parent_id, $new_child);
		
		if ($added) {
			update_option('at_dynamic_menu_structure', $menu_structure);
			wp_send_json_success(array('message' => 'Sub-pagina aggiunta con successo.'));
		} else {
			wp_send_json_error(array('message' => 'Parent non trovato.'));
		}
	}

	/**
	 * AJAX: Update menu item
	 */
	public function ajax_update_item()
	{
		check_ajax_referer('at_dynamic_menu_nonce', 'nonce');
		
		$item_id = sanitize_text_field($_POST['item_id']);
		$page_id = intval($_POST['page_id']);
		
		if (empty($item_id) || empty($page_id)) {
			wp_send_json_error(array('message' => 'Item ID e Page ID sono obbligatori.'));
		}
		
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		$updated = $this->update_item_recursive($menu_structure, $item_id, $page_id);
		
		if ($updated) {
			update_option('at_dynamic_menu_structure', $menu_structure);
			wp_send_json_success(array('message' => 'Pagina aggiornata con successo.'));
		} else {
			wp_send_json_error(array('message' => 'Elemento non trovato.'));
		}
	}

	/**
	 * AJAX: Delete menu item
	 */
	public function ajax_delete_item()
	{
		check_ajax_referer('at_dynamic_menu_nonce', 'nonce');
		
		$item_id = sanitize_text_field($_POST['item_id']);
		
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		$this->delete_item_recursive($menu_structure, $item_id);
		
		update_option('at_dynamic_menu_structure', $menu_structure);
		
		wp_send_json_success(array('message' => 'Elemento eliminato con successo.'));
	}
	
	/**
	 * AJAX: Toggle content verified status
	 */
	public function ajax_toggle_verified()
	{
		check_ajax_referer('at_dynamic_menu_nonce', 'nonce');
		
		$item_id = sanitize_text_field($_POST['item_id']);
		$verified = isset($_POST['verified']) ? (bool)$_POST['verified'] : false;
		
		if (empty($item_id)) {
			wp_send_json_error(array('message' => 'Item ID è obbligatorio.'));
		}
		
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		$updated = $this->toggle_verified_recursive($menu_structure, $item_id, $verified);
		
		if ($updated) {
			update_option('at_dynamic_menu_structure', $menu_structure);
			wp_send_json_success(array('message' => 'Stato verifica aggiornato.'));
		} else {
			wp_send_json_error(array('message' => 'Elemento non trovato.'));
		}
	}
	
	/**
	 * Recursively toggle verified status
	 */
		private function toggle_verified_recursive(&$items, $item_id, $verified)
	{
		foreach ($items as &$item) {
			if (isset($item['id']) && $item['id'] === $item_id) {
				$item['content_verified'] = $verified;
				// Keep content_synced for backward compatibility, but content_verified is the source of truth
				if ($verified && !isset($item['content_synced'])) {
					$item['content_synced'] = true;
				}
				return true;
			}
			
			if (!empty($item['children'])) {
				if ($this->toggle_verified_recursive($item['children'], $item_id, $verified)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Render menu item HTML
	 */
	private function render_menu_item($item, $depth = 0)
	{
		$page_id = isset($item['page_id']) ? intval($item['page_id']) : 0;
		$page_title = $page_id > 0 ? get_the_title($page_id) : '';
		$page_exists = $page_id > 0 && get_post($page_id) !== null;
		$is_missing = !$page_exists || empty($page_title);
		
		// Get old URL if available
		$old_url = isset($item['old_url']) ? $item['old_url'] : '';
		$old_path = isset($item['old_path']) ? $item['old_path'] : '';
		
		// Get new URL
		$new_url = '';
		if ($page_id > 0 && $page_exists) {
			$new_url = get_permalink($page_id);
		}
		
		// Check if content has been verified/synced
		// content_verified is the PRIMARY flag - if it's true, item is verified
		// content_synced is kept for backward compatibility but we prioritize content_verified
		$content_verified = isset($item['content_verified']) ? (bool)$item['content_verified'] : false;
		$content_synced = isset($item['content_synced']) ? (bool)$item['content_synced'] : false;
		
		// If content_synced is true but content_verified is not, migrate it (one-time fix)
		// This ensures backward compatibility with items marked by old sync scripts
		if ($content_synced && !$content_verified) {
			$content_verified = true;
			$item['content_verified'] = true; // Will be saved on next menu save
		}
		
		// The checkbox and green checkmark BOTH use this single flag
		$is_verified = $content_verified;
		
		// Add depth class for styling
		$depth_class = 'depth-' . min($depth, 5);
		$missing_class = $is_missing ? 'page-missing' : '';
		$verified_class = $is_verified ? 'content-verified' : '';
		
		echo '<div class="menu-item ' . esc_attr($depth_class) . ' ' . esc_attr($missing_class) . ' ' . esc_attr($verified_class) . '" data-id="' . esc_attr($item['id']) . '" data-depth="' . $depth . '">';
		echo '<div class="menu-item-header">';
		echo '<span class="dashicons dashicons-menu drag-handle"></span>';
		
		// Verification checkbox/toggle
		// The checkbox state should match the green checkmark - they both use $is_verified
		echo '<input type="checkbox" class="content-verified-checkbox" data-item-id="' . esc_attr($item['id']) . '" ' . ($is_verified ? 'checked' : '') . ' title="Contenuto verificato/sincronizzato">';
		
		echo '<span class="item-label">' . esc_html($item['label']) . '</span>';
		
		// Links section - compact with full URLs in tooltip
		echo '<span class="item-links">';
		if ($is_missing) {
			echo '<span class="item-link missing">⚠️ Missing (ID: ' . esc_html($page_id) . ')</span>';
		} else {
			if ($new_url) {
				echo '<a href="' . esc_url($new_url) . '" target="_blank" class="item-link-new" title="Nuovo: ' . esc_attr($new_url) . '">🔗 Nuovo</a>';
			}
		}
		if ($old_url) {
			echo '<a href="' . esc_url($old_url) . '" target="_blank" class="item-link-old" title="Vecchio: ' . esc_attr($old_url) . '">🔗 Vecchio</a>';
		} else {
			echo '<span class="item-link-old-missing" title="URL vecchio non disponibile">—</span>';
		}
		echo '</span>';
		
		// Actions - compact buttons
		echo '<div class="item-actions">';
		echo '<button type="button" class="button button-small edit-item" data-item-id="' . esc_attr($item['id']) . '" data-item-label="' . esc_attr($item['label']) . '" data-item-page-id="' . esc_attr($item['page_id']) . '" title="Modifica">✏️</button>';
		echo '<button type="button" class="button button-small add-child" data-parent-id="' . esc_attr($item['id']) . '" title="Aggiungi sub-pagina">➕</button>';
		echo '<button type="button" class="button button-small button-link-delete delete-item" data-item-id="' . esc_attr($item['id']) . '" title="Elimina">🗑️</button>';
		echo '</div>';
		echo '</div>';
		
		if (!empty($item['children'])) {
			echo '<div class="menu-children depth-' . min($depth + 1, 5) . '">';
			foreach ($item['children'] as $child) {
				$this->render_menu_item($child, $depth + 1);
			}
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Admin page content
	 */
	public function admin_page()
	{
		$menu_structure = get_option('at_dynamic_menu_structure', array());
		$pages_dropdown = $this->get_pages_dropdown();
		$message = isset($_GET['message']) ? $_GET['message'] : '';
		?>
		<div class="wrap">
			<h1>Menu Builder</h1>

			<?php if ($message): ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php
						switch ($message) {
							case 'saved':
								echo 'Menu salvato con successo.';
								break;
							case 'root_added':
								echo 'Elemento radice aggiunto con successo.';
								break;
							case 'child_added':
								echo 'Elemento figlio aggiunto con successo.';
								break;
							case 'item_deleted':
								echo 'Elemento eliminato con successo.';
								break;
							case 'item_updated':
								echo 'Elemento aggiornato con successo.';
								break;
							case 'item_not_found':
								echo 'Errore: elemento non trovato.';
								break;
							case 'default_created':
								echo 'Menu predefinito creato con successo! Tutte le pagine e sub-pagine sono state configurate automaticamente.';
								break;
							case 'labels_cleaned':
								echo 'Etichette del menu pulite con successo! Le virgolette doppie all\'inizio sono state rimosse.';
								break;
							case 'no_menu_to_clean':
								echo 'Nessun menu da pulire. Crea prima un menu.';
								break;
							case 'page_ids_fixed':
								$updated = isset($_GET['updated']) ? intval($_GET['updated']) : 0;
								$unchanged = isset($_GET['unchanged']) ? intval($_GET['unchanged']) : 0;
								$unresolved = isset($_GET['unresolved']) ? intval($_GET['unresolved']) : 0;
								$fixed_from_280 = isset($_GET['fixed_from_280']) ? intval($_GET['fixed_from_280']) : 0;
								$total = isset($_GET['total']) ? intval($_GET['total']) : 0;

								if ($total > 0) {
									echo sprintf(
										'Link del menu corretti! %d aggiornati, %d già corretti, %d non risolti (su %d). (Riparati da pagina 280: %d)',
										$updated,
										$unchanged,
										$unresolved,
										$total,
										$fixed_from_280
									);
								} else {
									// Backward compatibility with old params
									$matched = isset($_GET['matched']) ? intval($_GET['matched']) : 0;
									$not_matched = isset($_GET['not_matched']) ? intval($_GET['not_matched']) : 0;
									echo sprintf('Page ID del menu corretti! %d elementi abbinati, %d non abbinati. Verifica i risultati.', $matched, $not_matched);
									if ($not_matched > 0) {
										echo ' <strong>Nota:</strong> Alcuni elementi potrebbero richiedere correzione manuale.';
									}
								}
								break;
							case 'no_menu_to_fix':
								echo 'Nessun menu da correggere. Crea prima un menu.';
								break;
							case 'mapping_imported':
								$updated = isset($_GET['updated']) ? intval($_GET['updated']) : 0;
								echo sprintf('✅ Mapping importato con successo! %d elementi aggiornati.', $updated);
								break;
							case 'menu_ids_regenerated':
								$changed = isset($_GET['changed']) ? intval($_GET['changed']) : 0;
								$total = isset($_GET['total']) ? intval($_GET['total']) : 0;
								echo sprintf('🆔 ID menu rigenerati con successo! %d/%d elementi aggiornati.', $changed, $total);
								break;
							case 'no_menu_to_regen_ids':
								echo 'Nessun menu su cui rigenerare gli ID. Crea prima un menu.';
								break;
						}
						?>
					</p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width:100%">
				<h2>➕ Aggiungi Elemento Radice</h2>
				<form method="post" action="">
					<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
					<input type="hidden" name="action" value="add_root">
					
					<table class="form-table">
						<tr>
							<th scope="row"><label for="root_label">📝 Label</label></th>
							<td>
								<input type="text" id="root_label" name="root_label" class="regular-text" placeholder="es. Disposizioni generali" required>
								<p class="description">Il testo che appare nel menu</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="root_page_id">🔗 Link</label></th>
							<td>
								<input type="text" id="root_page_search" class="regular-text" placeholder="Cerca pagina..." autocomplete="off" style="margin-bottom: 5px;">
								<select id="root_page_id" name="root_page_id" required size="10" style="width: 100%; height: 200px;">
									<?php echo $pages_dropdown; ?>
								</select>
								<p class="description">Cerca e seleziona la pagina WordPress a cui punta il link</p>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<input type="submit" name="submit" class="button button-primary" value="➕ Aggiungi Elemento Radice">
					</p>
				</form>
			</div>

			<div class="card" style="max-width:100%">
				<h2>📋 Struttura Menu</h2>
				<div id="menu-structure">
					<?php if (empty($menu_structure)): ?>
						<div class="empty-menu">
							<p>🎯 <strong>Nessun elemento nel menu.</strong></p>
							<p>Aggiungi un elemento radice per iniziare a costruire il tuo menu.</p>
								</div>
					<?php else: ?>
						<?php foreach ($menu_structure as $item): ?>
							<?php $this->render_menu_item($item); ?>
						<?php endforeach; ?>
					<?php endif; ?>
					</div>
					
				<form method="post" action="" id="save-menu-form">
					<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
					<input type="hidden" name="action" value="save_menu">
					<input type="hidden" name="menu_structure" id="menu_structure_input">
					<p class="submit">
						<input type="submit" name="submit" class="button button-primary" value="Salva Menu">
					</p>
				</form>

				<div class="card" style="max-width:100%; margin-top: 20px; background: #f0f8ff; border-left: 4px solid #0073aa;">
					<h3>🚀 Creazione Automatica</h3>
					<p><strong>Vuoi creare automaticamente tutto il menu dell'Amministrazione Trasparente?</strong></p>
					<p>Questo creerà automaticamente:</p>
					<ul style="margin-left: 20px;">
						<li>✅ Tutte le pagine principali</li>
						<li>✅ Tutte le sub-pagine come link interni</li>
						<li>✅ La struttura completa del menu</li>
					</ul>
				<form method="post" action="">
						<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
						<input type="hidden" name="action" value="create_default_menu">
					<p class="submit">
							<input type="submit" name="submit" class="button button-secondary" style="background: #0073aa; color: white; border-color: #0073aa;" value="🚀 Crea Menu Predefinito">
						</p>
					</form>
					
					<form method="post" action="" style="margin-top: 20px;">
						<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
						<input type="hidden" name="action" value="clean_menu_labels">
						<p class="description">Rimuove le virgolette doppie all'inizio delle etichette del menu.</p>
						<p class="submit">
							<input type="submit" name="submit" class="button button-secondary" style="background: #d63638; color: white; border-color: #d63638;" value="🧹 Pulisci Etichette Menu">
						</p>
					</form>

					<form method="post" action="" style="margin-top: 12px;">
						<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
						<input type="hidden" name="action" value="fix_menu_page_ids">
						<p class="description"><strong>🪄 Auto-correggi Page ID:</strong> corregge automaticamente i link del menu abbinando <em>label → slug/titolo</em> (senza fuzzy guessing).</p>
						<p class="submit">
							<input type="submit" name="submit" class="button button-secondary" style="background: #2271b1; color: white; border-color: #2271b1;" value="🪄 Auto-correggi Link (Page ID)">
						</p>
					</form>
					
					<form method="post" action="" style="margin-top: 20px;">
						<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
						<input type="hidden" name="action" value="export_menu_for_matching">
						<p class="description"><strong>📤 Esporta per Matching AI:</strong> Scarica un file JSON con tutti gli elementi del menu e tutte le pagine. Usa questo file con ChatGPT per abbinare le etichette ai slug delle pagine.</p>
						<p class="submit">
							<input type="submit" name="submit" class="button button-secondary" style="background: #00a32a; color: white; border-color: #00a32a;" value="📤 Esporta Menu e Pagine">
						</p>
					</form>

					<form method="post" action="" style="margin-top: 12px;">
						<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
						<input type="hidden" name="action" value="regenerate_menu_item_ids">
						<p class="description"><strong>🆔 Rigenera ID univoci:</strong> necessario prima del matching/import se gli ID nel menu sono duplicati.</p>
						<p class="submit">
							<input type="submit" name="submit" class="button button-secondary" style="background: #6c2bd9; color: white; border-color: #6c2bd9;" value="🆔 Rigenera ID univoci (Menu)">
						</p>
					</form>
					
					<form method="post" action="" style="margin-top: 20px; border: 2px solid #2271b1; padding: 15px; background: #f0f6fc;">
						<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
						<input type="hidden" name="action" value="import_menu_mapping">
						<h3 style="margin-top: 0;">📥 Importa Mapping da ChatGPT</h3>
						<p class="description">Incolla qui il JSON con il mapping generato da ChatGPT. Il formato deve essere un array di oggetti: <code>[{"menu_item_id": "id1", "page_id": 123}, ...]</code></p>
						<textarea name="mapping_json" rows="10" style="width: 100%; font-family: monospace; font-size: 12px;" placeholder='[{"menu_item_id": "root_1", "page_id": 20}, {"menu_item_id": "child_1_1234", "page_id": 25}]'></textarea>
						<p class="submit">
							<input type="submit" name="submit" class="button button-primary" style="background: #2271b1; color: white; border-color: #2271b1;" value="📥 Importa Mapping">
						</p>
					</form>
				</div>
			</div>

			<!-- Add Child Dialog -->
			<div id="add-child-dialog" style="display:none;">
				<form method="post" action="" id="add-child-form">
					<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
					<input type="hidden" name="action" value="add_child">
					<input type="hidden" name="parent_id" id="parent_id">

					<table class="form-table">
						<tr>
							<th scope="row"><label for="child_label">📝 Label</label></th>
							<td>
								<input type="text" id="child_label" name="child_label" class="regular-text" placeholder="es. Piano triennale" required>
								<p class="description">Il testo che appare nel menu</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="child_type">🔗 Tipo Link</label></th>
							<td>
								<select id="child_type" name="child_type" required>
									<option value="page">📄 Pagina separata</option>
								</select>
								<p class="description">Tutti i link ora puntano a pagine separate</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="child_page_id">📄 Pagina</label></th>
							<td>
								<input type="text" id="child_page_search" class="regular-text" placeholder="Cerca pagina..." autocomplete="off" style="margin-bottom: 5px;">
								<select id="child_page_id" name="child_page_id" required size="10" style="width: 100%; height: 200px;">
									<?php echo $pages_dropdown; ?>
								</select>
								<p class="description">Cerca e seleziona la pagina WordPress a cui punta il link</p>
							</td>
						</tr>
						<!-- Anchor row removed - all items are now pages -->
					</table>
				</form>
			</div>

			<!-- Edit Item Dialog -->
			<div id="edit-item-dialog" style="display:none;">
				<form method="post" action="" id="edit-item-form">
					<?php wp_nonce_field('at_dynamic_menu_action', 'at_dynamic_menu_nonce'); ?>
					<input type="hidden" name="action" value="update_item">
					<input type="hidden" name="item_id" id="edit_item_id">

					<table class="form-table">
						<tr>
							<th scope="row"><label>📝 Label</label></th>
							<td>
								<input type="text" id="edit_item_label" class="regular-text" readonly style="background: #f0f0f0;">
								<p class="description">Il testo del menu (non modificabile)</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="edit_item_page_id">📄 Pagina</label></th>
							<td>
								<input type="text" id="edit_item_page_search" class="regular-text" placeholder="Cerca pagina..." autocomplete="off" style="margin-bottom: 5px;">
								<select id="edit_item_page_id" name="page_id" required size="10" style="width: 100%; height: 200px;">
									<?php echo $pages_dropdown; ?>
								</select>
								<p class="description">Cerca e seleziona la pagina WordPress corretta per questo elemento del menu</p>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>

		<style>
		.menu-item {
			border: 1px solid #ddd;
			margin: 3px 0;
			padding: 6px 8px;
			background: #fff;
			border-radius: 3px;
			transition: all 0.2s ease;
			font-size: 12px;
		}
		
		/* Nesting with light shades - more compact */
		.menu-item.depth-0 {
			background: #ffffff;
			border-left: 3px solid #2271b1;
		}
		.menu-item.depth-1 {
			background: #f8f9fa;
			border-left: 3px solid #72aee6;
			margin-left: 15px;
		}
		.menu-item.depth-2 {
			background: #f0f4f8;
			border-left: 3px solid #a7c5e8;
			margin-left: 30px;
		}
		.menu-item.depth-3 {
			background: #e8f0f6;
			border-left: 3px solid #c4d9ed;
			margin-left: 45px;
		}
		.menu-item.depth-4 {
			background: #e0ecf4;
			border-left: 3px solid #d6e3f0;
			margin-left: 60px;
		}
		.menu-item.depth-5 {
			background: #d8e8f2;
			border-left: 3px solid #e8f0f6;
			margin-left: 75px;
		}
		
		/* Missing page highlighting */
		.menu-item.page-missing {
			background: #fff3cd !important;
			border-left: 4px solid #ffc107 !important;
			border-color: #ffc107 !important;
		}
		.menu-item.page-missing:hover {
			background: #ffe69c !important;
			box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
		}
		.item-link.missing {
			color: #d63638 !important;
			font-weight: bold;
			background: #ffe69c;
			padding: 4px 8px;
			border-radius: 3px;
			display: inline-block;
		}
		
		.menu-item-header {
			display: flex;
			align-items: center;
			gap: 6px;
			flex-wrap: nowrap;
		}
		.drag-handle {
			cursor: move;
			color: #666;
			flex-shrink: 0;
			font-size: 14px;
			width: 16px;
		}
		.content-verified-checkbox {
			margin-right: 6px;
			cursor: pointer;
			width: 16px;
			height: 16px;
			flex-shrink: 0;
		}
		.menu-item.content-verified {
			border-left-color: #46b450 !important;
		}
		/* Green checkmark appears ONLY on the verified item itself, not on children */
		/* Use direct child selector to prevent inheritance to nested items */
		.menu-item.content-verified > .menu-item-header > .item-label::before {
			content: "✓ ";
			color: #46b450;
			font-weight: bold;
		}
		/* Ensure checkbox is visible and aligned */
		.menu-item.content-verified > .menu-item-header > .content-verified-checkbox:checked {
			accent-color: #46b450;
		}
		.item-label {
			font-weight: 600;
			min-width: 180px;
			flex: 1 1 auto;
			font-size: 12px;
			line-height: 1.4;
		}
		.item-links {
			display: flex;
			gap: 8px;
			align-items: center;
			flex: 0 0 auto;
			margin: 0 10px;
		}
		.item-link-new,
		.item-link-old {
			padding: 1px 5px;
			border-radius: 2px;
			text-decoration: none;
			font-size: 10px;
			white-space: nowrap;
			transition: all 0.2s;
			font-weight: 500;
		}
		.item-link-new {
			background: #2271b1;
			color: white;
		}
		.item-link-new:hover {
			background: #135e96;
			color: white;
			text-decoration: none;
		}
		.item-link-old {
			background: #646970;
			color: white;
		}
		.item-link-old:hover {
			background: #50575e;
			color: white;
			text-decoration: none;
		}
		.item-link-old-missing {
			color: #999;
			font-size: 11px;
			padding: 2px 6px;
		}
		.item-link.missing {
			color: #d63638 !important;
			font-weight: bold;
			font-size: 10px;
			background: #ffe69c;
			padding: 1px 5px;
			border-radius: 2px;
			display: inline-block;
		}
		.item-type {
			color: #666;
			font-size: 12px;
		}
		.item-page {
			color: #0073aa;
			font-size: 12px;
		}
		.item-anchor {
			color: #d63638;
			font-size: 12px;
		}
		.item-actions {
			margin-left: auto;
			display: flex;
			gap: 3px;
			flex-wrap: nowrap;
		}
		.item-actions .button {
			padding: 1px 5px;
			height: auto;
			line-height: 1.3;
			font-size: 10px;
			min-width: auto;
		}
		/* Compact menu structure container */
		#menu-structure {
			max-height: 70vh;
			overflow-y: auto;
			padding: 5px;
		}
		.menu-children {
			margin-top: 4px;
			padding-left: 0;
		}
		.menu-children.depth-1 {
			border-left: 2px solid #72aee6;
			padding-left: 10px;
		}
		.menu-children.depth-2 {
			border-left: 2px solid #a7c5e8;
			padding-left: 10px;
		}
		.menu-children.depth-3 {
			border-left: 2px solid #c4d9ed;
			padding-left: 10px;
		}
		.menu-children.depth-4 {
			border-left: 2px solid #d6e3f0;
			padding-left: 10px;
		}
		.menu-children.depth-5 {
			border-left: 2px solid #e8f0f6;
			padding-left: 10px;
		}
		
		/* Dialog Styling */
		#add-child-dialog {
			padding: 20px;
			border: 4px solid #0073aa;
			background: white;
			position: relative;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
		}
		
		.ui-dialog {
			border: 4px solid #0073aa !important;
			border-radius: 8px !important;
			box-shadow: 0 8px 24px rgba(0,0,0,0.2) !important;
		}
		
		.ui-dialog .ui-dialog-titlebar {
			background: #0073aa !important;
			color: white !important;
			border: none !important;
			border-radius: 4px 4px 0 0 !important;
			padding: 15px 20px !important;
		}
		
		.ui-dialog .ui-dialog-content {
			padding: 20px !important;
			background: white !important;
		}
		
		.ui-dialog .ui-dialog-buttonpane {
			background: #f9f9f9 !important;
			border-top: 1px solid #ddd !important;
			padding: 15px 20px !important;
		}
		
		.ui-dialog .ui-dialog-buttonset button {
			padding: 8px 16px !important;
			margin: 0 5px !important;
			border-radius: 4px !important;
		}
		
		/* Select2 styling */
		.select2-container {
			width: 100% !important;
		}
		.select2-container--default .select2-selection--single {
			height: 30px;
			border: 1px solid #8c8f94;
		}
		.select2-container--default .select2-selection--single .select2-selection__rendered {
			line-height: 30px;
		}
		.select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 28px;
		}
		/* Search input styling */
		#root_page_search,
		#child_page_search,
		#edit_item_page_search {
			width: 100%;
			padding: 6px;
			border: 1px solid #8c8f94;
			border-radius: 4px;
		}
		#root_page_search:focus,
		#child_page_search:focus,
		#edit_item_page_search:focus {
			border-color: #2271b1;
			box-shadow: 0 0 0 1px #2271b1;
			outline: none;
		}
		/* Select styling for large lists */
		#root_page_id,
		#child_page_id,
		#edit_item_page_id {
			border: 1px solid #8c8f94;
			border-radius: 4px;
			cursor: pointer;
			overflow-x: auto;
			overflow-y: auto;
			white-space: nowrap;
			min-width: 100%;
			width: 100%;
		}
		#root_page_id option,
		#child_page_id option,
		#edit_item_page_id option {
			padding: 4px 8px;
			cursor: pointer;
			white-space: nowrap;
			min-width: max-content;
		}
		#root_page_id option:hover:not(:disabled),
		#child_page_id option:hover:not(:disabled),
		#edit_item_page_id option:hover:not(:disabled) {
			background-color: #2271b1;
			color: white;
		}
		#root_page_id option:checked,
		#child_page_id option:checked,
		#edit_item_page_id option:checked {
			background-color: #2271b1;
			color: white;
		}
		/* Duplicate indicator styling */
		#root_page_id option[data-is-duplicate="1"],
		#child_page_id option[data-is-duplicate="1"],
		#edit_item_page_id option[data-is-duplicate="1"] {
			color: #d63638;
			font-weight: 500;
		}
		#root_page_id option[data-is-duplicate="1"]:hover,
		#child_page_id option[data-is-duplicate="1"]:hover,
		#edit_item_page_id option[data-is-duplicate="1"]:hover {
			background-color: #d63638;
			color: white;
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
			var nonce = '<?php echo wp_create_nonce('at_dynamic_menu_nonce'); ?>';
			
			// Show notification
			function showNotice(message, type) {
				type = type || 'success';
				var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
				$('.wrap h1').after(notice);
				setTimeout(function() {
					notice.fadeOut(function() { $(this).remove(); });
				}, 3000);
			}
			
			// AJAX helper
			function ajaxRequest(action, data, successCallback) {
				data.action = action;
				data.nonce = nonce;
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: data,
					success: function(response) {
						if (response.success) {
							showNotice(response.data.message, 'success');
							if (successCallback) successCallback(response);
						} else {
							showNotice(response.data.message || 'Errore', 'error');
						}
					},
					error: function() {
						showNotice('Errore di comunicazione con il server.', 'error');
					}
				});
			}
			// Simple search filter function - removes non-matching options instead of hiding
			function filterSelectOptions(searchInput, selectId) {
				var searchTerm = $(searchInput).val().toLowerCase().trim();
				var $select = $('#' + selectId);
				var selectedValue = $select.val();
				
				// Store all options in a data attribute if not already stored
				if (!$select.data('all-options')) {
					var allOptions = [];
					$select.find('option').each(function() {
						allOptions.push({
							value: $(this).val(),
							text: $(this).text(),
							slug: $(this).data('slug') || '',
							parent: $(this).data('parent') || 0,
							isDuplicate: $(this).data('is-duplicate') || '0'
						});
					});
					$select.data('all-options', allOptions);
				}
				
				// Clear and rebuild options
				$select.empty();
				var allOptions = $select.data('all-options');
				var $firstMatch = null;
				
				allOptions.forEach(function(opt) {
					var matches = searchTerm === '' || opt.text.toLowerCase().indexOf(searchTerm) !== -1;
					
					if (matches || opt.value === '') {
						var $option = $('<option>').val(opt.value).text(opt.text);
						// Restore data attributes
						if (opt.slug) $option.attr('data-slug', opt.slug);
						if (opt.parent) $option.attr('data-parent', opt.parent);
						if (opt.isDuplicate) $option.attr('data-is-duplicate', opt.isDuplicate);
						// Restore selection
						if (opt.value === selectedValue) {
							$option.prop('selected', true);
						}
						$select.append($option);
						
						if (opt.value !== '' && !$firstMatch) {
							$firstMatch = $option;
						}
					}
				});
				
				// Ensure selected value is maintained
				if (selectedValue) {
					$select.val(selectedValue);
				}
				
				// Scroll to selected or first match
				if (selectedValue && $select.find('option[value="' + selectedValue + '"]').length) {
					var $selectedOption = $select.find('option[value="' + selectedValue + '"]');
					var selectedIndex = $selectedOption.index();
					$select[0].scrollTop = Math.max(0, (selectedIndex - 2) * 20);
				} else if ($firstMatch && searchTerm !== '') {
					var firstIndex = $firstMatch.index();
					$select[0].scrollTop = Math.max(0, (firstIndex - 2) * 20);
				}
			}
			
			// Initialize search filters
			$('#root_page_search').on('input', function() {
				filterSelectOptions(this, 'root_page_id');
			});
			
			$('#child_page_search').on('input', function() {
				filterSelectOptions(this, 'child_page_id');
			});
			
			var editSearchUpdating = false;
			$('#edit_item_page_search').on('input', function() {
				if (!editSearchUpdating) {
					filterSelectOptions(this, 'edit_item_page_id');
				}
			});
			
			// Handle select change to update search field with selected page name
			$('#root_page_id, #child_page_id, #edit_item_page_id').on('change', function() {
				var selectedText = $(this).find('option:selected').text();
				var searchId = $(this).attr('id').replace('_page_id', '_page_search');
				if (selectedText && selectedText !== 'Seleziona una pagina...') {
					if (searchId === 'edit_item_page_search') {
						editSearchUpdating = true;
						$('#' + searchId).val(selectedText);
						setTimeout(function() { editSearchUpdating = false; }, 100);
					} else {
						$('#' + searchId).val(selectedText);
					}
				}
			});

			// Make menu sortable
			$('#menu-structure').sortable({
				handle: '.drag-handle',
				placeholder: 'menu-item-placeholder'
			});

			// Add child dialog
			$(document).on('click', '.add-child', function() {
				var parentId = $(this).data('parent-id');
				$('#parent_id').val(parentId);
				
				// Reset form
				$('#child_label').val('');
				$('#child_type').val('page');
				$('#child_page_id').val('');
				$('#child_page_search').val('');
				$('#child_anchor').val('');
				$('#anchor-row').hide();
				
				// Clear search to show all options
				$('#child_page_search').val('');
				filterSelectOptions($('#child_page_search')[0], 'child_page_id');
				
				$('#add-child-dialog').dialog({
					title: '➕ Aggiungi Sub-pagina',
					modal: true,
					width: 600,
					height: 'auto',
					resizable: false,
					open: function() {
						// Focus search field
						setTimeout(function() {
							$('#child_page_search').focus();
						}, 100);
					},
					buttons: {
						'➕ Aggiungi': function() {
							var label = $('#child_label').val();
							var pageId = $('#child_page_id').val();
							
							if (!label || !pageId) {
								showNotice('Label e pagina sono obbligatori.', 'error');
								return;
							}
							
							ajaxRequest('at_add_child', {
								parent_id: parentId,
								child_label: label,
								child_page_id: pageId
							}, function() {
								$(this).dialog('close');
								location.reload();
							}.bind(this));
						},
						'❌ Annulla': function() {
							$(this).dialog('close');
						}
					}
				});
			});

			// Anchor field is no longer needed - all items are pages
			$('#anchor-row').hide();

			// Edit item
			$(document).on('click', '.edit-item', function() {
				var itemId = $(this).data('item-id');
				var itemLabel = $(this).data('item-label');
				var itemPageId = $(this).data('item-page-id');
				
				$('#edit_item_id').val(itemId);
				$('#edit_item_label').val(itemLabel);
				
				// Clear any stored options data to force rebuild
				$('#edit_item_page_id').removeData('all-options');
				
				// Set the selected value
				$('#edit_item_page_id').val(itemPageId);
				
				// Show all options initially by filtering with empty string
				$('#edit_item_page_search').val('');
				filterSelectOptions($('#edit_item_page_search')[0], 'edit_item_page_id');
				
				// Pre-populate search with Label (menu item name) without triggering filter
				setTimeout(function() {
					editSearchUpdating = true;
					$('#edit_item_page_search').val(itemLabel);
					setTimeout(function() { editSearchUpdating = false; }, 100);
				}, 100);
				
				$('#edit-item-dialog').dialog({
					title: '✏️ Modifica Pagina',
					modal: true,
					width: 600,
					height: 'auto',
					resizable: false,
					open: function() {
						// Scroll to selected option
						if (itemPageId) {
							var $selectedOption = $('#edit_item_page_id option[value="' + itemPageId + '"]');
							if ($selectedOption.length) {
								var optionIndex = $selectedOption.index();
								$('#edit_item_page_id').scrollTop(optionIndex * 20);
							}
						}
						// Focus search field
						setTimeout(function() {
							$('#edit_item_page_search').focus();
						}, 100);
					},
					buttons: {
						'💾 Salva': function() {
							var pageId = $('#edit_item_page_id').val();
							
							if (!pageId) {
								showNotice('Seleziona una pagina.', 'error');
								return;
							}
							
							ajaxRequest('at_update_item', {
								item_id: itemId,
								page_id: pageId
							}, function() {
								$(this).dialog('close');
								location.reload();
							}.bind(this));
						},
						'❌ Annulla': function() {
							$(this).dialog('close');
						}
					}
				});
			});

			// Delete item
			$(document).on('click', '.delete-item', function() {
				if (confirm('🗑️ Sei sicuro di voler eliminare questo elemento?')) {
					var itemId = $(this).data('item-id');
					var $item = $(this).closest('.menu-item');
					
					ajaxRequest('at_delete_item', {
						item_id: itemId
					}, function() {
						$item.fadeOut(function() {
							$(this).remove();
						});
					});
				}
			});
			
			// Toggle content verified checkbox
			$(document).on('change', '.content-verified-checkbox', function() {
				var itemId = $(this).data('item-id');
				var verified = $(this).is(':checked');
				var $item = $(this).closest('.menu-item');
				
				ajaxRequest('at_toggle_verified', {
					item_id: itemId,
					verified: verified ? 1 : 0
				}, function() {
					if (verified) {
						$item.addClass('content-verified');
					} else {
						$item.removeClass('content-verified');
					}
				});
			});

			// Save menu structure (AJAX)
			$('#save-menu-form').on('submit', function(e) {
				e.preventDefault();
				var structure = [];
				$('#menu-structure .menu-item').each(function() {
					structure.push($(this).data('id'));
				});
				
				ajaxRequest('at_save_menu', {
					menu_structure: JSON.stringify(structure)
				});
			});
			
			// Add root item (AJAX)
			$('#add-root-form').on('submit', function(e) {
				e.preventDefault();
				var label = $('#root_label').val();
				var pageId = $('#root_page_id').val();
				
				if (!label || !pageId) {
					showNotice('Label e pagina sono obbligatori.', 'error');
					return;
				}
				
				ajaxRequest('at_add_root', {
					root_label: label,
					root_page_id: pageId
				}, function() {
					location.reload();
				});
			});
		});
		</script>
		<?php
	}
}

// Initialize admin class
new AT_Dynamic_Menu_Admin();

/**
 * AT Dynamic Menu Widget Class
 */
class AT_Dynamic_Menu_Widget extends WP_Widget
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			'at_dynamic_menu',
			'Amministrazione Trasparente (Dynamic)',
			array(
				'description' => 'Dynamic menu widget that builds nested structure from WordPress pages. Shortcode: [at_dynamic_menu title="Custom Title" search="true"]',
				'classname' => 'at-dynamic-menu-widget',
			)
		);
	}

	/**
	 * Widget display
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget($args, $instance)
	{
		echo $args['before_widget'];
		
		$title = !empty($instance['title']) ? $instance['title'] : '';
		if (!empty($title)) {
			echo $args['before_title'] . esc_html($title) . $args['after_title'];
		}

		// Get menu structure from database
		$menu_structure = get_option('at_dynamic_menu_structure', array());

		if (empty($menu_structure)) {
			echo '<p>Nessun menu configurato. Vai su <a href="' . admin_url('tools.php?page=at-dynamic-menu') . '">Menu Builder</a> per configurare il menu.</p>';
			echo $args['after_widget'];
			return;
		}

		// Add search input if search is enabled
		$show_search = !empty($instance['show_search']) ? $instance['show_search'] : 'true';
		if ($show_search !== 'false') {
			echo '<div class="at-dynamic-menu-search">';
			echo '<input type="text" id="at-dynamic-menu-search-input-' . $this->id . '" placeholder="Cerca nel menu..." class="at-dynamic-menu-search-field">';
			echo '</div>';
		}

		// Render menu structure
		$this->render_menu_structure($menu_structure);

		// Add JavaScript for search functionality if search is enabled
		if ($show_search !== 'false') {
			echo '<script>
			jQuery(document).ready(function($) {
				var $searchInput = $("#at-dynamic-menu-search-input-' . $this->id . '");
				var $menuItems = $searchInput.closest(".at-dynamic-menu-widget").find(".nav li");
				var $menuContainer = $searchInput.closest(".at-dynamic-menu-widget").find(".nav");
				
				$searchInput.on("input", function() {
					var searchTerm = $(this).val().toLowerCase();
					
					if (searchTerm.length === 0) {
						// Show all items when search is empty
						$menuItems.show();
						$menuContainer.find(".nav-child").show();
						return;
					}
					
					$menuItems.each(function() {
						var $item = $(this);
						var $link = $item.find("a");
						var itemText = $link.text().toLowerCase();
						var $children = $item.find(".nav-child");
						var hasVisibleChildren = false;
						
						// Check if this item matches
						var itemMatches = itemText.includes(searchTerm);
						
						// Check if any children match
						$children.find("li").each(function() {
							var $childLink = $(this).find("a");
							var childText = $childLink.text().toLowerCase();
							var childMatches = childText.includes(searchTerm);
							
							if (childMatches) {
								$(this).show();
								hasVisibleChildren = true;
							} else {
								$(this).hide();
							}
						});
						
						// Show item if it matches or has visible children
						if (itemMatches || hasVisibleChildren) {
							$item.show();
							if (hasVisibleChildren) {
								$children.show();
							}
						} else {
							$item.hide();
							$children.hide();
						}
					});
				});
			});
			</script>';
			
			// Add CSS for search styling
			echo '<style>
			.at-dynamic-menu-search {
				margin-bottom: 15px;
			}
			.at-dynamic-menu-search-field {
				width: 100%;
				padding: 8px 12px;
				border: 1px solid #ddd;
				border-radius: 4px;
				font-size: 14px;
				box-sizing: border-box;
			}
			.at-dynamic-menu-search-field:focus {
				outline: none;
				border-color: #0073aa;
				box-shadow: 0 0 0 1px #0073aa;
			}
			.at-dynamic-menu-widget .nav li {
				transition: opacity 0.2s ease;
			}
			
			/* Differentiation between sub-pages and subsections */
			.at-dynamic-menu-widget .item-page a {
				/* Regular sub-page styling */
				font-weight: 500;
				color: #333;
			}
			
			.at-dynamic-menu-widget .item-subsection a {
				/* Subsection styling - different visual treatment */
				font-weight: 400;
				color: #666;
				font-style: italic;
				padding-left: 10px;
				position: relative;
			}
			
			.at-dynamic-menu-widget .item-subsection a:hover {
				color: #0073aa;
			}
			
			/* Current page highlighting */
			.at-dynamic-menu-widget .nav li.current-page > a,
			.at-dynamic-menu-widget .nav li.current-page a.current {
				background-color: #0073aa;
				color: white;
				font-weight: bold;
				padding: 5px 10px;
				border-radius: 3px;
			}
			
			.at-dynamic-menu-widget .nav li.ancestor-page > a,
			.at-dynamic-menu-widget .nav li.ancestor-page a.ancestor {
				background-color: #f0f8ff;
				color: #0073aa;
				font-weight: 600;
				padding: 3px 8px;
				border-radius: 3px;
				border-left: 3px solid #0073aa;
			}
			
			.at-dynamic-menu-widget .nav li.current-page,
			.at-dynamic-menu-widget .nav li.ancestor-page {
				margin: 2px 0;
			}
			</style>';
		}

		echo $args['after_widget'];
	}

	/**
	 * Render menu structure recursively
	 */
	/**
	 * Clean up label by removing leading quotes and trimming whitespace
	 * 
	 * @param string $label The label to clean
	 * @return string The cleaned label
	 */
	private function clean_label($label) {
		if (!is_string($label)) {
			return '';
		}
		$clean = trim($label);
		// Remove leading quotes (both regular and HTML entity)
		$clean = ltrim($clean, '"');
		$clean = ltrim($clean, '&quot;');
		$clean = trim($clean);
		return $clean;
	}

	public function render_menu_structure($menu_items, $depth = 0)
	{
		if (empty($menu_items)) {
			return;
		}

		$ul_class = $depth === 0 ? 'nav menu' : 'nav-child unstyled small';
		echo '<ul class="' . esc_attr($ul_class) . '">' . "\n";

		foreach ($menu_items as $item) {
			$page = get_post($item['page_id']);
			if (!$page) {
				continue; // Skip if page doesn't exist
			}

			$url = get_permalink($page->ID);
			// Normalize URL to remove accented characters
			$url = $this->normalize_url($url);
			$has_children = !empty($item['children']);

			$li_classes = array("item-{$page->ID}");
			if ($has_children) {
				$li_classes[] = 'deeper parent';
			}

			// Add current page highlighting
			$current_page_id = get_the_ID();
			$is_current_page = ($current_page_id == $page->ID);
			$is_ancestor = $this->is_ancestor_page($page->ID, $current_page_id);
			
			if ($is_current_page) {
				$li_classes[] = 'current-page';
			}
			if ($is_ancestor) {
				$li_classes[] = 'ancestor-page';
			}

			printf(
				'<li class="%s">',
				esc_attr(implode(' ', $li_classes))
			);

			// Render the main link with current page class
			$link_classes = array();
			if ($is_current_page) {
				$link_classes[] = 'current';
			}
			if ($is_ancestor) {
				$link_classes[] = 'ancestor';
			}
			
			$link_class_attr = !empty($link_classes) ? ' class="' . esc_attr(implode(' ', $link_classes)) . '"' : '';
			
			// Clean up label - remove leading quotes and trim whitespace
			$clean_label = $this->clean_label($item['label']);
			
			printf(
				'<a href="%s"%s>%s</a>',
				esc_url($url),
				$link_class_attr,
				esc_html($clean_label)
			);

			// Render children in their own ul container
			if ($has_children) {
				echo '<ul class="nav-child unstyled small">' . "\n";
				foreach ($item['children'] as $child) {
					// Check if this is a subsection or a sub-page
					$is_subsection = isset($child['type']) && $child['type'] === 'subsection';
					
					if ($is_subsection) {
						// This is a subsection - link to anchor within parent page
						$parent_url = get_permalink($item['page_id']);
						// Normalize URL to remove accented characters
						$parent_url = $this->normalize_url($parent_url);
						$anchor = isset($child['anchor']) ? $child['anchor'] : $this->create_anchor_from_heading($child['label']);
						$child_url = $parent_url . '#' . $anchor;
						
						$child_li_classes = array("item-subsection");
						$child_link_classes = array();
						
						// Check if we're on the parent page and this subsection is visible
						$is_current_page = ($current_page_id == $item['page_id']);
						$is_subsection_current = $is_current_page && isset($_GET['section']) && $_GET['section'] === $anchor;
						
						if ($is_subsection_current) {
							$child_li_classes[] = 'current-subsection';
							$child_link_classes[] = 'current';
						}
						
						$child_li_class_attr = ' class="' . esc_attr(implode(' ', $child_li_classes)) . '"';
						$child_link_class_attr = !empty($child_link_classes) ? ' class="' . esc_attr(implode(' ', $child_link_classes)) . '"' : '';
						
						// Clean up child label - remove leading quotes
						$clean_child_label = $this->clean_label($child['label']);
						
						printf(
							'<li%s><a href="%s"%s>%s</a></li>' . "\n",
							$child_li_class_attr,
							esc_url($child_url),
							$child_link_class_attr,
							esc_html($clean_child_label)
						);
					} else {
						// This is a regular sub-page
						$child_page = get_post($child['page_id']);
						if (!$child_page) {
							continue;
						}

						$child_url = get_permalink($child_page->ID);
						// Normalize URL to remove accented characters
						$child_url = $this->normalize_url($child_url);
						$is_child_current = ($current_page_id == $child_page->ID);
						
						$child_li_classes = array("item-page");
						$child_link_classes = array();
						
						if ($is_child_current) {
							$child_li_classes[] = 'current-page';
							$child_link_classes[] = 'current';
						}
						
						$child_li_class_attr = ' class="' . esc_attr(implode(' ', $child_li_classes)) . '"';
						$child_link_class_attr = !empty($child_link_classes) ? ' class="' . esc_attr(implode(' ', $child_link_classes)) . '"' : '';
						
						// Clean up child label - remove leading quotes
						$clean_child_label = $this->clean_label($child['label']);
						
						printf(
							'<li%s><a href="%s"%s>%s</a></li>' . "\n",
							$child_li_class_attr,
							esc_url($child_url),
							$child_link_class_attr,
							esc_html($clean_child_label)
						);
					}
				}
				echo "</ul>\n";
			}

			echo "</li>\n";
		}

		echo "</ul>\n";
	}

	/**
	 * Check if a page is an ancestor of the current page
	 */
	private function is_ancestor_page($ancestor_id, $current_page_id) {
		if (!$current_page_id || !$ancestor_id) {
			return false;
		}
		
		$current_page = get_post($current_page_id);
		if (!$current_page) {
			return false;
		}
		
		// Check if current page is a child of the ancestor
		$parent_id = $current_page->post_parent;
		while ($parent_id) {
			if ($parent_id == $ancestor_id) {
				return true;
			}
			$parent = get_post($parent_id);
			$parent_id = $parent ? $parent->post_parent : 0;
		}
		
		return false;
	}

	/**
	 * Widget form
	 *
	 * @param array $instance Widget instance.
	 */
	public function form($instance)
	{
		$title = !empty($instance['title']) ? $instance['title'] : 'Amministrazione Trasparente';
		$show_search = !empty($instance['show_search']) ? $instance['show_search'] : 'true';
		?>
		<p>
			<label
				for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Titolo:', 'astra'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
				name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
				value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
			<label
				for="<?php echo esc_attr($this->get_field_id('show_search')); ?>"><?php esc_html_e('Mostra ricerca:', 'astra'); ?></label>
			<select class="widefat" id="<?php echo esc_attr($this->get_field_id('show_search')); ?>"
				name="<?php echo esc_attr($this->get_field_name('show_search')); ?>">
				<option value="true" <?php selected($show_search, 'true'); ?>><?php esc_html_e('Sì', 'astra'); ?></option>
				<option value="false" <?php selected($show_search, 'false'); ?>><?php esc_html_e('No', 'astra'); ?></option>
			</select>
		</p>
		<p class="description">
			<?php esc_html_e('Questo widget costruisce automaticamente una struttura di menu nidificata dalle tue pagine WordPress basata sulla gerarchia dell\'Amministrazione Trasparente.', 'astra'); ?>
		</p>
		<p class="description">
			<a href="<?php echo admin_url('tools.php?page=at-dynamic-menu'); ?>">Gestisci pagine radice</a>
		</p>
		<?php
	}

	/**
	 * Widget update
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Old widget instance.
	 * @return array Updated widget instance.
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = !empty($new_instance['title']) ? sanitize_text_field($new_instance['title']) : 'Amministrazione Trasparente';
		$instance['show_search'] = !empty($new_instance['show_search']) ? sanitize_text_field($new_instance['show_search']) : 'true';
		return $instance;
	}

	/**
	 * Create anchor from heading text
	 */
	private function create_anchor_from_heading($heading)
	{
		// Remove accents first, then convert to lowercase and replace spaces with hyphens
		$anchor = $this->remove_accents($heading);
		$anchor = strtolower($anchor);
		$anchor = preg_replace('/[^a-z0-9\s-]/', '', $anchor);
		$anchor = preg_replace('/\s+/', '-', $anchor);
		$anchor = trim($anchor, '-');
		
		return $anchor;
	}

	/**
	 * Normalize URL by removing accented characters
	 * Converts accented characters to their plain equivalents
	 * 
	 * @param string $url The URL to normalize
	 * @return string The normalized URL
	 */
	private function normalize_url($url)
	{
		// Parse the URL to get its components
		$parsed = parse_url($url);
		if (!$parsed) {
			return $url; // Return original if parsing fails
		}

		// Normalize the path component
		if (isset($parsed['path'])) {
			$path = $parsed['path'];
			
			// Split path into segments
			$segments = explode('/', trim($path, '/'));
			$normalized_segments = array();
			
			foreach ($segments as $segment) {
				if (empty($segment)) {
					continue;
				}
				
				// Decode URL encoding first to get the actual text
				$decoded = urldecode($segment);
				
				// Normalize accented characters to plain equivalents
				$normalized = $this->remove_accents($decoded);
				
				// Only encode if the normalized version is different from the decoded original
				// This preserves already-encoded segments that don't need normalization
				if ($normalized !== $decoded) {
					// Need to encode the normalized version
					$normalized_segments[] = rawurlencode($normalized);
				} else {
					// Keep original encoding
					$normalized_segments[] = $segment;
				}
			}
			
			// Reconstruct path
			$parsed['path'] = '/' . implode('/', $normalized_segments);
		}

		// Reconstruct the URL
		$scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
		$user = isset($parsed['user']) ? $parsed['user'] : '';
		$pass = isset($parsed['pass']) ? ':' . $parsed['pass'] : '';
		$auth = ($user || $pass) ? $user . $pass . '@' : '';
		$host = isset($parsed['host']) ? $parsed['host'] : '';
		$port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
		$path = isset($parsed['path']) ? $parsed['path'] : '';
		$query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
		$fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

		return $scheme . $auth . $host . $port . $path . $query . $fragment;
	}

	/**
	 * Remove accents from a string, converting to plain ASCII equivalents
	 * 
	 * @param string $string The string to remove accents from
	 * @return string The string with accents removed
	 */
	private function remove_accents($string)
	{
		// Italian and common European accented characters mapping
		$accents = array(
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
			'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
			'ý' => 'y', 'ÿ' => 'y',
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
			'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
			'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
			'Ý' => 'Y',
			'ç' => 'c', 'Ç' => 'C',
			'ñ' => 'n', 'Ñ' => 'N',
		);

		return strtr($string, $accents);
	}
}

/**
 * Register the widget
 */
function at_register_dynamic_menu_widget()
{
	register_widget('AT_Dynamic_Menu_Widget');
}
add_action('widgets_init', 'at_register_dynamic_menu_widget');

/**
 * Shortcode for the AT Dynamic Menu
 * Usage: [at_dynamic_menu title="Custom Title" search="true"]
 */
function at_dynamic_menu_shortcode($atts)
{
	$atts = shortcode_atts(array(
		'title' => '',
		'search' => 'true', // Default to true, can be set to 'false' to disable
	), $atts, 'at_dynamic_menu');

	ob_start();
	
	// Start widget output
	echo '<aside class="widget at-dynamic-menu-widget">';
	if (!empty($atts['title'])) {
		echo '<h2 class="widget-title">' . esc_html($atts['title']) . '</h2>';
	}

	// Get menu structure from database
	$menu_structure = get_option('at_dynamic_menu_structure', array());

	if (empty($menu_structure)) {
		echo '<p>Nessun menu configurato. Vai su <a href="' . admin_url('tools.php?page=at-dynamic-menu') . '">Menu Builder</a> per configurare il menu.</p>';
		echo '</aside>';
		return ob_get_clean();
	}

	// Add search input if search is enabled
	if ($atts['search'] !== 'false') {
		echo '<div class="at-dynamic-menu-search">';
		echo '<input type="text" id="at-dynamic-menu-search-input" placeholder="Cerca nel menu..." class="at-dynamic-menu-search-field">';
		echo '</div>';
	}

	// Render menu structure
	$widget = new AT_Dynamic_Menu_Widget();
	$widget->render_menu_structure($menu_structure);

	// Add JavaScript for search functionality if search is enabled
	if ($atts['search'] !== 'false') {
		echo '<script>
		jQuery(document).ready(function($) {
			var $searchInput = $("#at-dynamic-menu-search-input");
			var $menuItems = $(".at-dynamic-menu-widget .nav li");
			var $menuContainer = $(".at-dynamic-menu-widget .nav");
			
			$searchInput.on("input", function() {
				var searchTerm = $(this).val().toLowerCase();
				
				if (searchTerm.length === 0) {
					// Show all items when search is empty
					$menuItems.show();
					$menuContainer.find(".nav-child").show();
					return;
				}
				
				$menuItems.each(function() {
					var $item = $(this);
					var $link = $item.find("a");
					var itemText = $link.text().toLowerCase();
					var $children = $item.find(".nav-child");
					var hasVisibleChildren = false;
					
					// Check if this item matches
					var itemMatches = itemText.includes(searchTerm);
					
					// Check if any children match
					$children.find("li").each(function() {
						var $childLink = $(this).find("a");
						var childText = $childLink.text().toLowerCase();
						var childMatches = childText.includes(searchTerm);
						
						if (childMatches) {
							$(this).show();
							hasVisibleChildren = true;
		} else {
							$(this).hide();
						}
					});
					
					// Show item if it matches or has visible children
					if (itemMatches || hasVisibleChildren) {
						$item.show();
						if (hasVisibleChildren) {
							$children.show();
						}
		} else {
						$item.hide();
						$children.hide();
					}
				});
			});
		});
		</script>';
		
		// Add CSS for search styling and menu differentiation
		echo '<style>
		.at-dynamic-menu-search {
			margin-bottom: 15px;
		}
		.at-dynamic-menu-search-field {
			width: 100%;
			padding: 8px 12px;
			border: 1px solid #ddd;
			border-radius: 4px;
			font-size: 14px;
			box-sizing: border-box;
		}
		.at-dynamic-menu-search-field:focus {
			outline: none;
			border-color: #0073aa;
			box-shadow: 0 0 0 1px #0073aa;
		}
		.at-dynamic-menu-widget .nav li {
			transition: opacity 0.2s ease;
		}
		
		/* Differentiation between sub-pages and subsections */
		.at-dynamic-menu-widget .item-page a {
			/* Regular sub-page styling */
			font-weight: 500;
			color: #333;
		}
		
		.at-dynamic-menu-widget .item-subsection a {
			/* Subsection styling - different visual treatment */
			font-weight: 400;
			color: #666;
			font-style: italic;
			padding-left: 10px;
			position: relative;
		}
		
		
		.at-dynamic-menu-widget .item-subsection a:hover {
			color: #0073aa;
		}
		
		/* Current page highlighting */
		.at-dynamic-menu-widget .current-page a,
		.at-dynamic-menu-widget .current-subsection a {
			color: #0073aa;
			font-weight: 600;
		}
		</style>';
	}

	echo '</aside>';
	
	return ob_get_clean();
}
add_shortcode('at_dynamic_menu', 'at_dynamic_menu_shortcode');