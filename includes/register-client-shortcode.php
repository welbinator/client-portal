<?php

// Function to render the registration form
function cp_register_client_form() {
    ob_start();
    ?>
    <form id="cp-register-client-form" method="post">
        <p>
            <label for="cp_first_name">First Name</label>
            <input type="text" name="cp_first_name" id="cp_first_name" required>
        </p>
        <p>
            <label for="cp_last_name">Last Name</label>
            <input type="text" name="cp_last_name" id="cp_last_name" required>
        </p>
        <p>
            <label for="cp_email">Email</label>
            <input type="email" name="cp_email" id="cp_email" required>
        </p>
        <p>
            <label for="cp_username">Username</label>
            <input type="text" name="cp_username" id="cp_username" required>
        </p>
        <p>
            <label for="cp_password">Password</label>
            <input type="password" name="cp_password" id="cp_password" required>
        </p>
        <p>
            <input type="submit" name="cp_register_client_submit" value="Register">
        </p>
    </form>
    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('register_client', 'cp_register_client_form');

// Function to handle form submission
function cp_handle_client_registration() {
    if (isset($_POST['cp_register_client_submit'])) {
        $first_name = sanitize_text_field($_POST['cp_first_name']);
        $last_name = sanitize_text_field($_POST['cp_last_name']);
        $email = sanitize_email($_POST['cp_email']);
        $username = sanitize_text_field($_POST['cp_username']);
        $password = sanitize_text_field($_POST['cp_password']);

        $errors = [];

        if (username_exists($username)) {
            $errors[] = 'Username already exists.';
        }

        if (email_exists($email)) {
            $errors[] = 'Email already exists.';
        }

        if (empty($errors)) {
            $user_id = wp_create_user($username, $password, $email);

            if (!is_wp_error($user_id)) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                ));

                $user = new WP_User($user_id);
                $user->set_role('client');

                echo '<p>Registration successful!</p>';
            } else {
                echo '<p>There was an error: ' . $user_id->get_error_message() . '</p>';
            }
        } else {
            foreach ($errors as $error) {
                echo '<p>' . $error . '</p>';
            }
        }
    }
}

// Hook the form handling function to init
add_action('init', 'cp_handle_client_registration');
