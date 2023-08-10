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
        if ( is_null( $log ) ) {
            $log = zume_user_log( $user_id );
            if ( is_null( $log ) ) {
                return [];
            }
        }

        $stage = zume_get_stage( $user_id, $log );

        $log_keys = [];
        foreach( $log as $row ) {
            $log_keys[] = $row['log_key'];
        }

        $templates = self::get_ctas();

        $ctas = [];
        foreach($templates as $template) {
            if ( in_array( $stage['stage'], $template['stages'] ) ) {
                $ctas[] = $template;
            }
        }
        if ( ! empty( $ctas ) ) {
            foreach( $ctas as $key => $cta ) {
                foreach( $cta['required_keys'] as $required_key) {
                    if ( ! in_array( $required_key, $log_keys ) ) {
                        unset( $ctas[$key] );
                    }
                }
                foreach( $cta['disable_keys'] as $disable_key) {
                    if ( in_array( $disable_key, $log_keys ) ) {
                        unset( $ctas[$key] );
                    }
                }
            }
        }

        return $ctas;
    }

    public function guest( $params )
    {
        $templates = self::get_ctas();

        $ctas = [];
        foreach($templates as $template) {
            if ( in_array( 0, $template['stages'] ) ) {
                $ctas[] = $template;
            }
        }

        return $ctas;
    }

    public static function get_ctas()
    {
        $templates = [
            [
                'stages' => [0],
                'required_keys' => [],
                'disable_keys' => ['system_registered'],
                'label' => 'Register',
                'key' => 'registered',
                'type' => 'system',
                'subtype' => 'registered',
                'link' => '/register',
            ],
            [
                'stages' => [0,1],
                'required_keys' => [],
                'disable_keys' => ['system_joined_online_training'],
                'label' => 'Join Online Training',
                'key' => 'system_joined_online_training',
                'type' => 'system',
                'subtype' => 'joined_online_training',
                'link' => '/online-training',
            ],
            [
                'stages' => [1,2,3,4,5,6],
                'required_keys' => [],
                'disable_keys' => ['system_requested_a_coach'],
                'label' => 'Get a Coach',
                'key' => 'system_requested_a_coach',
                'type' => 'system',
                'subtype' => 'requested_a_coach',
                'link' => '/request-coach',
            ],
            [
                'stages' => [1],
                'required_keys' => [],
                'disable_keys' => ['system_plan_created'],
                'label' => 'Create a Plan',
                'key' => 'system_plan_created',
                'type' => 'system',
                'subtype' => 'plan_created',
                'link' => '/create-plan',
            ],
            [
                'stages' => [1,2],
                'required_keys' => [],
                'disable_keys' => ['system_invited_friends'],
                'label' => 'Invite Friends',
                'key' => 'system_invited_friends',
                'type' => 'system',
                'subtype' => 'invited_friends',
                'link' => '/invite-friends',
            ],
            [
                'stages' => [1,2,3,4,5,6],
                'required_keys' => [],
                'disable_keys' => ['system_set_profile'],
                'label' => 'Set Profile',
                'key' => 'system_set_profile',
                'type' => 'system',
                'subtype' => 'set_profile',
                'link' => '/profile',
            ],
            [
                'stages' => [2],
                'required_keys' => ['training_26_heard'],
                'disable_keys' => ['system_made_3_month_plan'],
                'label' => 'Create 3 Month Plan',
                'key' => 'system_made_3_month_plan',
                'type' => 'system',
                'subtype' => 'made_3_month_plan',
                'link' => '/profile',
            ],
            [
                'stages' => [3],
                'required_keys' => [],
                'disable_keys' => ['system_completed_3_month_plan'],
                'label' => 'Complete 3 Month Plan',
                'key' => 'system_completed_3_month_plan',
                'type' => 'system',
                'subtype' => 'completed_3_month_plan',
                'link' => '/profile',
            ],
            [
                'stages' => [3,4,5,6],
                'required_keys' => [],
                'disable_keys' => [],
                'label' => 'Submit Report',
                'key' => 'report_practitioner_report',
                'type' => 'report',
                'subtype' => 'practitioner_report',
                'link' => '/profile',
            ],
            [
                'stages' => [4,5,6],
                'required_keys' => [],
                'disable_keys' => ['system_joined_affinity_hub'],
                'label' => 'Join a Hub',
                'key' => 'system_joined_affinity_hub',
                'type' => 'system',
                'subtype' => 'joined_affinity_hub',
                'link' => '/profile',
            ],
        ];
        return $templates;
    }

}
Zume_System_CTA_API::instance();
