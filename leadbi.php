<?php

namespace LeadBI;

/**
 *
 * Plugin Name: LeadBI Tag for WordPress
 * Plugin URI: https://www.leadbi.com/features/
 * Description: LeadBI's WordPress plugin allows existing LeadBI customers and trial users to install the LeadBI tracking code on their existing WordPress blogs and websites and view the dashboard. 
 * Version: 1.7
 * Author: LeadBI
 * Author URI: https://www.leadbi.com/
 * Text Domain: leadbi
 * License: GPL2
 */

 
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// The class that contains the plugin info.
require_once plugin_dir_path(__FILE__) . 'includes/Info.php';

/**
 * The code that runs during plugin activation.
 */
function activate(){
    require_once plugin_dir_path(__FILE__) . 'includes/Activator.php';
    Activator::activate();
}

// register activation hook 
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate');


/**
 * Check for updates.
 */
// check updates

/**
 * Run the plugin.
 */

 function run() {
    require_once plugin_dir_path(__FILE__) . 'includes/Plugin.php';
    $plugin = new Plugin();
    $plugin->run();
 }


 // execute plugin
 run();