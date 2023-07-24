<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_System_User_Location_API
{
    public $namespace = 'zume_system/v1';
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if ( dt_is_rest()) {
            add_action('rest_api_init', [$this, 'add_api_routes']);
            add_filter('dt_allow_rest_access', [$this, 'authorize_url'], 10, 1);
        }
    }
    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }

    public function add_api_routes()
    {
        $namespace = $this->namespace;
        register_rest_route(
            $namespace, '/user_location', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'request_sorter'],
                'permission_callback' =>'__return_true'
            ]
        );
    }
    public function request_sorter( WP_REST_Request $request ) {
        $params = dt_recursive_sanitize_array( $request->get_params() );

        if( is_user_logged_in() ) {
            return $this->user( $params );
        } else {
            return $this->guest( $params );
        }
    }
    public function user( $params) {

        return array('lng' => '-119.699', 'lat' => '37.0744', 'level' => 'region', 'label' => 'California, United States', 'grid_id' => '100364453');
    }
    public function guest( $params ) {

        return array('lng' => '-119.699', 'lat' => '37.0744', 'level' => 'region', 'label' => 'California, United States', 'grid_id' => '100364453');
    }
}
Zume_System_User_Location_API::instance();
