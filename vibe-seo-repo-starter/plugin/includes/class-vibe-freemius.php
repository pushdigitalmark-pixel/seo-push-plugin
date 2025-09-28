<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Freemius {

    public static function init() {
        // Load Freemius SDK if present (safe-guarded)
        $sdk = VIBESEO_DIR . 'freemius/start.php';
        if ( file_exists( $sdk ) ) {
            require_once $sdk;

            if ( ! function_exists( 'vibe_fs' ) ) {
                function vibe_fs() {
                    global $vibe_fs;
                    if ( ! isset( $vibe_fs ) ) {
                        $vibe_fs = fs_dynamic_init( array(
                            'id'               => 'YOUR_FREEMIUS_ID',
                            'slug'             => 'vibe-coding-seo',
                            'type'             => 'plugin',
                            'public_key'       => 'pk_YOUR_PUBLIC_KEY',
                            'is_premium'       => true,
                            'has_paid_plans'   => true,
                            'is_org_compliant' => false,
                            'menu'             => array(
                                'slug'        => 'vibe-seo-dashboard',
                                'first-path'  => 'admin.php?page=vibe-seo-dashboard',
                                'support'     => true,
                                'contact'     => true,
                            ),
                        ) );
                    }
                    return $vibe_fs;
                }
                // Init Freemius only once
                vibe_fs();
                do_action( 'vibe_fs_loaded' );
            }
        } else {
            // SDK missing: show a notice only to admins on settings page
            add_action('admin_notices', function(){
                if ( ! current_user_can('manage_options') ) return;
                if ( ! isset($_GET['page']) || strpos($_GET['page'], 'vibe-seo') === false ) return;
                echo '<div class="notice notice-warning"><p>'
                    . esc_html__('Freemius SDK not found. To enable licensing, add the Freemius SDK under /freemius/ and update YOUR_FREEMIUS_ID / PUBLIC KEY in includes/class-vibe-freemius.php', 'vibe-coding-seo')
                    . '</p></div>';
            });
        }
    }
}
