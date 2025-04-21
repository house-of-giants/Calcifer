<?php
/**
 * Public class
 */
class Anything_Calculator_Public
{

  /**
   * Enqueue public styles
   */
  public function enqueue_styles()
  {
    wp_enqueue_style(
      'anything-calculator-public',
      ANYTHING_CALCULATOR_URL . 'public/css/public.css',
      array(),
      ANYTHING_CALCULATOR_VERSION
    );
  }

  /**
   * Enqueue public scripts
   */
  public function enqueue_scripts()
  {
    wp_enqueue_script(
      'anything-calculator-public',
      ANYTHING_CALCULATOR_URL . 'public/js/public.js',
      array('jquery'),
      ANYTHING_CALCULATOR_VERSION,
      true
    );

    // Pass data to script
    wp_localize_script(
      'anything-calculator-public',
      'anythingCalculatorPublic',
      array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_rest'),
        'restUrl' => get_rest_url(),
      )
    );
  }

  /**
   * Register REST API routes
   */
  public function register_rest_routes()
  {
    register_rest_route('anything-calculator/v1', '/calculate/(?P<id>\d+)', array(
      'methods' => 'POST',
      'callback' => array($this, 'calculate_formula'),
      'permission_callback' => function () {
        // Only allow logged-in users for sensitive calculations
        // or use '__return_true' to allow anyone to use the calculator
        return '__return_true';
      },
      'args' => array(
        'id' => array(
          'validate_callback' => function ($param) {
            return is_numeric($param) && absint($param) > 0;
          },
          'sanitize_callback' => 'absint',
        ),
      ),
    ));

    register_rest_route('anything-calculator/v1', '/formulas', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_formulas'),
      'permission_callback' => function () {
        // Anyone can view formulas
        return true;
      },
    ));
  }

  /**
   * Calculate formula
   */
  public function calculate_formula($request)
  {
    // Verify the nonce for CSRF protection (if desired)
    // if (!isset($_SERVER['HTTP_X_WP_NONCE']) || 
    //     !wp_verify_nonce($_SERVER['HTTP_X_WP_NONCE'], 'wp_rest')) {
    //     return new WP_Error('rest_forbidden', esc_html__('Invalid or missing nonce.'), array('status' => 403));
    // }

    // Get formula ID and ensure it's a valid ID
    $formula_id = absint($request->get_param('id'));
    if ($formula_id <= 0) {
      return new WP_Error('invalid_id', 'Invalid formula ID', array('status' => 400));
    }

    // Get input values
    $input_values = $request->get_json_params();

    // Validate input values
    if (!is_array($input_values)) {
      return new WP_Error('invalid_input', 'Input values must be an object', array('status' => 400));
    }

    // Sanitize input values
    $sanitized_inputs = array();
    foreach ($input_values as $key => $value) {
      // Allow only valid variable names
      $key = preg_replace('/[^a-zA-Z0-9]/', '', $key);

      // Ensure values are numeric
      if (!is_numeric($value)) {
        return new WP_Error(
          'invalid_input_value',
          sprintf('Input value for %s must be a number', $key),
          array('status' => 400)
        );
      }

      $sanitized_inputs[$key] = floatval($value);
    }

    // Calculate formula
    $formula_handler = new Formula_Handler();
    $result = $formula_handler->calculate($formula_id, $sanitized_inputs);

    return rest_ensure_response($result);
  }

  /**
   * Get formulas
   */
  public function get_formulas()
  {
    // Rate limiting can be added here if needed

    // Get all published formulas
    $formula_handler = new Formula_Handler();
    $formulas = $formula_handler->get_formulas();

    // Sanitize the output for the response
    $sanitized_formulas = array();
    foreach ($formulas as $formula) {
      $sanitized_formulas[] = array(
        'id' => absint($formula['id']),
        'title' => esc_html($formula['title']),
        'description' => wp_kses_post($formula['description']),
        'slug' => esc_attr($formula['slug']),
        // Don't expose the actual formula expression in API response for security
        // 'formula' => $formula['formula'], 
        'inputs' => $this->sanitize_inputs_for_output($formula['inputs']),
        'output' => array(
          'label' => esc_html($formula['output']['label']),
          'unit' => esc_html($formula['output']['unit']),
          'precision' => absint($formula['output']['precision']),
        ),
      );
    }

    return rest_ensure_response($sanitized_formulas);
  }

  /**
   * Sanitize inputs for API output
   */
  private function sanitize_inputs_for_output($inputs)
  {
    $sanitized = array();

    if (!is_array($inputs)) {
      return $sanitized;
    }

    foreach ($inputs as $input) {
      $sanitized[] = array(
        'name' => esc_attr($input['name']),
        'label' => esc_html($input['label']),
        'type' => esc_attr($input['type']),
        'default' => esc_attr($input['default']),
        'description' => esc_html($input['description']),
        'required' => (bool) $input['required'],
      );
    }

    return $sanitized;
  }
}