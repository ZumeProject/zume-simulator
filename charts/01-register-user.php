<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class Zume_Simulator_Path_Goals extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = ''; // lowercase
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
        $this->base_title = __( 'register user', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-simulator" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_head',[ $this, 'wp_head' ], 1000);
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
                let dt_country_select = `<?php echo zume_location_select_sample(); ?>`
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

                jQuery('#user-email').val(email)
                jQuery('#user-username').val(username)
                jQuery('#user-name').val(username)
                jQuery('#user-password').val(password)

                jQuery('#register-user').on('click', function(e){
                    e.preventDefault()
                    let email = jQuery('#user-email').val()
                    let name = jQuery('#user-name').val()
                    let username = jQuery('#user-username').val()
                    let password = jQuery('#user-password').val()
                    let locale = jQuery('#user-locale').val()
                    let location = jQuery('#location').val()
                    let days_ago = jQuery('#days_ago').val()
                    let data = {
                        email: email,
                        name: name,
                        username: username,
                        password: password,
                        locale: locale,
                        location: location,
                        days_ago: days_ago
                    }
                    jQuery('.loading-spinner').addClass('active')

                    window.API_post( '/wp-json/zume_simulator/v1/register_user', data, function(data){
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

}
new Zume_Simulator_Path_Goals();
