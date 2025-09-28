<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Admin {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'assets']);
        add_action('add_meta_boxes', [__CLASS__, 'register_metabox']);
        add_action('save_post', [__CLASS__, 'save_metabox']);
    }

    public static function menu() {
        add_menu_page(
            __('Vibe SEO','vibe-coding-seo'),
            __('Vibe SEO','vibe-coding-seo'),
            'manage_options',
            'vibe-seo-dashboard',
            [__CLASS__, 'dashboard_page'],
            'dashicons-chart-line',
            58
        );
        add_submenu_page('vibe-seo-dashboard', __('Settings','vibe-coding-seo'), __('Settings','vibe-coding-seo'), 'manage_options', 'vibe-seo-settings', [__CLASS__, 'settings_page']);
        add_submenu_page('vibe-seo-dashboard', __('Redirects','vibe-coding-seo'), __('Redirects','vibe-coding-seo'), 'manage_options', 'vibe-seo-redirects', [__CLASS__, 'redirects_page']);
        add_submenu_page('vibe-seo-dashboard', __('Sitemap','vibe-coding-seo'), __('Sitemap','vibe-coding-seo'), 'manage_options', 'vibe-seo-sitemap', [__CLASS__, 'sitemap_page']);
    }

    public static function assets($hook) {
        wp_enqueue_style('vibeseo-admin', VIBESEO_URL.'assets/admin.css', [], VIBESEO_VERSION);
        wp_enqueue_script('vibeseo-admin', VIBESEO_URL.'assets/admin.js', ['jquery'], VIBESEO_VERSION, true);
        wp_localize_script('vibeseo-admin', 'VibeSEO', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'rest'    => esc_url_raw( rest_url('vibe/v1') ),
            'nonce'   => wp_create_nonce('wp_rest')
        ]);
    }

    public static function dashboard_page() {
        $settings = Vibe_Helpers::get_settings();
        ?>
        <div class="wrap vibeseo-wrap">
            <h1>Vibe Coding SEO – לוח מחוונים</h1>
            <div class="vibeseo-grid">
                <div class="card">
                    <h2><?php esc_html_e('SEO Site Score','vibe-coding-seo'); ?></h2>
                    <p><?php echo esc_html( self::avg_score() ); ?>/100</p>
                </div>
                <div class="card">
                    <h2><?php esc_html_e('Sitemap Status','vibe-coding-seo'); ?></h2>
                    <p><a href="<?php echo home_url('/sitemap.xml'); ?>" target="_blank"><?php echo home_url('/sitemap.xml'); ?></a></p>
                </div>
                <div class="card">
                    <h2><?php esc_html_e('Quick Wins','vibe-coding-seo'); ?></h2>
                    <ul>
                        <li><?php esc_html_e('Missing meta descriptions on some posts','vibe-coding-seo'); ?></li>
                        <li><?php esc_html_e('No internal links in 5 posts','vibe-coding-seo'); ?></li>
                    </ul>
                </div>
            </div>

            <h2><?php esc_html_e('GSC / GA4 KPIs (Basic)','vibe-coding-seo'); ?></h2>
            <p><?php esc_html_e('Connect your accounts in Settings to see data.','vibe-coding-seo'); ?></p>
        </div>
        <?php
    }

    private static function avg_score() {
        global $wpdb;
        $posts = get_posts(['post_type'=>['post','page'],'posts_per_page'=>50,'post_status'=>'publish']);
        if (!$posts) return 0;
        $sum=0;$c=0;
        foreach($posts as $p){
            $s = (int) get_post_meta($p->ID, '_vibe_seo_score', true);
            if ($s>0){ $sum += $s; $c++; }
        }
        return $c>0 ? round($sum/$c) : 0;
    }

    public static function settings_page() {
        if ( isset($_POST['vibe_settings_nonce']) && wp_verify_nonce($_POST['vibe_settings_nonce'], 'vibe_save_settings') ) {
            $data = [
                'api_base_url'   => sanitize_text_field($_POST['api_base_url'] ?? ''),
                'api_public_key' => sanitize_text_field($_POST['api_public_key'] ?? ''),
                'api_secret_key' => sanitize_text_field($_POST['api_secret_key'] ?? ''),
                'default_tone'   => sanitize_text_field($_POST['default_tone'] ?? 'professional'),
                'default_wordcount' => (int)($_POST['default_wordcount'] ?? 1200),
                'default_image_size'=> sanitize_text_field($_POST['default_image_size'] ?? '500x500'),
                'save_mode'      => sanitize_text_field($_POST['save_mode'] ?? 'draft'),
                'parallel_jobs'  => (int)($_POST['parallel_jobs'] ?? 3),
                'og_auto'        => isset($_POST['og_auto']) ? 1 : 0,
                'image_auto_featured' => isset($_POST['image_auto_featured']) ? 1 : 0,
                'license_key'    => sanitize_text_field($_POST['license_key'] ?? ''),
            ];
            Vibe_Helpers::update_settings($data);
            echo '<div class="updated"><p>' . esc_html__('Settings saved.','vibe-coding-seo') . '</p></div>';
        }
        $s = Vibe_Helpers::get_settings();
        ?>
        <div class="wrap vibeseo-wrap">
            <h1><?php esc_html_e('Vibe SEO Settings','vibe-coding-seo'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('vibe_save_settings','vibe_settings_nonce'); ?>
                <table class="form-table">
                    <tr><th>API Base URL</th><td><input type="text" name="api_base_url" value="<?php echo esc_attr($s['api_base_url']); ?>" class="regular-text"></td></tr>
                    <tr><th>API Public Key</th><td><input type="text" name="api_public_key" value="<?php echo esc_attr($s['api_public_key']); ?>" class="regular-text"></td></tr>
                    <tr><th>API Secret Key</th><td><input type="password" name="api_secret_key" value="<?php echo esc_attr($s['api_secret_key']); ?>" class="regular-text"></td></tr>
                    <tr><th><?php esc_html_e('Default Tone','vibe-coding-seo'); ?></th>
                        <td>
                            <select name="default_tone">
                                <option value="professional" <?php selected($s['default_tone'],'professional'); ?>>ענייני/מקצועי</option>
                                <option value="casual" <?php selected($s['default_tone'],'casual'); ?>>קליל</option>
                            </select>
                        </td>
                    </tr>
                    <tr><th><?php esc_html_e('Default Target Word Count','vibe-coding-seo'); ?></th><td><input type="number" name="default_wordcount" value="<?php echo esc_attr($s['default_wordcount']); ?>"></td></tr>
                    <tr><th><?php esc_html_e('Default Image Size','vibe-coding-seo'); ?></th><td><input type="text" name="default_image_size" value="<?php echo esc_attr($s['default_image_size']); ?>"></td></tr>
                    <tr><th><?php esc_html_e('Save Mode','vibe-coding-seo'); ?></th>
                        <td>
                            <select name="save_mode">
                                <option value="draft" <?php selected($s['save_mode'],'draft'); ?>>טיוטה</option>
                                <option value="publish" <?php selected($s['save_mode'],'publish'); ?>>פרסום מיידי</option>
                                <option value="review" <?php selected($s['save_mode'],'review'); ?>>להגשה לביקורת</option>
                            </select>
                        </td>
                    </tr>
                    <tr><th><?php esc_html_e('Parallel Jobs','vibe-coding-seo'); ?></th><td><input type="number" name="parallel_jobs" value="<?php echo esc_attr($s['parallel_jobs']); ?>"></td></tr>
                    <tr><th>OG Auto</th><td><label><input type="checkbox" name="og_auto" <?php checked($s['og_auto']); ?>> <?php esc_html_e('Generate OG tags automatically','vibe-coding-seo'); ?></label></td></tr>
                    <tr><th>Auto Featured Image</th><td><label><input type="checkbox" name="image_auto_featured" <?php checked($s['image_auto_featured']); ?>> <?php esc_html_e('Set generated image as featured by default','vibe-coding-seo'); ?></label></td></tr>
                    <tr><th>License Key</th><td><input type="text" name="license_key" value="<?php echo esc_attr($s['license_key']); ?>" class="regular-text"></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function redirects_page() {
        if ( isset($_POST['vibe_redirects_nonce']) && wp_verify_nonce($_POST['vibe_redirects_nonce'], 'vibe_save_redirects') ) {
            $rows = $_POST['redirects'] ?? array();
            $clean = array();
            if (is_array($rows)) {
                foreach($rows as $row) {
                    $from = sanitize_text_field($row['from'] ?? '');
                    $to   = esc_url_raw($row['to'] ?? '');
                    $code = (int)($row['code'] ?? 301);
                    if ($from && $to) $clean[] = compact('from','to','code');
                }
            }
            Vibe_Helpers::update_settings(['redirects'=>$clean]);
            echo '<div class="updated"><p>' . esc_html__('Redirects saved.','vibe-coding-seo') . '</p></div>';
        }
        $s = Vibe_Helpers::get_settings();
        ?>
        <div class="wrap vibeseo-wrap">
            <h1><?php esc_html_e('Redirects','vibe-coding-seo'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('vibe_save_redirects','vibe_redirects_nonce'); ?>
                <table class="widefat fixed">
                    <thead><tr><th>From (path)</th><th>To (URL)</th><th>Code</th></tr></thead>
                    <tbody id="vibe-redirects-body">
                    <?php
                    $rows = $s['redirects'] ?? array();
                    if (!$rows) $rows[] = array('from'=>'/old-page','to'=>home_url('/new-page'), 'code'=>301);
                    foreach($rows as $i=>$r): ?>
                        <tr>
                            <td><input type="text" name="redirects[<?php echo $i;?>][from]" value="<?php echo esc_attr($r['from']); ?>" class="regular-text"></td>
                            <td><input type="text" name="redirects[<?php echo $i;?>][to]" value="<?php echo esc_attr($r['to']); ?>" class="regular-text"></td>
                            <td>
                                <select name="redirects[<?php echo $i;?>][code]">
                                    <option value="301" <?php selected($r['code'],301); ?>>301</option>
                                    <option value="302" <?php selected($r['code'],302); ?>>302</option>
                                    <option value="410" <?php selected($r['code'],410); ?>>410</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p><button type="button" class="button" id="vibe-add-redirect">+ הוסף שורה</button></p>
                <?php submit_button(__('Save Redirects','vibe-coding-seo')); ?>
            </form>
        </div>
        <?php
    }

    public static function sitemap_page() {
        ?>
        <div class="wrap vibeseo-wrap">
            <h1><?php esc_html_e('Sitemap','vibe-coding-seo'); ?></h1>
            <p><?php esc_html_e('Your sitemap is available at','vibe-coding-seo'); ?> <a target="_blank" href="<?php echo home_url('/sitemap.xml'); ?>"><?php echo home_url('/sitemap.xml'); ?></a></p>
        </div>
        <?php
    }

    public static function register_metabox() {
        $screens = apply_filters('vibe_metabox_screens', array('post','page'));
        foreach($screens as $screen){
            add_meta_box('vibe_seo_box', 'Vibe SEO', [__CLASS__, 'metabox_cb'], $screen, 'side', 'high');
        }
        if (class_exists('WooCommerce')) {
            add_meta_box('vibe_seo_box', 'Vibe SEO', [__CLASS__, 'metabox_cb'], 'product', 'side', 'high');
        }
    }

    public static function metabox_cb($post) {
        $keywords = get_post_meta($post->ID, '_vibe_focus_keywords', true);
        if (!is_array($keywords)) $keywords = array();
        $score    = (int) get_post_meta($post->ID, '_vibe_seo_score', true);
        $tone     = get_post_meta($post->ID, '_vibe_tone', true) ?: 'professional';
        $wordc    = (int) get_post_meta($post->ID, '_vibe_wordcount', true) ?: 0;
        $save_mode= get_option('vibe_settings')['save_mode'] ?? 'draft';
        wp_nonce_field('vibe_save_meta','vibe_meta_nonce');
        ?>
        <div class="vibe-box">
            <p><label>מילות מפתח יעד (מופרדות בפסיקים)</label><br>
                <input type="text" name="vibe_focus_keywords" value="<?php echo esc_attr( implode(', ', $keywords) ); ?>" style="width:100%;"></p>
            <p><label>טון כתיבה</label><br>
                <select name="vibe_tone">
                    <option value="professional" <?php selected($tone,'professional'); ?>>ענייני/מקצועי</option>
                    <option value="casual" <?php selected($tone,'casual'); ?>>קליל</option>
                </select>
            </p>
            <p><label>אורך יעד (מילים)</label><br>
                <input type="number" name="vibe_wordcount" value="<?php echo esc_attr($wordc); ?>" min="0"></p>

            <hr>
            <p><strong>ציון SEO:</strong> <span class="vibe-score <?php echo self::score_class($score); ?>"><?php echo (int)$score; ?>/100</span></p>
            <p><button class="button" type="button" onclick="document.getElementById('vibe_recalc_score').value='1'; this.form.submit();">חשב ציון</button></p>

            <hr>
            <p><strong>יצירת תוכן/תמונה (AI):</strong></p>
            <p>
                <button class="button button-primary" name="vibe_ai_generate" value="content">צור תוכן (טיוטה)</button>
                <button class="button" name="vibe_ai_generate" value="image">צור תמונה</button>
            </p>
            <p style="font-size:11px;color:#666">שמירה כברירת מחדל: <?php echo esc_html($save_mode); ?></p>
        </div>
        <?php
    }

    private static function score_class($s){
        if ($s >= 80) return 'green';
        if ($s >= 50) return 'yellow';
        return 'red';
    }

    public static function save_metabox($post_id) {
        if ( ! isset($_POST['vibe_meta_nonce']) || ! wp_verify_nonce($_POST['vibe_meta_nonce'], 'vibe_save_meta') ) return;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

        if ( isset($_POST['vibe_focus_keywords']) ) {
            $kw = Vibe_Helpers::sanitize_keywords( $_POST['vibe_focus_keywords'] );
            update_post_meta($post_id, '_vibe_focus_keywords', $kw);
        }
        if ( isset($_POST['vibe_tone']) ) {
            update_post_meta($post_id, '_vibe_tone', sanitize_text_field($_POST['vibe_tone']));
        }
        if ( isset($_POST['vibe_wordcount']) ) {
            update_post_meta($post_id, '_vibe_wordcount', (int)$_POST['vibe_wordcount']);
        }
        if ( isset($_POST['vibe_recalc_score']) ) {
            $score = Vibe_Score::calculate($post_id);
            update_post_meta($post_id, '_vibe_seo_score', $score);
        }
        if ( isset($_POST['vibe_ai_generate']) ) {
            $action = sanitize_text_field($_POST['vibe_ai_generate']);
            if ($action === 'content') {
                Vibe_Jobs::enqueue('content', $post_id, array());
            } elseif ($action === 'image') {
                Vibe_Jobs::enqueue('image', $post_id, array());
            }
        }
    }
}
