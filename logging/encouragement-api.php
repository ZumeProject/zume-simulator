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
            $namespace, '/user_encouragement', [
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

        $log = zume_user_log( $params['user_id'] );

        $log_keys = [];
        foreach( $log as $row ) {
            $log_keys[] = $row['log_key'];
        }

        // current plan
//        $current_plan = zume_user_current_plan( $params['user_id'], $log_keys );

        // recommended plan
//        $recommended_plan = zume_user_recommended_plan( $params['user_id'] );

        // disable plan
//        if ( $current_plan === $recommended_plan ) {
//
//        }

        // encouragement plan
//        $encouragement_plan = zume_user_encouragement_plan( $params['user_id'], $log_keys );

        return $log_keys;
    }


    /**
    0
    :
    "system_registered"
    1
    :
    "system_plan_created"
    2
    :
    "system_requested_a_coach"
    3
    :
    "system_joined_online_training"
    4
    :
    "system_set_profile"
    5
    :
    "system_invited_friends"
    6
    :
    "reports_practitioner_report"
    7
    :
    "system_joined_affinity_hub"
    8
    :
    "system_hub_checkin"
    9
    :
    "training_1_heard"
    10
    :
    "training_2_heard"
    11
    :
    "training_3_heard"
    12
    :
    "training_4_heard"
    13
    :
    "training_5_heard"
    14
    :
    "training_6_heard"
    15
    :
    "training_7_heard"
    16
    :
    "training_8_heard"
    17
    :
    "training_9_heard"
    18
    :
    "training_10_heard"
    19
    :
    "training_11_heard"
    20
    :
    "training_12_heard"
    21
    :
    "training_13_heard"
    22
    :
    "training_14_heard"
    23
    :
    "training_15_heard"
    24
    :
    "training_16_heard"
    25
    :
    "training_17_heard"
    26
    :
    "training_18_heard"
    27
    :
    "training_19_heard"
    28
    :
    "training_20_heard"
    29
    :
    "training_21_heard"
    30
    :
    "training_22_heard"
    31
    :
    "training_23_heard"
    32
    :
    "training_24_heard"
    33
    :
    "training_32_heard"
    34
    :
    "training_31_heard"
    35
    :
    "training_30_heard"
    36
    :
    "training_25_heard"
    37
    :
    "training_26_heard"
    38
    :
    "training_27_heard"
    39
    :
    "training_28_heard"
    40
    :
    "training_29_heard"
    41
    :
    "system_made_3_month_plan"
    42
    :
    "system_training_completed"
    43
    :
    "system_completed_3_month_plan"
    44
    :
    "system_first_practitioner_report"
    45
    :
    "coaching_4_modeling"
    46
    :
    "coaching_5_modeling"
    47
    :
    "coaching_7_modeling"
    48
    :
    "coaching_8_modeling"
    49
    :
    "coaching_10_modeling"
    50
    :
    "coaching_11_modeling"
    51
    :
    "coaching_12_modeling"
    52
    :
    "coaching_13_modeling"
    53
    :
    "coaching_16_modeling"
    54
    :
    "coaching_17_modeling"
    55
    :
    "coaching_19_modeling"
    56
    :
    "coaching_21_modeling"
    57
    :
    "coaching_22_modeling"
    58
    :
    "coaching_26_modeling"
    59
    :
    "coaching_31_modeling"
    60
    :
    "coaching_32_modeling"
    61
    :
    "system_mawl_completed"
    62
    :
    "system_seeing_generational_fruit"
     */

    public static function _return_set( $set = 'set1' ) {
        $list = [
            '' => [
                'plan' => ['[[Not Configured}}'],
                'reset' => ['[[Not Configured}}'],
            ],
            'set1' => [
                'plan' => ['[[Not Configured}}'],
                'reset' => ['[[Not Configured}}'],
            ],
            'set2' => [
                'plan' => [
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
                'plan' => [
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
                'plan' => [
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
                'plan' => [
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
        return $list[$set];
    }
    public function guest($params)
    {
        return [];
    }
}
Zume_System_Encouragement_API::instance();
