<?php
/**
 * Plugin Name: Zúme - Simulator
 * Plugin URI: https://github.com/ZumeProject/zume-simulator
 * Description: Zúme - Simulator is for reporting metrics on the Zume Critical Path.
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
    $plugin_data = get_file_data( __FILE__, [ 'Version' => 'Version', 'Plugin Name' => 'Plugin Name' ], false );
    $plugins['zume-simulator'] = [
        'plugin_url' => trailingslashit( plugin_dir_url( __FILE__ ) ),
        'version' => $plugin_data['Version'] ?? null,
        'name' => $plugin_data['Plugin Name'] ?? null,
    ];
    return $plugins;
});

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class Zume_Simulator {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $is_rest = dt_is_rest();

        if ( $is_rest ) {
            require_once ('api/goals.php');
            require_once ('api/queries.php');
            require_once( 'api/rest-api.php' );
        }

        require_once ('stats/loader.php');
        require_once ('stats-alltime/loader.php');

        require_once ('maps/cluster-1-last100.php');
        require_once ('maps/heatmap.php');
        require_once ('maps/map-2-network-activities.php');
        require_once ('maps/map-3-trainees.php');
        require_once ('maps/map-4-practitioners.php');
        require_once ('maps/map-5-churches.php');

        if ( is_admin() ) {
            require_once( 'admin/admin-menu-and-tabs.php' ); // adds starter admin page and section for plugin
        }


//        if ( strpos( dt_get_url_path(), 'metrics' ) !== false || ( $is_rest && strpos( dt_get_url_path(), 'zume-simulator-metrics' ) !== false ) ){
        require_once( 'charts/loader.php' );
//        }

        $this->i18n();

        if ( is_admin() ) {
            add_filter( 'plugin_row_meta', [ $this, 'plugin_description_links' ], 10, 4 );
        }
    }

    public function plugin_description_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            // You can still use `array_unshift()` to add links at the beginning.

            $links_array[] = '<a href="https://disciple.tools">Disciple.Tools Community</a>'; // @todo replace with your links.
            // @todo add other links here
        }

        return $links_array;
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {
        // add elements here that need to fire on activation
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        // add functions here that need to happen on deactivation
        delete_option( 'dismissed-zume-simulator' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        $domain = 'zume-simulator';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'zume-simulator';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( 'zume_simulator::' . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}


// Register activation hook.
register_activation_hook( __FILE__, [ 'Zume_Simulator', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'Zume_Simulator', 'deactivation' ] );


if ( ! function_exists( 'zume_simulator_hook_admin_notice' ) ) {
    function zume_simulator_hook_admin_notice() {
        global $zume_simulator_required_dt_theme_version;
        $wp_theme = wp_get_theme();
        $current_version = $wp_theme->version;
        $message = "'Disciple.Tools - Zúme Critical Path' plugin requires 'Disciple.Tools' theme to work. Please activate 'Disciple.Tools' theme or make sure it is latest version.";
        if ( $wp_theme->get_template() === 'disciple-tools-theme' ){
            $message .= ' ' . sprintf( esc_html( 'Current Disciple.Tools version: %1$s, required version: %2$s' ), esc_html( $current_version ), esc_html( $zume_simulator_required_dt_theme_version ) );
        }
        // Check if it's been dismissed...
        if ( ! get_option( 'dismissed-zume-simulator', false ) ) { ?>
            <div class="notice notice-error notice-zume-simulator is-dismissible" data-notice="zume-simulator">
                <p><?php echo esc_html( $message );?></p>
            </div>
            <script>
                jQuery(function($) {
                    $( document ).on( 'click', '.notice-zume-simulator .notice-dismiss', function () {
                        $.ajax( ajaxurl, {
                            type: 'POST',
                            data: {
                                action: 'dismissed_notice_handler',
                                type: 'zume-simulator',
                                security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                            }
                        })
                    });
                });
            </script>
        <?php }
    }
}

/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( !function_exists( 'dt_hook_ajax_notice_handler' ) ){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST['type'] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}

/**
 * Plugin Releases and updates
 * @todo Uncomment and change the url if you want to support remote plugin updating with new versions of your plugin
 * To remove: delete the section of code below and delete the file called version-control.json in the plugin root
 *
 * This section runs the remote plugin updating service, so you can issue distributed updates to your plugin
 *
 * @note See the instructions for version updating to understand the steps involved.
 * @link https://github.com/DiscipleTools/zume-simulator/wiki/Configuring-Remote-Updating-System
 *
 * @todo Enable this section with your own hosted file
 * @todo An example of this file can be found in (version-control.json)
 * @todo Github is a good option for delivering static json.
 */
/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
//add_action( 'plugins_loaded', function (){
//    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) || wp_doing_cron() ){
//        // Check for plugin updates
//        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
//            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' )){
//                require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
//            }
//        }
//        if ( class_exists( 'Puc_v4_Factory' ) ){
//            Puc_v4_Factory::buildUpdateChecker(
//                'https://raw.githubusercontent.com/DiscipleTools/zume-simulator/master/version-control.json',
//                __FILE__,
//                'zume-simulator'
//            );
//
//        }
//    }
//} );


    function zume_elements() {
        $items = [
            "training_item_01" => [
                "value" => 2,
                "subtype" => "training_item_01",
                "label" => "God Uses Ordinary People"
            ],
            "training_item_02" => [
                "value" => 2,
                "subtype" => "training_item_02",
                "label" => "Simple Definition of Disciple and Church"
            ],
            "training_item_03" => [
                "value" => 2,
                "subtype" => "training_item_03",
                "label" => "Spiritual Breathing is Hearing and Obeying God"
            ],
            "training_item_04" => [
                "value" => 2,
                "subtype" => "training_item_04",
                "label" => "SOAPS Bible Reading"
            ],
            "training_item_05" => [
                "value" => 2,
                "subtype" => "training_item_05",
                "label" => "Accountability Groups"
            ],
            "training_item_06" => [
                "value" => 2,
                "subtype" => "training_item_06",
                "label" => "Consumer vs Producer Lifestyle"
            ],
            "training_item_07" => [
                "value" => 2,
                "subtype" => "training_item_07",
                "label" => "How to Spend an Hour in Prayer"
            ],
            "training_item_08" => [
                "value" => 2,
                "subtype" => "training_item_08",
                "label" => "Relational Stewardship – List of 100"
            ],
            "training_item_09" => [
                "value" => 2,
                "subtype" => "training_item_09",
                "label" => "The Kingdom Economy"
            ],
            "training_item_10" => [
                "value" => 2,
                "subtype" => "training_item_10",
                "label" => "The Gospel and How to Share It"
            ],
            "training_item_11" => [
                "value" => 2,
                "subtype" => "training_item_11",
                "label" => "Baptism and How To Do It"
            ],
            "training_item_12" => [
                "value" => 2,
                "subtype" => "training_item_12",
                "label" => "Prepare Your 3-Minute Testimony"
            ],
            "training_item_13" => [
                "value" => 2,
                "subtype" => "training_item_13",
                "label" => "Vision Casting the Greatest Blessing"
            ],
            "training_item_14" => [
                "value" => 2,
                "subtype" => "training_item_14",
                "label" => "Duckling Discipleship – Leading Immediately"
            ],
            "training_item_15" => [
                "value" => 2,
                "subtype" => "training_item_15",
                "label" => "Eyes to See Where the Kingdom Isn’t"
            ],
            "training_item_16" => [
                "value" => 2,
                "subtype" => "training_item_16",
                "label" => "The Lord’s Supper and How To Lead It"
            ],
            "training_item_17" => [
                "value" => 2,
                "subtype" => "training_item_17",
                "label" => "Prayer Walking and How To Do It"
            ],
            "training_item_18" => [
                "value" => 2,
                "subtype" => "training_item_18",
                "label" => "A Person of Peace and How To Find One"
            ],
            "training_item_19" => [
                "value" => 2,
                "subtype" => "training_item_19",
                "label" => "The BLESS Prayer Pattern"
            ],
            "training_item_20" => [
                "value" => 2,
                "subtype" => "training_item_20",
                "label" => "Faithfulness is Better Than Knowledge"
            ],
            "training_item_21" => [
                "value" => 2,
                "subtype" => "training_item_21",
                "label" => "3/3 Group Meeting Pattern"
            ],
            "training_item_22" => [
                "value" => 2,
                "subtype" => "training_item_22",
                "label" => "Training Cycle for Maturing Disciples"
            ],
            "training_item_23" => [
                "value" => 2,
                "subtype" => "training_item_23",
                "label" => "Leadership Cells"
            ],
            "training_item_24" => [
                "value" => 2,
                "subtype" => "training_item_24",
                "label" => "Expect Non-Sequential Growth"
            ],
            "training_item_25" => [
                "value" => 2,
                "subtype" => "training_item_25",
                "label" => "Pace of Multiplication Matters"
            ],
            "training_item_26" => [
                "value" => 2,
                "subtype" => "training_item_26",
                "label" => "Always Part of Two Churches"
            ],
            "training_item_27" => [
                "value" => 2,
                "subtype" => "training_item_27",
                "label" => "Coaching Checklist"
            ],
            "training_item_28" => [
                "value" => 2,
                "subtype" => "training_item_28",
                "label" => "Leadership in Networks"
            ],
            "training_item_29" => [
                "value" => 2,
                "subtype" => "training_item_29",
                "label" => "Peer Mentoring Groups"
            ],
            "training_item_30" => [
                "value" => 2,
                "subtype" => "training_item_30",
                "label" => "Four Fields Tool"
            ],
            "training_item_31" => [
                "value" => 2,
                "subtype" => "training_item_31",
                "label" => "Generational Mapping"
            ]
        ];
        return $items;
    }
