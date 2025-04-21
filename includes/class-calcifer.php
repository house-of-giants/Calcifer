<?php
/**
 * The main plugin class
 */
class Calcifer
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
		require_once CALCIFER_PATH . 'admin/class-calcifer-admin.php';

		// Public class
		require_once CALCIFER_PATH . 'public/class-calcifer-public.php';

		// Formula handler class
		require_once CALCIFER_PATH . 'includes/class-formula-handler.php';
	}

	/**
	 * Register admin hooks
	 */
	private function define_admin_hooks()
	{
		$admin = new Calcifer_Admin();

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
		$public = new Calcifer_Public();

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
	 * Get theme color palette
	 * 
	 * Retrieves the color palette from the current theme
	 */
	private function get_theme_colors()
	{
		// Default colors
		$colors = array(
			'primary' => '#3498db',   // Default primary color
			'secondary' => '#2c3e50', // Default secondary color
			'text' => '#333333',      // Default text color
			'background' => '#ffffff' // Default background color
		);

		// Get settings
		$settings = get_option('calcifer_settings', array());
		$color_mappings = isset($settings['color_mappings']) ? $settings['color_mappings'] : array();

		// Get theme colors from WordPress
		$theme_colors = array();
		if (function_exists('wp_get_global_settings')) {
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
				// Use the custom CSS variable directly (we'll handle this in CSS)
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
				// Try to auto-detect from theme if no specific mapping is set (original behavior)
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

		return $colors;
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

		// Get theme colors
		$theme_colors = $this->get_theme_colors();

		// Register and localize block script for passing formulas data
		wp_register_script(
			'calcifer-block-editor',
			CALCIFER_URL . 'build/index.js',
			array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data'),
			CALCIFER_VERSION
		);

		// Pass formulas to block script
		wp_localize_script(
			'calcifer-block-editor',
			'calciferData',
			array(
				'formulas' => $formulas,
				'restUrl' => get_rest_url(),
				'nonce' => wp_create_nonce('wp_rest'),
				'themeColors' => $theme_colors,
			)
		);

		// Make sure the script is enqueued
		wp_enqueue_script('calcifer-block-editor');

		// Register the block using block.json
		register_block_type(
			CALCIFER_PATH . 'build/blocks/calculator',
			array(
				'editor_script' => 'calcifer-block-editor',
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
			return '<div class="calcifer-error">Please select a formula in the block settings.</div>';
		}

		// Get formula details
		$formula_handler = new Formula_Handler();
		$formula = $formula_handler->get_formula($formula_id);

		if (!$formula) {
			return '<div class="calcifer-error">Selected formula not found.</div>';
		}

		// Add dynamic theme colors as inline style for this specific calculator
		$inline_style = $this->get_calculator_theme_colors($theme);

		// Start output buffer
		ob_start();

		// Add inline styles
		echo '<style>' . $inline_style . '</style>';

		// Include the template
		include CALCIFER_PATH . 'public/templates/calculator.php';

		// Return the template content
		return ob_get_clean();
	}

	/**
	 * Get calculator theme colors based on theme setting
	 */
	private function get_calculator_theme_colors($theme)
	{
		// Get theme colors
		$colors = $this->get_theme_colors();

		// Default container CSS class selector
		$selector = '.calcifer-container';

		// Prepare colors based on theme
		if ($theme === 'dark') {
			// For dark theme, we might want to adjust colors for better contrast
			return "
				{$selector} {
					--calcifer-primary-color: {$colors['primary']};
					--calcifer-secondary-color: {$colors['secondary']};
					--calcifer-text-color: #f5f5f5;
					--calcifer-background-color: {$colors['secondary']};
				}
			";
		} else {
			// Light theme
			return "
				{$selector} {
					--calcifer-primary-color: {$colors['primary']};
					--calcifer-secondary-color: {$colors['secondary']};
					--calcifer-text-color: {$colors['text']};
					--calcifer-background-color: {$colors['background']};
				}
			";
		}
	}

	/**
	 * Run the plugin
	 */
	public function run()
	{
		// Plugin activation hook
		register_activation_hook(CALCIFER_PATH . 'calcifer.php', array($this, 'activate'));

		// Plugin deactivation hook
		register_deactivation_hook(CALCIFER_PATH . 'calcifer.php', array($this, 'deactivate'));
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