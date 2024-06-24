<?php
// Add the meta box conditionally

function cp_add_meta_box() {
    $options = get_option('cp_settings');
    $portal_page_id = isset($options['cp_portal_page']) ? $options['cp_portal_page'] : '';

    if (!empty($portal_page_id)) {
        add_meta_box(
            'cp_meta_box',           // ID
            'Choose Client',         // Title
            'cp_meta_box_callback',  // Callback
            'page',                  // Screen (post type)
            'side',                  // Context
            'default'                // Priority
        );
    }
    
}

add_action('add_meta_boxes', 'cp_add_meta_box');


// Meta box callback function
function cp_meta_box_callback($post) {
    error_log("Meta box callback triggered for post ID: " . $post->ID);

    $clients = get_users(array('role' => 'client'));
    $selected_client = get_post_meta($post->ID, '_cp_selected_client', true);

    // If no client is selected, set the default to the first client
    if (empty($selected_client) && !empty($clients)) {
        $selected_client = $clients[0]->ID;
    }

    error_log("Selected client ID: " . $selected_client);

    ?>
    <label for="cp_client_select">Client</label>
    <select name="cp_client_select" id="cp_client_select">
        <option value="">Select a client</option>
        <?php
        foreach ($clients as $client) {
            $first_name = get_user_meta($client->ID, 'first_name', true);
            $last_name = get_user_meta($client->ID, 'last_name', true);
            $full_name = trim($first_name . ' ' . $last_name);
            if (empty($full_name)) {
                $full_name = $client->user_login; // Fallback to username if no name is provided
            }
            $selected = ($selected_client == $client->ID) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($client->ID) . '" ' . $selected . '>' . esc_html($full_name) . '</option>';
        }
        ?>
    </select>
    <?php
    wp_nonce_field('cp_save_meta_box_data', 'cp_meta_box_nonce');
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function loadClientContent(clientID, postID) {
                var nonce = '<?php echo wp_create_nonce('cp_load_client_content'); ?>';

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cp_load_client_content',
                        client_id: clientID,
                        post_id: postID,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            wp.data.dispatch('core/editor').resetBlocks(wp.blocks.parse(response.data.content));
                        }
                    }
                });
            }

            $('#cp_client_select').change(function() {
                var clientID = $(this).val();
                var postID = <?php echo $post->ID; ?>;
                loadClientContent(clientID, postID);
            });

            // Trigger change event on page load to load the initial content
            $('#cp_client_select').trigger('change');
        });
    </script>
    <?php
}








// Save the meta box data and content
function cp_save_meta_box_data($post_id) {
    if (!isset($_POST['cp_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['cp_meta_box_nonce'], 'cp_save_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['cp_client_select'])) {
        $selected_client = sanitize_text_field($_POST['cp_client_select']);
        update_post_meta($post_id, '_cp_selected_client', $selected_client);

        // Ensure we get the content from the block editor
        $post = get_post($post_id);
        $content = $post->post_content;
        
        update_post_meta($post_id, '_cp_client_content_' . $selected_client, $content);
    }
}

add_action('save_post', 'cp_save_meta_box_data');






// Load client-specific content via AJAX
function cp_load_client_content() {
    check_ajax_referer('cp_load_client_content', 'nonce');

    $client_id = intval($_POST['client_id']);
    $post_id = intval($_POST['post_id']);

    $client_content = get_post_meta($post_id, '_cp_client_content_' . $client_id, true);
    

    if (empty($client_content)) {
        $client_content = '';
    }

    wp_send_json_success(array('content' => $client_content));
}

add_action('wp_ajax_cp_load_client_content', 'cp_load_client_content');



// Filter the content based on the client viewing the page
function cp_filter_client_content($content) {
    if (is_page()) {
        $options = get_option('cp_settings');
        $portal_page_id = isset($options['cp_portal_page']) ? $options['cp_portal_page'] : '';

        if (get_the_ID() == $portal_page_id) {
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                if (in_array('client', $user->roles)) {
                    $client_content = get_post_meta(get_the_ID(), '_cp_client_content_' . $user->ID, true);
                    
                    if (!empty($client_content)) {
                        // Directly return the client content without applying the_content filter again
                        return $client_content;
                    } else {
                        return ''; // Return empty content if no client-specific content is found
                    }
                } else {
                    // If the user is logged in but not a client, show nothing
                    return '';
                }
            } else {
                // If the user is not logged in, show nothing
                return '';
            }
        }
    }
    return $content;
}

add_filter('the_content', 'cp_filter_client_content');







// Ensure the meta box only appears on the selected "Client Portal" page
function cp_display_meta_box() {
    global $post;
    if (!$post) {
        
        return;
    }

    $post_id = $post->ID;
    

    $options = get_option('cp_settings');
    $portal_page_id = isset($options['cp_portal_page']) ? $options['cp_portal_page'] : '';

    if ($post_id == $portal_page_id) {
        
        cp_add_meta_box();
    } else {
        
    }
}

add_action('add_meta_boxes', 'cp_display_meta_box');





