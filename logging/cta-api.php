<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_System_CTA_API
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
        if (dt_is_rest()) {
            add_action('rest_api_init', [$this, 'add_api_routes']);
            add_filter('dt_allow_rest_access', [$this, 'authorize_url'], 10, 1);
        }
    }

    public function authorize_url($authorized)
    {
        if (isset($_SERVER['REQUEST_URI']) && strpos(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), $this->namespace) !== false) {
            $authorized = true;
        }
        return $authorized;
    }

    public function add_api_routes()
    {
        $namespace = $this->namespace;
        register_rest_route(
            $namespace, '/user_ctas', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'request_sorter'],
                'permission_callback' => '__return_true'
            ]
        );
    }

    public function request_sorter(WP_REST_Request $request)
    {
        $params = dt_recursive_sanitize_array( $request->get_params() );

        if ( is_user_logged_in() ) {
            return $this->user($params);
        } else {
            return $this->guest($params);
        }
    }

    public function user($params)
    {
        if ( ! isset( $params['user_id'] ) ) {
            return new WP_Error( 'no_user_id', 'No user id provided', array( 'status' => 400 ) );
        }
        return self::_get_ctas( $params['user_id'] );
    }
    public static function _get_ctas( $user_id, $log = NULL ) : array
    {

        if ( ! is_null( $log ) ) {
            $log = zume_user_log( $user_id );
        }

        $set = '';

        foreach( $log as $row ) {

        }

        return [
            [
                'label' => 'Register',
                'key' => 'registered',
                'link' => '/set-profile',
            ],
            [
                'label' => 'Get a Coach',
                'key' => 'requested_a_coach',
                'link' => '/invite-friends',
            ],
            [
                'label' => 'Join Online Training',
                'key' => 'joined_online_training',
                'link' => '/create-plan',
            ],

        ];
    }

    public function guest($params)
    {
        return [
            [
                'label' => 'Register',
                'key' => 'registered',
                'link' => '/set-profile',
            ],
            [
                'label' => 'Get a Coach',
                'key' => 'requested_a_coach',
                'link' => '/invite-friends',
            ],
            [
                'label' => 'Join Online Training',
                'key' => 'joined_online_training',
                'link' => '/create-plan',
            ],

        ];
    }

}
Zume_System_CTA_API::instance();
