<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Stats_Endpoints
{
    public $namespace = 'zume_simulator/v1';
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        if ( $this->dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }
    }
    public function add_api_routes() {
        $namespace = $this->namespace;


        register_rest_route(
            $namespace, '/log', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'log' ],
                'permission_callback' => '__return_true'
            ]
        );
        register_rest_route(
            $namespace, '/journey_log', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'journey_log' ],
                'permission_callback' => '__return_true'
            ]
        );
        register_rest_route(
            $namespace, '/register_user', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'register_user' ],
                'permission_callback' => '__return_true'
            ]
        );

    }

    public function log( WP_REST_Request $request ) {
        return Zume_Simulator_Query::log( dt_recursive_sanitize_array( $request->get_params() ) );
    }
    public function location( WP_REST_Request $request ) {
        return DT_Ipstack_API::get_location_grid_meta_from_current_visitor();
    }
    public function journey_log( WP_REST_Request $request ) {
        return $request->get_params() ;
    }

    public function register_user( WP_REST_Request $request ){
        $params = $request->get_params();

//        if ( ! current_user_can('manage_options' ) ) {
//            return new WP_Error( 'unauthorized', 'You are not authorized to create users', [ 'status' => 401 ] );
//        }

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
                true,
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



    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
    public function dt_is_rest( $namespace = null ) {
        // https://github.com/DiscipleTools/disciple-tools-theme/blob/a6024383e954cec2ac4e7a1a31fb4601c940f485/dt-core/global-functions.php#L60
        // Added here so that in non-dt sites there is no dependency.
        $prefix = rest_get_url_prefix();
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST
            || isset( $_GET['rest_route'] )
            && strpos( trim( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '\\/' ), $prefix, 0 ) === 0 ) {
            return true;
        }
        $rest_url    = wp_parse_url( site_url( $prefix ) );
        $current_url = wp_parse_url( add_query_arg( array() ) );
        $is_rest = strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
        if ( $namespace ){
            return $is_rest && strpos( $current_url['path'], $namespace ) != false;
        } else {
            return $is_rest;
        }
    }
}
Zume_Simulator_Stats_Endpoints::instance();
