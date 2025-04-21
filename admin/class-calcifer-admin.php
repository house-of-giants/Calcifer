<?php
/**
 * Admin class
 */
class Calcifer_Admin
{

  /**
   * Constructor
   */
  public function __construct()
  {
    // Register AJAX actions
    add_action('wp_ajax_calcifer_dismiss_getting_started', array($this, 'dismiss_getting_started'));

    // Add admin footer text
    add_filter('admin_footer_text', array($this, 'admin_footer_text'), 10, 1);

    // Register settings
    add_action('admin_init', array($this, 'register_settings'));
  }

  /**
   * Register plugin settings
   */
  public function register_settings()
  {
    register_setting(
      'calcifer_settings',
      'calcifer_settings',
      array(
        'sanitize_callback' => array($this, 'sanitize_settings'),
        'default' => array(
          'primary_color' => '#3498db',
          'button_style' => 'rounded',
          'show_branding' => true,
          'allow_custom_css' => false,
          'custom_css' => '',
          'color_mappings' => array(
            'primary' => '',
            'secondary' => '',
            'text' => '',
            'background' => '',
            'primary_custom' => '',
            'secondary_custom' => '',
            'text_custom' => '',
            'background_custom' => '',
          )
        )
      )
    );
  }

  /**
   * Sanitize settings
   */
  public function sanitize_settings($input)
  {
    $sanitized = array();

    // Sanitize color
    $sanitized['primary_color'] = isset($input['primary_color']) ? sanitize_hex_color($input['primary_color']) : '#3498db';

    // Sanitize button style
    $sanitized['button_style'] = isset($input['button_style']) && in_array($input['button_style'], array('rounded', 'square', 'pill'))
      ? $input['button_style']
      : 'rounded';

    // Sanitize branding
    $sanitized['show_branding'] = isset($input['show_branding']) ? (bool) $input['show_branding'] : true;

    // Sanitize advanced settings
    $sanitized['allow_custom_css'] = isset($input['allow_custom_css']) ? (bool) $input['allow_custom_css'] : false;
    $sanitized['custom_css'] = isset($input['custom_css']) ? $this->sanitize_css($input['custom_css']) : '';

    // Sanitize color mappings
    $sanitized['color_mappings'] = array(
      'primary' => isset($input['color_mappings']['primary']) ? sanitize_text_field($input['color_mappings']['primary']) : '',
      'secondary' => isset($input['color_mappings']['secondary']) ? sanitize_text_field($input['color_mappings']['secondary']) : '',
      'text' => isset($input['color_mappings']['text']) ? sanitize_text_field($input['color_mappings']['text']) : '',
      'background' => isset($input['color_mappings']['background']) ? sanitize_text_field($input['color_mappings']['background']) : '',
      'primary_custom' => isset($input['color_mappings']['primary_custom']) ? sanitize_text_field($input['color_mappings']['primary_custom']) : '',
      'secondary_custom' => isset($input['color_mappings']['secondary_custom']) ? sanitize_text_field($input['color_mappings']['secondary_custom']) : '',
      'text_custom' => isset($input['color_mappings']['text_custom']) ? sanitize_text_field($input['color_mappings']['text_custom']) : '',
      'background_custom' => isset($input['color_mappings']['background_custom']) ? sanitize_text_field($input['color_mappings']['background_custom']) : '',
    );

    return $sanitized;
  }

  /**
   * Basic CSS sanitization
   */
  private function sanitize_css($css)
  {
    // Remove potentially malicious tags
    $css = wp_strip_all_tags($css);

    // Allow only basic CSS characters
    $css = preg_replace('/[^a-zA-Z0-9_\-\s\{\}\:\;\.\,\#\(\)\!\%\@\*\/]/', '', $css);

    return $css;
  }

  /**
   * Custom admin footer text on Calcifer admin pages
   */
  public function admin_footer_text($footer_text)
  {
    $current_screen = get_current_screen();

    // Only modify footer on Calcifer admin pages
    if (
      isset($current_screen->id) && (
        strpos($current_screen->id, 'calcifer') !== false ||
        $current_screen->post_type === 'calcifer_formula'
      )
    ) {
      $footer_text = sprintf(
        'If you enjoy using %1$s, please consider %2$s. Any support is appreciated!',
        '<strong>Calcifer</strong>',
        '<a href="https://houseofgiants.gumroad.com/l/calcifer" target="_blank">supporting its development</a>'
      );
    }

    return $footer_text;
  }

  /**
   * Enqueue admin styles
   */
  public function enqueue_styles($hook)
  {
    // Load on our plugin pages and when editing formulas
    if (
      strpos($hook, 'calcifer') === false &&
      !(get_current_screen() && get_current_screen()->post_type === 'calcifer_formula')
    ) {
      return;
    }

    wp_enqueue_style(
      'calcifer-admin',
      CALCIFER_URL . 'admin/css/admin.css',
      array(),
      CALCIFER_VERSION
    );
  }

  /**
   * Enqueue admin scripts
   */
  public function enqueue_scripts($hook)
  {
    // Load on our plugin pages and when editing formulas
    if (
      strpos($hook, 'calcifer') === false &&
      !(get_current_screen() && get_current_screen()->post_type === 'calcifer_formula')
    ) {
      return;
    }

    wp_enqueue_script(
      'calcifer-admin',
      CALCIFER_URL . 'admin/js/admin.js',
      array('jquery', 'jquery-ui-sortable'),
      CALCIFER_VERSION,
      true
    );

    // Pass data to script
    wp_localize_script(
      'calcifer-admin',
      'calciferAdmin',
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
      'Calcifer',
      'Calcifer',
      'manage_options',
      'calcifer',
      array($this, 'display_admin_page'),
      'dashicons-calculator',
      30
    );

    add_submenu_page(
      'calcifer',
      'Formulas',
      'Formulas',
      'manage_options',
      'edit.php?post_type=calcifer_formula',
      null
    );

    add_submenu_page(
      'calcifer',
      'Add New Formula',
      'Add New Formula',
      'manage_options',
      'post-new.php?post_type=calcifer_formula',
      null
    );

    add_submenu_page(
      'calcifer',
      'Settings',
      'Settings',
      'manage_options',
      'calcifer-settings',
      array($this, 'display_settings_page')
    );
  }

  /**
   * Display admin page
   */
  public function display_admin_page()
  {
    include CALCIFER_PATH . 'admin/partials/admin-page.php';
  }

  /**
   * Display settings page
   */
  public function display_settings_page()
  {
    include CALCIFER_PATH . 'admin/partials/settings-page.php';
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

    register_post_type('calcifer_formula', $args);
  }

  /**
   * Add formula meta boxes
   */
  public function add_formula_meta_boxes()
  {
    add_meta_box(
      'calcifer_formula_details',
      'Formula Details',
      array($this, 'render_formula_details_meta_box'),
      'calcifer_formula',
      'normal',
      'high'
    );

    add_meta_box(
      'calcifer_formula_inputs',
      'Formula Inputs',
      array($this, 'render_formula_inputs_meta_box'),
      'calcifer_formula',
      'normal',
      'high'
    );

    add_meta_box(
      'calcifer_formula_output',
      'Formula Output',
      array($this, 'render_formula_output_meta_box'),
      'calcifer_formula',
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
    $formula = get_post_meta($post->ID, '_calcifer_formula', true);

    // Add nonce for security
    wp_nonce_field('calcifer_formula_details', 'calcifer_formula_details_nonce');

    // Include template
    include CALCIFER_PATH . 'admin/partials/meta-boxes/formula-details.php';
  }

  /**
   * Render formula inputs meta box
   */
  public function render_formula_inputs_meta_box($post)
  {
    // Get inputs
    $inputs = get_post_meta($post->ID, '_calcifer_inputs', true);

    // Ensure inputs is an array
    if (!is_array($inputs)) {
      $inputs = array();
    }

    // Add nonce for security
    wp_nonce_field('calcifer_formula_inputs', 'calcifer_formula_inputs_nonce');

    // Include template
    include CALCIFER_PATH . 'admin/partials/meta-boxes/formula-inputs.php';
  }

  /**
   * Render formula output meta box
   */
  public function render_formula_output_meta_box($post)
  {
    // Get output
    $output = get_post_meta($post->ID, '_calcifer_output', true);

    // Ensure output is an array
    if (!is_array($output)) {
      $output = array(
        'label' => 'Result',
        'unit' => '',
        'precision' => 2,
      );
    }

    // Add nonce for security
    wp_nonce_field('calcifer_formula_output', 'calcifer_formula_output_nonce');

    // Include template
    include CALCIFER_PATH . 'admin/partials/meta-boxes/formula-output.php';
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
    if (get_post_type($post_id) !== 'calcifer_formula') {
      return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    // Save formula details - with improved nonce and sanitization
    if (
      isset($_POST['calcifer_formula_details_nonce']) &&
      wp_verify_nonce(sanitize_text_field($_POST['calcifer_formula_details_nonce']), 'calcifer_formula_details')
    ) {
      $formula = isset($_POST['calcifer_formula']) ? $this->sanitize_formula($_POST['calcifer_formula']) : '';
      if (!empty($formula)) {
        update_post_meta($post_id, '_calcifer_formula', $formula);
      }
    }

    // Save formula inputs
    if (
      isset($_POST['calcifer_formula_inputs_nonce']) &&
      wp_verify_nonce(sanitize_text_field($_POST['calcifer_formula_inputs_nonce']), 'calcifer_formula_inputs')
    ) {
      $inputs = isset($_POST['calcifer_inputs']) ? $this->sanitize_inputs($_POST['calcifer_inputs']) : array();
      update_post_meta($post_id, '_calcifer_inputs', $inputs);
    }

    // Save formula output
    if (
      isset($_POST['calcifer_formula_output_nonce']) &&
      wp_verify_nonce(sanitize_text_field($_POST['calcifer_formula_output_nonce']), 'calcifer_formula_output')
    ) {
      $output = array(
        'label' => isset($_POST['calcifer_output_label']) ? sanitize_text_field($_POST['calcifer_output_label']) : 'Result',
        'unit' => isset($_POST['calcifer_output_unit']) ? sanitize_text_field($_POST['calcifer_output_unit']) : '',
        'precision' => isset($_POST['calcifer_output_precision']) ? absint($_POST['calcifer_output_precision']) : 2,
      );
      update_post_meta($post_id, '_calcifer_output', $output);
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
   * AJAX handler for dismissing the getting started box
   */
  public function dismiss_getting_started()
  {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'calcifer_dismiss_getting_started')) {
      wp_send_json_error('Invalid nonce');
      return;
    }

    // Store user preference
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'calcifer_getting_started_dismissed', '1');

    wp_send_json_success();
    exit;
  }
}