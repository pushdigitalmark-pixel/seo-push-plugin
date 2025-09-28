<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Score {

    public static function init(){}

    public static function calculate($post_id) {
        $post = get_post($post_id);
        if (!$post) return 0;
        $keywords = get_post_meta($post_id, '_vibe_focus_keywords', true);
        if (!is_array($keywords)) $keywords = array();
        $kw = isset($keywords[0]) ? mb_strtolower($keywords[0]) : '';

        $title = get_the_title($post);
        $slug  = basename(get_permalink($post));
        $desc  = get_post_meta($post_id, '_vibe_meta_description', true);
        $h1    = Vibe_Helpers::get_post_h1($post);
        $content = wp_strip_all_tags($post->post_content);

        $score = 0;

        // 25% Title/H1/Slug/Meta
        $match = 0;
        if ($kw && stripos($title, $kw) !== false) $match += 25;
        if ($kw && stripos($h1, $kw) !== false)   $match += 25;
        if ($kw && stripos($slug, sanitize_title($kw)) !== false) $match += 25;
        if ($kw && stripos((string)$desc, $kw) !== false) $match += 25;
        $score += round(0.25 * ($match/25*25) / 100 * 100);

        // 15% Structure H2/H3/Lists
        $struct = 0;
        $h2 = preg_match_all('/<h2[^>]*>/i', $post->post_content);
        $h3 = preg_match_all('/<h3[^>]*>/i', $post->post_content);
        if ($h2>=2) $struct += 50;
        if ($h3>=2) $struct += 30;
        if (stripos($post->post_content, '<ul')!==false || stripos($post->post_content, '<ol')!==false) $struct += 20;
        $score += round(0.15 * ($struct/100*100) / 100 * 100);

        // 10% Density
        $density = 0;
        if ($kw) {
            $words = str_word_count( wp_strip_all_tags($post->post_content) );
            $count = substr_count( mb_strtolower($content), $kw );
            if ($words > 0) {
                $ratio = ($count / max(1,$words)) * 100;
                if ($ratio >= 1 && $ratio <= 2) $density = 100;
                elseif ($ratio < 1) $density = max(0, $ratio*100);
                else $density = max(0, 100 - ($ratio-2)*100);
            }
        }
        $score += round(0.10 * $density);

        // 10% Length + coverage (approx by length)
        $len = mb_strlen($content);
        $lenScore = ($len >= 1200 ? 100 : ($len/1200*100));
        $score += round(0.10 * $lenScore);

        // 10% links
        $links = 0;
        if (preg_match_all('/<a\s[^>]*href=/i', $post->post_content, $m)) {
            // Count internal/external heuristic
            $links = min(100, count($m[0]) * 20);
        }
        $score += round(0.10 * $links);

        // 5% images+alt
        $imgscore = 0;
        if (preg_match_all('/<img[^>]*>/i', $post->post_content, $mm)) {
            $count=0;$withAlt=0;
            foreach($mm[0] as $img){
                $count++;
                if (stripos($img,'alt=')!==false) $withAlt++;
            }
            if ($count>0) $imgscore = round(($withAlt/$count)*100);
        }
        $score += round(0.05 * $imgscore);

        // 5% readability (heuristic: avg sentence length)
        $sentences = preg_split('/[\.!\?]+/u', $content);
        $sentences = array_filter(array_map('trim',$sentences));
        $avg = 0;
        if ($sentences) {
            $sum=0;
            foreach($sentences as $s){ $sum += str_word_count($s); }
            $avg = $sum / max(1,count($sentences));
        }
        $readability = 100 - min(100, max(0, ($avg-20)*5)); // penalize if avg > 20 words
        $score += round(0.05 * $readability);

        // 10% Lighthouse basic on-page - placeholder: check basic things
        $basic = 0;
        if ($title && $desc) $basic += 40;
        if (has_post_thumbnail($post)) $basic += 30;
        if (is_ssl()) $basic += 30;
        $score += round(0.10 * $basic);

        // 10% schema valid (assume present from Vibe_SEO->output_schema)
        $score += 10; // optimistic baseline for MVP

        return max(0, min(100, $score));
    }
}
