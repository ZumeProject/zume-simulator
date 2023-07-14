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
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $is_rest = dt_is_rest();

        if ( $is_rest ) {
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
if ( ! function_exists( 'zume_location_list' ) ) {
    function zume_location_list( $grid_id = null ) {
        $list = array(
            array('lng' => '-119.699','lat' => '37.0744','level' => 'region','label' => 'California, United States','grid_id' => '100364453'),
            array('lng' => '-114.757','lat' => '54.6427','level' => 'region','label' => 'Alberta, Canada','grid_id' => '100041940'),
            array('lng' => '-105.605','lat' => '39.1902','level' => 'region','label' => 'Colorado, United States','grid_id' => '100364539'),
            array('lng' => '-100','lat' => '31','level' => 'region','label' => 'Texas, United States of America','grid_id' => '100366941'),
            array('lng' => '-99.1456','lat' => '19.4194','level' => 'region','label' => 'Ciudad de México, México','grid_id' => '100245639'),
            array('lng' => '-99.1456','lat' => '19.4194','level' => 'region','label' => 'Mexico City, Mexico','grid_id' => '100245639'),
            array('lng' => '-90.5131','lat' => '14.6414','level' => 'region','label' => 'Guatemala, Guatemala','grid_id' => '100132337'),
            array('lng' => '-85.9823','lat' => '35.9886','level' => 'region','label' => 'Tennessee, United States','grid_id' => '100366703'),
            array('lng' => '-79.3897','lat' => '35.5569','level' => 'region','label' => 'North Carolina, United States','grid_id' => '100366162'),
            array('lng' => '-71.5783','lat' => '43.6899','level' => 'region','label' => 'New Hampshire, United States','grid_id' => '100366017'),
            array('lng' => '-3.25','lat' => '37.25','level' => 'region','label' => 'Granada, Spain','grid_id' => '100075268'),
            array('lng' => '7.75','lat' => '10.3333','level' => 'region','label' => 'Kaduna, Nigeria','grid_id' => '100254189'),
            array('lng' => '16.3731','lat' => '48.2083','level' => 'region','label' => 'Wien, Österreich','grid_id' => '100003596'),
            array('lng' => '19.0408','lat' => '47.4983','level' => 'region','label' => 'Budapest, Hungary','grid_id' => '100134485'),
            array('lng' => '32.5764','lat' => '-25.9153','level' => 'region','label' => 'Maputo, Mozambique','grid_id' => '100249267'),
            array('lng' => '32.5764','lat' => '-25.9153','level' => 'region','label' => 'Maputo, Mozambique','grid_id' => '100249533'),
            array('lng' => '36.8172','lat' => '-1.28325','level' => 'region','label' => 'Nairobi, Kenya','grid_id' => '100234685'),
            array('lng' => '42.9545','lat' => '14.7979','level' => 'region','label' => 'Al Hudaydah, Yemen','grid_id' => '100380173'),
            array('lng' => '44.0167','lat' => '13.5667','level' => 'region','label' => 'Ta\'izz, Yemen','grid_id' => '100380435'),
            array('lng' => '44.0333','lat' => '36.1833','level' => 'region','label' => 'Erbil Governorate, Iraq','grid_id' => '100222764'),
            array('lng' => '44.2','lat' => '15.35','level' => 'region','label' => 'Sana\'a, Yemen','grid_id' => '100380228'),
            array('lng' => '44.2','lat' => '15.35','level' => 'region','label' => 'صنعاء اليمن','grid_id' => '100380228'),
            array('lng' => '44.2059','lat' => '15.3539','level' => 'region','label' => 'Sana\'a, Yemen','grid_id' => '100380228'),
            array('lng' => '45.3333','lat' => '14.2667','level' => 'region','label' => 'Al Bayda\', Yemen','grid_id' => '100380143'),
            array('lng' => '74.3142','lat' => '31.5657','level' => 'region','label' => 'Punjab, Pakistan','grid_id' => '100260114'),
            array('lng' => '74.5667','lat' => '42.8667','level' => 'region','label' => 'Бишкек, Киргизия','grid_id' => '100235155'),
            array('lng' => '85.13','lat' => '25.37','level' => 'region','label' => 'Bihar, India','grid_id' => '100219470'),
            array('lng' => '102.51','lat' => '18.14','level' => 'region','label' => 'Vientiane, Laos','grid_id' => '100238955'),
            array('lng' => '104.322','lat' => '15.12','level' => 'region','label' => 'Si Sa Ket, Thailand','grid_id' => '100344410'),
            array('lng' => '112.629','lat' => '31.358','level' => 'region','label' => 'Hubei, People\'s Republic of China','grid_id' => '100052035'),
            array('lng' => '115.138','lat' => '-8.36917','level' => 'region','label' => 'Bali, Indonesia','grid_id' => '100134675'),
            array('lng' => '115.138','lat' => '-8.36917','level' => 'region','label' => 'Bali, Indonesia','grid_id' => '100148999'),
            array('lng' => '121.466','lat' => '25.012','level' => 'region','label' => '台湾新北市','grid_id' => '100352885'),
            array('lng' => '127','lat' => '37.5833','level' => 'region','label' => '서울특별시, 대한민국','grid_id' => '100238808')
        );

        if ( $grid_id ) {
            foreach ( $list as $item ) {
                if ( $item['grid_id'] == $grid_id ) {
                    return $item;
                }
            }
        } else {
            return $list;
        }
    }
}
if ( ! function_exists('zume_location_select_sample' ) ) {
    function zume_location_select_sample() {
        $list = zume_location_list();
        shuffle( $list );

        $html = '<select name="location" id="location" style=" width:200px;">';
        foreach( $list as $item ){
            $html .= '<option value="' . $item['grid_id'] . '">' . $item['label'] . '</option>';
        }
        $html .= '</select>';
        return $html;
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

    $html .= zume_location_select_sample();

    $html .= '<select id="days_ago" style=" width:150px;">
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
