<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class Zume_Simulator_Path_Goals extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $permissions = ['manage_dt'];
    public $base_slug = ''; // lowercase
    public $slug = ''; // lowercase
    public $title;
    public $base_title;
    public $namespace = 'zume_simulator/v1';
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/groups/overview.js'; // should be full file name plus extension

    public function __construct() {
        parent::__construct();
        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }

        if ( !$this->has_permission() ){
            return;
        }
        $this->base_title = __( 'register user', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-simulator" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_head',[ $this, 'wp_head' ], 1000);
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
    }

    public function register_user( WP_REST_Request $request ){
        $params = $request->get_params();

        if ( isset( $params['email'], $params['name'], $params['username'], $params['password'] ) ){
            $user_roles = [ 'multiplier' ];

            if ( empty( $params['name'] ) ) {
                $params['name'] = $params['username'];
            }
            if ( isset( $params['locale'] ) ) {
                $locale = $params['locale'];
            }
            $phone = null;
            if ( isset( $params['phone'] ) ) {
                $phone = $params['phone'];
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

            $fields = [
                'user_email' => $params['email'],
                'user_phone' => $params['phone'],
                'location_grid_meta' => [
                    'values' => [
                        [
                            'label' => $params['label'],
                            'level' => $params['level'],
                            'lng' => $params['lng'],
                            'lat' => $params['lat'],
                        ]
                    ],
                ]
            ];
            $contact_location = DT_Posts::update_post( 'contacts', $contact_id, $fields, true, false );

            dt_report_insert( [
                'user_id' => $user_id,
                'post_id' => $contact_id,
                'post_type' => 'zume',
                'type' => 'system',
                'subtype' => 'registered',
                'value' => 0,
                'lng' => $params['lng'],
                'lat' => $params['lat'],
                'level' => $params['level'],
                'label' => $params['label'],
                'grid_id' => $params['grid_id'],
                'time_end' =>  strtotime( 'Today -'.$params['days_ago'].' days' ),
                'hash' => hash('sha256', maybe_serialize($params)  . time() ),
            ] );

            Zume_System_Encouragement_API::_install_plan( $user_id, Zume_System_Encouragement_API::_get_recommended_plan( $user_id, 'system', 'registered' ) );

            return [
                'user_id' => $user_id,
                'contact_id' => $contact_id,
                'contact_location' => $contact_location,
            ];
        } else {
            return new WP_Error( 'missing_error', 'Missing fields', [ 'status' => 400 ] );
        }
    }

    public function base_menu( $content ) {
        $content .= '<li>ZÚME SIMULATOR</li>';
        $content .= '<li><hr></li>';
        $content .= '<li><a href="'.site_url('/zume-simulator/'.$this->base_slug).'" id="'.$this->base_slug.'-menu">' .  $this->base_title . '</a></li>';
        return $content;
    }

    public function wp_head() {
        $this->styles();
        $this->js_api();
        ?>
        <script>

            jQuery(document).ready(function(){
                "use strict";
                window.API_post = (url, data, callback ) => {
                    return jQuery.post(url, data, callback);
                }

                let chart = jQuery('#chart')

                let dt_language_select = `<?php echo dt_language_select(); ?>`
                let dt_country_select = `<?php echo $this->location_select_sample(); ?>`
                chart.empty().html(`
                        <div id="zume-simulator-path">
                            <div class="grid-x">
                                <div class="cell"><h2>SIMULATOR</h2></div>
                            </div>
                            <hr>
                            <span class="loading-spinner active"></span>
                            <div class="grid-x">
                                <div class="cell small-12">
                                    This plugin is for development only. It is a collection of tools to simulate and test the zume training system
                                </div>
                            </div>
                            <hr>
                            <div class="grid-x">
                                <div class="cell small-12">
                                    <label for="user-name">Display Name</label>
                                    <input type="text" id="user-name" placeholder="display name">
                                    <label for="user-email">Email</label>
                                    <input type="text" id="user-email" placeholder="email">
                                    <label for="user-username">User Name</label>
                                    <input type="text" id="user-username" placeholder="username">
                                    <label for="user-password">Password</label>
                                    <input type="text" id="user-password" placeholder="password">
                                    <label for="user-phone">Phone</label>
                                    <input type="text" id="user-phone" placeholder="phone">
                                    <label for="user-days_ago">Number of Days Ago</label>
                                    <select id="days_ago">
                                        <option value="0">today</option>
                                        <option value="3">3 Days Ago</option>
                                        <option value="7">7 Days Ago</option>
                                        <option value="10">10 Days Ago</option>
                                        <option value="14">14 Days Ago</option>
                                        <option value="18">18 Days Ago</option>
                                        <option value="24">24 Days Ago</option>
                                        <option value="30">30 Days Ago</option>
                                        <option value="60">60 Days Ago</option>
                                        <option value="90">90 Days Ago</option>
                                        <option value="100">100 Days Ago</option>
                                    </select>
                                    <label for="user-locale">Language</label>
                                    ${dt_language_select}
                                    <label for="user-locale">Location</label>
                                    ${dt_country_select}
                                    <br><br><button class="button" id="register-user">Register User</button>
                                    <div id="results"></div>
                                </div>
                            </div>
                        </div>
                    `)

                function makeid(length) {
                    let result = '';
                    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    const charactersLength = characters.length;
                    let counter = 0;
                    while (counter < length) {
                        result += characters.charAt(Math.floor(Math.random() * charactersLength));
                        counter += 1;
                    }
                    return result;
                }

                let username = makeid(8)
                let email = username + '@emailtest.com'
                let password = username + '_password'
                let phone = Math.floor(Math.random() * 1000000000);

                jQuery('#user-email').val(email)
                jQuery('#user-username').val(username)
                jQuery('#user-name').val(username)
                jQuery('#user-password').val(password)
                jQuery('#user-phone').val(phone)

                jQuery('#register-user').on('click', function(e){
                    e.preventDefault()
                    let email = jQuery('#user-email').val()
                    let name = jQuery('#user-name').val()
                    let username = jQuery('#user-username').val()
                    let password = jQuery('#user-password').val()
                    let phone = jQuery('#user-phone').val()
                    let locale = jQuery('#user-locale').val()
                    let location = jQuery('#location')

                    let days_ago = jQuery('#days_ago').val()
                    let data = {
                        email: email,
                        name: name,
                        username: username,
                        password: password,
                        locale: locale,
                        phone: phone,
                        lng: location.find(':selected').data('lng'),
                        lat: location.find(':selected').data('lat'),
                        level: location.find(':selected').data('level'),
                        label: location.find(':selected').data('label'),
                        grid_id: location.find(':selected').data('grid-id'),
                        days_ago: days_ago
                    }
                    jQuery('.loading-spinner').addClass('active')

                    makeRequest('POST', 'register_user', data, window.site_info.rest_root ).done( function( data ) {
                        console.log(data)
                        jQuery('#results').empty().html(`SUCCESS<br><br><a href="/contacts/${data.contact_id}" target="_blank">View This User</a>`)

                        jQuery('.loading-spinner').removeClass('active')
                    })

                })

                jQuery('.loading-spinner').delay(3000).removeClass('active')
            })

        </script>
        <?php
    }
    public function styles() {
        ?>
        <style>
            .side-menu-item-highlight {
                font-weight: 300;
            }
            #-menu {
                font-weight: 700;
            }
            .zume-cards {
                max-width: 700px;
            }
            #location {
                width: 100% !important;
            }
        </style>
        <?php
    }

    public function location_select_sample() {
        $list = $this->_dev_location_list();
        shuffle( $list );

        $html = '<select name="location" id="location" style=" width:200px;">';
        foreach( $list as $item ){
            $html .= '<option value="'.$item['grid_id'].'" data-lng="'.$item['lng'].'" data-lat="'.$item['lat'].'" data-level="'.$item['level'].'" data-label="'.$item['label'].'" data-grid-id="'.$item['grid_id'].'">' . $item['label'] . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    public function  _dev_location_list( $grid_id = null )
    {
        $list = array(
            array('lng' => '-119.699', 'lat' => '37.0744', 'level' => 'region', 'label' => 'California, United States', 'grid_id' => '100364453'),
            array('lng' => '-114.757', 'lat' => '54.6427', 'level' => 'region', 'label' => 'Alberta, Canada', 'grid_id' => '100041940'),
            array('lng' => '-105.605', 'lat' => '39.1902', 'level' => 'region', 'label' => 'Colorado, United States', 'grid_id' => '100364539'),
            array('lng' => '-100', 'lat' => '31', 'level' => 'region', 'label' => 'Texas, United States of America', 'grid_id' => '100366941'),
            array('lng' => '-99.1456', 'lat' => '19.4194', 'level' => 'region', 'label' => 'Ciudad de México, México', 'grid_id' => '100245639'),
            array('lng' => '-99.1456', 'lat' => '19.4194', 'level' => 'region', 'label' => 'Mexico City, Mexico', 'grid_id' => '100245639'),
            array('lng' => '-90.5131', 'lat' => '14.6414', 'level' => 'region', 'label' => 'Guatemala, Guatemala', 'grid_id' => '100132337'),
            array('lng' => '-85.9823', 'lat' => '35.9886', 'level' => 'region', 'label' => 'Tennessee, United States', 'grid_id' => '100366703'),
            array('lng' => '-79.3897', 'lat' => '35.5569', 'level' => 'region', 'label' => 'North Carolina, United States', 'grid_id' => '100366162'),
            array('lng' => '-71.5783', 'lat' => '43.6899', 'level' => 'region', 'label' => 'New Hampshire, United States', 'grid_id' => '100366017'),
            array('lng' => '-3.25', 'lat' => '37.25', 'level' => 'region', 'label' => 'Granada, Spain', 'grid_id' => '100075268'),
            array('lng' => '7.75', 'lat' => '10.3333', 'level' => 'region', 'label' => 'Kaduna, Nigeria', 'grid_id' => '100254189'),
            array('lng' => '16.3731', 'lat' => '48.2083', 'level' => 'region', 'label' => 'Wien, Österreich', 'grid_id' => '100003596'),
            array('lng' => '19.0408', 'lat' => '47.4983', 'level' => 'region', 'label' => 'Budapest, Hungary', 'grid_id' => '100134485'),
            array('lng' => '32.5764', 'lat' => '-25.9153', 'level' => 'region', 'label' => 'Maputo, Mozambique', 'grid_id' => '100249267'),
            array('lng' => '32.5764', 'lat' => '-25.9153', 'level' => 'region', 'label' => 'Maputo, Mozambique', 'grid_id' => '100249533'),
            array('lng' => '36.8172', 'lat' => '-1.28325', 'level' => 'region', 'label' => 'Nairobi, Kenya', 'grid_id' => '100234685'),
            array('lng' => '42.9545', 'lat' => '14.7979', 'level' => 'region', 'label' => 'Al Hudaydah, Yemen', 'grid_id' => '100380173'),
            array('lng' => '44.0167', 'lat' => '13.5667', 'level' => 'region', 'label' => 'Ta\'izz, Yemen', 'grid_id' => '100380435'),
            array('lng' => '44.0333', 'lat' => '36.1833', 'level' => 'region', 'label' => 'Erbil Governorate, Iraq', 'grid_id' => '100222764'),
            array('lng' => '44.2', 'lat' => '15.35', 'level' => 'region', 'label' => 'Sana\'a, Yemen', 'grid_id' => '100380228'),
            array('lng' => '44.2', 'lat' => '15.35', 'level' => 'region', 'label' => 'صنعاء اليمن', 'grid_id' => '100380228'),
            array('lng' => '44.2059', 'lat' => '15.3539', 'level' => 'region', 'label' => 'Sana\'a, Yemen', 'grid_id' => '100380228'),
            array('lng' => '45.3333', 'lat' => '14.2667', 'level' => 'region', 'label' => 'Al Bayda\', Yemen', 'grid_id' => '100380143'),
            array('lng' => '74.3142', 'lat' => '31.5657', 'level' => 'region', 'label' => 'Punjab, Pakistan', 'grid_id' => '100260114'),
            array('lng' => '74.5667', 'lat' => '42.8667', 'level' => 'region', 'label' => 'Бишкек, Киргизия', 'grid_id' => '100235155'),
            array('lng' => '85.13', 'lat' => '25.37', 'level' => 'region', 'label' => 'Bihar, India', 'grid_id' => '100219470'),
            array('lng' => '102.51', 'lat' => '18.14', 'level' => 'region', 'label' => 'Vientiane, Laos', 'grid_id' => '100238955'),
            array('lng' => '104.322', 'lat' => '15.12', 'level' => 'region', 'label' => 'Si Sa Ket, Thailand', 'grid_id' => '100344410'),
            array('lng' => '112.629', 'lat' => '31.358', 'level' => 'region', 'label' => 'Hubei, People\'s Republic of China', 'grid_id' => '100052035'),
            array('lng' => '115.138', 'lat' => '-8.36917', 'level' => 'region', 'label' => 'Bali, Indonesia', 'grid_id' => '100134675'),
            array('lng' => '115.138', 'lat' => '-8.36917', 'level' => 'region', 'label' => 'Bali, Indonesia', 'grid_id' => '100148999'),
            array('lng' => '121.466', 'lat' => '25.012', 'level' => 'region', 'label' => '台湾新北市', 'grid_id' => '100352885'),
            array('lng' => '127', 'lat' => '37.5833', 'level' => 'region', 'label' => '서울특별시, 대한민국', 'grid_id' => '100238808')
        );

        if ($grid_id) {
            foreach ($list as $item) {
                if ($item['grid_id'] == $grid_id) {
                    return $item;
                }
            }
        }
        return $list;
    }
    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace  ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }

}
new Zume_Simulator_Path_Goals();
