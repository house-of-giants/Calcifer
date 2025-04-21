<?php
/**
 * Formula Handler class
 */
class Formula_Handler
{

  /**
   * Custom post type name
   */
  private $post_type = 'calcifer_formula';

  /**
   * Get all formulas
   */
  public function get_formulas()
  {
    $args = array(
      'post_type' => $this->post_type,
      'posts_per_page' => -1,
      'post_status' => 'publish',
    );

    $posts = get_posts($args);
    $formulas = array();

    foreach ($posts as $post) {
      $formulas[] = $this->get_formula_data($post);
    }

    return $formulas;
  }

  /**
   * Get a single formula
   */
  public function get_formula($id)
  {
    $post = get_post($id);

    if (!$post || $post->post_type !== $this->post_type) {
      return false;
    }

    return $this->get_formula_data($post);
  }

  /**
   * Check if a formula exists by slug
   */
  public function formula_exists($slug)
  {
    $args = array(
      'post_type' => $this->post_type,
      'posts_per_page' => 1,
      'post_status' => 'publish',
      'name' => $slug,
    );

    $posts = get_posts($args);

    return !empty($posts);
  }

  /**
   * Create a new formula
   */
  public function create_formula($title, $description, $slug, $formula, $inputs, $output)
  {
    // Sanitize data
    $title = sanitize_text_field($title);
    $description = wp_kses_post($description);
    $slug = sanitize_title($slug);
    $formula = $this->sanitize_formula($formula);
    $sanitized_inputs = $this->sanitize_inputs($inputs);
    $sanitized_output = $this->sanitize_output($output);

    // Verify data validity
    if (empty($title) || empty($formula) || empty($sanitized_inputs)) {
      return false;
    }

    // Create post
    $post_id = wp_insert_post(array(
      'post_title' => $title,
      'post_content' => $description,
      'post_type' => $this->post_type,
      'post_status' => 'publish',
      'post_name' => $slug,
    ));

    if (is_wp_error($post_id)) {
      return false;
    }

    // Save formula details
    update_post_meta($post_id, '_calcifer_formula', $formula);
    update_post_meta($post_id, '_calcifer_inputs', $sanitized_inputs);
    update_post_meta($post_id, '_calcifer_output', $sanitized_output);

    return $post_id;
  }

  /**
   * Update a formula
   */
  public function update_formula($id, $title, $description, $formula, $inputs, $output)
  {
    // Sanitize data
    $id = absint($id);
    $title = sanitize_text_field($title);
    $description = wp_kses_post($description);
    $formula = $this->sanitize_formula($formula);
    $sanitized_inputs = $this->sanitize_inputs($inputs);
    $sanitized_output = $this->sanitize_output($output);

    // Verify data validity
    if (empty($id) || empty($title) || empty($formula) || empty($sanitized_inputs)) {
      return false;
    }

    // Update post
    $post_id = wp_update_post(array(
      'ID' => $id,
      'post_title' => $title,
      'post_content' => $description,
    ));

    if (is_wp_error($post_id)) {
      return false;
    }

    // Save formula details
    update_post_meta($post_id, '_calcifer_formula', $formula);
    update_post_meta($post_id, '_calcifer_inputs', $sanitized_inputs);
    update_post_meta($post_id, '_calcifer_output', $sanitized_output);

    return $post_id;
  }

  /**
   * Delete a formula
   */
  public function delete_formula($id)
  {
    return wp_delete_post($id, true);
  }

  /**
   * Get formula data from post
   */
  private function get_formula_data($post)
  {
    return array(
      'id' => $post->ID,
      'title' => $post->post_title,
      'description' => $post->post_content,
      'slug' => $post->post_name,
      'formula' => get_post_meta($post->ID, '_calcifer_formula', true),
      'inputs' => get_post_meta($post->ID, '_calcifer_inputs', true),
      'output' => get_post_meta($post->ID, '_calcifer_output', true),
    );
  }

  /**
   * Sanitize formula string
   */
  private function sanitize_formula($formula)
  {
    // Only allow alphanumeric characters, spaces, simple math operators, and parentheses
    return preg_replace('/[^a-zA-Z0-9\s\+\-\*\/\(\)\.]/', '', $formula);
  }

  /**
   * Sanitize inputs array
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
      $name = preg_replace('/[^a-zA-Z0-9]/', '', $input['name']);

      // Skip if name is empty after sanitization
      if (empty($name)) {
        continue;
      }

      $sanitized[] = array(
        'name' => $name,
        'label' => isset($input['label']) ? sanitize_text_field($input['label']) : $name,
        'type' => isset($input['type']) && in_array($input['type'], array('number', 'range', 'select'))
          ? sanitize_text_field($input['type'])
          : 'number',
        'default' => isset($input['default']) ? sanitize_text_field($input['default']) : '',
        'description' => isset($input['description']) ? sanitize_text_field($input['description']) : '',
        'required' => isset($input['required']) && $input['required'] ? true : false,
      );
    }

    return $sanitized;
  }

  /**
   * Sanitize output settings
   */
  private function sanitize_output($output)
  {
    if (!is_array($output)) {
      $output = array();
    }

    return array(
      'label' => isset($output['label']) ? sanitize_text_field($output['label']) : 'Result',
      'unit' => isset($output['unit']) ? sanitize_text_field($output['unit']) : '',
      'precision' => isset($output['precision']) ? absint($output['precision']) : 2,
    );
  }

  /**
   * Calculate formula result
   */
  public function calculate($formula_id, $input_values)
  {
    // Sanitize formula ID
    $formula_id = absint($formula_id);

    // Get formula
    $formula = $this->get_formula($formula_id);

    if (!$formula) {
      return array(
        'success' => false,
        'message' => 'Formula not found',
      );
    }

    // Validate inputs
    if (!is_array($input_values)) {
      return array(
        'success' => false,
        'message' => 'Invalid input values',
      );
    }

    // Sanitize input values
    $sanitized_input_values = array();
    foreach ($input_values as $key => $value) {
      // Only allow alphanumeric characters for input names
      $key = preg_replace('/[^a-zA-Z0-9]/', '', $key);

      // Validate numeric input
      if (!is_numeric($value)) {
        return array(
          'success' => false,
          'message' => 'Input values must be numeric',
        );
      }

      $sanitized_input_values[$key] = floatval($value);
    }

    // Check for required inputs
    foreach ($formula['inputs'] as $input) {
      if (
        $input['required'] &&
        (!isset($sanitized_input_values[$input['name']]) ||
          $sanitized_input_values[$input['name']] === '')
      ) {
        return array(
          'success' => false,
          'message' => sprintf('Missing required input: %s', $input['label']),
        );
      }
    }

    // Prepare variables for evaluation
    $variables = array();
    foreach ($sanitized_input_values as $key => $value) {
      $variables[$key] = floatval($value);
    }

    // Evaluate formula using math expression parser
    try {
      $result = $this->evaluate_expression($formula['formula'], $variables);

      // Format result based on output settings
      $precision = isset($formula['output']['precision']) ? $formula['output']['precision'] : 2;
      $formatted_result = number_format($result, $precision);

      return array(
        'success' => true,
        'result' => $result,
        'formatted_result' => $formatted_result,
        'label' => $formula['output']['label'],
        'unit' => isset($formula['output']['unit']) ? $formula['output']['unit'] : '',
      );
    } catch (Exception $e) {
      return array(
        'success' => false,
        'message' => $e->getMessage(),
      );
    }
  }

  /**
   * Evaluate a mathematical expression
   * This is a simple implementation. For complex formulas, consider using a library.
   */
  private function evaluate_expression($expression, $variables)
  {
    // Make the expression lowercase if variables are lowercase
    $expression = trim($expression);
    $lowerExpression = strtolower($expression);

    // Replace variable names with their values - case insensitive
    foreach ($variables as $name => $value) {
      // Ensure variable names have word boundaries to prevent partial matching
      $pattern = '/\b' . preg_quote($name, '/') . '\b/i'; // \b for word boundaries, i for case insensitive
      $expression = preg_replace($pattern, $value, $expression);

      // Also try with lowercase variables
      $lowerName = strtolower($name);
      $pattern = '/\b' . preg_quote($lowerName, '/') . '\b/i';
      $expression = preg_replace($pattern, $value, $expression);
    }

    // Double-check that all variables have been replaced
    if (preg_match('/[a-zA-Z]/', $expression)) {
      throw new Exception('Formula contains undefined variables');
    }

    // Sanitize the expression to prevent code injection
    // Only allow numbers, basic operators, parentheses, dots and spaces
    $expression = preg_replace('/[^0-9\+\-\*\/\(\)\.\s]/', '', $expression);

    // Log sanitized expression for debugging
    error_log('Sanitized expression: ' . $expression);

    // Safer alternative to eval - use a 3rd party library in production!
    try {
      // Add safety checks to prevent eval abuse
      if (!preg_match('/^[0-9\+\-\*\/\(\)\.\s]*$/', $expression)) {
        throw new Exception('Invalid mathematical expression');
      }

      // Check for balanced parentheses
      if (substr_count($expression, '(') !== substr_count($expression, ')')) {
        throw new Exception('Mismatched parentheses in formula');
      }

      // Check for valid operator sequences
      if (preg_match('/[\+\-\*\/]{2,}/', $expression)) {
        throw new Exception('Invalid operator sequence in formula');
      }

      // Add 0+ to ensure the expression starts with a number or operation
      $code = '0+' . $expression . ';';

      // Set a time limit for evaluation (1 second)
      set_time_limit(1);

      // Create a new closure for isolated evaluation
      $result = null;
      $safeEval = function ($code) {
        return eval ('return ' . $code);
      };

      $result = $safeEval($code);

      // Reset time limit
      set_time_limit(30);

      if ($result === false && ($error = error_get_last())) {
        throw new Exception('Error evaluating expression: ' . $error['message']);
      }

      // Check for NaN or Infinity results
      if (is_nan($result) || !is_finite($result)) {
        throw new Exception('Calculation resulted in an invalid number');
      }

      return $result;
    } catch (ParseError $e) {
      throw new Exception('Error parsing expression: ' . $e->getMessage() . ' - Expression: ' . $expression);
    } catch (Exception $e) {
      throw $e;
    }
  }
}