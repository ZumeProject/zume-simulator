<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Stats_Endpoints
{
    public $permissions = ['manage_dt'];
    public $namespace = 'zume_simulator/v1';
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }
    }
    public function add_api_routes() {
        $namespace = $this->namespace;

        register_rest_route(
            $namespace, '/register_user', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'register_user' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );
        register_rest_route(
            $namespace, '/user_progress', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'user_progress' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );
        register_rest_route(
            $namespace, '/reset_tracking', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'reset_tracking' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );
    }

    public function register_user( WP_REST_Request $request ){
        $params = $request->get_params();

        if ( isset( $params['email'], $params['name'], $params['username'], $params['password'] ) ){
            $user_roles = [ 'multiplier' ];

            if ( empty( $params['name'] ) ) {
                $params['name'] = $params['username'];
            }

            if ( isset( $params['user-optional-fields'] ) ) {
                $optional_fields = $params['user-optional-fields'];
            }
            if ( isset( $params['locale'] ) ) {
                $locale = $params['locale'];
            }

            $user_object = wp_get_current_user();
            $user_object->add_cap( 'create_users' );
            $user_object->add_cap( 'create_contacts' );
            $user_object->add_cap( 'access_contacts' );

            $user_id = Disciple_Tools_Users::create_user(
                $params['username'],
                $params['email'],
                $params['name'],
                $user_roles,
                 null,
                $locale ?? null,
                false,
                $params['password'],
                [],
                false
            );

            if ( is_wp_error( $user_id ) ) {
                return $user_id;
            }

            $contact_id = Disciple_Tools_Users::get_contact_for_user( $user_id );

            $location = DT_Mapbox_API::forward_lookup( $params['location'] );
            $location_grid = [
                'label' => $params['location'],
                'level' => 'city',
                'lng' => $location['features'][0]['center'][0],
                'lat' => $location['features'][0]['center'][1],
            ];
            $geocoder = new Location_Grid_Geocoder();
            $grid_row = $geocoder->get_grid_id_by_lnglat( $location_grid['lng'], $location_grid['lat'] );
            $location_grid['grid_id'] = $grid_row['grid_id'];
            $add_location = Location_Grid_Meta::add_user_location_grid_meta( $user_id, $location_grid );

            $fields = [
                'location_grid_meta' => [
                    'values' => [
                        [
                            'label' => $params['location'],
                            'level' => 'city',
                            'lng' => $location['features'][0]['center'][0],
                            'lat' => $location['features'][0]['center'][1],
                        ]
                    ],
                ]
            ];
            $contact_location = DT_Posts::update_post( 'contacts', $contact_id, $fields, true, false );

            dt_report_insert( [
                'type' => 'zume_system',
                'subtype' => 'registered',
                'post_id' => $contact_id,
                'value' => 0,
                'grid_id' => $grid_row['grid_id'],
                'label' => str_replace( ',', ', ', $params['location'] ),
                'lat' => $grid_row['latitude'],
                'lng' => $grid_row['longitude'],
                'level' => 'city',
                'user_id' => $user_id,
                'time_end' =>  strtotime( 'Today -'.$params['days_ago'].' days' ),
                'hash' => hash('sha256', maybe_serialize($params)  . time() ),
            ] );

            return [
                'user_id' => $user_id,
                'contact_id' => $contact_id,
                'location' => $add_location,
                'contact_location' => $contact_location,
            ];
        } else {
            return new WP_Error( 'missing_error', 'Missing fields', [ 'status' => 400 ] );
        }
    }

    public function reset_tracking( WP_REST_Request $request ) {
        global $wpdb;
        return $wpdb->query( "DELETE FROM $wpdb->dt_reports WHERE type = 'zume_system' OR type = 'zume_training' OR type = 'zume_coaching';" );
    }

    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
    public function user_progress( WP_REST_Request $request ) {
        global $wpdb;
        $params = dt_recursive_sanitize_array( $request->get_params() );
        $user_id = (int) $params['user_id'];
        $sql = "SELECT id, user_id, type, subtype, REPLACE(label,', ',',') as label FROM wp_dt_reports WHERE user_id = {$user_id} AND type LIKE 'zume%' ORDER BY time_end DESC";
        return $wpdb->get_results( $sql, ARRAY_A );
    }

}
Zume_Simulator_Stats_Endpoints::instance();
