<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_System_Profile_API
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
        register_rest_route(
            $this->namespace, '/user_profile', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'request_sorter'],
                'permission_callback' => '__return_true'
            ]
        );
    }
    public function request_sorter( WP_REST_Request $request )
    {
        $params = dt_recursive_sanitize_array($request->get_params());
        global $wpdb;
        $coaching_contact_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM wp_3_postmeta WHERE meta_key = 'trainee_user_id' AND meta_value = %s", $params['user_id'] ), );
        return [
            "profile" => zume_get_user_profile($params['user_id']),
            "stage" => zume_get_user_stage($params['user_id']),
            "coaching_contact_id" => $coaching_contact_id,
        ];
    }
}
Zume_System_Profile_API::instance();
