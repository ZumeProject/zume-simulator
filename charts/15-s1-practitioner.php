<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Path_S1 extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 's1_practitioners'; // lowercase
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
        $this->base_title = __( 'S1 (Partial)', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-path/$this->base_slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
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
                        <div id="zume-path">
                            <div class="grid-x">
                                <div class="cell small-6"><h1>Stage 1 - Partial Practitioner</h1></div>
                                <div class="cell small-6 right">Learning through doing. Implementing partial checklist / 4-fields</div>
                            </div>
                            <hr>
                            <div class="grid-x">
                               <h2>Cumulative</h2>
                            </div>
                            <div class="grid-x">
                                <div class="cell total_s1"><span class="loading-spinner active"></span></div>
                            </div>
                            <div class="grid-x grid-margin-x grid-margin-y">
                                 <div class="cell medium-6 no_coach"><span class="loading-spinner active"></span></div>
                                 <div class="cell medium-6 has_not_reported"><span class="loading-spinner active"></span></div>
                            </div>
                            <hr>
                            <div class="grid-x">
                                <div class="cell center"><h1 id="range-title">Last 30 Days</h1></div>
                                <div class="cell small-6">
                                    <h2>Time Range</h2>
                                </div>
                                <div class="cell small-6">
                                    <span style="float: right;">
                                        <select id="range-filter">
                                            <option value="30">Last 30 days</option>
                                            <option value="7">Last 7 days</option>
                                            <option value="90">Last 90 days</option>
                                            <option value="365">Last 1 Year</option>
                                        </select>
                                    </span>
                                    <span class="loading-spinner active" style="float: right; margin:0 10px;"></span>
                                </div>
                            </div>
                            <div class="grid-x grid-margin-x grid-margin-y">
                                 <div class="cell medium-12 in_and_out"><span class="loading-spinner active"></span></div>
                                 <div class="cell medium-6 coaching_request"><span class="loading-spinner active"></span></div>
                                 <div class="cell medium-6 reporting_churches"><span class="loading-spinner active"></span></div>
                                 <div class="cell medium-6 joined_affinity_hub"><span class="loading-spinner active"></span></div>
                            </div>
                        </div>
                    `)

                // totals
                window.spin_add()
                window.API_get( window.site_info.total_url, { stage: "att", key: "total_s1" }, ( data ) => {
                    data.link = ''
                    data.label = '(S1) Partial Practitioners'
                    data.description = 'Learning through doing. Implementing partial checklist / 4-fields'
                    jQuery('.'+data.key).html(window.template_map_list(data))
                    window.click_listener( data )
                    window.spin_remove()
                })
                window.spin_add()
                window.API_get( window.site_info.total_url, { stage: "general", key: "no_coach" }, ( data ) => {
                    data.valence = 'valence-grey'
                    data.label = 'Has No Coach'
                    data.description = 'Description'
                    jQuery('.'+data.key).html(window.template_single_list(data))
                    window.click_listener( data )
                    window.spin_remove()
                })
                window.spin_add()
                window.API_get( window.site_info.total_url, { stage: "general", key: "has_not_reported" }, ( data ) => {
                    data.valence = 'valence-grey'
                    data.label = 'Has Not Reported'
                    data.description = 'Description'
                    jQuery('.'+data.key).html(window.template_single_list(data))
                    window.click_listener( data )
                    window.spin_remove()
                })

                window.path_load = ( range ) => {

                    window.spin_add()
                    window.API_get( window.site_info.total_url, { stage: "general", key: "in_and_out", range: range }, ( data ) => {
                        data.label = 'Stage 1 Flow'
                        data.description = 'Description'
                        jQuery('.'+data.key).html( window.template_in_out( data ) )
                        window.click_listener( data )
                        window.spin_remove()
                    })
                    window.spin_add()
                    window.API_get( window.site_info.total_url, { stage: "general", key: "coaching_request", range: range }, ( data ) => {
                        data.label = 'Coaching Requests'
                        jQuery('.'+data.key).html(window.template_single_list(data))
                        window.click_listener( data )
                        window.spin_remove()
                    })
                    window.spin_add()
                    window.API_get( window.site_info.total_url, { stage: "general", key: "reporting_churches", range: range }, ( data ) => {
                        data.label = 'Reporting Churches'
                        jQuery('.'+data.key).html(window.template_single_list(data))
                        window.click_listener( data )
                        window.spin_remove()
                    })
                    window.spin_add()
                    window.API_get( window.site_info.total_url, { stage: "general", key: "joined_affinity_hub", range: range }, ( data ) => {
                        data.label = 'Joined Affinity Hub'
                        jQuery('.'+data.key).html(window.template_single_map(data))
                        window.click_listener( data )
                        window.spin_remove()
                    })

                }
                window.setup_filter()

                window.click_listener = ( data ) => {
                    window.load_list(data)
                    window.load_map(data)
                }
            })

        </script>
        <?php
    }

    public function data() {
        return [
            'translations' => [
                'title_overview' => __( 'Project Overview', 'disciple_tools' ),
            ],
        ];
    }

}
new Zume_Simulator_Path_S1();
