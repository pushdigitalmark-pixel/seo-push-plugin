<?php
if (!defined('ABSPATH')) exit;

/** תפריט עליון + עמוד הגדרות */
if (!function_exists('seo_push_add_admin_menu')) {
  add_action('admin_menu','seo_push_add_admin_menu');
  function seo_push_add_admin_menu(){
    add_menu_page(
      'SEO Push הגדרות',     // page title
      'SEO Push',            // menu title (סיידבר)
      'manage_options',      // capability
      'seo-push-settings',   // slug
      'seo_push_settings_page', // callback
      'dashicons-chart-line',
      90
    );
  }
}

/** רישום הגדרות ושדות */
if (!function_exists('seo_push_settings_init')) {
  add_action('admin_init','seo_push_settings_init');
  function seo_push_settings_init(){
    register_setting('seo_push_options_group','seo_push_settings',[
      'type'=>'array',
      'sanitize_callback'=>'seo_push_sanitize_settings',
      'default'=>[
        'keywords'=>'',
        'tone'=>'professional',
        'target_words'=>1200,
        'save_mode'=>'draft',
      ],
    ]);

    add_settings_section('seo_push_section_general','הגדרות כלליות','__return_false','seo-push-settings');
    add_settings_field('seo_push_keywords','מילות מפתח יעד','seo_push_field_keywords','seo-push-settings','seo_push_section_general');
    add_settings_field('seo_push_tone','טון כתיבה','seo_push_field_tone','seo-push-settings','seo_push_section_general');
    add_settings_field('seo_push_target_words','אורך יעד (מילים)','seo_push_field_target_words','seo-push-settings','seo_push_section_general');
    add_settings_field('seo_push_save_mode','מצב שמירה','seo_push_field_save_mode','seo-push-settings','seo_push_section_general');
  }
}

if (!function_exists('seo_push_sanitize_settings')) {
  function seo_push_sanitize_settings($input){
    $out = [];
    $out['keywords']     = isset($input['keywords']) ? sanitize_text_field($input['keywords']) : '';
    $tone                = $input['tone'] ?? 'professional';
    $out['tone']         = in_array($tone,['professional','casual'],true) ? $tone : 'professional';
    $out['target_words'] = max(0,(int)($input['target_words'] ?? 1200));
    $mode                = $input['save_mode'] ?? 'draft';
    $out['save_mode']    = in_array($mode,['draft','publish','review'],true) ? $mode : 'draft';
    return $out;
  }
}

if (!function_exists('seo_push_settings_page')) {
  function seo_push_settings_page(){ ?>
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

/** עוזר: שליפת הגדרות */
if (!function_exists('seo_push_get_settings')) {
  function seo_push_get_settings(){
    $defaults = ['keywords'=>'','tone'=>'professional','target_words'=>1200,'save_mode'=>'draft'];
    $s = get_option('seo_push_settings',[]);
    $s = wp_parse_args($s,$defaults);
    // תאימות אחורה לשדה ישן אם קיים:
    if (empty($s['keywords'])) {
      $legacy = get_option('seo_push_keyword','');
      if (!empty($legacy)) $s['keywords']=sanitize_text_field($legacy);
    }
    return $s;
  }
}

/** Renderers */
if (!function_exists('seo_push_field_keywords')) {
  function seo_push_field_keywords(){
    $s = seo_push_get_settings();
    printf('<input type="text" name="seo_push_settings[keywords]" value="%s" class="regular-text" placeholder="לדוגמה: קידום אתרים, אוטומציה"> <p class="description">הפרד בפסיקים.</p>', esc_attr($s['keywords']));
  }
}
if (!function_exists('seo_push_field_tone')) {
  function seo_push_field_tone(){
    $s = seo_push_get_settings(); ?>
    <select name="seo_push_settings[tone]">
      <option value="professional" <?php selected($s['tone'],'professional'); ?>>ענייני/מקצועי</option>
      <option value="casual" <?php selected($s['tone'],'casual'); ?>>קליל</option>
    </select>
  <?php }
}
if (!function_exists('seo_push_field_target_words')) {
  function seo_push_field_target_words(){
    $s = seo_push_get_settings();
    printf('<input type="number" name="seo_push_settings[target_words]" value="%d" class="small-text" min="0" step="50">',(int)$s['target_words']);
  }
}
if (!function_exists('seo_push_field_save_mode')) {
  function seo_push_field_save_mode(){
    $s = seo_push_get_settings(); ?>
    <select name="seo_push_settings[save_mode]">
      <option value="draft"   <?php selected($s['save_mode'],'draft'); ?>>טיוטה</option>
      <option value="publish" <?php selected($s['save_mode'],'publish'); ?>>פרסום מיידי</option>
      <option value="review"  <?php selected($s['save_mode'],'review'); ?>>להגשה לביקורת</option>
    </select>
  <?php }
}
