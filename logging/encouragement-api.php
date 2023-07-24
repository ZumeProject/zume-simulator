<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_System_Encouragement_API
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
            $namespace, '/user_encouragement', [
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

        $list = [
            '' => [
                'cta' => [ '[[Not Configured}}' ],
                'time' => [ '[[Not Configured}}' ],
                'reset' => [ '[[Not Configured}}' ],
            ],
            'set1' => [
                'cta' => [ '[[Not Configured}}' ],
                'time' => [ '[[Not Configured}}' ],
                 'reset' => [ '[[Not Configured}}' ],
             ],
             'set2' => [
                'cta' => [
                    'Make a Plan',
                    'Request Coach',
                    'Join Online Training'
                ],
                'time' => [
                    '1 day after event',
                    '2 days after event',
                    '3 days after event',
                    '4 days after event',
                    '5 days after event',
                    '6 days after event',
                    '7 days after event',
                    '2 weeks after event',
                    '3 weeks after event',
                    '4 weeks after event',
                    '2 months after event',
                    '3 months after event'
                ],
                'reset' => [
                    'Plan created'
                ],
            ],
            'set3' => [
                'cta' => [
                    'Request Coach',
                    'Set Profile',
                    'Invite Friends'
                ],
                'time' => [
                    '1 day before planned training',
                    '1 days after planned training',
                    '2 weeks after event with no checkin',
                    '3 weeks after event with no checkin',
                    '4 weeks after event with no checkin',
                    '5 weeks after event with no checkin',
                    '6 weeks after event with no checkin',
                ],
                'reset' => [
                    'Training checkins'
                ],
            ],
            'set4' => [
                'cta' => [
                    'Request Coach',
                    'Set Profile',
                    'Completed 3-Month Plan',
                    'Report as Practitioner'
                ],
                'time' => [
                    '1 week after completed training',
                    '2 weeks after completed training',
                    '3 weeks after completed training',
                    '4 weeks after completed training',
                    '5 weeks after completed training',
                    '6 weeks after completed training',
                    '7 weeks after completed training',
                    '8 weeks after completed training',
                    '9 weeks after completed training',
                    '10 weeks after completed training',
                    '11 weeks after completed training',
                    '12 weeks after completed training',
                ],
                'reset' => [
                    'Completed 3-Month Plan',
                    'Makes first practitioner report',
                ],
           ],
           'set5' => [
                'cta' => [
                    'Set Profile',
                ],
                'time' => [
                    'Immediately after event, coach notification',
                    'Immediately after event, challenge to set profile',
                    '1 day after request ??',
                    '2 day after request ?? ',
                    '3 day after request ??',
                ],
                'reset' => [
                    'Coach establishes communication'
                ],
           ],
       ];

       return [
           'cta' => [ '[[Not Configured}}' ],
           'time' => [ '[[Not Configured}}' ],
           'reset' => [ '[[Not Configured}}' ],
       ];
    }
    public function guest( $params ) {

        return [];
    }
}
Zume_System_Encouragement_API::instance();
