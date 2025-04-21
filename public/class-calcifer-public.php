<?php
/**
 * Public class
 */
class Calcifer_Public
{

	/**
	 * Enqueue public styles
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style(
			'calcifer-public',
			CALCIFER_URL . 'public/css/public.css',
			array(),
			CALCIFER_VERSION
		);

		// Add dynamic theme colors
		$this->add_theme_color_styles();
	}

	/**
	 * Add theme color styles
	 */
	private function add_theme_color_styles()
	{
		// Get settings
		$settings = get_option('calcifer_settings', array());
		$color_mappings = isset($settings['color_mappings']) ? $settings['color_mappings'] : array();

		// Get theme colors
		$colors = array(
			'primary' => '#3498db',   // Default primary color
			'secondary' => '#2c3e50', // Default secondary color
			'text' => '#333333',      // Default text color
			'background' => '#ffffff' // Default background color
		);

		// Get theme colors from WordPress
		$theme_colors = array();
		if (function_exists('wp_get_global_settings')) {
			// Get the theme.json color palette
			$wp_settings = wp_get_global_settings();

			if (!empty($wp_settings['color']['palette'])) {
				$theme_palette = $wp_settings['color']['palette'];

				// Flatten the theme colors if they're nested in a 'default' or other palette groups
				if (isset($theme_palette['default']) || isset($theme_palette['theme']) || isset($theme_palette['custom'])) {
					// It's a nested structure, so flatten it
					foreach ($theme_palette as $palette_group => $palette_colors) {
						if (is_array($palette_colors)) {
							foreach ($palette_colors as $color) {
								if (isset($color['slug']) && isset($color['color']) && isset($color['name'])) {
									$theme_colors[] = $color;
								}
							}
						}
					}
				} else {
					// It's already a flat structure
					foreach ($theme_palette as $color) {
						if (isset($color['slug']) && isset($color['color']) && isset($color['name'])) {
							$theme_colors[] = $color;
						}
					}
				}
			}
		}

		// Process color mappings
		foreach (array('primary', 'secondary', 'text', 'background') as $color_key) {
			$color_mapping = isset($color_mappings[$color_key]) ? $color_mappings[$color_key] : '';

			if ($color_mapping === 'custom' && !empty($color_mappings[$color_key . '_custom'])) {
				// For custom variables, we need to handle them specially
				$custom_var = $color_mappings[$color_key . '_custom'];
				// Remove '--' prefix if present to avoid double dashes
				$custom_var = ltrim($custom_var, '-');
				$colors[$color_key] = 'var(--' . $custom_var . ', ' . $colors[$color_key] . ')';
			} elseif (!empty($color_mapping) && !empty($theme_colors)) {
				// Find the mapped theme color
				foreach ($theme_colors as $theme_color) {
					if ($theme_color['slug'] === $color_mapping) {
						$colors[$color_key] = $theme_color['color'];
						break;
					}
				}
			} elseif (empty($color_mapping) && !empty($theme_colors)) {
				// Try to auto-detect from theme if no specific mapping is set
				foreach ($theme_colors as $color) {
					if (!empty($color['slug']) && !empty($color['color'])) {
						// Look for common primary/secondary color names in slugs
						if ($color_key === 'primary' && strpos($color['slug'], 'primary') !== false) {
							$colors['primary'] = $color['color'];
						} elseif ($color_key === 'secondary' && strpos($color['slug'], 'secondary') !== false) {
							$colors['secondary'] = $color['color'];
						} elseif ($color_key === 'secondary' && strpos($color['slug'], 'accent') !== false && $colors['secondary'] === '#2c3e50') {
							// Use accent as secondary if no secondary is found
							$colors['secondary'] = $color['color'];
						} elseif (
							($color_key === 'text' && $color['slug'] === 'text') ||
							($color_key === 'text' && $color['slug'] === 'foreground')
						) {
							$colors['text'] = $color['color'];
						} elseif ($color_key === 'background' && $color['slug'] === 'background') {
							$colors['background'] = $color['color'];
						}
					}
				}
			}
		}

		// Override with primary color from settings if it exists (for backward compatibility)
		if (!empty($settings['primary_color'])) {
			$use_settings_color = true;

			// Only use settings color if no mapping is defined
			if (isset($color_mappings['primary']) && !empty($color_mappings['primary'])) {
				$use_settings_color = false;
			}

			if ($use_settings_color) {
				$colors['primary'] = $settings['primary_color'];
			}
		}

		// Create CSS for direct color values
		$css = "
		:root {
			--calcifer-primary-color: {$colors['primary']};
			--calcifer-secondary-color: {$colors['secondary']};
			--calcifer-text-color: {$colors['text']};
			--calcifer-background-color: {$colors['background']};
		}";

		// Add button style if defined
		if (!empty($settings['button_style'])) {
			switch ($settings['button_style']) {
				case 'square':
					$css .= "
					.calcifer-button {
						border-radius: 0;
					}";
					break;
				case 'pill':
					$css .= "
					.calcifer-button {
						border-radius: 50px;
					}";
					break;
			}
		}

		// Add custom CSS if enabled
		if (!empty($settings['allow_custom_css']) && !empty($settings['custom_css'])) {
			$css .= "\n/* Custom CSS */\n" . $settings['custom_css'];
		}

		// Add the inline style
		wp_add_inline_style('calcifer-public', $css);
	}

	/**
	 * Enqueue public scripts
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script(
			'calcifer-public',
			CALCIFER_URL . 'public/js/public.js',
			array('jquery'),
			CALCIFER_VERSION,
			true
		);

		// Pass data to script
		wp_localize_script(
			'calcifer-public',
			'calciferPublic',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('wp_rest'),
				'restUrl' => get_rest_url(),
			)
		);

		// Add page loading indicator script if calculators are present
		add_action('wp_footer', array($this, 'add_page_loader'));
	}

	/**
	 * Add page loader for calculator initialization
	 */
	public function add_page_loader()
	{
		// Only add the loader if we have calculators on the page
		?>
		<script>
			(function ($) {
				$(document).ready(function () {
					// Check if we have any calculators on the page
					if ($('.calcifer-container').length > 0) {
						// Add container-specific overlay to each calculator
						$('.calcifer-container').each(function () {
							const $container = $(this);

							// Add overlay to this specific calculator
							$container.append('<div class="calcifer-container-loader"><div class="calcifer-spinner"></div><div class="calcifer-loading-text">Loading Calculator...</div></div>');

							// Position the loader
							$container.css('position', 'relative');

							// Remove loader when page is fully loaded or after timeout
							$(window).on('load', function () {
								$container.find('.calcifer-container-loader').fadeOut(300, function () {
									$(this).remove();
								});
							});

							// Fallback if window.load doesn't fire
							setTimeout(function () {
								$container.find('.calcifer-container-loader').fadeOut(300, function () {
									$(this).remove();
								});
							}, 2000);
						});
					}
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes()
	{
		register_rest_route('calcifer/v1', '/calculate/(?P<id>\d+)', array(
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

		register_rest_route('calcifer/v1', '/formulas', array(
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