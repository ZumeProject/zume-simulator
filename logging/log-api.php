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
                'callback' => [$this, 'rest_log'],
                'permission_callback' => '__return_true'
            ]
        );
    }
    public function rest_log( WP_REST_Request $request ) {
        $params = dt_recursive_sanitize_array( $request->get_params() );
        if ( ! isset( $params['type'], $params['subtype'] ) ) {
            return new WP_Error(__METHOD__, 'Missing required parameters: type, subtype.', ['status' => 400] );
        }
        return self::log( $params['type'], $params['subtype'], $params );
    }

    /**
     * @param string $type
     * @param string $subtype
     * @param array $data
     * @return array|WP_Error
     */
    public static function log( string $type, string $subtype, array $data = [] ) {
        $added_log = [];
        if ( ! isset( $type, $subtype ) ) {
            return new WP_Error(__METHOD__, 'Missing required parameters: type, subtype.', ['status' => 400] );
        }
        $data = dt_recursive_sanitize_array( $data );

        $report = [
            'user_id' => null,
            'post_id' => null,
            'parent_id' => null,
            'post_type' => 'zume',
            'type' => $type,
            'subtype' => $subtype,
            'value' => 0,
            'lng' => null,
            'lat' => null,
            'level' => null,
            'label' => null,
            'grid_id' => null,
            'time_end' => time(),
            'hash' => null
        ];


        self::_prepare_user_id( $report, $data );
        self::_prepare_location( $report, $data );

        // if no user_id found, just insert anonymous log
        if( empty( $report['user_id'] ) ) {
            $report['hash'] = hash('sha256', maybe_serialize($report)  . time() );
            $added_log[] = dt_report_insert( $report, true, true );
            return $added_log;
        }

        $log = zume_user_log( $report['user_id'] );

        self::_prepare_post_id( $report, $data );
        self::_prepare_time_end( $report, $data );
        self::_prepare_value( $report, $data );

        $report['hash'] = hash('sha256', maybe_serialize($report)  . time() );
        $added_log[] = dt_report_insert( $report, true, false );

        // run additional actions
        self::_add_additional_log_actions( $added_log, $report, $log );

        return $added_log;
    }
    private static function _prepare_user_id( &$report, $data ) {
        if ( isset( $data['user_id'] ) && ! empty( $data['user_id'] ) ) {
            $report['user_id'] = absint( $data['user_id'] );
        } else if ( is_user_logged_in() ) {
            $report['user_id'] = get_current_user_id();
        }
        else {
            $report['user_id'] = 0;
        }
        return $report;
    }
    private static function _prepare_location( &$report, $data ) {

        if ( isset( $data['lng'], $data['lat'], $data['level'], $data['label'], $data['grid_id'] ) ) {
            $report['lng'] = $data['lng'];
            $report['lat'] = $data['lat'];
            $report['level'] = $data['level'];
            $report['label'] = $data['label'];
            $report['grid_id'] = $data['grid_id'];

            return $report;
        }
        else {
            $location = zume_get_user_location( $report['user_id'], true );
            if ( ! empty( $location ) ) {
                $report['lng'] = $location['lng'];
                $report['lat'] = $location['lat'];
                $report['level'] = $location['level'];
                $report['label'] = $location['label'];
                $report['grid_id'] = $location['grid_id'];

                return $report;
            }
        }

        error_log(__METHOD__);
        error_log('Silent log warning: Failing to generate a viable location lookup.');
        $report['lng'] = null;
        $report['lat'] = null;
        $report['level'] = null;
        $report['label'] = null;
        $report['grid_id'] = null;

        return $report;
    }
    private static function _prepare_post_id( &$report, $data ) {

        if ( isset( $data['post_id'] ) && ! empty( $data['post_id'] ) ) {
            $report['post_id'] = absint( $data['post_id'] );
        }
        else if ( isset( $report['user_id'] ) && ! empty( $report['user_id'] ) ) {
            $contact = Disciple_Tools_Users::get_contact_for_user( $report['user_id'] );
            if ( ! is_wp_error( $contact ) && ! empty( $contact ) ) {
                $report['post_id'] = $contact;
            }
        }

        return $report;
    }
    private static function _prepare_time_end( &$report, $data ) {
        $report['time_end'] = time();

        if ( isset( $data['time_end'] ) && ! empty( $data['time_end'] && is_numeric( $data['time_end'] ) ) ) {
            $report['time_end'] = $data['time_end'];
        }

        return $report;
    }
    private static function _prepare_value( &$report, $data ) {
        $stage = zume_get_stage( $report['user_id'] );
        $report['value'] = $stage['stage'];

        return $report;
    }


    public static function _add_additional_log_actions( &$added_log, $data, $log ) {

        $type = $data['type'];
        $subtype = $data['subtype'];
        $pre = substr( $subtype, 0, 3 );

        /**
         * business logic:
         * - if a user joins an online training, create a plan_created log entry
         */
        if ( 'system' === $type && 'joined_online_training' === $subtype ) {
            $data_item = $data;
            $data_item['type'] = 'system';
            $data_item['subtype'] = 'plan_created';
            $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
            $added_log[] = dt_report_insert( $data_item, true, false );
        }
        /**
         * business logic:
         * - if a user completes a plan, create a made_3_month_plan log entry
         */
        if ( 'system' === $type && 'completed_3_month_plan' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'system', 'made_3_month_plan' ) ) {
                $data_item = $data;
                $data_item['type'] = 'system';
                $data_item['subtype'] = 'made_3_month_plan';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        /**
         * business logic:
         * - if a user submits a practitioner report, create a first_practitioner_report log entry if needed
         */
        if ( 'reports' === $type && 'practitioner_report' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'system', 'first_practitioner_report' ) ) {
                $data_item = $data;
                $data_item['type'] = 'system';
                $data_item['subtype'] = 'first_practitioner_report';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }

        /**
         * business logic:
         * - if a user submits a training HOST log, create low level training log entries if needed
         */
        else if ( 'training' === $type && str_contains( $subtype, 'trained' ) ) {
            if ( self::_needs_to_be_logged( $log, 'training', $pre.'shared' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'shared';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', $pre.'obeyed' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'obeyed';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', $pre.'heard' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && str_contains( $subtype, 'shared' ) ) {
            if ( self::_needs_to_be_logged( $log, 'training', $pre.'obeyed' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'obeyed';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', $pre.'heard' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && str_contains( $subtype, 'obeyed' ) ) {
            if ( self::_needs_to_be_logged( $log, 'training', $pre.'heard' ) ) {
                $data_item = $data;
                $data_item['type'] = 'training';
                $data_item['subtype'] = $pre.'heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'coaching' === $type && str_contains( $subtype, 'launching' ) ) {
            if ( self::_needs_to_be_logged( $log, 'coaching', $pre.'watching' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'watching';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'coaching', $pre.'assisting' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'assisting';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'coaching', $pre.'modeling' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'modeling';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'coaching' === $type && str_contains( $subtype, 'watching' ) ) {
            if ( self::_needs_to_be_logged( $log, 'coaching', $pre.'assisting' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'assisting';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'coaching', $pre.'modeling' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'modeling';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'coaching' === $type && str_contains( $subtype, 'assisting' ) ) {
            if ( self::_needs_to_be_logged( $log, 'coaching', $pre.'modeling' ) ) {
                $data_item = $data;
                $data_item['type'] = 'coaching';
                $data_item['subtype'] = $pre.'modeling';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }

        /**
         * business logic:
         * - if a user checks in to a training session, then add all the training items covered in that session
         */
       if ( 'training' === $type && 'set_a_01' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '01_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '01_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
           if ( self::_needs_to_be_logged( $log, 'training', '02_heard' ) ) {
               $data_item = $data;
               $data_item['subtype'] = '02_heard';
               $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
               $added_log[] = dt_report_insert( $data_item, true, false );
           }
           if ( self::_needs_to_be_logged( $log, 'training', '03_heard' ) ) {
               $data_item = $data;
               $data_item['subtype'] = '03_heard';
               $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
               $added_log[] = dt_report_insert( $data_item, true, false );
           }
           if ( self::_needs_to_be_logged( $log, 'training', '04_heard' ) ) {
               $data_item = $data;
               $data_item['subtype'] = '04_heard';
               $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
               $added_log[] = dt_report_insert( $data_item, true, false );
           }
           if ( self::_needs_to_be_logged( $log, 'training', '05_heard' ) ) {
               $data_item = $data;
               $data_item['subtype'] = '05_heard';
               $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
               $added_log[] = dt_report_insert( $data_item, true, false );
           }
        }
        else if ( 'training' === $type && 'set_a_02' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '06_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '06_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '07_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '07_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '08_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '08_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_03' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '09_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '09_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '10_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '10_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '11_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '11_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_04' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '12_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '12_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '13_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '13_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '14_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '14_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '15_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '15_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '16_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '16_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_05' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '17_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '17_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '18_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '18_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '19_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '19_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_06' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '20_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '20_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '21_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '21_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_07' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '22_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '22_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_08' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '23_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '23_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_09' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '24_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '24_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '25_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '25_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '26_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '26_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '27_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '27_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'system', 'training_completed' ) ) {
                $data_item = $data;
                $data_item['type'] = 'system';
                $data_item['subtype'] = 'training_completed';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }
        else if ( 'training' === $type && 'set_a_10' === $subtype ) {
            if ( self::_needs_to_be_logged( $log, 'training', '28_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '28_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '29_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '29_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '30_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '30_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '31_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '31_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'training', '32_heard' ) ) {
                $data_item = $data;
                $data_item['subtype'] = '32_heard';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
            if ( self::_needs_to_be_logged( $log, 'system', 'training_completed' ) ) {
                $data_item = $data;
                $data_item['type'] = 'system';
                $data_item['subtype'] = 'training_completed';
                $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
                $added_log[] = dt_report_insert( $data_item, true, false );
            }
        }

        /**
         * business logic:
         * - if a user is logged in, and the system has not yet logged that the user has registered, then log it
         */
        if ( is_user_logged_in() && self::_needs_to_be_logged( $log, 'system', 'registered' ) ) {
            $data_item = $data;
            $data_item['type'] = 'system';
            $data_item['subtype'] = 'registered';
            $data_item['hash'] = hash('sha256', maybe_serialize( $data_item )  . time() );
            $added_log[] = dt_report_insert( $data_item, true, false );
        }

        return $added_log;
    }
    private static function _needs_to_be_logged( $log, $type, $subtype ) : bool {
        $already_logged = true;
        foreach ( $log as $log_item ) {
            if ( $log_item['type'] === $type && $log_item['subtype'] === $subtype ) {
                $already_logged = false;
                break;
            }
        }
        return $already_logged;
    }


    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
}
Zume_System_Log_API::instance();
