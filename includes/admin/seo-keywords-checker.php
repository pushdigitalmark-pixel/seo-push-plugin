<?php
/**
 * SEO Keywords Checker Admin Page
 */

if (!defined('ABSPATH')) exit;

// Add admin menu page
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        __('SEO Keywords Checker', 'seo-push-plugin'),
        __('SEO Keywords Checker', 'seo-push-plugin'),
        'manage_options',
        'seo-keywords-checker',
        'seo_keywords_checker_admin_page'
    );
});

function seo_keywords_checker_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('SEO Keywords Checker', 'seo-push-plugin'); ?></h1>
        <form id="seo-keywords-checker-form">
            <label for="seo_keywords"><?php _e('Enter keywords (one per line):', 'seo-push-plugin'); ?></label><br>
            <textarea id="seo_keywords" name="seo_keywords" rows="8" cols="40"></textarea><br><br>
            <button type="button" class="button button-primary" id="seo-keywords-check-btn"><?php _e('Check Keyword Locations', 'seo-push-plugin'); ?></button>
        </form>
        <div id="seo-keywords-results" style="margin-top:30px;"></div>
    </div>
    <script>
    document.getElementById('seo-keywords-check-btn').addEventListener('click', function() {
        const keywords = document.getElementById('seo_keywords').value;
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=seo_keywords_check&keywords=' + encodeURIComponent(keywords)
        })
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data && data.results) {
                html += '<table class="widefat"><thead><tr><th>Keyword</th><th>Title</th><th>Description</th><th>H1</th><th>H2</th><th>Alt</th><th>Content</th></tr></thead><tbody>';
                data.results.forEach(row => {
                    html += '<tr>';
                    html += `<td>${row.keyword}</td>`;
                    html += `<td>${row.in_title ? '✅' : '❌'}</td>`;
                    html += `<td>${row.in_description ? '✅' : '❌'}</td>`;
                    html += `<td>${row.in_h1 ? '✅' : '❌'}</td>`;
                    html += `<td>${row.in_h2 ? '✅' : '❌'}</td>`;
                    html += `<td>${row.in_alt ? '✅' : '❌'}</td>`;
                    html += `<td>${row.in_content ? '✅' : '❌'}</td>`;
                    html += '</tr>';
                });
                html += '</tbody></table>';
            } else {
                html = '<p>No results.</p>';
            }
            document.getElementById('seo-keywords-results').innerHTML = html;
        });
    });
    </script>
    <?php
}

// Ajax handler
add_action('wp_ajax_seo_keywords_check', function() {
    $keywords_raw = isset($_POST['keywords']) ? sanitize_textarea_field($_POST['keywords']) : '';
    $keywords = array_filter(array_map('trim', explode("\n", $keywords_raw)));
    $results = [];

    // Get current post/page if in edit screen, else use front page
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : get_option('page_on_front');
    $post = get_post($post_id);

    $title = $post ? $post->post_title : get_bloginfo('name');
    $content = $post ? $post->post_content : '';
    $description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    if (!$description) $description = get_bloginfo('description');

    $h1s = [];
    $h2s = [];
    $alts = [];
    if ($content) {
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $content, $h1_matches);
        $h1s = $h1_matches[1];
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $content, $h2_matches);
        $h2s = $h2_matches[1];
        preg_match_all('/<img[^>]+alt="([^"]*)"/i', $content, $alt_matches);
        $alts = $alt_matches[1];
    }

    foreach ($keywords as $kw) {
        $kw_lc = mb_strtolower($kw);
        $res = [
            'keyword' => $kw,
            'in_title' => stripos($title, $kw) !== false,
            'in_description' => stripos($description, $kw) !== false,
            'in_h1' => false,
            'in_h2' => false,
            'in_alt' => false,
            'in_content' => stripos(strip_tags($content), $kw) !== false,
        ];
        foreach ($h1s as $h) if (stripos($h, $kw) !== false) { $res['in_h1'] = true; break; }
        foreach ($h2s as $h) if (stripos($h, $kw) !== false) { $res['in_h2'] = true; break; }
        foreach ($alts as $a) if (stripos($a, $kw) !== false) { $res['in_alt'] = true; break; }
        $results[] = $res;
    }

    wp_send_json(['results' => $results]);
});
