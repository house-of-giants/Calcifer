<?php
/**
 * The main plugin class
 */
class Anything_Calculator
{

  /**
   * Initialize the plugin
   */
  public function __construct()
  {
    $this->load_dependencies();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    $this->register_blocks();
  }

  /**
   * Load required dependencies
   */
  private function load_dependencies()
  {
    // Admin class
    require_once ANYTHING_CALCULATOR_PATH . 'admin/class-anything-calculator-admin.php';

    // Public class
    require_once ANYTHING_CALCULATOR_PATH . 'public/class-anything-calculator-public.php';

    // Formula handler class
    require_once ANYTHING_CALCULATOR_PATH . 'includes/class-formula-handler.php';
  }

  /**
   * Register admin hooks
   */
  private function define_admin_hooks()
  {
    $admin = new Anything_Calculator_Admin();

    // Admin menu
    add_action('admin_menu', array($admin, 'add_admin_menu'));

    // Admin scripts and styles
    add_action('admin_enqueue_scripts', array($admin, 'enqueue_styles'));
    add_action('admin_enqueue_scripts', array($admin, 'enqueue_scripts'));

    // Register custom post type for formulas
    add_action('init', array($admin, 'register_formula_post_type'));

    // Register meta boxes
    add_action('add_meta_boxes', array($admin, 'add_formula_meta_boxes'));

    // Save meta boxes
    add_action('save_post', array($admin, 'save_formula_meta_boxes'));
  }

  /**
   * Register public hooks
   */
  private function define_public_hooks()
  {
    $public = new Anything_Calculator_Public();

    // Public scripts and styles
    add_action('wp_enqueue_scripts', array($public, 'enqueue_styles'));
    add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));

    // Register REST API endpoints
    add_action('rest_api_init', array($public, 'register_rest_routes'));
  }

  /**
   * Register Gutenberg blocks
   */
  private function register_blocks()
  {
    add_action('init', array($this, 'register_calculator_block'));
  }

  /**
   * Register calculator block
   */
  public function register_calculator_block()
  {
    // Check if Gutenberg is available
    if (!function_exists('register_block_type')) {
      return;
    }

    // Get all formulas
    $formula_handler = new Formula_Handler();
    $formulas = $formula_handler->get_formulas();

    // Register and localize block script for passing formulas data
    wp_register_script(
      'anything-calculator-block-editor',
      ANYTHING_CALCULATOR_URL . 'build/index.js',
      array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data'),
      ANYTHING_CALCULATOR_VERSION
    );

    // Pass formulas to block script
    wp_localize_script(
      'anything-calculator-block-editor',
      'anythingCalculatorData',
      array(
        'formulas' => $formulas,
        'restUrl' => get_rest_url(),
        'nonce' => wp_create_nonce('wp_rest'),
      )
    );

    // Make sure the script is enqueued
    wp_enqueue_script('anything-calculator-block-editor');

    // Register the block using block.json
    register_block_type(
      ANYTHING_CALCULATOR_PATH . 'build/blocks/calculator',
      array(
        'editor_script' => 'anything-calculator-block-editor',
        'render_callback' => array($this, 'render_calculator_block'),
      )
    );
  }

  /**
   * Render calculator block
   */
  public function render_calculator_block($attributes)
  {
    // Extract attributes
    $formula_id = isset($attributes['formulaId']) ? $attributes['formulaId'] : 0;
    $title = isset($attributes['title']) ? $attributes['title'] : 'Calculator';
    $description = isset($attributes['description']) ? $attributes['description'] : '';
    $theme = isset($attributes['theme']) ? $attributes['theme'] : 'light';

    // If no formula is selected, return empty
    if (empty($formula_id)) {
      return '<div class="anything-calculator-error">Please select a formula in the block settings.</div>';
    }

    // Get formula details
    $formula_handler = new Formula_Handler();
    $formula = $formula_handler->get_formula($formula_id);

    if (!$formula) {
      return '<div class="anything-calculator-error">Selected formula not found.</div>';
    }

    // Start output buffer
    ob_start();

    // Include the template
    include ANYTHING_CALCULATOR_PATH . 'public/templates/calculator.php';

    // Return the template content
    return ob_get_clean();
  }

  /**
   * Run the plugin
   */
  public function run()
  {
    // Plugin activation hook
    register_activation_hook(ANYTHING_CALCULATOR_PATH . 'anything-calculator.php', array($this, 'activate'));

    // Plugin deactivation hook
    register_deactivation_hook(ANYTHING_CALCULATOR_PATH . 'anything-calculator.php', array($this, 'deactivate'));
  }

  /**
   * Plugin activation
   */
  public function activate()
  {
    // Create default formulas
    $formula_handler = new Formula_Handler();

    // Check if BMI calculator formula exists
    if (!$formula_handler->formula_exists('bmi-calculator')) {
      // Create BMI calculator formula
      $formula_handler->create_formula(
        'BMI Calculator',
        'Calculate Body Mass Index (BMI) based on height and weight.',
        'bmi-calculator',
        'Weight / (Height * Height)',
        array(
          array(
            'name' => 'Weight',
            'label' => 'Weight',
            'type' => 'number',
            'default' => '70',
            'description' => 'Weight in kilograms (kg)',
            'required' => true,
          ),
          array(
            'name' => 'Height',
            'label' => 'Height',
            'type' => 'number',
            'default' => '1.75',
            'description' => 'Height in meters (m)',
            'required' => true,
          ),
        ),
        array(
          'label' => 'Body Mass Index',
          'unit' => 'kg/m²',
          'precision' => 1,
        )
      );
    }

    // Check if percentage calculator formula exists
    if (!$formula_handler->formula_exists('percentage-calculator')) {
      // Create percentage calculator formula
      $formula_handler->create_formula(
        'Percentage Calculator',
        'Calculate percentages for any value.',
        'percentage-calculator',
        'Value * (Percentage / 100)',
        array(
          array(
            'name' => 'Value',
            'label' => 'Value',
            'type' => 'number',
            'default' => '100',
            'description' => 'The base value',
            'required' => true,
          ),
          array(
            'name' => 'Percentage',
            'label' => 'Percentage',
            'type' => 'number',
            'default' => '25',
            'description' => 'Percentage value',
            'required' => true,
          ),
        ),
        array(
          'label' => 'Result',
          'unit' => '',
          'precision' => 2,
        )
      );
    }

    // Check if tip calculator formula exists
    if (!$formula_handler->formula_exists('tip-calculator')) {
      // Create tip calculator formula
      $formula_handler->create_formula(
        'Tip Calculator',
        'Calculate appropriate tip based on bill amount and tip percentage.',
        'tip-calculator',
        'BillAmount * (TipPercentage / 100)',
        array(
          array(
            'name' => 'BillAmount',
            'label' => 'Bill Amount',
            'type' => 'number',
            'default' => '50',
            'description' => 'Total bill amount',
            'required' => true,
          ),
          array(
            'name' => 'TipPercentage',
            'label' => 'Tip Percentage',
            'type' => 'number',
            'default' => '15',
            'description' => 'Percentage you want to tip',
            'required' => true,
          ),
        ),
        array(
          'label' => 'Tip Amount',
          'unit' => '$',
          'precision' => 2,
        )
      );
    }

    // Flush rewrite rules
    flush_rewrite_rules();
  }

  /**
   * Plugin deactivation
   */
  public function deactivate()
  {
    // Flush rewrite rules
    flush_rewrite_rules();
  }
}