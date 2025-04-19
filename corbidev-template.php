<?php
/**
 * Plugin Name: Template Manager
 * Description: Plugin modulaire pour gérer des templates HTML avec un moteur de rendu.
 * Version: 1.0.0
 * Author: Eric
 */

if (!defined('ABSPATH')) {exit;} // Sécurité

require_once plugin_dir_path(__FILE__) . 'includes/TemplateManager.php';

function run_template_manager() {
    new TemplateManager();
}
add_action('plugins_loaded', 'run_template_manager');
