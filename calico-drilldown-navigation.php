<?php
/**
 * Plugin Name: Calico Drill Down Menu for Elementor
 * Plugin URI: https://github.com/CalicoDesignCo/calico-drill-down-menu-for-elementor
 * Update URI: https://github.com/CalicoDesignCo/calico-drill-down-menu-for-elementor
 * Description: WordPress menus with icons and sliding, accessible drill-down dropdowns.
 * Version: 1.1.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: elementor
 * Author: Calico Design
 * Text Domain: calico-drilldown-navigation
 * License: GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'CDN_NAV_VERSION', '1.1.0' );
require_once __DIR__ . '/includes/class-cdn-nav-updater.php';
CDN_Nav_Updater::register( __FILE__ );
function cdn_nav_assets() {
    wp_register_style( 'calico-drilldown-navigation', plugins_url( 'assets/navigation.css', __FILE__ ), array(), CDN_NAV_VERSION );
    wp_register_script( 'calico-drilldown-navigation', plugins_url( 'assets/navigation.js', __FILE__ ), array( 'elementor-frontend' ), CDN_NAV_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'cdn_nav_assets' );
add_action( 'elementor/widgets/register', function( $manager ) {
    cdn_nav_assets();
    require_once __DIR__ . '/includes/widget.php';
    $manager->register( new \Calico_Drilldown_Navigation_Widget() );
} );
add_action( 'admin_notices', function() {
    if ( current_user_can( 'activate_plugins' ) && ! did_action( 'elementor/loaded' ) ) {
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'Calico Drill Down Menu for Elementor requires Elementor to be active.', 'calico-drilldown-navigation' ) . '</p></div>';
    }
} );
