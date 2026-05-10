<?php
defined( 'ABSPATH' ) || exit;

// ──────────────────────────────────────────────────────────────────────────
//  Updater bootstrap (plugins_loaded priority 1):
// ──────────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', function() {
    // 1) Load our universal drop-in. Because that file begins with "namespace UUPD\V1;",
    //    both the class and the helper live under UUPD\V1.
    require_once __DIR__ . '/updater.php';

    // 2) Build a single $updater_config array:
    $updater_config = [
    	'vendor'      => 'stingray82',
        'plugin_file' => plugin_basename( __FILE__ ),             // e.g. "simply-static-export-notify/simply-static-export-notify.php"
		'slug'        => 'wp-waf-manager-fork',
		'name'        => 'WP WAF Manager',       // human‐readable plugin name
        'version'     => WPWAF_VERSION, // same as the VERSION constant above             // your secret key for private updater
        'server'      => 'https://raw.githubusercontent.com/stingray82/wpwafmanager/release/wpwaf-manager-fork/uupd/index.json',
    ];

    // 3) Call the helper in the UUPD\V2 namespace:
    \RUP\Updater\Updater_V2::register( $updater_config );
}, 20 );
