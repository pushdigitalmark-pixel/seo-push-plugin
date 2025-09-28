<?php
/**
 * Plugin Name: SEO Push Plugin
 * Description: תוסף SEO מתקדם עם כלים לבדיקת מיקומי מילות מפתח ועוד.
 * Version: 1.0.0
 * Author: pushdigitalmark-pixel
 */

if (!defined('ABSPATH')) exit;

// טעינת כלי בדיקת מילות מפתח
require_once plugin_dir_path(__FILE__) . 'includes/admin/seo-keywords-checker.php';

// כאן תוכל להוסיף require_once לקבצים נוספים של התוסף בעתיד
