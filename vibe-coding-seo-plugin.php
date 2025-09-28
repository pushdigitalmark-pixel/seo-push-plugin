<?php
/*
Plugin Name: Vibe Coding SEO Plugin
Description: Safe stub to verify activation (no includes).
Version: 1.0.7
Author: Push Digital
Text Domain: vibe-coding-seo-plugin
Requires PHP: 7.4
Requires at least: 5.8
Tested up to: 6.6
*/
if (!defined('ABSPATH')) exit;

add_action('admin_notices', function () {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-success"><p>Vibe SEO: התוסף הופעל (מצב סטאב).</p></div>';
    }
});
