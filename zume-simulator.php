<?php
/**
 * Plugin Name: Zúme - Simulator
 * Plugin URI: https://github.com/ZumeProject/zume-simulator
 * Description: Zúme - Simulator is for reporting metrics on the Zume Simulator.
 * Text Domain: zume-simulator
 * Domain Path: /languages
 * Version:  0.1
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/ZumeProject/zume-simulator
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Gets the instance of the `Zume_Simulator` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
function zume_simulator() {
    $zume_simulator_required_dt_theme_version = '1.19';
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;

    if ( phpversion() < '8.0' ) {
        add_action( 'admin_notices', 'zume_simulator_hook_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        return false;
    }

    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = class_exists( 'Disciple_Tools' );
    if ( $is_theme_dt && version_compare( $version, $zume_simulator_required_dt_theme_version, '<' ) ) {
        add_action( 'admin_notices', 'zume_simulator_hook_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        return false;
    }
    if ( !$is_theme_dt ){
        return false;
    }
    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ){
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }

    return Zume_Simulator::instance();

}
add_action( 'after_setup_theme', 'zume_simulator', 20 );

//register the D.T Plugin
add_filter( 'dt_plugins', function ( $plugins ){
    $plugin_data = get_file_data( __FILE__, [ 'Version' => 'Version', 'Plugin Name' => 'Zume Simulator' ], false );
    $plugins['zume-simulator'] = [
        'plugin_url' => trailingslashit( plugin_dir_url( __FILE__ ) ),
        'version' => $plugin_data['Version'] ?? null,
        'name' => $plugin_data['Plugin Name'] ?? null,
    ];
    return $plugins;
});


class Zume_Simulator {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    private function __construct() {
        require_once( 'api/completions-api.php' );

        if ( dt_is_rest() ) {
            require_once ('api/queries.php');
            require_once( 'api/rest-api.php' );
        }

        require_once( 'charts/loader.php' );

        $this->i18n();

        if ( is_admin() ) {
            add_filter( 'plugin_row_meta', [ $this, 'plugin_description_links' ], 10, 4 );
        }
    }
    public function plugin_description_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            // You can still use `array_unshift()` to add links at the beginning.
            $links_array[] = '<a href="https://disciple.tools">Disciple.Tools Community</a>';
        }
        return $links_array;
    }
    public function i18n() {
        $domain = 'zume-simulator';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }
    public function __toString() {
        return 'zume-simulator';
    }
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }
    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( 'zume_simulator::' . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}


function zume_simulator_selectors() {
    global $wpdb;
    $users = $wpdb->get_results(
        "SELECT ID, display_name
                    FROM $wpdb->users
                    JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id AND $wpdb->usermeta.meta_key = 'wp_capabilities'
                    ORDER BY ID DESC
                    LIMIT 100
                    ", ARRAY_A
    );

    $html = '<select id="user_id" style=" width:150px;">';
    foreach( $users as $user ) {
        $html .= '<option value="' . $user['ID'] . '">' . $user['display_name'] . '</option>';
    }
    $html .= '</select>';

    return $html;
}
function zume_time_selector() {
    $html = '<select id="days_ago" style=" width:150px;">
                <option value="0">today</option>
                <option value="3">3 Days Ago</option>
                <option value="7">7 Days Ago</option>
                <option value="10">10 Days Ago</option>
                <option value="14">14 Days Ago</option>
                <option value="18">18 Days Ago</option>
                <option value="24">24 Days Ago</option>
                <option value="30">30 Days Ago</option>
                <option value="60">60 Days Ago</option>
                <option value="90">90 Days Ago</option>
                <option value="100">100 Days Ago</option>
            </select>';

    return $html;
}
