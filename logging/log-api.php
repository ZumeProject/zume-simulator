<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_System_Log_API
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

    public function add_api_routes()
    {
        $namespace = $this->namespace;

        register_rest_route(
            $namespace, '/log', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'log'],
                'permission_callback' => '__return_true'
            ]
        );
    }
    public function log( WP_REST_Request $request ) {
        global $wpdb;
        $params = dt_recursive_sanitize_array( $request->get_params() );

        if ( ! isset( $params['type'], $params['subtype'] ) ) {
            return new WP_Error(__METHOD__, 'Missing required parameters: type, subtype.', ['status' => 400] );
        }

        // get time
        $time = time();
        $today = date( 'Ymd', strtotime( 'Today' ) );

                // BEGIN @todo dev only, remove for production.
                if ( isset( $params['days_ago'] ) && ! empty( $params['days_ago'] ) ) {
                    $today = strtotime( 'Today -'.$params['days_ago'].' days' ); // @todo dev only, remove for production.
                } // END

        // process user relevant fields
        if ( ! empty( $params['user_id'] ) || is_user_logged_in() ) {
            if ( empty( $data['user_id'] ) && is_user_logged_in() ) {
                $data['user_id'] = get_current_user_id();
            }
            $contact = Disciple_Tools_Users::get_contact_for_user( $params['user_id'] );
            if ( ! is_wp_error( $contact ) && ! empty( $contact ) ) {
                $params['post_id'] = $contact;

                $log = zume_user_log( $params['user_id'] );

                if ( empty( $params['value'] ) && '0' != $params['value'] ) {
                    $stage = zume_get_stage( $params['user_id'], $log );
                    $params['value'] = $stage['stage'];
                }
            }
        }

        // get hash
        $hash = hash('sha256', maybe_serialize($params)  . $today );


        // test hash for duplicate
        if ( in_array( $params['subtype'], ['login', 'checkin' ] ) ) {
            $hash = hash('sha256', maybe_serialize($params)  . time() );
        } else {
            $duplicate_found = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT
                    `id`
                FROM
                    `$wpdb->dt_reports`
                WHERE hash = %s AND hash IS NOT NULL;",
                    $hash
                )
            );
            if ($duplicate_found) {
                return new WP_Error(__METHOD__, 'Duplicate entry for today.', ['status' => 409]);
            }
        }

        // merge complete data array
        $data = wp_parse_args(
            $params,
            [
                'user_id' => null,
                'post_id' => null,
                'post_type' => 'zume',
                'type' => null,
                'subtype' => null,
                'value' => 0,
                'lng' => null,
                'lat' => null,
                'level' => null,
                'label' => null,
                'grid_id' => null,
                'time_end' => $time,
                'hash' => $hash
            ]
        );

        // add log
        $added_log = [];
        $added_log[] = dt_report_insert( $data, true, false );

        $this->_add_additional_log_actions( $added_log, $data, $log );

        return $added_log;
    }
    public function _add_additional_log_actions( &$added_log, $data, $log ) {

        $type = $data['type'];
        $subtype = $data['subtype'];
        $pre = substr( $subtype, 0, 3 );

        if ( 'system' === $type && 'joined_online_training' === $subtype ) {
            $data_item = $data;
            $data_item['type'] = 'system';
            $data_item['subtype'] = 'plan_created';
            $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
            $added_log[] = dt_report_insert( $data_item, true, false );
        }
        if ( 'system' === $type && 'completed_3_month_plan' === $subtype ) {
            if ( $this->_needs_to_be_logged( $log, 'system', 'made_3_month_plan' ) ) {
                $data_item = $data;
                $data_item['type'] = 'system';
                $data_item['subtype'] = 'made_3_month_plan';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }


        // additional HOST
        else if ( 'training' === $type && str_contains( $subtype, 'trained' ) ) {
            if ( $this->_needs_to_be_logged( $log, 'training', $pre.'shared' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'shared';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( $this->_needs_to_be_logged( $log, 'training', $pre.'obeyed' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'obeyed';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( $this->_needs_to_be_logged( $log, 'training', $pre.'heard' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && str_contains( $subtype, 'shared' ) ) {
            if ( $this->_needs_to_be_logged( $log, 'training', $pre.'obeyed' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'obeyed';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( $this->_needs_to_be_logged( $log, 'training', $pre.'heard' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && str_contains( $subtype, 'obeyed' ) ) {
            if ( $this->_needs_to_be_logged( $log, 'training', $pre.'heard' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        // additional MAWL
        else if ( 'coaching' === $type && str_contains( $subtype, 'launching' ) ) {
            if ( $this->_needs_to_be_logged( $log, 'coaching', $pre.'watching' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'watching';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( $this->_needs_to_be_logged( $log, 'coaching', $pre.'assisting' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'assisting';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( $this->_needs_to_be_logged( $log, 'coaching', $pre.'modeling' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'modeling';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'coaching' === $type && str_contains( $subtype, 'watching' ) ) {
            if ( $this->_needs_to_be_logged( $log, 'coaching', $pre.'assisting' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'watching';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( $this->_needs_to_be_logged( $log, 'coaching', $pre.'modeling' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'modeling';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'coaching' === $type && str_contains( $subtype, 'assisting' ) ) {
            if ( $this->_needs_to_be_logged( $log, 'coaching', $pre.'modeling' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'modeling';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }

        // check if log is registered
        if ( is_user_logged_in() && $this->_needs_to_be_logged( $log, 'system', 'registered' ) ) {
            $data_item = $data;
            $data_item['type'] = 'system';
            $data_item['subtype'] = 'registered';
            $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
            $added_log[] = dt_report_insert( $data_item, true, false );
        }



        return $added_log;
    }
    public function _needs_to_be_logged( $log, $type, $subtype ) : bool {
        $already_logged = true;
        foreach ( $log as $log_item ) {
            if ( $log_item['type'] === $type && $log_item['subtype'] === $subtype ) {
                $already_logged = false;
                break;
            }
        }
        return $already_logged;
    }
    public function _is_valid_types( $data ) : bool {
        // test types and subtypes
        return true;
    }
    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
}
Zume_System_Log_API::instance();
