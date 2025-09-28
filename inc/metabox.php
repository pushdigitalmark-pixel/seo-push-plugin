// בראשי, אחרי ה־define:
require_once VIBE_CODING_SEO_PLUGIN_PATH . 'inc/activator.php';
require_once VIBE_CODING_SEO_PLUGIN_PATH . 'inc/metabox.php';
require_once VIBE_CODING_SEO_PLUGIN_PATH . 'inc/rest.php';

// הפעלה חד-פעמית לטבלאות:
register_activation_hook(__FILE__, 'vibe_seo_activate');
<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * הוספת מטה־בוקס לפוסטים ומוצרים
 */
add_action('add_meta_boxes', function () {
    $screens = array('post');
    if ( post_type_exists('product') ) $screens[] = 'product';

    foreach ($screens as $screen) {
        add_meta_box(
            'vibe_seo_box',
            'Vibe SEO',
            'vibe_seo_box_html',
            $screen,
            'side',
            'high'
        );
    }
});

/**
 * HTML של המטה־בוקס
 */
function vibe_seo_box_html($post) {
    wp_nonce_field('vibe_seo_meta', 'vibe_seo_meta_nonce');
    $keywords = get_post_meta($post->ID, '_vibe_focus_keywords', true);
    $score    = (int) get_post_meta($post->ID, '_vibe_seo_score', true);
    ?>
    <p>
        <label for="vibe_focus_keywords"><strong>מילות מפתח יעד</strong> (מופרדות בפסיקים)</label><br>
        <input type="text" id="vibe_focus_keywords" name="vibe_focus_keywords"
               value="<?php echo esc_attr($keywords); ?>" class="widefat" />
    </p>

    <p><strong>ציון SEO:</strong> <?php echo $score ? $score . '/100' : '—'; ?></p>

    <p>
        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <?php wp_nonce_field('vibe_seo_meta', 'vibe_seo_meta_nonce'); ?>
            <input type="hidden" name="action" value="vibe_compute_score">
            <input type="hidden" name="post_id" value="<?php echo (int)$post->ID; ?>">
            <button class="button button-primary">חשב ציון</button>
        </form>
    </p>
    <?php
}

/**
 * שמירת מילות מפתח בעת שמירת פוסט
 */
add_action('save_post', function ($post_id) {
    if ( ! isset($_POST['vibe_seo_meta_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['vibe_seo_meta_nonce'], 'vibe_seo_meta') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;

    if ( isset($_POST['vibe_focus_keywords']) ) {
        update_post_meta($post_id, '_vibe_focus_keywords', sanitize_text_field($_POST['vibe_focus_keywords']));
    }
});
