<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( strpos( dt_get_url_path(), 'zume_app' ) !== false || dt_is_rest() ){
    Zume_Public_Stats_Churches::instance();
}

/**
 * Class Disciple_Tools_Plugin_Starter_Template_Magic_Link
 */
class Zume_Public_Stats_Churches extends DT_Magic_Url_Base {

    public $magic = false;
    public $parts = false;
    public $page_title = 'All Time Activity';
    public $root = "zume_app";
    public $type = 'stats_churches';
    public $post_type = 'activity';
    private $meta_key = '';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $this->meta_key = $this->root . '_' . $this->type . '_magic_key';
        parent::__construct();

        /**
         * post type and module section
         */
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );

        /**
         * tests if other URL
         */
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }
        /**
         * tests magic link parts are registered and have valid elements
         */
        if ( !$this->check_parts_match( false ) ){
            return;
        }

        // load if valid url
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );

    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        $allowed_js[] = 'jquery-cookie';
        $allowed_js[] = 'mapbox-cookie';
        $allowed_js[] = 'mapbox-gl';
        $allowed_js[] = 'lodash';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        $allowed_css[] = 'mapbox-gl-css';
        return $allowed_css;
    }

    public function scripts() {
        wp_enqueue_script( 'lodash' );
    }

    /**
     * Writes custom styles to header
     *
     * @see DT_Magic_Url_Base()->header_style() for default state
     */
    public function header_style(){
        ?>
        <style>
            body {
                background-color: white;
                padding: 0;
            }
        </style>
        <?php
    }

    /**
     * Writes javascript to the footer
     * @see DT_Magic_Url_Base()->footer_javascript() for default state
     */
    public function footer_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'stats' => [
                    'total_churches' => $this->_total(),
                    'unique_locations_with_churches' => $this->_unique_locations(),
                ],
                'translation' => [
                    'title' => 'Trainings'
                ]
            ]) ?>][0]
            /* <![CDATA[ */

            window.post_request = ( action, data ) => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: action, parts: jsObject.parts, data: data }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                        jQuery('.loading-spinner').removeClass('active')
                    })
            }
        </script>
        <?php
    }

    public function body(){
        DT_Mapbox_API::geocoder_scripts();
        ?>
        <div id="chart"><span class="loading-spinner active"></span></div>
        <script>
            jQuery(document).ready(function(){
                let chart = jQuery('#chart')

                chart.empty().append(
                    `<div class="grid-x grid-padding-x">
                        <div class="cell medium-3 text-center callout">
                            <h2>Total Churches</h2>
                            <span class="hero_number" id="total_churches">0</span>
                            <p>This is the number of churches recorded in the zume.training/coaches system by coaches.</p>
                        </div>
                        <div class="medium-3 cell text-center callout">
                            <h2>Total Unique Locations With A Church</h2>
                            <p class="hero_number" id="unique_locations_with_churches">0</p>
                            <p class="hero_subdetail">out of 44,141</p>
                            <p>These are unique locations out of the 44,141 unique county level locations in the saturation grid which have a church reported and geocoded in it.</p>
                        </div>
                      </div>
                    <style>
                        .hero_number {
                            font-size: 6em;
                            padding-bottom:0;
                            margin-bottom:0;
                        }
                        .hero_subdetail {
                            font-size: 2em;
                            font-weight:bold;
                        }
                        .callout {
                            margin:.5em;
                            border-radius: 10px;
                        }
                    </style>
                `)

                jQuery('#total_churches').html(jsObject.stats.total_churches)
                jQuery('#unique_locations_with_churches').html(jsObject.stats.unique_locations_with_churches)

            })
        </script>
        <?php
    }

    /**
     * Register REST Endpoints
     */
    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace, '/'.$this->type, [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'endpoint' ],
                    'permission_callback' => function( WP_REST_Request $request ){
                        return true;
                    },
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['parts'], $params['action'], $params['data'] ) ) {
            return new WP_Error( __METHOD__, "Missing parameters", [ 'status' => 400 ] );
        }

        $params = dt_recursive_sanitize_array( $params );
        $action = sanitize_text_field( wp_unslash( $params['action'] ) );

        switch ( $action ) {

            default:
                return new WP_Error( __METHOD__, "Missing valid action", [ 'status' => 400 ] );
        }
    }

    public function _unique_locations( ){
        $flat_grid = Zume_App_Heatmap::query_saturation_list();
        $grid_totals = Zume_App_Heatmap::query_church_grid_totals( 'full' );

        $unique_locations = 0;
        foreach($flat_grid as $grid_id => $grid ) {
            if ( isset( $grid_totals[$grid_id] ) ) {
                $unique_locations++;
            }
        }
        return number_format( $unique_locations );
    }
    public function _total( ){
        global $wpdb;
        $total = $wpdb->get_var("SELECT count(*)
                FROM wp_3_posts p
                JOIN wp_3_postmeta pm ON pm.post_id=p.ID AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                WHERE post_type = 'groups'");

        return number_format( $total );
    }



}
