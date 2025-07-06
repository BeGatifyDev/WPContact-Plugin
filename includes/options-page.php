<?php

if (!defined('ABSPATH')) {
    exit;
}


// Hook the settings page
add_action('admin_menu', 'contact_plugin_settings_page');

function contact_plugin_settings_page()
{
    add_options_page(
        'Contact Plugin Settings', // Page title
        'Contact Plugin', // Menu title
        'manage_options', // Capability
        'contact-plugin-settings', // Menu slug
        'contact_plugin_render_settings_page' // Callback
    );
}

// Register settings
add_action('admin_init', 'contact_plugin_register_settings');

function contact_plugin_register_settings()
{
    register_setting('contact_plugin_options_group', 'contact_plugin_admin_email');
    register_setting('contact_plugin_options_group', 'contact_plugin_success_message');
}

function contact_plugin_render_settings_page()
{
    ?>
    <div class="wrap">
        <h1>Contact Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('contact_plugin_options_group');
            do_settings_sections('contact_plugin_options_group');
            ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Admin Email</th>
                    <td>
                        <input type="email" name="contact_plugin_admin_email" value="<?php echo esc_attr(get_option('contact_plugin_admin_email', get_bloginfo('admin_email'))); ?>" class="regular-text" required />
                        <p class="description">Default is site admin email. Enter to override.</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Success Message</th>
                    <td>
                        <textarea name="contact_plugin_success_message" rows="3" class="large-text"><?php echo esc_textarea(get_option('contact_plugin_success_message', '✅ Your message has been sent successfully!')); ?></textarea>
                        <p class="description">Message shown to users after form submission.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

