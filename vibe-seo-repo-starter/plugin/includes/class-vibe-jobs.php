<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Jobs {
    public static function init(){}

    public static function table($suffix){
        global $wpdb;
        return $wpdb->prefix . 'vibe_' . $suffix;
    }

    public static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $sql1 = "CREATE TABLE IF NOT EXISTS ". self::table('jobs') ." (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            job_uid VARCHAR(64) NOT NULL,
            type VARCHAR(32) NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            status VARCHAR(16) NOT NULL DEFAULT 'queued',
            payload LONGTEXT NULL,
            result LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY job_uid (job_uid),
            KEY post_id (post_id)
        ) $charset_collate;";

        $sql2 = "CREATE TABLE IF NOT EXISTS ". self::table('audit_log') ." (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            change_json LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        dbDelta($sql1);
        dbDelta($sql2);
    }

    public static function drop_tables() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS ". self::table('jobs'));
        $wpdb->query("DROP TABLE IF EXISTS ". self::table('audit_log'));
    }

    public static function enqueue($type, $post_id, $payload) {
        global $wpdb;
        $uid = wp_generate_uuid4();
        $row = array(
            'job_uid' => $uid,
            'type'    => $type,
            'post_id' => (int)$post_id,
            'status'  => 'queued',
            'payload' => wp_json_encode($payload),
            'created_at' => current_time('mysql'),
            'updated_at' => null
        );
        $wpdb->insert( self::table('jobs'), $row );
        self::call_cloud($uid, $type, $post_id, $payload);
    }

    private static function call_cloud($uid, $type, $post_id, $payload) {
        $settings = Vibe_Helpers::get_settings();
        $base = rtrim($settings['api_base_url'] ?? '', '/');
        if (!$base) return;
        $endpoint = $base . '/jobs/' . $type;
        $body = array(
            'job_uid' => $uid,
            'post_id' => (int)$post_id,
            'site'    => home_url(),
            'webhook' => rest_url('vibe/v1/webhook/job/'.$uid),
            'payload' => $payload,
        );
        $json = wp_json_encode($body);
        $sig  = Vibe_Helpers::hmac_signature($json, $settings['api_secret_key'] ?? '');
        wp_remote_post($endpoint, array(
            'headers' => array(
                'Content-Type'=>'application/json',
                'x-vibe-key' => $settings['api_public_key'] ?? '',
                'x-vibe-signature' => $sig,
            ),
            'body' => $json,
            'timeout' => 60
        ));
    }

    public static function complete_job($uid, $data) {
        global $wpdb;
        // Save results depending on type
        $job = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ". self::table('jobs') ." WHERE job_uid=%s", $uid) );
        if (!$job) return;

        $post_id = (int)$job->post_id;
        $artifacts = $data['artifacts'] ?? array();

        if ($job->type === 'content') {
            if (!empty($artifacts['post_content'])) {
                // Save as draft revision or update content based on settings
                $postarr = array(
                    'ID' => $post_id,
                    'post_content' => $artifacts['post_content'],
                );
                // Create revision-like behavior by saving and setting status based on settings
                $settings = Vibe_Helpers::get_settings();
                $mode = $settings['save_mode'] ?? 'draft';
                if ($mode==='publish') $postarr['post_status'] = 'publish';
                else $postarr['post_status'] = 'draft';
                wp_update_post($postarr);
            }
            if (!empty($artifacts['meta_title'])) update_post_meta($post_id, '_vibe_meta_title', $artifacts['meta_title']);
            if (!empty($artifacts['meta_description'])) update_post_meta($post_id, '_vibe_meta_description', $artifacts['meta_description']);
        }

        if ($job->type === 'image' && !empty($artifacts['image_url'])) {
            // sideload image
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            $image_id = media_sideload_image($artifacts['image_url'], $post_id, null, 'id');
            if (!is_wp_error($image_id)) {
                if (!empty($artifacts['alt'])) update_post_meta($image_id, '_wp_attachment_image_alt', sanitize_text_field($artifacts['alt']));
                $settings = Vibe_Helpers::get_settings();
                if (!empty($settings['image_auto_featured'])) set_post_thumbnail($post_id, $image_id);
            }
        }

        $wpdb->update( self::table('jobs'), array(
            'status' => 'done',
            'result' => wp_json_encode($data),
            'updated_at' => current_time('mysql')
        ), array('job_uid'=>$uid) );
    }
}
