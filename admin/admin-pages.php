<?php

// Add a menu item for the plugin settings
function cp_add_admin_menu() {
    add_menu_page(
        'Client Portal Settings',    // Page title
        'Client Portal',             // Menu title
        'manage_options',            // Capability
        'client-portal',             // Menu slug
        'cp_settings_page',          // Function to display the page
        'dashicons-admin-generic',   // Icon
        81                           // Position
    );
}

add_action('admin_menu', 'cp_add_admin_menu');

// Display the settings page
function cp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Client Portal Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cp_settings_group');
            do_settings_sections('client-portal');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function cp_register_settings() {
    register_setting('cp_settings_group', 'cp_settings');

    add_settings_section(
        'cp_general_settings_section', // ID
        'General Settings',            // Title
        'cp_general_settings_section_callback', // Callback
        'client-portal'                // Page
    );

    add_settings_field(
        'cp_portal_page',              // ID
        'Client Portal Page',          // Title
        'cp_portal_page_callback',     // Callback
        'client-portal',               // Page
        'cp_general_settings_section'  // Section
    );
}

add_action('admin_init', 'cp_register_settings');

// Section callback
function cp_general_settings_section_callback() {
    echo 'Configure the general settings for the Client Portal plugin.';
}

// Field callback
function cp_portal_page_callback() {
    $options = get_option('cp_settings');
    $selected_page = isset($options['cp_portal_page']) ? $options['cp_portal_page'] : '';
    $pages = get_pages();

    echo '<select name="cp_settings[cp_portal_page]">';
    echo '<option value="">Select a page</option>';
    foreach ($pages as $page) {
        $selected = ($selected_page == $page->ID) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
