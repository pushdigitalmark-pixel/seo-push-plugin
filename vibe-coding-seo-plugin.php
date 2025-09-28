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
