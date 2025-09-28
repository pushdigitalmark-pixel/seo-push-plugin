<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * חישוב ציון בסיסי: נוכחות מילת המפתח בכותרת, בסלאג ובתוכן + אורך תוכן
 * (MVP — אפשר לשפר לפי המשקולות שלך בהמשך)
 */
function vibe_compute_seo_score( $post_id ) {
    $post = get_post($post_id);
    if ( ! $post ) return 0;

    $keywords_raw = get_post_meta($post_id, '_vibe_focus_keywords', true);
    $kw = '';
    if ($keywords_raw) {
        $parts = array_map('trim', explode(',', $keywords_raw));
        $kw = strtolower($parts[0] ?? '');
    }

    $score = 0; $max = 100;

    if ($kw) {
        $title = strtolower($post->post_title);
        $slug  = strtolower($post->post_name);
        $cont  = strtolower( wp_strip_all_tags($post->post_content) );

        // Title/H1/Slug/Meta (פישוט ל-MVP)
        if ( false !== strpos($title, $kw) ) $score += 25;
        if ( false !== strpos($slug,  $kw) ) $score += 15;

        // צפיפות מילת מפתח (מאוד בסיסי)
        $kw_count = substr_count($cont, $kw);
        $words    = max(1, str_word_count($cont, 0));
        $density  = ($kw_count / $words) * 100; // באחוזים בערך
        if ($density >= 0.8 && $density <= 3.0) $score += 10;

        // אורך
        if ($words >= 800) $score += 10;

        // קישורים (חיפוש פשוט)
        if ( strpos($post->post_content, '<a ') !== false ) $score += 10;

        // תמונות + alt (חיפוש פשוט)
        if ( strpos($post->post_content, '<img ') !== false ) $score += 5;
    }

    // מבנה H2/H3 (חיפוש פשוט)
    if ( preg_match_all('/<h2|<h3/i', $post->post_content) >= 2 ) $score += 10;

    return min($score, $max);
}

/**
 * מטפל בכפתור "חשב ציון" מהמטה־בוקס (ללא JS)
 */
add_action('admin_post_vibe_compute_score', function () {
    if ( ! current_user_can('edit_posts') ) wp_die('No permission');
    if ( ! isset($_POST['vibe_seo_meta_nonce']) || ! wp_verify_nonce($_POST['vibe_seo_meta_nonce'], 'vibe_seo_meta') ) wp_die('Bad nonce');

    $post_id = (int)($_POST['post_id'] ?? 0);
    if ( ! $post_id ) wp_die('Missing post_id');

    $score = vibe_compute_seo_score($post_id);
    update_post_meta($post_id, '_vibe_seo_score', (int)$score);

    wp_safe_redirect( get_edit_post_link($post_id, 'url') . '&vibe_score_updated=1' );
    exit;
});
