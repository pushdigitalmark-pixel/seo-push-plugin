<?php
/*
Plugin Name: Vibe Coding SEO Plugin
Description: Minimal valid skeleton plugin to use as a template/wrapper.
Version: 1.0.0
Author: Push Digital
Text Domain: vibe-coding-seo-plugin
Requires PHP: 7.4
Requires at least: 5.8
Tested up to: 6.6
*/
if (!defined('ABSPATH')) exit;

define('VIBE_CODING_SEO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VIBE_CODING_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load core.
add_action('plugins_loaded', function () {
    $bootstrap = VIBE_CODING_SEO_PLUGIN_PATH . 'inc/bootstrap.php';
    if (file_exists($bootstrap)) {
        require_once $bootstrap;
    }
});

// Enqueue assets if exist.
add_action('wp_enqueue_scripts', function () {
    $js = VIBE_CODING_SEO_PLUGIN_PATH . 'assets/app.js';
    if (file_exists($js)) {
        wp_enqueue_script('vibe-coding-seo-app', VIBE_CODING_SEO_PLUGIN_URL . 'assets/app.js', array('jquery'), '1.0.0', true);
    }
    $css = VIBE_CODING_SEO_PLUGIN_PATH . 'assets/app.css';
    if (file_exists($css)) {
        wp_enqueue_style('vibe-coding-seo-style', VIBE_CODING_SEO_PLUGIN_URL . 'assets/app.css', array(), '1.0.0');
    }
});
// יצירת תפריט בלוח הבקרה
add_action('admin_menu', 'seo_push_add_admin_menu');
function seo_push_add_admin_menu() {
    add_menu_page(
        'SEO Push הגדרות',     // כותרת העמוד
        'SEO Push',            // שם התפריט
        'manage_options',      // הרשאות
        'seo-push-settings',   // slug ייחודי
        'seo_push_settings_page', // פונקציית התוכן
        'dashicons-chart-line', // אייקון
        90                      // מיקום בתפריט
    );
}

// תוכן העמוד
function seo_push_settings_page() {
    ?>
    <div class="wrap">
        <h1>הגדרות SEO Push</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('seo_push_options_group');
            do_settings_sections('seo-push-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// רישום שדה פשוט (מילת מפתח לדוגמה)
add_action('admin_init', 'seo_push_settings_init');
function seo_push_settings_init() {
    register_setting('seo_push_options_group', 'seo_push_keyword');

    add_settings_section(
        'seo_push_section',
        'הגדרות כלליות',
        null,
        'seo-push-settings'
    );

    add_settings_field(
        'seo_push_keyword',
        'מילת מפתח ראשית',
        'seo_push_keyword_render',
        'seo-push-settings',
        'seo_push_section'
    );
}

function seo_push_keyword_render() {
    $value = get_option('seo_push_keyword', '');
    echo "<input type='text' name='seo_push_keyword' value='" . esc_attr($value) . "' />";
}
