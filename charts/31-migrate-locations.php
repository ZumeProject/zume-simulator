<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Migrator_Locations extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'migratelocation'; // lowercase
    public $slug = 'location'; // lowercase
    public $title;
    public $base_title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/groups/overview.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];
    public $namespace = 'zume_simulator/v1';

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->namespace = $this->namespace.'/'.$this->base_slug.'/'.$this->slug;
        $this->base_title = __( 'migrate location', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-simulator/$this->base_slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_head',[ $this, 'wp_head' ], 1000);
        }

        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }
    }
    public function add_api_routes() {
        $namespace = $this->namespace;

        // migrator
        register_rest_route(
            $namespace, '/get_user_id', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'get_user_id' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );
        register_rest_route(
            $namespace, '/process_record', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'process_record' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );

    }
    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }

    public static function get_user_id( WP_REST_Request $request ) {
        global $wpdb;
        $user_id = $wpdb->get_results(
            "SELECT pm.meta_value as user_id, pm.post_id as contact_id, pm1.meta_value
                    FROM zume_postmeta pm
                    LEFT JOIN zume_postmeta pm1 ON pm1.post_id=pm.post_id AND pm1.meta_key = 'location_grid_meta'
                    WHERE pm.meta_key = 'corresponds_to_user'
                    AND	pm1.meta_value IS NULL
                   ;", ARRAY_A
        );
        shuffle($user_id);
        return $user_id[0];
    }
    public static function process_record( WP_REST_Request $request )
    {
//        global $wpdb;

        $data = [];
        $params = dt_recursive_sanitize_array( $request->get_params() );
        $user_id = $params['user_id'];
        $contact_id = $params['contact_id'];
        // create contact id

        $location = get_user_meta( $user_id, 'zume_location_grid_meta_from_ip', true );
        if ( ! $location ) {
            return $location;
        }

        $fields = [
            'location_grid_meta' => [
                'values' => [
                    [
                        "label" => $location['label'],
                        "level" => $location['level'],
                        "lng" => $location['lng'],
                        "lat" => $location['lat'],
//                        "grid_id" => $location['grid_id'],
                        "source" => $location['source'],
                    ]
                ],
            ]
        ];
        $update_user_contact = DT_Posts::update_post( 'contacts', $contact_id, $fields, true, false );
//        if ( !is_wp_error( $update_user_contact ) ){
//            update_user_option( $user_id, 'corresponds_to_contact', $new_user_contact['ID'] );
//        }

        return $update_user_contact;

    }


    public function wp_head() {
        $this->js_api();

        ?>
        <script>
            jQuery(document).ready(function(){
                const namespace = '<?php echo $this->namespace ?>'
                "use strict";

                let chart = jQuery('#chart')
                chart.empty().html(`
                        <div id="zume-simulator-path">
                            <div class="grid-x">
                                <div class="cell"><h2>MIGRATE</h2></div>
                            </div>
                            <hr>
                            <div class="grid-x">
                                <div class="cell small-12">
                                    <button class="button" id="process_record">Assign Locations</button><span class="process_record loading-spinner"></span>
                                </div>
                            </div>
                            <hr>
                            <div class="grid-x" id="loop-list"></div>
                        </div>
                    `)

                window.inc = 0
                jQuery('#process_record').on('click', function(){
                    loop()
                })

                function loop() {
                    if ( window.inc > 10 ) {
                        return;
                    }
                    let hash = (+new Date).toString(36);

                    jQuery('#loop-list').prepend(`<div class="cell small-12 ${hash}"><span class="${hash} loading-spinner active"></span></div>`)

                    makeRequest('POST', 'get_user_id', [], namespace)
                        .done(function(response) {
                            console.log(response)

                            if ( response ) {
                                jQuery('.cell.'+hash).append( response.user_id )

                                makeRequest('POST', 'process_record', { user_id: response.user_id, contact_id: response.contact_id }, namespace)
                                    .done(function(response2) {
                                        console.log(response2)
                                        jQuery('.'+hash+'.loading-spinner').removeClass( 'active' )

                                        window.inc++
                                        loop()
                                    })
                            }
                        })
                }
            })
        </script>
        <?php
    }


}
new Zume_Simulator_Migrator_Locations();
