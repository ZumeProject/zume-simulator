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
            $namespace, '/get_encouragement', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'request_sorter'],
                'permission_callback' => '__return_true'
            ]
        );
        register_rest_route(
            $namespace, '/send_encouragement', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'send_encouragement'],
                'permission_callback' => '__return_true'
            ]
        );
        register_rest_route(
            $namespace, '/respond_encouragement', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'respond_encouragement'],
                'permission_callback' => '__return_true'
            ]
        );
    }
    public function request_sorter( WP_REST_Request $request )
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
        return self::_get_encouragement( $params['user_id'], $params['type'], $params['subtype'] );
    }
    public static function _get_encouragement( $user_id, $type, $subtype, $log = NULL ) {
        dt_write_log(__FUNCTION__ . ' ' . $user_id .' - '. $type .' - '. $subtype );

        $plan = self::_get_recommended_plan( $user_id, $type, $subtype );
        if ( empty( $plan ) ) {
            dt_write_log( 'no new plan : get_current_plan' );
            return self::_get_current_plan( $user_id );
        }

        dt_write_log( 'installing new plan' );
        self::_delete_current_plan( $user_id );
        self::_install_plan( $user_id, $plan );

        return self::_get_current_plan( $user_id );
    }
    public static function _get_current_plan( $user_id ) {
        global $wpdb;
        $raw_plan = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $wpdb->dt_zume_message_plan WHERE user_id = %d"
            , $user_id ), ARRAY_A );

        $log = zume_user_log( $user_id );
//        dt_write_log($log);
        if ( empty( $log ) ) {
            return $raw_plan;
        }
        else {
            $relays = [
                'sent' => [],
                'responded' => []
            ];
            foreach( $log as $item ) {
                if( 'sent' === $item['subtype'] ) {
                    $relays['sent'][] = $item['post_id'];
                }
                if( 'responded' === $item['subtype'] ) {
                    $relays['responded'][] = $item['post_id'];
                }
            }
//            dt_write_log( $relays );
            foreach( $raw_plan as $index => $item ) {
                $raw_plan[$index]['sent'] = false;
                $raw_plan[$index]['responded'] = false;
                if ( in_array( $item['message_post_id'], $relays['sent'] ) ) {
                    $raw_plan[$index]['sent'] = true;
                }
                if ( in_array( $item['message_post_id'], $relays['responded'] ) ) {
                    $raw_plan[$index]['responded'] = true;
                }
            }
//            dt_write_log( $raw_plan );
            return $raw_plan;
        }
    }
    public static function _delete_current_plan( $user_id ) {
        global $wpdb;
        $wpdb->query($wpdb->prepare( "DELETE FROM $wpdb->dt_zume_message_plan WHERE user_id = $user_id AND sent IS NULL", $user_id ) );
    }
    public static function _install_plan( $user_id, $plan ) {
        global $wpdb;

        if ( ! is_null( $plan ) && ! is_array( $plan ) ) {
            return;
        }

        $user = get_user_by( 'id', $user_id );
        foreach( $plan as $message ) {
            $message['user_id'] = $user_id;
            $message['to'] = $user->user_email;
            $wpdb->insert( $wpdb->dt_zume_message_plan, $message );
        }
    }
    public static function _get_recommended_plan( $user_id, $type, $subtype ) {
        $plan = false;

        if ( 'system' === $type && 'registered' === $subtype ) {
            $plan = [
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23605,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Registered Post 1 Day',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime('+1 day'),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23606,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Registered Post 2 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime('+2 day'),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23607,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Registered Post 3 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime('+3 day'),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23608,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Registered Post 4 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime('+1 week'),
                ],
            ];
        }
        else if ( 'system' === $type && 'plan_created' === $subtype ) {
            $plan = [
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23621,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Plan Created Post 1 Day',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+1 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23632,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Plan Created Post 2 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
            ];
        }
        else if ( 'system' === $type && 'training_completed' === $subtype ) {
            $plan = [
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23631,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Training Completed Post 1 Day',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+1 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 236382,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Training Completed Post 2 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 236933,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Training Completed Post 3 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
            ];
        }
        else if ( 'system' === $type && 'first_practitioner_report' === $subtype ) {
            $plan = [
                [
                    'user_id' => $user_id,
                    'message_post_id' => 2343,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Post First Practitioner Report  1 Day',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+1 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23644,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Post First Practitioner Report 2 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23634,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Post First Practitioner Report 3 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23624,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Post First Practitioner Report 2 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23643,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'Post First Practitioner Report 3 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
            ];
        }
        else if ( 'system' === $type && 'mawl_completed' === $subtype ) {
            $plan = [
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23645,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'MAWL Completed 1 Day',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+1 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23655,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'MAWL Completed 2 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23656,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'MAWL Completed 3 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23657,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'MAWL Completed 4 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23658,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'MAWL Completed 5 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
            ];
        }
        else if ( 'system' === $type && 'seeing_generational_fruit' === $subtype ) {
            $plan = [
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23667,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'seeing_generational_fruit 1 Day',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+1 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23677,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'seeing_generational_fruit2 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
                [
                    'user_id' => $user_id,
                    'message_post_id' => 23678,
                    'message_type' => 'email',
                    'to' => '',
                    'subject' => 'seeing_generational_fruit 3 Days',
                    'message' => 'laskjdf ;laskd jf;alskj df;aslkd jf;laskj df;laskj d;flaskj d;flaks djf;l',
                    'headers' => '',
                    'drop_date' => strtotime( '+2 day' ),
                ],
            ];
        }

        return $plan;
    }
    public static function _query_plans() {
        global $wpdb;
        $raw_plans = $wpdb->get_results( "
            SELECT p.ID, p.post_parent, pm.meta_value as subject, pm1.meta_value as body, pm2.meta_value as footer, pm3.meta_value as action_keys
                FROM $wpdb->posts p
                LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'zume_email_subject'
                LEFT JOIN $wpdb->postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'zume_email_body'
                LEFT JOIN $wpdb->postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'zume_email_footer'
                LEFT JOIN $wpdb->postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'zume_action_keys'
                WHERE p.post_type = 'zume_messages'
                AND p.post_status = 'publish'", ARRAY_A
        );

        $plans = [];
        foreach( $raw_plans as $plan ) {
            $plans[$plan['ID']] = $plan;
        }

        return $plans;
    }

    public function send_encouragement( WP_REST_Request $request ) {
        $params = dt_recursive_sanitize_array( $request->get_params() );

        return $params;
    }
    public function respond_encouragement( WP_REST_Request $request ) {
        $params = dt_recursive_sanitize_array( $request->get_params() );

        return $params;
    }

    public function guest($params)
    {
        return [];
    }
}
Zume_System_Encouragement_API::instance();
