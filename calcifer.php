<?php
/**
 * Plugin Name: Calcifer
 * Plugin URI: https://hlpipe.com
 * Description: A flexible calculator plugin that allows users to create custom formulas and display them as Gutenberg blocks.
 * Version: 1.0.0
 * Author: H&L Pipe
 * Author URI: https://hlpipe.com
 * Text Domain: calcifer
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

// Define plugin constants
define('CALCIFER_VERSION', '1.0.0');
define('CALCIFER_PATH', plugin_dir_path(__FILE__));
define('CALCIFER_URL', plugin_dir_url(__FILE__));

// Include required files
require_once CALCIFER_PATH . 'includes/class-calcifer.php';

// Initialize the plugin
function run_calcifer()
{
  $plugin = new Calcifer();
  $plugin->run();
}
run_calcifer();