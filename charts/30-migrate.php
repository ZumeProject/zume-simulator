<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Migrator extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'migrate'; // lowercase
    public $slug = ''; // lowercase
    public $title;
    public $base_title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/groups/overview.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->base_title = __( 'migrate', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-simulator/$this->base_slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_head',[ $this, 'wp_head' ], 1000);
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
                            <div class="grid-x">
                                <div class="cell small-12">
                                    <button class="button" id="create_contacts">Create Contact IDs</button><span class="create_contacts loading-spinner"></span>
                                </div>
                            </div>
                            <hr>

                            <div class="grid-x" id="loop-list"></div>
                        </div>
                    `)

                window.inc = 0
                jQuery('#create_contacts').on('click', function(){
                    loop()
                })

                function loop() {
                    if ( window.inc > 1000 ) {
                        return;
                    }
                    let hash = (+new Date).toString(36);

                    jQuery('#loop-list').prepend(`<div class="cell small-12 ${hash}"><span class="${hash} loading-spinner active"></span></div>`)

                    makeRequest('POST', 'get_user_list', [], 'zume_simulator/v1/')
                        .done(function(response) {
                            console.log(response)

                            if ( response ) {
                                jQuery('.cell.'+hash).append( response )

                                makeRequest('POST', 'create_contact_id', { user_id: response }, 'zume_simulator/v1/')
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



    public static function get_user_id( WP_REST_Request $request ) {
        global $wpdb;
        $user_id = $wpdb->get_results(
            "SELECT ID
                    FROM zume_users
                    WHERE ID NOT IN (
                    SELECT user_id
                        FROM zume_usermeta um
                        WHERE um.meta_key = 'zume_corresponds_to_contact')
                   ;", ARRAY_A
        );
        shuffle($user_id);
        return $user_id[0]['ID'];
    }
    public static function create_contact_id( WP_REST_Request $request )
    {
        $data = [];
        $params = dt_recursive_sanitize_array( $request->get_params() );
        $user_id = $params['user_id'];
        $user = get_user_by('id', $user_id );

        // create contact id
        $title = $user->display_name;

        $phone = get_user_meta( $user_id, 'zume_phone_number', true );
        $email = $user->user_email;
        $language = get_user_meta( $user_id, 'zume_language', true );

        $fields = [
            'title' => $title,
            'type' => 'user',
            'corresponds_to_user' => $user_id,
            'user_ui_language' => $language,
            'user_language' => $language,
            'user_preferred_language' => $language,
            'user_phone' => $phone,
            'user_email' => $email,
            'user_communications_email' => $email,
            'contact_email' => [ 'values' => [ [ 'value' => $email ] ] ],
            'user_friend_key' => self::get_unique_public_key(),
        ];
        $data['contact_fields'] = $fields;

        // Add contact id to user record
        $new_user_contact = DT_Posts::create_post( 'contacts', $fields, true, false );
        if ( !is_wp_error( $new_user_contact ) ){
            update_user_option( $user_id, 'corresponds_to_contact', $new_user_contact['ID'] );
        }
        $data['new_contact'] = $new_user_contact;
        $data['user_meta'] = [];


        // Add roles and permissions
        $data['roles'] = Disciple_Tools_Users::save_user_roles( $user_id, [ 'multiplier' ] );


        return $data;

    }
    public static function get_unique_public_key() {
        global $wpdb;
        $duplicate_check = 1;
        while ( $duplicate_check != 0 ) {
            $key = hash( 'sha256', rand( 0, 100000 ) . uniqid(  ) . time() . rand( 0, 100000000000 ) );
            $key = str_replace( '0', '', $key );
            $key = str_replace( 'O', '', $key );
            $key = str_replace( 'o', '', $key );
            $key = strtoupper( substr( $key, 0, 5 ) );
            $duplicate_check = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", 'user_friend_key', $key ) );
        }
        return $key;
    }


}
new Zume_Simulator_Migrator();
