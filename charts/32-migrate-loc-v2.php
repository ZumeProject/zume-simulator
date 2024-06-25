<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Migrator_Loc2 extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'migratelocation2'; // lowercase
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
        $this->base_title = __( 'migrate location 2', 'disciple_tools' );

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

        $users = $wpdb->get_col(
            "SELECT pm.meta_value as user_id
                    FROM zume_postmeta pm
                    LEFT JOIN zume_postmeta pm1 ON pm1.post_id=pm.post_id AND pm1.meta_key = 'location_grid_meta'
                    LEFT JOIN zume_usermeta um1 ON um1.user_id=pm.meta_value AND um1.meta_key = 'zume_recent_ip'
                    WHERE pm.meta_key = 'corresponds_to_user'
                    AND pm1.meta_value IS NULL
                    AND um1.meta_value IS NOT NULL;
                   ;"
        );

        sort( $users);

        update_post_meta( 5, 'ip_address_location_upgrade', $users );

        return true;
    }
    public static function process_record( WP_REST_Request $request )
    {
        $params = dt_recursive_sanitize_array( $request->get_params() );
        $fields = [];

        $user_list = get_post_meta( 5, 'ip_address_location_upgrade', true);
        sort( $user_list);
//        dt_write_log($user_list[0]);
//        dt_write_log($user_list);
        $user_id = $user_list[0];
        if ( empty( $user_id ) ) {
            return 'done.';
        }
        unset($user_list[0]);
        sort( $user_list);
        update_post_meta( 5, 'ip_address_location_upgrade', $user_list );

        $ip_address = get_user_meta( $user_id, 'zume_recent_ip', true );
        if ( empty( $ip_address ) ) {
            return $user_id . ': no ip-address';
        }
        $result = DT_Ipstack_API::geocode_ip_address( $ip_address );
        $location = DT_Ipstack_API::convert_ip_result_to_location_grid_meta( $result );
        if ( empty( $location ) ) {
            return $user_id . ': No location data created';
        }

        $contact_id = get_user_meta( $user_id, 'zume_corresponds_to_contact', true );

        $fields['location_grid_meta'] = [
            'values' => [
                [
                    "label" => $location['label'],
                    "level" => $location['level'],
                    "lng" => $location['lng'],
                    "lat" => $location['lat'],
                    "source" => $location['source'],
                ]
            ]
        ];


        $update_user_contact = DT_Posts::update_post( 'contacts', $contact_id, $fields, true, false );

        return $update_user_contact['ID'] . ' - '. $location['label'];

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
                                    <button class="button" id="set_records">Set Records</button><span class="set_records loading-spinner"></span><br>
                                    <button class="button" id="process_record">Assign Locations</button><span class="process_record loading-spinner"></span>
                                </div>
                            </div>
                            <hr>
                            <div class="grid-x" id="loop-list"></div>
                        </div>
                    `)

                jQuery('#set_records').on('click', function(){
                    makeRequest('POST', 'get_user_id', [], namespace)
                        .done(function(response) {
                            console.log(response)
                            jQuery('#set_records').addClass('success')
                        })
                })

                window.inc = 0
                jQuery('#process_record').on('click', function(){
                    loop()
                })

                function loop() {
                    if ( window.inc > 100 ) {
                        return;
                    }
                    let hash = (+new Date).toString(36);

                    jQuery('#loop-list').prepend(`<div class="cell small-12 ${hash}"><span class="${hash} loading-spinner active"></span></div>`)

                    makeRequest('POST', 'process_record', { }, namespace)
                        .done(function(response2) {
                            console.log(response2)

                            jQuery('.'+hash+'.loading-spinner').removeClass( 'active' )
                            jQuery('.cell.'+hash).append( response2 )

                            window.inc++
                            loop()
                        })
                }
            })
        </script>
        <?php
    }


}
new Zume_Simulator_Migrator_Loc2();
