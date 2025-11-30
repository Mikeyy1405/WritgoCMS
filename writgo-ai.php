<?php
/**
 * Plugin Name: WritgoCMS AI
 * Plugin URI: https://github.com/Mikeyy1405/WritgoCMS
 * Description: AI-Powered Content Generation and Integrations. Features OpenAI, Claude, Bol.com API integration.
 * Version: 1.0.0
 * Author: WritgoCMS Team
 * Author URI: https://github.com/Mikeyy1405
 * License: GPLv2 or later
 * Text Domain: writgocms
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WRITGO_AI_VERSION', '1.0.0' );
define( 'WRITGO_AI_DIR', plugin_dir_path( __FILE__ ) );
define( 'WRITGO_AI_URL', plugin_dir_url( __FILE__ ) );

// Enqueue Scripts (Frontend)
function writgo_ai_scripts() {
    wp_enqueue_style( 'writgo-ai-style', WRITGO_AI_URL . 'assets/css/style.css', array(), WRITGO_AI_VERSION );
    wp_enqueue_script( 'writgo-ai-main', WRITGO_AI_URL . 'assets/js/main.js', array( 'jquery' ), WRITGO_AI_VERSION, true );

    wp_localize_script( 'writgo-ai-main', 'writgocms', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'writgocms_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'writgo_ai_scripts' );

// Enqueue Scripts (Admin)
function writgo_ai_admin_scripts( $hook ) {
    wp_enqueue_style( 'writgo-ai-admin', WRITGO_AI_URL . 'assets/css/admin.css', array(), WRITGO_AI_VERSION );
    wp_enqueue_script( 'writgo-ai-admin', WRITGO_AI_URL . 'assets/js/admin.js', array( 'jquery' ), WRITGO_AI_VERSION, true );

    wp_localize_script( 'writgo-ai-admin', 'writgocmsAdmin', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'writgocms_admin_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'writgo_ai_admin_scripts' );

// Includes
if ( file_exists( WRITGO_AI_DIR . 'inc/class-bol-api.php' ) ) {
    require_once WRITGO_AI_DIR . 'inc/class-bol-api.php';
}
if ( file_exists( WRITGO_AI_DIR . 'inc/class-ai-integration.php' ) ) {
    require_once WRITGO_AI_DIR . 'inc/class-ai-integration.php';
}
if ( file_exists( WRITGO_AI_DIR . 'inc/admin-settings.php' ) ) {
    require_once WRITGO_AI_DIR . 'inc/admin-settings.php';
}

// Init
function writgo_ai_init() {
    $bol_api_key = get_option( 'writgocms_bol_api_key', '' );
    if ( ! empty( $bol_api_key ) && class_exists( 'WritgoCMS_Bol_API' ) ) {
        WritgoCMS_Bol_API::get_instance();
    }

    $openai_key = get_option( 'writgocms_openai_api_key', '' );
    $claude_key = get_option( 'writgocms_claude_api_key', '' );
    if ( ( ! empty( $openai_key ) || ! empty( $claude_key ) ) && class_exists( 'WritgoCMS_AI_Integration' ) ) {
        WritgoCMS_AI_Integration::get_instance();
    }
}
add_action( 'init', 'writgo_ai_init' );
