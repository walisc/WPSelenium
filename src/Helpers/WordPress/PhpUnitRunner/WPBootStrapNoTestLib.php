<?php

/*
 * Extract of the bootstrap found in the wordpress test library (wordpress-tests-lib/includes/bootstrap.php). We us this
 * file if we can't download the test library
 */


global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $phpmailer, $wp_theme_directories;
define( 'ABSPATH', getenv('WPSELENIUM_WP_SITE_PATH'). '/' );

$_SERVER['SERVER_PROTOCOL'] = '';
require_once ABSPATH . 'wp-config.php';
