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
// === Admin: Settings Page (multi-fields) ===
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

add_action('admin_init', 'seo_push_settings_init');
function seo_push_settings_init() {
    // נרשום אופציה אחת שמכילה את כל ההגדרות כמערך
    register_setting('seo_push_options_group', 'seo_push_settings', [
        'type' => 'array',
        'sanitize_callback' => 'seo_push_sanitize_settings',
        'default' => [
            'keywords' => '',
            'tone' => 'professional',
            'target_words' => 1200,
            'save_mode' => 'draft',
        ],
    ]);

    add_settings_section('seo_push_section_general', 'הגדרות כלליות', '__return_false', 'seo-push-settings');

    add_settings_field('seo_push_keywords', 'מילות מפתח יעד', 'seo_push_field_keywords', 'seo-push-settings', 'seo_push_section_general');
    add_settings_field('seo_push_tone', 'טון כתיבה', 'seo_push_field_tone', 'seo-push-settings', 'seo_push_section_general');
    add_settings_field('seo_push_target_words', 'אורך יעד (מילים)', 'seo_push_field_target_words', 'seo-push-settings', 'seo_push_section_general');
    add_settings_field('seo_push_save_mode', 'מצב שמירה', 'seo_push_field_save_mode', 'seo-push-settings', 'seo_push_section_general');
}

function seo_push_sanitize_settings($input) {
    $out = [];
    $out['keywords'] = isset($input['keywords']) ? sanitize_text_field($input['keywords']) : '';
    $tone = isset($input['tone']) ? $input['tone'] : 'professional';
    $out['tone'] = in_array($tone, ['professional','casual'], true) ? $tone : 'professional';
    $out['target_words'] = max(0, (int)($input['target_words'] ?? 1200));
    $mode = isset($input['save_mode']) ? $input['save_mode'] : 'draft';
    $out['save_mode'] = in_array($mode, ['draft','publish','review'], true) ? $mode : 'draft';
    return $out;
}

function seo_push_settings_page() {
    ?>
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
    <?php
}

// ==== Field renderers ====
function seo_push_get_settings() {
    $defaults = ['keywords'=>'','tone'=>'professional','target_words'=>1200,'save_mode'=>'draft'];
    $s = get_option('seo_push_settings', []);
    return wp_parse_args($s, $defaults);
}

function seo_push_field_keywords() {
    $s = seo_push_get_settings();
    printf(
        '<input type="text" name="seo_push_settings[keywords]" value="%s" class="regular-text" placeholder="לדוגמה: פרסום ai, אוטומציה קידום אתרים">',
        esc_attr($s['keywords'])
    );
    echo '<p class="description">ניתן להפריד בפסיקים.</p>';
}

function seo_push_field_tone() {
    $s = seo_push_get_settings();
    ?>
    <select name="seo_push_settings[tone]">
        <option value="professional" <?php selected($s['tone'],'professional'); ?>>ענייני/מקצועי</option>
        <option value="casual" <?php selected($s['tone'],'casual'); ?>>קליל</option>
    </select>
    <?php
}

function seo_push_field_target_words() {
    $s = seo_push_get_settings();
    printf(
        '<input type="number" name="seo_push_settings[target_words]" value="%d" class="small-text" min="0" step="50">',
        (int)$s['target_words']
    );
}

function seo_push_field_save_mode() {
    $s = seo_push_get_settings();
    ?>
    <select name="seo_push_settings[save_mode]">
        <option value="draft"   <?php selected($s['save_mode'],'draft'); ?>>טיוטה</option>
        <option value="publish" <?php selected($s['save_mode'],'publish'); ?>>פרסום מיידי</option>
        <option value="review"  <?php selected($s['save_mode'],'review'); ?>>להגשה לביקורת</option>
    </select>
    <?php
}
