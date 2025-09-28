<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Redirects {
    public static function init() {
        add_action('template_redirect', [__CLASS__, 'maybe_redirect'], 1);
    }

    public static function maybe_redirect() {
        if ( is_admin() || !empty($_POST) ) return;
        $s = Vibe_Helpers::get_settings();
        $map = $s['redirects'] ?? array();
        if (!$map) return;
        $req = esc_url_raw( home_url( add_query_arg(array(), $GLOBALS['wp']->request) ) );
        $path = '/' . ltrim($GLOBALS['wp']->request ?? '', '/');
        foreach($map as $r) {
            if (!empty($r['from']) && $path === $r['from']) {
                $code = (int)($r['code'] ?? 301);
                if ($code === 410) {
                    status_header(410);
                    exit;
                } else {
                    wp_redirect( $r['to'], $code );
                    exit;
                }
            }
        }
    }
}
