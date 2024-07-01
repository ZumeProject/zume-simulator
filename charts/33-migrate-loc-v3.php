<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Migrator_Loc3 extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'migratelocation3'; // lowercase
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
        $this->base_title = __( 'migrate plans install', 'disciple_tools' );

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
            "SELECT um.umeta_id
                    FROM zume_usermeta um
                    JOIN zume_usermeta um1 ON um1.user_id=um.user_id AND um1.meta_key = 'zume_corresponds_to_contact'
                    WHERE um.meta_key LIKE 'zume_group%'
                   ;"
        );

        sort( $users);

        update_post_meta( 5, 'legacy_plan_install', $users );

        return true;
    }
    public static function process_record( WP_REST_Request $request )
    {
        global $wpdb;
        $params = dt_recursive_sanitize_array( $request->get_params() );
        $fields = [];

        $user_list = get_post_meta( 5, 'legacy_plan_install', true);
        sort( $user_list);
        $user_id = $user_list[0];
        if ( empty( $user_id ) ) {
            return 'done.';
        }
        unset($user_list[0]);
        sort( $user_list);
        update_post_meta( 5, 'legacy_plan_install', $user_list );

        $result = $wpdb->get_row($wpdb->prepare( "
            SELECT um.umeta_id, um.user_id, um.meta_value, um1.meta_value as contact_id
                FROM zume_usermeta um
                JOIN zume_usermeta um1 ON um1.user_id=um.user_id AND um1.meta_key = 'zume_corresponds_to_contact'
                WHERE um.meta_key LIKE 'zume_group%'
                AND um.umeta_id = %s
        ", $user_id), ARRAY_A );

        $group = unserialize( $result['meta_value'] );
        if ( empty($group['owner'] ) ) {
            return false;
        }

        $title = $group['group_name'];
        $user_id = $result['user_id'];
        $contact_id = $result['contact_id'];

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
            'title' => $title,
            'assigned_to' => $user_id,
            'set_type' => 'set_a',
            'visibility' => 'private',
            'created_date' => $creation_time,
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
                $cid = $wpdb->get_var("
                        SELECT um.meta_value as contact_id
                        FROM zume_users u
                        JOIN zume_usermeta um ON um.user_id=u.ID AND um.meta_key = 'zume_corresponds_to_contact'
                        WHERE u.user_email = '$coleader_email'"
                );
                if ( empty($cid ) ) {
                    continue;
                }
                $coleader_contact_ids[] = $cid;
            }

            if ( ! empty( $coleader_contact_ids ) ) {
                foreach( $coleader_contact_ids as $coleader_contact_id ) {
                    $fields['participants']['values'][] = [ 'value' => $coleader_contact_id ];
                }
            }
        }

        return DT_Posts::create_post( 'zume_plans', $fields, true, false );

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
                                    <button class="button" id="process_record">Add Plans</button><span class="process_record loading-spinner"></span>
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
                    // if ( window.inc > 100 ) {
                    //     return;
                    // }
                    let hash = (+new Date).toString(36);

                    jQuery('#loop-list').prepend(`<div class="cell small-12 ${hash}"><span class="${hash} loading-spinner active"></span></div>`)

                    makeRequest('POST', 'process_record', { }, namespace)
                        .done(function(response2) {
                            console.log(response2)


                            jQuery('.'+hash+'.loading-spinner').removeClass( 'active' )
                            if ( ! response2 ) {
                                jQuery('.cell.'+hash).append( 'Unable to create plan' )
                            } else {
                                jQuery('.cell.'+hash).append( response2.ID )
                            }

                            window.inc++
                            loop()
                        })
                }
            })
        </script>
        <?php
    }


}
new Zume_Simulator_Migrator_Loc3();
