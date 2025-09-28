<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Rest {
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'routes']);
    }

    public static function routes() {
        // Webhook receiver (cloud -> WP)
        register_rest_route('vibe/v1', '/webhook/job/(?P<id>[\w\-]+)', array(
            array(
                'methods'  => 'POST',
                'callback' => [__CLASS__, 'webhook_job'],
                'permission_callback' => '__return_true',
            ),
        ));

        // Enqueue job (admin UI -> WP -> cloud)
        register_rest_route('vibe/v1', '/enqueue', array(
            array(
                'methods'  => 'POST',
                'callback' => [__CLASS__, 'enqueue_job'],
                'permission_callback' => function() { return current_user_can('edit_posts'); },
                'args' => array(
                    'post_id' => array('required'=>true, 'type'=>'integer'),
                    'type'    => array('required'=>true, 'type'=>'string'),
                    'payload' => array('required'=>false),
                ),
            ),
        ));
    }

    public static function enqueue_job( WP_REST_Request $req ) {
        $post_id = (int) $req->get_param('post_id');
        $type    = sanitize_text_field( $req->get_param('type') );
        $payload = $req->get_param('payload');
        if ( ! in_array( $type, array('content','image'), true ) ) {
            return new WP_REST_Response(array('ok'=>false,'error'=>'bad_type'), 400);
        }
        if ( ! get_post($post_id) ) {
            return new WP_REST_Response(array('ok'=>false,'error'=>'bad_post'), 404);
        }
        Vibe_Jobs::enqueue($type, $post_id, is_array($payload)?$payload:array());
        return array('ok'=>true);
    }

    public static function webhook_job( WP_REST_Request $req ) {
        $id = sanitize_text_field($req['id']);
        $payload = $req->get_body();
        $sig = $req->get_header('x-vibe-signature');
        $settings = Vibe_Helpers::get_settings();
        $expected = Vibe_Helpers::hmac_signature($payload, $settings['api_secret_key'] ?? '');
        if (!$sig || !hash_equals($expected, $sig)) {
            return new WP_REST_Response(array('ok'=>false,'error'=>'invalid_signature'), 403);
        }
        $data = json_decode($payload, true);
        if (!$data) return new WP_REST_Response(array('ok'=>false,'error'=>'bad_json'), 400);

        Vibe_Jobs::complete_job($id, $data);
        return array('ok'=>true);
    }
}
