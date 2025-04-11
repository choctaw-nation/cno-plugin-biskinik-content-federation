<?php
/**
 * Plugin Name: Biskinik Content Federation
 * Plugin URI: https://github.com/choctaw-nation/cno-plugin-biskinik-content-federation
 * Description: Federates content from the Nation site to the Biskinik site.
 * Version: 1.2.2
 * Author: Choctaw Nation of Oklahoma
 * Author URI: https://www.choctawnation.com
 * Text Domain: cno
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 8.2
 * Requires at least: 6.6.0
 * Tested up to: 6.7.2
 *
 * @package ChoctawNation
 * @subpackage BiskinikContentFederation
 */

use ChoctawNation\BiskinikContentFederation\Plugin_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once __DIR__ . '/inc/class-plugin-loader.php';
$plugin_loader = new Plugin_Loader();

register_activation_hook( __FILE__, array( $plugin_loader, 'activate' ) );
register_deactivation_hook( __FILE__, array( $plugin_loader, 'deactivate' ) );
