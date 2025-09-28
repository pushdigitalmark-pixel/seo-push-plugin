<?php
/*
Plugin Name: Vibe Coding SEO Plugin
Description: Minimal valid skeleton plugin with safe boot & settings.
Version: 1.0.4
Author: Push Digital
Text Domain: vibe-coding-seo-plugin
Requires PHP: 7.4
Requires at least: 5.8
Tested up to: 6.6
*/
if ( ! defined('ABSPATH') ) exit;

/** תחילה נגדיר קבועים (חשוב לפני require_once) */
define('VIBE_CODING_SEO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VIBE_CODING_SEO_PLUGIN_URL',  plugin_dir_url(__FILE__));

/** עוזר לטעינה בטוחה – לא מפיל את האתר אם קובץ חסר */
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

/** טוענים קבצי ליבה (אם קיימים) */
vibe_seo_safe_require('inc/activator.php');
if ( function_exists('vibe_seo_activate') ) {
    register_activation_hook(__FILE__, 'vibe_seo_activate');
}
vibe_seo_safe_require('inc/metabox.php');
vibe_seo_safe_require('inc/score.php');

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
        wp_enqueue_script('vibe-coding-seo-app', VIBE_CODING_SEO_PLUGIN_URL . 'assets/app.js', array('jquery'), '1.0.4', true);
    }
    if ( file_exists($css) ) {
        wp_enqueue_style('vibe-coding-seo-style', VIBE_CODING_SEO_PLUGIN_URL . 'assets/app.css', array(), '1.0.4');
    }
});

/* ------------------------------------------------------------------------- *
 * Admin Settings Page – ללא כפילויות ועם guards
 * ------------------------------------------------------------------------- */

if ( ! function_exists('seo_push_add_admin_menu') ) {
    add_action('admin_menu', 'seo_push_add_admin_menu');
    function seo_push_add_admin_menu() {
        add_menu_page(
            'SEO Push הגדרות',
            'SEO Push',
            'manage_options',
            'seo-push-settings',
            'seo_push_settings_page',
            'dashicons-chart-line',
            90
        );
    }
}

if ( ! function_exists('seo_push_settings_init') ) {
    add_action('admin_init', 'seo_push_settings_init');
    function seo_push_settings_init() {
        register_setting('seo_push_options_group', 'seo_push_settings', [
            'type'              => 'array',
            'sanitize_callback' => 'seo_push_sanitize_settings',
            'default'           => [
                'keywords'     => '',
                'tone'         => 'professional',
                'target_words' => 1200,
                'save_mode'    => 'draft',
            ],
        ]);

        add_settings_section('seo_push_section_general', 'הגדרות כלליות', '__return_false', 'seo-push-settings');

        add_settings_field('seo_push_keywords',     'מילות מפתח יעד',   'seo_push_field_keywords',     'seo-push-settings', 'seo_push_section_general');
        add_settings_field('seo_push_tone',         'טון כתיבה',         'seo_push_field_tone',         'seo-push-settings', 'seo_push_section_general');
        add_settings_field('seo_push_target_words', 'אורך יעד (מילים)',  'seo_push_field_target_words', 'seo-push-settings', 'seo_push_section_general');
        add_settings_field('seo_push_save_mode',    'מצב שמירה',         'seo_push_field_save_mode',    'seo-push-settings', 'seo_push_section_general');
    }
}

if ( ! function_exists('seo_push_sanitize_settings') ) {
    function seo_push_sanitize_settings($input) {
        $out = [];
        $out['keywords']     = isset($input['keywords']) ? sanitize_text_field($input['keywords']) : '';
        $tone                = $input['tone'] ?? 'professional';
        $out['tone']         = in_array($tone, ['professional','casual'], true) ? $tone : 'professional';
        $out['target_words'] = max(0, (int)($input['target_words'] ?? 1200));
        $mode                = $input['save_mode'] ?? 'draft';
        $out['save_mode']    = in_array($mode, ['draft','publish','review'], true) ? $mode : 'draft';
        return $out;
    }
}

if ( ! function_exists('seo_push_settings_page') ) {
    function seo_push_settings_page() { ?>
        <div class="wrap">
            <h1>SEO Push – הגדרות</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('seo_push_options_group');
                do_settings_sections('seo-push-settings');
                submit_button('שמור שינויים');
                ?>
            </form>
        </div>
    <?php }
}

if ( ! function_exists('seo_push_get_settings') ) {
    function seo_push_get_settings() {
        $defaults = ['keywords'=>'','tone'=>'professional','target_words'=>1200,'save_mode'=>'draft'];
        $s = get_option('seo_push_settings', []);
        $s = wp_parse_args($s, $defaults);

        // תאימות אחורה לאופציה ישנה
        if ( empty($s['keywords']) ) {
            $legacy = get_option('seo_push_keyword', '');
            if ( ! empty($legacy) ) {
                $s['keywords'] = sanitize_text_field($legacy);
            }
        }
        return $s;
    }
}

if ( ! function_exists('seo_push_field_keywords') ) {
    function seo_push_field_keywords() {
        $s = seo_push_get_settings();
        printf(
            '<input type="text" name="seo_push_settings[keywords]" value="%s" class="regular-text" placeholder="לדוגמה: פרסום ai, אוטומציה קידום אתרים"> <p class="description">ניתן להפריד בפסיקים.</p>',
            esc_attr($s['keywords'])
        );
    }
}

if ( ! function_exists('seo_push_field_tone') ) {
    function seo_push_field_tone() {
        $s = seo_push_get_settings(); ?>
        <select name="seo_push_settings[tone]">
            <option value="professional" <?php selected($s['tone'],'professional'); ?>>ענייני/מקצועי</option>
            <option value="casual"       <?php selected($s['tone'],'casual'); ?>>קליל</option>
        </select>
    <?php }
}

if ( ! function_exists('seo_push_field_target_words') ) {
    function seo_push_field_target_words() {
        $s = seo_push_get_settings();
        printf('<input type="number" name="seo_push_settings[target_words]" value="%d" class="small-text" min="0" step="50">', (int)$s['target_words']);
    }
}

if ( ! function_exists('seo_push_field_save_mode') ) {
    function seo_push_field_save_mode() {
        $s = seo_push_get_settings(); ?>
        <select name="seo_push_settings[save_mode]">
            <option value="draft"   <?php selected($s['save_mode'],'draft'); ?>>טיוטה</option>
            <option value="publish" <?php selected($s['save_mode'],'publish'); ?>>פרסום מיידי</option>
            <option value="review"  <?php selected($s['save_mode'],'review'); ?>>להגשה לביקורת</option>
        </select>
    <?php }
}
