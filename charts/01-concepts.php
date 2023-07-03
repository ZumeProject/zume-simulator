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
        $this->base_title = __( 'simulator', 'disciple_tools' );

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
                let chart = jQuery('#chart')
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
                        </div>
                    `)

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
        </style>
        <?php
    }

}
new Zume_Simulator_Path_Goals();
