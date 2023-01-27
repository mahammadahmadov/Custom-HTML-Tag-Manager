<?php
/*
Plugin Name: Custom HTML Tag Manager
Description: Allows you to add custom HTML tags to your website and manage their display on specific sub-pages.
Version:           1.0.0
Author:            Mahammad Ahmadov
Author URI:        https://www.linkedin.com/in/mahammadahmadov/
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/

// Register custom post type for custom HTML tags
function register_custom_html_tag_post_type() {
    $args = array(
        'labels' => array(
            'name' => 'Custom HTML Tags',
            'singular_name' => 'Custom HTML Tag',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'supports' => array('title', 'editor', 'page-attributes'),
    );
    register_post_type('custom_html_tag', $args);
}
add_action('init', 'register_custom_html_tag_post_type');

// Output custom HTML tags in the footer
function output_custom_html_tags() {
    $args = array(
        'post_type' => 'custom_html_tag',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    $tags = get_posts($args);
    foreach ($tags as $tag) {
        $page_id = get_post_meta($tag->ID, 'page_id', true);
        $page_slug = get_post_meta($tag->ID, 'page_slug', true);
        if (($page_id == get_the_ID() || $page_slug == get_post_field( 'post_name', get_post() )) || $page_slug == "*" ) {
            echo $tag->post_content;
        }
    }
}
add_action('wp_footer', 'output_custom_html_tags');

// Add custom meta boxes for page settings
function add_custom_html_tag_meta_boxes() {
    add_meta_box(
        'custom_html_tag_page_settings',
        'Page Settings',
        'custom_html_tag_page_settings_meta_box',
        'custom_html_tag',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_custom_html_tag_meta_boxes');

// Output the content of the custom meta box
function custom_html_tag_page_settings_meta_box($post) {
    $page_id = get_post_meta($post->ID, 'page_id', true);
    $page_slug = get_post_meta($post->ID, 'page_slug', true);
    $is_disabled = get_post_meta($post->ID, 'is_disabled', true);
    ?>
    <label for="page_id">Page ID:</label>
    <input type="text" id="page_id" name="page_id" value="<?php echo $page_id; ?>" /><br />
    <label for="page_slug">Page Slug:</label>
    <input type="text" id="page_slug" name="page_slug" value="<?php echo $page_slug; ?>" /><br />
    <label for="is_disabled">Disable Tag:</label>
    <input type="checkbox" id="is_disabled" name="is_disabled" value="1" <?php checked( $is_disabled, 1 ); ?> /><br />
    <?php
}

// Save the custom meta box data
function save_custom_html_tag_page_settings($post_id) {
    update_post_meta($post_id, 'page_id', $_POST['page_id']);
    update_post_meta($post_id, 'page_slug', $_POST['page_slug']);
    update_post_meta($post_id, 'is_disabled', isset($_POST['is_disabled']) ? 1 : 0);
}
add_action('save_post', 'save_custom_html_tag_page_settings');

// Restrict access to custom HTML tags to administrators
function restrict_custom_html_tag_access() {
    if (!current_user_can('manage_options')) {
        wp_die('Access denied');
    }
}
add_action('load-post.php', 'restrict_custom_html_tag_access');
add_action('load-post-new.php', 'restrict_custom_html_tag_access');
