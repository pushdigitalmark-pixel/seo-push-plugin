<?php
/*
Plugin Name: Vibe Coding SEO Plugin
Description: SEO helper – settings page + post metabox + basic score.
Version: 1.1.1
Author: Push Digital
Text Domain: vibe-coding-seo-plugin
Requires PHP: 7.0
Requires at least: 5.8
Tested up to: 6.6
*/
if ( ! defined('ABSPATH') ) exit;

/** קבועים */
define('VIBE_CODING_SEO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VIBE_CODING_SEO_PLUGIN_URL',  plugin_dir_url(__FILE__));
vibe_seo_safe_require('inc/ai.php');
vibe_seo_safe_require('inc/secret.php'); // נוצר בבילד או הוגדר ב-wp-config.php
vibe_seo_safe_require('inc/ai.php');     // מכיל את ה-AJAX ליצירת המאמר


/** טעינה בטוחה (ללא fn() כדי לתמוך ב־PHP ישנים) */
// לפני שמטעינים inc/ai.php:
vibe_seo_safe_require('inc/secret.php'); // ייווצר אוטומטית בבילד
vibe_seo_safe_require('inc/ai.php');

// אם אין secret.php – אזהרה לאדמין בלבד
if ( ! defined('VIBE_OPENAI_KEY') ) {
    add_action('admin_notices', function(){
        if ( current_user_can('manage_options') ) {
            echo '<div class="notice notice-error"><p>Vibe SEO: חסר קובץ מפתח <code>inc/secret.php</code> או קבוע <code>VIBE_OPENAI_KEY</code>. צור Release כדי שייוצר אוטומטית.</p></div>';
        }
    });
}

function vibe_seo_safe_require($rel){
    $abs = VIBE_CODING_SEO_PLUGIN_PATH . ltrim($rel,'/');
    if (file_exists($abs)) { require_once $abs; return true; }
    add_action('admin_notices', function () use ($rel) {
        if ( current_user_can('manage_options') ) {
            echo '<div class="notice notice-warning"><p>Vibe SEO: קובץ חסר: <code>'.esc_html($rel).'</code></p></div>';
        }
    });
    return false;
}

/** מודולים */
vibe_seo_safe_require('inc/activator.php');
vibe_seo_safe_require('inc/settings.php');
vibe_seo_safe_require('inc/metabox.php');
vibe_seo_safe_require('inc/score.php');

if ( function_exists('vibe_seo_activate') ) {
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
        background:#fff;padding:24px 26px;border-radius:16px;border:1px solid #e7e7e7;box-shadow:0 6px 20px rgba(0,0,0,.06)}
      body.toplevel_page_seo-push-settings .form-table th{
        width:230px;font-weight:600;color:#23282d;vertical-align:middle;padding-top:16px}
      body.toplevel_page_seo-push-settings .form-table td{padding-top:12px;padding-bottom:18px}
      body.toplevel_page_seo-push-settings input[type=text],
      body.toplevel_page_seo-push-settings input[type=number],
      body.toplevel_page_seo-push-settings select{
        width:100%;max-width:520px;height:40px;border-radius:10px;border:1px solid #d9d9d9;
        box-shadow:none;padding:6px 12px;font-size:14px;background:#fff}
      body.toplevel_page_seo-push-settings .description{margin-top:6px;color:#6b7280;font-size:12.5px}
      body.toplevel_page_seo-push-settings .submit .button-primary{
        height:42px;line-height:40px;padding:0 20px;border-radius:10px;font-size:15px;box-shadow:0 8px 18px rgba(0,115,170,.18)}
    ';
    wp_add_inline_style('vibe-seo-admin', $css);
});
vibe_seo_safe_require('inc/secret.php'); // נוצר בבילד מגיטהאב אקשנס (או הגדר ב-wp-config.php)
vibe_seo_safe_require('inc/ai.php');
