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
        $location = $params['location'] ?? self::_get_location( $user_id );

        $log = self::_query_user_log( $user_id );

        return [
            'profile' => self::_get_profile( $user_id ),
            'location' => $location,
            'stage' => self::_get_stage( $user_id, $log ),
            'state' => self::_get_state( $user_id, $log ),
            'encouragements' => self::_get_encouragements( $user_id, $log ),
            'completions' => self::_get_completions( $user_id, $log )
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

        $name = '';
        $contact_id = Disciple_Tools_Users::get_contact_for_user( $user_id );
        $contact = [];
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
    public static function _get_location( $params ) {
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
    public static function _get_stage( $user_id, $log = NULL ) {

        if ( ! is_null( $log ) ) {
            $log = self::_query_user_log( $user_id );
        }

        $funnel = zume_funnel_stages();
        $stage = $funnel[0];

        if ( count($log) > 0 ) {

            $funnel_steps = [
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
            ];

            foreach( $log as $index => $value ) {
                if ( 'registered' == $value['subtype'] ) {
                    $funnel_steps[1] = true;
                }
                if ( 'plan_created' == $value['subtype'] ) {
                    $funnel_steps[2] = true;
                }
                if ( 'training_completed' == $value['subtype'] ) {
                    $funnel_steps[3] = true;
                }
                if ( 'first_practitioner_report' == $value['subtype'] ) {
                    $funnel_steps[4] = true;
                }
                if ( 'mawl_completed' == $value['subtype'] ) {
                    $funnel_steps[5] = true;
                }
                if ( 'seeing_generational_fruit' == $value['subtype'] ) {
                    $funnel_steps[6] = true;
                }
            }

            if ( $funnel_steps[6] ) {
                $stage = $funnel[6];
            } else if ( $funnel_steps[5] ) {
                $stage = $funnel[5];
            } else if ( $funnel_steps[4] ) {
                $stage = $funnel[4];
            } else if ( $funnel_steps[3] ) {
                $stage = $funnel[3];
            } else if ( $funnel_steps[2] ) {
                $stage = $funnel[2];
            } else if ( $funnel_steps[1] ) {
                $stage = $funnel[1];
            } else {
                $stage = $funnel[0];
            }

        }
        return $stage;
    }
    public static function _query_user_log( $user_id ) {
        global $wpdb;
        $sql = $wpdb->prepare( "SELECT CONCAT( r.type, '_', r.subtype ) as log_key, r.*
                FROM $wpdb->dt_reports r
                WHERE r.user_id = %s
                AND r.post_type = 'zume'
                ", $user_id );
        return $wpdb->get_results( $sql, ARRAY_A );
    }
    public static function _get_encouragements( $user_id, $log = NULL ) {
        $encouragements = Zume_System_Encouragement_API::_get_encouragements( $user_id );

        // reduce encouragements to only those that are not completed
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
    public static function _get_state( $user_id, $log = NULL ) {
        if ( ! is_null( $log ) ) {
            $log = self::_query_user_log( $user_id );
        }

        $data = [
            'has_registered' => false,
            'has_a_plan' => false,
            'has_coach' => false,
            'has_set_profile' => false,
            'has_invited_friends' => false,
            'has_3_month_plan' => false,
        ];

        foreach( $log as $index => $value ) {

            if( $value['subtype'] == 'registered' ) {
                $data['has_registered'] = true;
            }
            if( $value['subtype'] == 'plan_created' ) {
                $data['has_a_plan'] = true;
            }

            if( $value['subtype'] == 'requested_a_coach' ) {
                $data['has_coach'] = true;
            }
            if( $value['subtype'] == 'set_profile' ) {
                $data['has_set_profile'] = true;
            }
            if( $value['subtype'] == 'invited_friends' ) {
                $data['has_invited_friends'] = true;
            }
            if( $value['subtype'] == 'completed_3_month_plan' ) {
                $data['has_3_month_plan'] = true;
            }

        }

        return $data;
    }
    public static function _get_completions( $user_id, $log = NULL ) {
        if ( ! is_null( $log ) ) {
            $log = self::_query_user_log( $user_id );
        }

        $data = [];

        foreach( $log as $index => $value ) {
            $data[$value['log_key']] = true ;
        }

        return $data;
    }
}
Zume_System_Profile_API::instance();
