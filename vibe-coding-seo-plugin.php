<?php
/*
Plugin Name: Vibe Coding SEO Plugin
Description: SEO helper – settings page + post metabox + basic score.
Version: 1.1.0
Author: Push Digital
Text Domain: vibe-coding-seo-plugin
Requires PHP: 7.4
Requires at least: 5.8
Tested up to: 6.6
*/
if (!defined('ABSPATH')) exit;

/** קבועים */
define('VIBE_CODING_SEO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VIBE_CODING_SEO_PLUGIN_URL',  plugin_dir_url(__FILE__));

/** טעינה בטוחה */
function vibe_seo_safe_require($rel){
    $abs = VIBE_CODING_SEO_PLUGIN_PATH . ltrim($rel,'/');
    if (file_exists($abs)) { require_once $abs; return true; }
    add_action('admin_notices', fn() => current_user_can('manage_options') &&
        print('<div class="notice notice-warning"><p>Vibe SEO: קובץ חסר: <code>'.esc_html($rel).'</code></p></div>')
    );
    return false;
}

/** טוענים מודולים */
vibe_seo_safe_require('inc/activator.php');   // יוצר טבלאות (אם הוספת)
vibe_seo_safe_require('inc/settings.php');    // תפריט + עמוד הגדרות
vibe_seo_safe_require('inc/metabox.php');     // מטה־בוקס לפוסט/מוצר
vibe_seo_safe_require('inc/score.php');       // חישוב ציון + כפתור

if (function_exists('vibe_seo_activate')) {
    register_activation_hook(__FILE__, 'vibe_seo_activate');
}

/** עיצוב עדין לעמוד ההגדרות */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_seo-push-settings') return;
    wp_register_style('vibe-seo-admin', false);
    wp_enqueue_style('vibe-seo-admin');
    $css = '
      body.toplevel_page_seo-push-settings .wrap{max-width:980px}
      body.toplevel_page_seo-push-settings .form-table{
        background:#fff;padding:24px 26px;border-
