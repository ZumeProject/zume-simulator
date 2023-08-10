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
    public function request_sorter( WP_REST_Request $request ) {
        $params = dt_recursive_sanitize_array( $request->get_params() );

        if( is_user_logged_in() ) {
            return $this->user( $params );
        } else {
            return $this->guest( $params );
        }
    }
    public function user( $params) {
        $user_id = (int) $params['user_id'] ?? get_current_user_id();

        $location = zume_get_user_location( $user_id, true );

        $log = zume_user_log( $user_id );

        return [
            'profile' => self::_get_profile( $user_id ),
            'location' => $location,
            'stage' => zume_get_stage( $user_id, $log ),
        ];
    }
    public function guest( $params ) {

        $location = self::_get_location( $params );

        return [
            'profile' => [
                'name' => 'Guest',
                'user_id' => 0,
                'contact_id' => 0,
                'language' => 'en',
                'location' => $location,
            ],
            'state' => [
                'stage' => 0,
                'has_coach' => false,
                'has_set_profile' => false,
                'has_invited_friends' => false,
                'has_a_plan' => false,
                'has_3_month_plan' => false,
                'has_affinity_hub' => false,
            ],
            'encouragements' => [
                [
                    'label' => 'Set Profile',
                    'key' => 'set_profile',
                    'link' => '/set-profile',
                ],
                [
                    'label' => 'Invite Friends',
                    'key' => 'invited_friends',
                    'link' => '/invite-friends',
                ],
                [
                    'label' => 'Create Plan',
                    'key' => 'plan_created',
                    'link' => '/create-plan',
                ],
                [
                    'label' => 'Start Training',
                    'key' => 'start_training',
                    'link' => '/start-training',
                ],
            ],
            'completions' => []
        ];
    }
    public static function _get_profile( $user_id ) {

        $contact_id = zume_get_contact_id( $user_id );

        if ( $contact_id ) {
            $contact = DT_Posts::get_post( 'contacts', (int) $contact_id, false, false, true );
            $name = $contact['name'] ?? '';
        } else {
            $user = get_user_by( 'ID', $user_id );
            $name = $user->display_name;
        }

        return [
            'name' => $name,
            'user_id' => $user_id,
            'contact_id' => $contact_id,
            'language' => 'en',
        ];
    }
    public static function _get_completions( $user_id, $log = NULL ) {
        if ( ! is_null( $log ) ) {
            $log = zume_user_log( $user_id );
        }

        $data = [];

        foreach( $log as $index => $value ) {
            $data[$value['log_key']] = true ;
        }

        return $data;
    }
}
Zume_System_Profile_API::instance();
