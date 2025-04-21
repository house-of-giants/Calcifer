<?php
/**
 * Plugin Name: Anything Calculator
 * Plugin URI: https://hlpipe.com
 * Description: A flexible calculator plugin that allows users to create custom formulas and display them as Gutenberg blocks.
 * Version: 1.0.0
 * Author: H&L Pipe
 * Author URI: https://hlpipe.com
 * Text Domain: anything-calculator
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

// Define plugin constants
define('ANYTHING_CALCULATOR_VERSION', '1.0.0');
define('ANYTHING_CALCULATOR_PATH', plugin_dir_path(__FILE__));
define('ANYTHING_CALCULATOR_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ANYTHING_CALCULATOR_PATH . 'includes/class-anything-calculator.php';

// Initialize the plugin
function run_anything_calculator()
{
  $plugin = new Anything_Calculator();
  $plugin->run();
}
run_anything_calculator();