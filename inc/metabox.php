// בראשי, אחרי ה־define:
require_once VIBE_CODING_SEO_PLUGIN_PATH . 'inc/activator.php';
require_once VIBE_CODING_SEO_PLUGIN_PATH . 'inc/metabox.php';
require_once VIBE_CODING_SEO_PLUGIN_PATH . 'inc/rest.php';

// הפעלה חד-פעמית לטבלאות:
register_activation_hook(__FILE__, 'vibe_seo_activate');
