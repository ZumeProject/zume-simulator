<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Stats_Endpoints
{
    public $permissions = ['manage_dt'];
    public $namespace = 'zume_simulator/v1';
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }
    }
    public function add_api_routes() {
        $namespace = $this->namespace;

        register_rest_route(
            $namespace, '/user_progress', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'user_progress' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );
        register_rest_route(
            $namespace, '/reset_tracking', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'reset_tracking' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );
    }

    public function user_progress( WP_REST_Request $request ) {
        global $wpdb;
        $params = dt_recursive_sanitize_array( $request->get_params() );
        $user_id = (int) $params['user_id'];
        $sql = $wpdb->prepare( "SELECT id, user_id, type, subtype, label FROM wp_dt_reports WHERE user_id = %s AND post_type = 'zume' ORDER BY time_end DESC", $user_id );
        return $wpdb->get_results( $sql, ARRAY_A );
    }
    public function reset_tracking( WP_REST_Request $request ) {
        global $wpdb;
        return $wpdb->query( "DELETE FROM $wpdb->dt_reports WHERE post_type = 'zume';" );
    }
    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }

}
Zume_Simulator_Stats_Endpoints::instance();
