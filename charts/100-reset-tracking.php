<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Reset_Tracking extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'reset_tracking'; // lowercase
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
        $this->base_title = __( 'reset tracking', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-simulator/$this->base_slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_head',[ $this, 'wp_head' ], 1000);
        }
    }

    public function base_menu( $content ) {
        $content .= '<li><hr></li>';
        $content .= '<li><a href="'.site_url('/zume-simulator/'.$this->base_slug).'" id="'.$this->base_slug.'-menu">' .  $this->base_title . '</a></li>';
        return $content;
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
                                <div class="cell"><h2>RESET TRACKING</h2></div>
                            </div>
                            <hr>
                            <span class="loading-spinner active"></span>
                            <div class="grid-x">
                                <div class="cell small-12">
                                    Reset the tracking information in the reports table so as to reset the funnel information.
                                </div>
                            </div>
                            <hr>
                            <div class="grid-x">
                                <div class="cell small-12">
                                    <button class="button" id="reset-tracking">Reset Tracking</button>
                                </div>
                            </div>
                        </div>
                    `)

                jQuery('#reset-tracking').on('click', function(){
                    jQuery('.loading-spinner').addClass('active')

                    makeRequest('POST', 'reset_tracking', [], 'zume_simulator/v1/')
                    .done(function(response) {
                        console.log(response)
                        jQuery('.loading-spinner').removeClass('active')
                    })

                })

                jQuery('.loading-spinner').delay(3000).removeClass('active')
            })

        </script>
        <?php
    }


}
new Zume_Simulator_Reset_Tracking();
