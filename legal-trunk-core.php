<?php
/**
 * Plugin Name:     Local Trunk Core
 * Plugin URI:      https://techwithdee.com
 * Description:     Core Integration for Building the Local Trunk Web App
 * Version:         1.0.0
 * Author:          Damilare Shobowale
 * Author URI:      https://techwithdee.com
 * Developer:       Damilare Shobowale
 * Developer URI:   https://techwithdee.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     legal-trunk-core
 * Domain Path:     /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	die('You are not allowed to call this page directly.');
}

require __DIR__ . '/vendor/autoload.php';

define('LOCAL_TRUNK_CORE_SYSTEM_FILE_PATH', __FILE__);
define('LOCAL_TRUNK_CORE_VERSION_NUMBER', '1.0.0');

define( 'LOCAL_TRUNK_CORE_SYSTEM_SRC_DIRECTORY', plugin_dir_url( __FILE__ ). 'src');
define( 'LOCAL_TRUNK_CORE_SYSTEM_ASSETS_URL', plugin_dir_url( __FILE__ ). 'assets');
define( 'LOCAL_TRUNK_CORE_SYSTEM_ASSETS_DIRECTORY', plugin_dir_path( __FILE__ ). 'assets');
define( 'LOCAL_TRUNK_CORE_SYSTEM_ASSETS_IMG_URL', LOCAL_TRUNK_CORE_SYSTEM_ASSETS_URL. '/img');
define( 'LOCAL_TRUNK_CORE_SYSTEM_ASSETS_IMG_DIRECTORY', LOCAL_TRUNK_CORE_SYSTEM_ASSETS_DIRECTORY. '/img');

add_action( 'plugins_loaded', 'wc_gateway_init', 11);

function wc_gateway_init() {
	\LegalTrunkCore\Init::init();
}