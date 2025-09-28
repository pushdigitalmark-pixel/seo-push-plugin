<?php
if ( ! defined('ABSPATH') ) exit;

function vibe_seo_activate() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    $jobs = $wpdb->prefix . 'vibe_jobs';
    $sql1 = "CREATE TABLE IF NOT EXISTS $jobs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(32) NOT NULL,
        post_id BIGINT UNSIGNED,
        status VARCHAR(16) DEFAULT 'pending',
        payload LONGTEXT,
        result LONGTEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    $audit = $wpdb->prefix . 'vibe_audit_log';
    $sql2 = "CREATE TABLE IF NOT EXISTS $audit (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED,
        user_id BIGINT UNSIGNED,
        change_json LONGTEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql1);
    dbDelta($sql2);
}
