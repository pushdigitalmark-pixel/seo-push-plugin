<?php
/**
 * Plugin Name: Vibe Coding SEO
 * Plugin URI:  https://push-digital.example/vibe-coding-seo
 * Description: תוסף SEO בעברית: יצירת/אופטימיזציית תוכן עם AI (דרך Webhooks), ניקוד SEO פר-עמוד, Meta/OG/Schema, Sitemap ו-Redirects. MVP.
 * Version:     0.1.3
 * Author:      Vibe Coding
 * Author URI:  https://push-digital.example
 * License:     GPLv2 or later
 * Text Domain: vibe-coding-seo
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VIBESEO_VERSION', '0.1.0' );
define( 'VIBESEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'VIBESEO_URL', plugin_dir_url( __FILE__ ) );
define( 'VIBESEO_SLUG', 'vibe-coding-seo' );

require_once VIBESEO_DIR . 'includes/helpers.php';
require_once VIBESEO_DIR . 'includes/class-vibe-admin.php';
require_once VIBESEO_DIR . 'includes/class-vibe-seo.php';
require_once VIBESEO_DIR . 'includes/class-vibe-redirects.php';
require_once VIBESEO_DIR . 'includes/class-vibe-sitemap.php';
require_once VIBESEO_DIR . 'includes/class-vibe-score.php';
require_once VIBESEO_DIR . 'includes/class-vibe-rest.php';
require_once VIBESEO_DIR . 'includes/class-vibe-freemius.php';
require_once VIBESEO_DIR . 'includes/class-vibe-jobs.php';

/**
 * Activation/Deactivation
 */
register_activation_hook( __FILE__, function() {
    // Create options defaults
    $defaults = array(
        'api_base_url'      => '',
        'api_public_key'    => '',
        'api_secret_key'    => '',
        'default_tone'      => 'professional',
        'default_wordcount' => 1200,
        'default_image_size'=> '500x500',
        'save_mode'         => 'draft',
        'parallel_jobs'     => 3,
        'og_auto'           => 1,
        'image_auto_featured' => 0,
        'license_key'       => '',
        'rate_limits'       => array(
            'free' => array('articles_per_day'=>3,'images_per_day'=>3,'fixes_per_day'=>20),
            'pro'  => array('articles_per_day'=>30,'images_per_day'=>999,'fixes_per_day'=>200)
        ),
        'redirects'         => array(),
        'gsc'               => array('connected'=>false),
        'ga4'               => array('connected'=>false),
    );
    add_option('vibe_settings', $defaults);

    // Create DB tables
    Vibe_Jobs::create_tables();
    flush_rewrite_rules();
});

register_uninstall_hook( __FILE__, 'vibeseo_uninstall' );
function vibeseo_uninstall() {
    // Leave data by default to avoid loss. Uncomment to delete tables/options on uninstall.
    // delete_option('vibe_settings');
    // Vibe_Jobs::drop_tables();
}

/**
 * Init
 */
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'vibe-coding-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    Vibe_Admin::init();
    Vibe_SEO::init();
    Vibe_Redirects::init();
    Vibe_Sitemap::init();
    Vibe_Score::init();
    Vibe_Rest::init();
    Vibe_Jobs::init();
    Vibe_Freemius::init();
});
