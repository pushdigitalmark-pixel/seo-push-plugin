<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Helpers {

    public static function get_settings() {
        $s = get_option('vibe_settings', array());
        if (!is_array($s)) $s = array();
        return wp_parse_args($s, array(
            'api_base_url' => '',
            'api_public_key' => '',
            'api_secret_key' => '',
            'default_tone' => 'professional',
            'default_wordcount' => 1200,
            'default_image_size'=> '500x500',
            'save_mode' => 'draft',
            'parallel_jobs'=> 3,
            'og_auto' => 1,
            'image_auto_featured' => 0,
            'license_key' => '',
            'rate_limits' => array(),
            'redirects'   => array(),
            'gsc' => array('connected'=>false),
            'ga4' => array('connected'=>false),
        ));
    }

    public static function update_settings($data) {
        $cur = self::get_settings();
        $merged = array_replace_recursive($cur, $data);
        update_option('vibe_settings', $merged);
        return $merged;
    }

    public static function hmac_signature($payload, $secret) {
        return hash_hmac('sha256', $payload, $secret);
    }

    public static function bool_to_checked($val) {
        return $val ? 'checked' : '';
    }

    public static function sanitize_keywords($str) {
        $arr = array_filter(array_map('trim', explode(',', wp_strip_all_tags($str))));
        return array_values($arr);
    }

    public static function get_post_h1($post) {
        // Try first H1 in content, else post_title
        if (!$post) return '';
        $content = $post->post_content;
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $content, $m)) {
            return wp_strip_all_tags($m[1]);
        }
        return get_the_title($post);
    }
}
