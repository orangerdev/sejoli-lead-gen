<?php
/**
 *
 * @link              https://ridwan-arifandi.com
 * @since             1.0.0
 * @package           Sejoli
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - Lead Campaign
 * Plugin URI:        https://sejoli.co.id
 * Description:       Integrate Sejoli Premium WordPress Membership Plugin with Lead Campaign Addon.
 * Version:           1.0.1
 * Requires PHP:      7.4.1
 * Author:            Sejoli
 * Author URI:        https://sejoli.co.id
 * Text Domain:       sejoli-lead-form
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Version constant for easy CSS refreshes

if (!function_exists('lfb_plugin_action_links')){

define('LFB_VER', '1.0.1');

define('LFB_PLUGIN_URL', plugin_dir_url(__FILE__));
define( 'LFB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

include_once(plugin_dir_path(__FILE__) . 'inc/themehunk-menu/admin-menu.php');
include_once( plugin_dir_path(__FILE__) . 'inc/lfb-constant.php' );

/**
 * Add the settings link to the Lead Form Plugin plugin row
 *
 * @param array $links - Links for the plugin
 * @return array - Links
 */
function lfb_plugin_action_links($links){
    $settings_page = add_query_arg(array('page' => 'lead-forms'), admin_url('/admin.php?'));
    $settings_link = '<a href="'.esc_url($settings_page).'">'.__('Settings', 'sejoli-lead-form' ).'</a>';
    array_unshift($links, $settings_link);
    
    return $links;
}

include_once( plugin_dir_path(__FILE__) . 'inc/lf-db.php' );
register_activation_hook(__FILE__, 'lfb_plugin_activate');

if(!function_exists('lfb_include_file')) {

    function lfb_include_file(){
        include_once( plugin_dir_path(__FILE__) . 'inc/inc.php' );
    }
    add_action('init','lfb_include_file');
    
}

include_once( plugin_dir_path(__FILE__) . 'inc/lfb-widget.php' );
// include_once( plugin_dir_path(__FILE__) . 'elementor/lfb-addon-elementor.php' );
// show notify
// include_once( plugin_dir_path(__FILE__) . 'notify/notify.php' );
}