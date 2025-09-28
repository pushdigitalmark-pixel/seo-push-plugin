=== Vibe Coding SEO (MVP) ===
Contributors: vibecoding
Tags: seo, ai, content, schema, sitemap, redirects, hebrew
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 0.1.0
License: GPLv2 or later

Hebrew-first SEO plugin with AI webhooks, per-post score, meta/OG/schema, sitemap, redirects. MVP scaffold.

== Features ==
* Meta Title/Description, OG/Twitter
* Article/Product schema (WooCommerce)
* XML Sitemap at /sitemap.xml
* Redirects manager (301/302/410)
* Per-post SEO score (0–100) with basic heuristics
* Admin metabox for keywords/tone/wordcount and AI actions (content/image) – webhooks
* Cloud webhook endpoint: /wp-json/vibe/v1/webhook/job/{id}
* Jobs & audit tables

== Installation ==
1. Upload the ZIP and activate.
2. Go to Vibe SEO → Settings: set API Base URL + keys if using cloud.
3. In post editor side metabox: set focus keywords, click "חשב ציון" to see score.

== Notes ==
This is an MVP scaffold. Production hardening required (error handling, permissions, nonce checks, etc.).
