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
