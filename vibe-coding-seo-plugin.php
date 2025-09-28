<?php
/*
Plugin Name: Vibe Coding SEO Plugin
Description: Minimal valid skeleton plugin with safe boot & settings.
Version: 1.0.5
Author: Push Digital
Text Domain: vibe-coding-seo-plugin
Requires PHP: 7.4
Requires at least: 5.8
Tested up to: 6.6
*/
if ( ! defined('ABSPATH') ) exit;

/** קבועים (חייב לפני include) */
define('VIBE_CODING_SEO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VIBE_CODING_SEO_PLUGIN_URL',  plugin_dir_url(__FILE__));

/** טעינה בטוחה — לא מפיל אתר אם קובץ חסר */
if ( ! function_exists('vibe_seo_safe_require') ) {
    function vibe_seo_safe_require( $relative ) {
        $abs = VIBE_CODING_SEO_PLUGIN_PATH . ltrim($relative, '/');
        if ( file_exists($abs) ) {
            require_once $abs;
            return true;
        }
        add_action('admin_notices', function() use ($relative){
            if ( current_user_can('manage_options') ) {
                echo '<div class="notice notice-warning"><p>Vibe SEO: קובץ חסר: <code>' . esc_html($relative) . '</code></p></div>';
            }
        });
        return false;
    }
}

/** טוענים את כל קבצי הליבה פעם אחת בלבד */
vibe_seo_safe_require('inc/activator.php');
vibe_seo_safe_require('inc/metabox.php');
vibe_seo_safe_require('inc/rest.php');
vibe_seo_safe_require('inc/score.php');

/** רישום פעולת הפעלה רק אם הפונקציה קיימת */
if ( function_exists('vibe_seo_activate') ) {
    register_activation_hook(__FILE__, 'vibe_seo_activate');
}

/** Bootstrap אופציונלי */
add_action('plugins_loaded', function () {
    $bootstrap = VIBE_CODING_SEO_PLUGIN_PATH . 'inc/bootstrap.php';
    if ( file_exists($bootstrap) ) {
        require_once $bootstrap;
    }
});

/** Enqueue נכסים רק אם קיימים */
add_action('wp_enqueue_scripts', function () {
    $js  = VIBE_CODING_SEO_PLUGIN_PATH . 'assets/app.js';
    $css = VIBE_CODING_SEO_PLUGIN_PATH . 'assets/app.css';
    if ( file_exists($js) ) {
        wp_enqueue_script('vibe-coding-seo-app', VIBE_CODING_SEO_PLUGIN_URL . 'assets/app.js', array('jquery'), '1.0.5', true);
    }
    if ( file_exists($css) ) {
        wp_enqueue_style('vibe-coding-seo-style', VIBE_CODING_SEO_PLUGIN_URL . 'assets/app.css', array(), '1.0.5');
    }
});

/* ------------------------------------------------------------------------- *
 * Admin Settings Page
 * ------------------------------------------------------------------------- */

if ( ! function_exists('seo_push_add_admin_menu') ) {
    add_action('admin_menu', 'seo_push_add_admin_menu');
    function seo_push_add_admin_menu() {
        add_menu_page(
            'SEO Push הגדרות',
            }
}
// --- Admin UI polish for SEO Push settings page ---
add_action('admin_enqueue_scripts', function ($hook) {
    // נטען רק בעמוד: הגדרות → SEO Push
    if ($hook !== 'toplevel_page_seo-push-settings') return;

    // נרשום "סגנון ריק" ונזריק אליו CSS אינליין
    wp_register_style('vibe-seo-admin', false);
    wp_enqueue_style('vibe-seo-admin');

    $css = '
    /* מסגרת כללית */
    body.toplevel_page_seo-push-settings .wrap { max-width: 980px; }
    body.toplevel_page_seo-push-settings .wrap h1 { margin-bottom: 18px; }

    /* כרטיס יפה להגדרות */
    body.toplevel_page_seo-push-settings .form-table {
        background: #fff;
        padding: 24px 26px;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0,0,0,.06);
        border: 1px solid #e7e7e7;
    }

    /* שתי עמודות נוחות: תווית / שדה */
    body.toplevel_page_seo-push-settings .form-table th {
        width: 230px;
        font-weight: 600;
        color: #23282d;
        vertical-align: middle;
        padding-top: 16px;
    }
    body.toplevel_page_seo-push-settings .form-table td {
        padding-top: 12px;
        padding-bottom: 18px;
    }

    /* שדות גדולים ונקיים */
    body.toplevel_page_seo-push-settings input[type="text"],
    body.toplevel_page_seo-push-settings input[type="number"],
    body.toplevel_page_seo-push-settings select {
        width: 100%;
        max-width: 520px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid #d9d9d9;
        box-shadow: none;
        padding: 6px 12px;
        font-size: 14px;
        background: #fff;
    }
    body.toplevel_page_seo-push-settings .description {
        margin-top: 6px;
        color: #6b7280;
        font-size: 12.5px;
    }

    /* כפתור שמירה יותר מודגש */
    body.toplevel_page_seo-push-settings .submit .button-primary {
        height: 42px;
        line-height: 40px;
        padding: 0 20px;
        border-radius: 10px;
        font-size: 15px;
        box-shadow: 0 8px 18px rgba(0,115,170,.18);
    }

    /* ריווח יפה מלמעלה (כשהודעת admin notice תופסת מקום) */
    body.toplevel_page_seo-push-settings .wrap > form { margin-top: 12px; }
    ';

    wp_add_inline_style('vibe-seo-admin', $css);
});

