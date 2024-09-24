<?php
/*
Plugin Name: WP Migration Plugin
Description: Plugin to migrate posts and pages between WordPress installations.
Version: 1.0
Author: Daniel Cubas
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! session_id() ) {
    session_start();
}

define( 'WPMIG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPMIG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once WPMIG_PLUGIN_DIR . 'admin/admin-page.php';
require_once WPMIG_PLUGIN_DIR . 'includes/export.php';
require_once WPMIG_PLUGIN_DIR . 'includes/import.php';

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'wpmig_activate' );
register_deactivation_hook( __FILE__, 'wpmig_deactivate' );

function wpmig_activate() {
    // Actions to perform on plugin activation.
}

function wpmig_deactivate() {
    // Actions to perform on plugin deactivation.
}

function wpmig_handle_post_requests() {
    if ( isset( $_POST['export_content'] ) ) {
        wpmig_export_content();
    }

    if ( isset( $_POST['import_content'] ) ) {
        wpmig_import_content();
    }
}

add_action( 'admin_post_wpmig_export_content', 'wpmig_handle_post_requests' );
add_action( 'admin_post_wpmig_import_content', 'wpmig_handle_post_requests' );

?>
