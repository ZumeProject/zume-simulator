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
        global $wpdb;
        if ( ! isset( $params['days_ago'] ) ) {
            $params['days_ago'] = 0;
        }

        // setup vars
        $user_id = (int) $params['user_id'];
        $location = $this->_get_location( $params );
        $days_ago = (int) $params['days_ago'] ?? 0;
        $days_ago_timestamp = time();
        if ( $days_ago > 0 ) {
            $days_ago_timestamp = strtotime( 'Today -'.$days_ago.' days' );
        }
        $has_coach = false;
        $has_set_profile = false;
        $has_invited_friends = false;
        $has_a_plan = false;
        $stage = 0;
        $completions = [];

        // query
        $sql = "SELECT CONCAT( r.type, '_', r.subtype ) as log_key, r.*
                FROM wp_dt_reports r
                WHERE r.user_id = {$user_id}
                AND r.post_type = 'zume'
                AND r.time_end <= {$days_ago_timestamp}
                ORDER BY r.time_end";
        $results = $wpdb->get_results( $sql, ARRAY_A );

        // get contact
        $contact_id = Disciple_Tools_Users::get_contact_for_user($user_id);
        if ( $contact_id ) {
            $contact = DT_Posts::get_post( 'contacts', (int) $contact_id, false, false, true );
        }

        // modify results
        if ( count($results) > 0 ) {
            foreach( $results as $index => $value ) {
                if( $value['value'] > $stage ) {
                    $stage = $value['value'];
                }
                if( $value['subtype'] == 'requested_a_coach' ) {
                    $has_coach = true;
                }
                $results[$index]['timestamp'] = date( 'd-m-Y H:i:s',  $value['timestamp'] );
                $results[$index]['time_end'] = date( 'D j, M Y',  $value['time_end'] );

                if ( isset( $training_items[$value['subtype']]['completed'] ) && ! $training_items[$value['subtype']]['completed'] ) {
                    $training_items[$value['subtype']]['completed'] = true;
                    $training_completed++;
                }
                if( $value['subtype'] == 'set_profile' ) {
                    $has_set_profile = true;
                }
                if( $value['subtype'] == 'invited_friends' ) {
                    $has_invited_friends = true;
                }
                if( $value['subtype'] == 'plan_created' ) {
                    $has_a_plan = true;
                }

                $completions[$value['log_key']] = true ;
            }
        }



        return [
            'profile' => [
                'name' => $contact['name'],
                'user_id' => $user_id,
                'contact_id' => $contact_id,
                'first_event' => $results[0]['timestamp'],
                'last_event' => $results[count($results)-1]['timestamp'],
                'language' => 'en',
                'location' => $location,
            ],
            'state' => [
                'stage' => $stage,
                'has_coach' => $has_coach,
                'has_set_profile' => $has_set_profile,
                'has_invited_friends' => $has_invited_friends,
                'has_a_plan' => $has_a_plan,
                'has_3_month_plan' => false,
                'has_affinity_hub' => false,
            ],
            'encouragements' => [
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

            ],
            'completions' => $completions
        ];

        return $profile;
    }
    public function guest( $params ) {

        $location = $this->_get_location( $params );

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
    public function _get_location( $params ) {
        if ( empty( $params['location'] ?? null ) ) {
            // @todo replace with location lookup system
            $location = [
                'lng' => -119.699,
                'lat' => 37.0744,
                'level' => 'region',
                'label' => 'California, United States',
                'grid_id' => 100364453
            ];
        } else {
            $location = $params['location'];
        }
        return $location;
    }
}
Zume_System_Profile_API::instance();
