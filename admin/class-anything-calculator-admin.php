<?php
/**
 * Admin class
 */
class Anything_Calculator_Admin
{

  /**
   * Enqueue admin styles
   */
  public function enqueue_styles($hook)
  {
    // Load on our plugin pages and when editing formulas
    if (
      strpos($hook, 'anything-calculator') === false &&
      !(get_current_screen() && get_current_screen()->post_type === 'ac_formula')
    ) {
      return;
    }

    wp_enqueue_style(
      'anything-calculator-admin',
      ANYTHING_CALCULATOR_URL . 'admin/css/admin.css',
      array(),
      ANYTHING_CALCULATOR_VERSION
    );
  }

  /**
   * Enqueue admin scripts
   */
  public function enqueue_scripts($hook)
  {
    // Load on our plugin pages and when editing formulas
    if (
      strpos($hook, 'anything-calculator') === false &&
      !(get_current_screen() && get_current_screen()->post_type === 'ac_formula')
    ) {
      return;
    }

    wp_enqueue_script(
      'anything-calculator-admin',
      ANYTHING_CALCULATOR_URL . 'admin/js/admin.js',
      array('jquery', 'jquery-ui-sortable'),
      ANYTHING_CALCULATOR_VERSION,
      true
    );

    // Pass data to script
    wp_localize_script(
      'anything-calculator-admin',
      'anythingCalculatorAdmin',
      array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_rest'),
        'restUrl' => get_rest_url(),
      )
    );
  }

  /**
   * Add admin menu
   */
  public function add_admin_menu()
  {
    add_menu_page(
      'Anything Calculator',
      'Anything Calculator',
      'manage_options',
      'anything-calculator',
      array($this, 'display_admin_page'),
      'dashicons-calculator',
      30
    );

    add_submenu_page(
      'anything-calculator',
      'Formulas',
      'Formulas',
      'manage_options',
      'edit.php?post_type=ac_formula',
      null
    );

    add_submenu_page(
      'anything-calculator',
      'Add New Formula',
      'Add New Formula',
      'manage_options',
      'post-new.php?post_type=ac_formula',
      null
    );

    add_submenu_page(
      'anything-calculator',
      'Settings',
      'Settings',
      'manage_options',
      'anything-calculator-settings',
      array($this, 'display_settings_page')
    );
  }

  /**
   * Display admin page
   */
  public function display_admin_page()
  {
    include ANYTHING_CALCULATOR_PATH . 'admin/partials/admin-page.php';
  }

  /**
   * Display settings page
   */
  public function display_settings_page()
  {
    include ANYTHING_CALCULATOR_PATH . 'admin/partials/settings-page.php';
  }

  /**
   * Register formula post type
   */
  public function register_formula_post_type()
  {
    $labels = array(
      'name' => 'Formulas',
      'singular_name' => 'Formula',
      'add_new' => 'Add New',
      'add_new_item' => 'Add New Formula',
      'edit_item' => 'Edit Formula',
      'new_item' => 'New Formula',
      'all_items' => 'All Formulas',
      'view_item' => 'View Formula',
      'search_items' => 'Search Formulas',
      'not_found' => 'No formulas found',
      'not_found_in_trash' => 'No formulas found in Trash',
      'parent_item_colon' => '',
      'menu_name' => 'Formulas',
    );

    $args = array(
      'labels' => $labels,
      'public' => false,
      'publicly_queryable' => false,
      'show_ui' => true,
      'show_in_menu' => false,
      'query_var' => true,
      'rewrite' => array('slug' => 'formula'),
      'capability_type' => 'post',
      'has_archive' => false,
      'hierarchical' => false,
      'menu_position' => null,
      'supports' => array('title', 'editor'),
    );

    register_post_type('ac_formula', $args);
  }

  /**
   * Add formula meta boxes
   */
  public function add_formula_meta_boxes()
  {
    add_meta_box(
      'ac_formula_details',
      'Formula Details',
      array($this, 'render_formula_details_meta_box'),
      'ac_formula',
      'normal',
      'high'
    );

    add_meta_box(
      'ac_formula_inputs',
      'Formula Inputs',
      array($this, 'render_formula_inputs_meta_box'),
      'ac_formula',
      'normal',
      'high'
    );

    add_meta_box(
      'ac_formula_output',
      'Formula Output',
      array($this, 'render_formula_output_meta_box'),
      'ac_formula',
      'normal',
      'high'
    );
  }

  /**
   * Render formula details meta box
   */
  public function render_formula_details_meta_box($post)
  {
    // Get formula
    $formula = get_post_meta($post->ID, '_ac_formula', true);

    // Add nonce for security
    wp_nonce_field('ac_formula_details', 'ac_formula_details_nonce');

    // Include template
    include ANYTHING_CALCULATOR_PATH . 'admin/partials/meta-boxes/formula-details.php';
  }

  /**
   * Render formula inputs meta box
   */
  public function render_formula_inputs_meta_box($post)
  {
    // Get inputs
    $inputs = get_post_meta($post->ID, '_ac_inputs', true);

    // Ensure inputs is an array
    if (!is_array($inputs)) {
      $inputs = array();
    }

    // Add nonce for security
    wp_nonce_field('ac_formula_inputs', 'ac_formula_inputs_nonce');

    // Include template
    include ANYTHING_CALCULATOR_PATH . 'admin/partials/meta-boxes/formula-inputs.php';
  }

  /**
   * Render formula output meta box
   */
  public function render_formula_output_meta_box($post)
  {
    // Get output
    $output = get_post_meta($post->ID, '_ac_output', true);

    // Ensure output is an array
    if (!is_array($output)) {
      $output = array(
        'label' => 'Result',
        'unit' => '',
        'precision' => 2,
      );
    }

    // Add nonce for security
    wp_nonce_field('ac_formula_output', 'ac_formula_output_nonce');

    // Include template
    include ANYTHING_CALCULATOR_PATH . 'admin/partials/meta-boxes/formula-output.php';
  }

  /**
   * Save formula meta boxes
   */
  public function save_formula_meta_boxes($post_id)
  {
    // Check if we're autosaving
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // Check the post type
    if (get_post_type($post_id) !== 'ac_formula') {
      return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    // Save formula details - with improved nonce and sanitization
    if (
      isset($_POST['ac_formula_details_nonce']) &&
      wp_verify_nonce(sanitize_text_field($_POST['ac_formula_details_nonce']), 'ac_formula_details')
    ) {
      $formula = isset($_POST['ac_formula']) ? $this->sanitize_formula($_POST['ac_formula']) : '';
      if (!empty($formula)) {
        update_post_meta($post_id, '_ac_formula', $formula);
      }
    }

    // Save formula inputs
    if (
      isset($_POST['ac_formula_inputs_nonce']) &&
      wp_verify_nonce(sanitize_text_field($_POST['ac_formula_inputs_nonce']), 'ac_formula_inputs')
    ) {
      $inputs = isset($_POST['ac_inputs']) ? $this->sanitize_inputs($_POST['ac_inputs']) : array();
      update_post_meta($post_id, '_ac_inputs', $inputs);
    }

    // Save formula output
    if (
      isset($_POST['ac_formula_output_nonce']) &&
      wp_verify_nonce(sanitize_text_field($_POST['ac_formula_output_nonce']), 'ac_formula_output')
    ) {
      $output = array(
        'label' => isset($_POST['ac_output_label']) ? sanitize_text_field($_POST['ac_output_label']) : 'Result',
        'unit' => isset($_POST['ac_output_unit']) ? sanitize_text_field($_POST['ac_output_unit']) : '',
        'precision' => isset($_POST['ac_output_precision']) ? absint($_POST['ac_output_precision']) : 2,
      );
      update_post_meta($post_id, '_ac_output', $output);
    }
  }

  /**
   * Sanitize formula string to prevent XSS and other injection attacks
   */
  private function sanitize_formula($formula)
  {
    if (empty($formula) || !is_string($formula)) {
      return '';
    }

    // First sanitize as text field
    $formula = sanitize_text_field($formula);

    // Then only allow alphanumeric characters, spaces, simple math operators, and parentheses
    return preg_replace('/[^a-zA-Z0-9\s\+\-\*\/\(\)\.]/', '', $formula);
  }

  /**
   * Sanitize inputs
   */
  private function sanitize_inputs($inputs)
  {
    $sanitized = array();

    if (!is_array($inputs)) {
      return $sanitized;
    }

    foreach ($inputs as $input) {
      if (!isset($input['name']) || empty($input['name'])) {
        continue; // Skip inputs without names
      }

      // Sanitize the name to ensure it's a valid variable name
      $name = preg_replace('/[^a-zA-Z0-9]/', '', sanitize_text_field($input['name']));

      // Skip if name is empty after sanitization or if it starts with a number
      if (empty($name) || is_numeric(substr($name, 0, 1))) {
        continue;
      }

      $sanitized[] = array(
        'name' => $name,
        'label' => isset($input['label']) ? sanitize_text_field($input['label']) : $name,
        'type' => isset($input['type']) && in_array($input['type'], array('number', 'range', 'select'))
          ? sanitize_text_field($input['type'])
          : 'number',
        'default' => isset($input['default']) ? sanitize_text_field($input['default']) : '',
        'description' => isset($input['description']) ? sanitize_textarea_field($input['description']) : '',
        'required' => isset($input['required']) && $input['required'] ? true : false,
      );
    }

    return $sanitized;
  }

  /**
   * Register AJAX handlers
   */
  public function __construct()
  {
    // Register AJAX actions
    add_action('wp_ajax_ac_dismiss_getting_started', array($this, 'dismiss_getting_started'));
  }

  /**
   * AJAX handler for dismissing the getting started box
   */
  public function dismiss_getting_started()
  {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ac_dismiss_getting_started')) {
      wp_send_json_error('Invalid nonce');
      return;
    }

    // Store user preference
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'ac_getting_started_dismissed', '1');

    wp_send_json_success();
    exit;
  }
}