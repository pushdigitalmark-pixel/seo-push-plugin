<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Sitemap {
    public static function init() {
        add_action('init', [__CLASS__, 'rewrite']);
        add_action('template_redirect', [__CLASS__, 'render']);
        
    }

    public static function rewrite() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?vibe_sitemap=1', 'top');
        add_rewrite_tag('%vibe_sitemap%', '([0-9]+)');
    }

    public static function render() {
        if ( get_query_var('vibe_sitemap') ) {
            header('Content-Type: application/xml; charset=UTF-8');
            echo self::generate_xml();
            exit;
        }
    }

    private static function generate_xml() {
        $urls = array();
        $posts = get_posts(array('post_type'=>['post','page'],'posts_per_page'=>1000,'post_status'=>'publish'));
        foreach($posts as $p) {
            $urls[] = array('loc'=>get_permalink($p), 'lastmod'=>get_the_modified_date('c',$p));
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach($urls as $u){
            $xml .= '<url><loc>'.esc_url($u['loc']).'</loc><lastmod>'.esc_html($u['lastmod']).'</lastmod></url>'."\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }
}
