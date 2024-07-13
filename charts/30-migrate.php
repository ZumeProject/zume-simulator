<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Migrator extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'migrate'; // lowercase
    public $slug = ''; // lowercase
    public $title;
    public $base_title;
    public $namespace = 'zume_simulator/v1';
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/groups/overview.js'; // should be full file name plus extension
    public $permissions = ['manage_dt'];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->base_title = __( 'Migrate Legacy Group', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-simulator" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_head',[ $this, 'wp_head' ], 1000);
        }
        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }
    }

    public function wp_head() {
        $this->js_api();
        ?>
        <script>
            jQuery(document).ready(function(){
                "use strict";

                let chart = jQuery('#chart')
                chart.empty().html(`
                        <div id="zume-simulator-path">
                            <div class="grid-x">
                                <div class="cell"><h2>MIGRATE</h2></div>
                            </div>
                            <hr>

                                <div class="cell small-12">
                                    <input type="text" id="zume_meta_key" value="" placeholder="Add zume key like zume_group_636950134a032" />
                                    <button class="button" id="migrate_meta">Install Legacy  Group</button><span class="migrate_spinner loading-spinner"></span>
                                </div>
                            </div>
                            <hr>

                            <div class="grid-x" id="loop-list"></div>
                        </div>
                    `)


                jQuery('#migrate_meta').on('click', function(){
                    jQuery('#loop-list').html('')
                    let key_value = jQuery('#zume_meta_key').val()
                    jQuery('.migrate_spinner.loading-spinner').addClass('active')

                    makeRequest('POST', 'migrate_group_meta', { key: key_value }, 'zume_simulator/v1/')
                        .done(function(response) {
                            console.log(response)
                            jQuery('#loop-list').html(response)
                            jQuery('.migrate_spinner.loading-spinner').removeClass('active')
                        }) /* makeRequest*/
                })/* on click*/


            }) /* is ready*/
        </script>
        <?php
    }

    public function add_api_routes() {
        $namespace = $this->namespace;

        register_rest_route(
            $namespace, '/migrate_group_meta', [
                'methods'  => [ 'GET', 'POST' ],
                'callback' => [ $this, 'process_meta' ],
                'permission_callback' => function () {
                    return dt_has_permissions($this->permissions);
                }
            ]
        );
    }

    public static function process_meta( WP_REST_Request $request ) {

        global $wpdb;
        $params = dt_recursive_sanitize_array( $request->get_params() );
        if ( ! isset( $params['key'] ) || empty( $params['key'] ) ) {
            return new WP_Error(__METHOD__, 'no key', ['status' => 404 ] );
        }

        // get group from key
        $public_zume_key = $params['key'];
        $row = $wpdb->get_row($wpdb->prepare( "
                            SELECT *
                            FROM zume_usermeta um
                            WHERE um.meta_key LIKE %s
                              ", $public_zume_key ), ARRAY_A );
        if ( ! $row ) {
            return new WP_Error(__METHOD__, 'no record found', ['status' => 404 ] );
        } else {
            $group = unserialize( $row['meta_value'] );
        }

//        dt_write_log( $group );

        extract( $group );
        $user_id = $owner;

        // check if key is present in plans
        $is_installed = $wpdb->get_var($wpdb->prepare( "SELECT pm.post_id
                                FROM zume_usermeta um
                                JOIN zume_postmeta pm ON pm.meta_value=um.meta_key AND pm.meta_key = 'join_key'
                                WHERE um.meta_key = %s;", $key ) );

        // if not installed, then proceed
        if ( $is_installed ) {
            return 'Already installed: <a href="/zume_plans/'.$is_installed.'">' . $is_installed . "</a>";
        }

        // build post
        $contact_id = get_user_meta( $owner, 'zume_corresponds_to_contact', true );

        $creation_time = strtotime( $group['created_date'] );
        $set_a_01 = $creation_time + 604800;
        $set_a_02 = $creation_time + ( 604800 * 2 );
        $set_a_03 = $creation_time + ( 604800 * 3 );
        $set_a_04 = $creation_time + ( 604800 * 4 );
        $set_a_05 = $creation_time + ( 604800 * 5 );
        $set_a_06 = $creation_time + ( 604800 * 6 );
        $set_a_07 = $creation_time + ( 604800 * 7 );
        $set_a_08 = $creation_time + ( 604800 * 8 );
        $set_a_09 = $creation_time + ( 604800 * 9 );
        $set_a_10 = $creation_time + ( 604800 * 10 );

        $fields = [
            'title' => $group_name,
            'assigned_to' => $owner,
            'set_type' => 'set_a',
            'visibility' => 'private',
            'created_date' => $creation_time,
            'join_key' => $key,
            'set_a_01' => $set_a_01,
            'set_a_02' => $set_a_02,
            'set_a_03' => $set_a_03,
            'set_a_04' => $set_a_04,
            'set_a_05' => $set_a_05,
            'set_a_06' => $set_a_06,
            'set_a_07' => $set_a_07,
            'set_a_08' => $set_a_08,
            'set_a_09' => $set_a_09,
            'set_a_10' => $set_a_10,
            'set_a_01_completed' => $group['session_1_complete'],
            'set_a_02_completed' => $group['session_2_complete'],
            'set_a_03_completed' => $group['session_3_complete'],
            'set_a_04_completed' => $group['session_4_complete'],
            'set_a_05_completed' => $group['session_5_complete'],
            'set_a_06_completed' => $group['session_6_complete'],
            'set_a_07_completed' => $group['session_7_complete'],
            'set_a_08_completed' => $group['session_8_complete'],
            'set_a_09_completed' => $group['session_9_complete'],
            'set_a_10_completed' => $group['session_10_complete'],
            'participants' => [
                'values' => [
                    [ 'value' => $contact_id ]
                ]
            ]
        ];

        // if coleaders
        if ( ! empty( $group['coleaders'] ) ) {
            $coleaders = $group['coleaders'];

            $coleader_contact_ids = [];
            foreach( $coleaders as $coleader_email ) {
                $cl_raw = $wpdb->get_results(
                    "
                        SELECT um.meta_value as contact_id
                        FROM zume_users u
                        JOIN zume_usermeta um ON um.user_id=u.ID AND um.meta_key = 'zume_corresponds_to_contact'
                        WHERE u.user_email = '$coleader_email'
                     ", ARRAY_A );
                if ( empty( $cl_raw ) ) {
                    continue;
                }

                $cid = $cl_raw['contact_id'];// lookup user_id from user_email

                $coleader_contact_ids[] = $cid;
            }

            if ( ! empty( $coleader_contact_ids ) ) {
                foreach( $coleader_contact_ids as $coleader_contact_id ) {
                    $fields['participants']['values'][] = [ 'value' => $coleader_contact_id ];
                }
            }
        }

        $plan_post= DT_Posts::create_post( 'zume_plans', $fields, true, false );

        if ( ! is_wp_error( $plan_post ) ) {

            update_post_meta( $plan_post['ID'], 'join_key', $public_zume_key );

            $location = $wpdb->get_row( $wpdb->prepare(
                "SELECT lng, lat, level, label, grid_id, source
                    FROM zume_postmeta pm
                    JOIN zume_dt_location_grid_meta lgm ON pm.post_id=lgm.post_id
                    WHERE pm.meta_key = 'corresponds_to_user' AND pm.meta_value = %d
                    ORDER BY grid_meta_id desc
                    LIMIT 1",
                $user_id ), ARRAY_A );

            // session completed
            if ( $group['session_1_complete'] ) {
                Zume_System_Log_API::log('training', 'set_a_01', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime(  $group['session_1_complete'] ),
                ] );
            }
            if ($group['session_2_complete']) {
                Zume_System_Log_API::log('training', 'set_a_02', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_2_complete'] ),
                ] );
            }
            if ($group['session_3_complete']) {
                Zume_System_Log_API::log('training', 'set_a_03', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_3_complete'] ),
                ]);
            }
            if ($group['session_4_complete']) {
                Zume_System_Log_API::log('training', 'set_a_04', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_4_complete'] ),
                ]);
            }
            if ($group['session_5_complete']) {
                Zume_System_Log_API::log('training', 'set_a_05', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_5_complete'] ),
                ]);
            }
            if ($group['session_6_complete']) {
                Zume_System_Log_API::log('training', 'set_a_06', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_6_complete'] ),
                ]);
            }
            if ($group['session_7_complete']) {
                Zume_System_Log_API::log('training', 'set_a_07', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_7_complete'] ),
                ]);
            }
            if ($group['session_8_complete']) {
                Zume_System_Log_API::log('training', 'set_a_08', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_8_complete'] ),
                ]);
            }
            if ($group['session_9_complete']) {
                Zume_System_Log_API::log('training', 'set_a_09', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_9_complete'] ),
                ]);
            }
            if ($group['session_10_complete']) {
                Zume_System_Log_API::log('training', 'set_a_10', [
                    'user_id' => $user_id,
                    'post_id' => $contact_id,
                    'post_type' => 'zume',
                    'value' => 2,
                    'lng' => $location['lng'],
                    'lat' => $location['lat'],
                    'level' => $location['level'],
                    'label' => $location['label'],
                    'grid_id' => $location['grid_id'],
                    'time_end' => strtotime( $group['session_10_complete'] ),
                ]);
            }
        }

        return 'Already installed: <a href="/zume_plans/'.$plan_post['ID'].'">' . $plan_post['ID'] . "</a>";
    }



    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
}
new Zume_Simulator_Migrator();
