<?php
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('vibe_seo_activate') ) {
    function vibe_seo_activate( $network_wide = false ) {
        global $wpdb;

        // פונקציה פנימית שיוצרת/משדרגת סכימה לבלוג הנוכחי
        $create_tables = function() use ( $wpdb ) {
            $charset_collate = $wpdb->get_charset_collate();

            $jobs  = $wpdb->prefix . 'vibe_jobs';
            $audit = $wpdb->prefix . 'vibe_audit_log';

            // חשוב: dbDelta דורש את הגדרת ה-KEYs בתוך אותו CREATE
            $sql_jobs = "CREATE TABLE $jobs (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                type VARCHAR(32) NOT NULL,
                post_id BIGINT UNSIGNED NULL,
                status VARCHAR(16) NOT NULL DEFAULT 'pending',
                payload LONGTEXT NULL,
                result LONGTEXT NULL,
                error TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY type_idx (type),
                KEY status_idx (status),
                KEY post_idx (post_id),
                KEY created_idx (created_at)
            ) $charset_collate;";

            $sql_audit = "CREATE TABLE $audit (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id BIGINT UNSIGNED NULL,
                user_id BIGINT UNSIGNED NULL,
                change_json LONGTEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY post_idx (post_id),
                KEY user_idx (user_id),
                KEY created_idx (created_at)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql_jobs );
            dbDelta( $sql_audit );

            // שמירת גרסת סכימה לאופציות
            update_option( 'vibe_seo_db_version', '1.1.0' );
        };

        // תמיכה בהפעלה רשתית (Multisite)
        if ( is_multisite() && $network_wide ) {
            $current_blog_id = get_current_blog_id();
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( (int) $blog_id );
                $create_tables();
            }
            switch_to_blog( $current_blog_id );
        } else {
            $create_tables();
        }
    }
}

/**
 * דוגמת משדרג סכימה – תרוץ בעתיד אם נעלה גרסה
 * קרא לה מ-plugins_loaded למשל, אם הגרסה השתנתה.
 */
if ( ! function_exists('vibe_seo_maybe_upgrade_db') ) {
    function vibe_seo_maybe_upgrade_db() {
        $current = get_option( 'vibe_seo_db_version', '0' );
        if ( version_compare( $current, '1.1.0', '<' ) ) {
            // כאן אפשר לקרוא שוב ל-vibe_seo_activate כדי להריץ dbDelta,
            // או לכתוב מיגרציה ייעודית.
            vibe_seo_activate( is_multisite() && is_network_admin() );
        }
    }
    add_action( 'plugins_loaded', 'vibe_seo_maybe_upgrade_db' );
}
