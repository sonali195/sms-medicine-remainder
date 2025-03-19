Pill Notifications Plugin

Description

The Pill Notifications Plugin allows users to manage and track their pill reminders through shortcodes and form submissions. The plugin provides features for adding, editing, and deleting pill notifications, along with listing medicines and displaying form results.

Installation

Upload the plugin folder to the /wp-content/plugins/ directory.

Activate the plugin through the 'Plugins' menu in WordPress.

The necessary database table will be created automatically upon activation.

Features

Custom shortcodes for rendering pill notification forms and medicine listings.

Admin actions for saving, editing, and deleting pill notifications.

JavaScript and CSS file enqueuing for front-end support.

Shortcodes

[pill_notifications_form] - Displays the pill notification form.

[pill_notifications_results] - Shows the submitted pill notification results.

[medicine_listing] - Displays a list of medicines.

[edit_pill_reminder_form] - Shows the edit form for pill reminders.

Hooks & Actions

register_activation_hook(__FILE__, [$this, 'create_database_table']); - Creates the database table upon plugin activation.

add_action('admin_post_save_pill_notification', [$this, 'save_form_data']); - Saves pill notification form data.

add_action('admin_post_nopriv_save_pill_notification', [$this, 'save_form_data']); - Handles form submission for non-logged-in users.

add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']); - Enqueues necessary scripts.

add_action('admin_post_edit_pill_notification', [$this, 'edit_pill_notification']); - Handles editing of a pill notification.

add_action('admin_post_delete_pill_notification', [$this, 'delete_pill_notification']); - Handles deletion of a pill notification.

Usage

To display the pill notification form, add the following shortcode in a post or page:

[pill_notifications_form]

To list all medicines:

[medicine_listing]

To display the results of pill notifications:

[pill_notifications_results]

License

This plugin is open-source and can be modified as per your needs.
