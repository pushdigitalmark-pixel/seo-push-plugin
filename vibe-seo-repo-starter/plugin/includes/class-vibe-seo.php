<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_SEO {

    public static function init() {
        add_action('wp_head', [__CLASS__, 'output_meta'], 5);
        add_action('wp_head', [__CLASS__, 'output_schema'], 20);
    }

    public static function output_meta() {
        if (!is_singular()) return;
        global $post;
        $title = get_post_meta($post->ID, '_vibe_meta_title', true);
        $desc  = get_post_meta($post->ID, '_vibe_meta_description', true);
        if (!$title) $title = get_the_title($post);
        if (!$desc)  $desc  = get_the_excerpt($post) ?: wp_trim_words( wp_strip_all_tags($post->post_content), 30 );
        $canonical = get_permalink($post);

        echo "\n<!-- Vibe SEO Meta -->\n";
        echo '<title>' . esc_html( wp_strip_all_tags($title) ) . "</title>\n";
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

        // OG/Twitter
        $s = Vibe_Helpers::get_settings();
        if ( ! empty($s['og_auto']) ) {
            $image_id = get_post_thumbnail_id($post);
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
            echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
            if ($image_url) echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
            echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        }
        echo "<!-- /Vibe SEO Meta -->\n";
    }

    public static function output_schema() {
        if (!is_singular()) return;
        global $post;
        $type = get_post_type($post);
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => ($type === 'product' ? "Product" : "Article"),
            "headline" => get_the_title($post),
            "datePublished" => get_the_date('c', $post),
            "dateModified"  => get_the_modified_date('c', $post),
            "mainEntityOfPage" => get_permalink($post),
        );
        if ($type === 'product' && class_exists('WooCommerce')) {
            $product = wc_get_product($post);
            if ($product) {
                $schema["name"] = $product->get_name();
                $schema["offers"] = array(
                    "@type" => "Offer",
                    "price" => $product->get_price(),
                    "priceCurrency" => get_woocommerce_currency(),
                    "availability" => $product->is_in_stock() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
                );
            }
        }
        echo "<script type=\"application/ld+json\">" . wp_json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "</script>\n";
    }
}
