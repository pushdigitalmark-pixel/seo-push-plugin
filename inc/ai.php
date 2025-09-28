<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_vibe_generate_article', 'vibe_generate_article_ajax');

function vibe_generate_article_ajax(){
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => 'אין הרשאה'), 403);
    }
    check_ajax_referer('vibe_generate_article');

    $post_id  = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    $keywords = isset($_POST['keywords']) ? sanitize_text_field($_POST['keywords']) : '';
    if (!$post_id) wp_send_json_error(array('message'=>'post_id חסר'), 400);

    // פרמטרים תוכניים (בלי מפתח)
    $s = function_exists('seo_push_get_settings') ? seo_push_get_settings() : array();
    $tone         = isset($s['tone']) ? $s['tone'] : 'professional';
    $target_words = isset($s['target_words']) ? (int)$s['target_words'] : 1200;

    // *** המפתח מגיע רק מהשרת (wp-config.php או משתנה סביבה) ***
    $api_key = defined('VIBE_OPENAI_KEY') ? VIBE_OPENAI_KEY : getenv('VIBE_OPENAI_KEY');
    if (empty($api_key)) {
        wp_send_json_error(array('message'=>'מפתח OpenAI חסר (VIBE_OPENAI_KEY). הגדר ב-wp-config.php.'), 400);
    }

    $prompt = "אתה כותב תוכן SEO בעברית בסגנון ".($tone==='casual'?'קליל':'ענייני/מקצועי').". ".
              "כתוב מאמר בעברית על: {$keywords}. יעד ~{$target_words} מילים, כולל H2/H3, רשימות, FAQ וסיכום.";

    $endpoint = 'https://api.openai.com/v1/chat/completions';
    $body = array(
        'model' => 'gpt-4o-mini',
        'messages' => array(
            array('role'=>'system','content'=>'אתה כותב SEO בעברית. שמור על מבנה ברור וכותרות.'),
            array('role'=>'user','content'=>$prompt),
        ),
        'temperature' => 0.7,
    );

    $response = wp_remote_post($endpoint, array(
        'timeout' => 60,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode($body),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message'=>'שגיאת רשת: '.$response->get_error_message()), 500);
    }

    $code = wp_remote_retrieve_response_code($response);
    $json = json_decode(wp_remote_retrieve_body($response), true);

    if ($code < 200 || $code >= 300 || empty($json['choices'][0]['message']['content'])) {
        $msg = isset($json['error']['message']) ? $json['error']['message'] : 'שגיאה מה-API';
        wp_send_json_error(array('message'=>$msg), 500);
    }

    $content_raw = $json['choices'][0]['message']['content'];

    $allowed = array(
        'p'=>array(),'br'=>array(),'strong'=>array(),'em'=>array(),
        'ul'=>array(),'ol'=>array(),'li'=>array(),
        'h1'=>array(),'h2'=>array(),'h3'=>array(),'h4'=>array(),
        'blockquote'=>array(),'code'=>array(),'pre'=>array()
    );
    $content_clean = wp_kses( wpautop($content_raw), $allowed );

    // מחזירים לעורך (לא שומרים בכוח)
    wp_send_json_success(array('content'=>$content_clean));
}
$api_key = defined('VIBE_OPENAI_KEY') ? VIBE_OPENAI_KEY : '';
if (empty($api_key)) {
    wp_send_json_error(['message' => 'מפתח OpenAI חסר (VIBE_OPENAI_KEY). צור Release או הגדר inc/secret.php.'], 400);
}
