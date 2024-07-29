<?php
/*
Plugin Name: MemberDash - Client Portal
Plugin URI: https://apexbranding.design
Description: A plugin to create a client portal with a custom user role.
Version: 1.0
Author: James Welbes
Author URI: https://apexbranding.design
License: GPL2
Text Domain: md-client-portal
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the code to create the Client user role
require_once(plugin_dir_path(__FILE__) . 'includes/register-client-shortcode.php');

require_once(plugin_dir_path(__FILE__) . 'admin/admin-pages.php');

require_once(plugin_dir_path(__FILE__) . 'admin/client-portal-page.php');

// Function to add the Client user role
function cp_add_client_role() {
    add_role(
        'client',
        __('Client'),
        array(
            'read' => true, // True allows this capability
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );
}

// Function to remove the Client user role
function cp_remove_client_role() {
    remove_role('client');
}

// Activate the plugin and add the role
register_activation_hook(__FILE__, 'cp_add_client_role');

// Deactivate the plugin and remove the role
register_deactivation_hook(__FILE__, 'cp_remove_client_role');

