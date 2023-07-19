<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Next_Step extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'simulate_next_steps'; // lowercase
    public $slug = '';
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
        $this->base_title = __( 'simulate next steps', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "zume-simulator/$this->base_slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_head',[ $this, 'wp_head' ], 1000);
        }
    }

    public function base_menu( $content ) {
        $content .= '<li class=""><a href="'.site_url('/zume-simulator/'.$this->base_slug).'" id="'.$this->base_slug.'-menu">' .  $this->base_title . '</a></li>';
        return $content;
    }
    public function wp_head() {
        $this->js_api();
        ?>
        <style>
            .left-border {
                border-left: 1px solid #ddd;
            }
            .alt-color {
                background-color: #02b4e9;
            }
            .button.done {
                background-color: #39ea29;
            }
            .button.done:hover {
                background-color: #25951b;
            }
            #location {
                width: 200px;
            }
            .button.zume {
                border-radius: 0 !important;
            }
            .button.zume.button-grey {
                background-color: lightgrey;
                color: black;
                cursor: default;
            }
            .button.zume.button-grey:hover {
                background-color: lightgrey;
                color: black;
            }
            .button.zume.done {
                background-color: #39ea29 !important;
            }
            .button.zume.done:hover {
                background-color: #25951b;
            }
        </style>

        <script>
            window.user_id = '<?php echo get_current_user_id() ?>'

            jQuery(document).ready(function(){
                "use strict";
                let chart = jQuery('#chart')
                window.training_items = [<?php echo json_encode( zume_training_items() ) ?>][0]
                let host_buttons_html = ''
                let mawl_buttons_html = ''

                jQuery.each( window.training_items, function(i,v){

                    host_buttons_html += `<div class="primary button-group expanded no-gaps"><a class="button zume button-grey clear">${v.title}</a>`
                    jQuery.each(v.host, function(ih, vh ) {
                        if ( 'Heard' === vh.label ) {
                            host_buttons_html += `<button class="button zume ${vh.type}${vh.subtype}" data-top="${vh.type}"  data-subtype="${vh.subtype}" data-stage="2">${vh.label}</button>`
                        }
                    })
                    host_buttons_html += `</div><br>`

                })

                chart.empty().html(`
                        <div class="grid-x">
                            <div class="cell medium-9">
                                <span class="loading-spinner active"></span>
                            </div>
                            <div class="cell medium-3 right">
                                <h2>Simulate Next Steps</h2>
                            </div>
                            <div class="cell">
                                <hr>
                            </div>
                        </div>

                        <div class="grid-x grid-padding-x">
                            <div class="cell small-6">
                                <div id="funnel" style="height:980px; padding: 1em; overflow: hidden scroll; border: 1px solid lightgrey;">
                                    <div class="grid-x grid-padding-x">

                                        <div class="cell small-8">
                                            <h2>KEY FUNNEL STEPS</h2>
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <h2>CTAs</h2>
                                        </div>

                                        <div class="cell">
                                            <hr>
                                        </div>


                                         <div class="cell">
                                            <h2>Anonymous (0)</h2>
                                        </div>
                                        <div class="cell small-8">
                                            <button class="button zume systemregistered" data-top="system" data-subtype="registered" data-stage="0">Registered</button>
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <button class="button zume alt-color systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-stage="0">Coach Request</button><br>
                                            <button class="button zume alt-color systemjoined_online_training" data-top="system" data-subtype="joined_online_training" data-stage="0">Joined Online Training</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>

                                        <div class="cell">
                                            <h2>Registrant (1)</h2>
                                        </div>
                                        <div class="cell small-8">
                                           <button class="button zume systemmade_a_plan" data-top="system" data-subtype="made_a_plan" data-stage="1">Made a Plan</button>
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <button class="button zume alt-color systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-stage="1">Coach Request</button><br>
                                            <button class="button zume alt-color systemset_profile" data-top="system" data-subtype="set_profile" data-stage="1">Set Profile</button><br>
                                            <button class="button zume alt-color systeminvited_friends" data-top="system" data-subtype="invited_friends" data-stage="1">Invited Friends</button><br>
                                            <button class="button zume alt-color systemjoined_online_training" data-top="system" data-subtype="joined_online_training" data-stage="1">Joined Online Training</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>Active Training Trainee (2)</h2>
                                        </div>
                                        <div class="cell small-8">
                                            ${host_buttons_html}
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <button class="button zume alt-color systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-stage="2">Coach Request</button><br>
                                            <button class="button zume alt-color systemset_profile" data-top="system" data-subtype="set_profile" data-stage="2">Set Profile</button><br>
                                            <button class="button zume alt-color systeminvited_friends" data-top="system" data-subtype="invited_friends" data-stage="2">Invited Friends</button><br>
                                            <button class="button zume alt-color systemmade_3_month_plan" data-top="system" data-subtype="made_3_month_plan" data-stage="2">Made 3-Month Plan</button>
                                        </div>
                                        <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>Post-Training Trainee (3)</h2>
                                        </div>
                                        <div class="cell small-8">
                                           <button class="button zume systemmade_first_lifestyle_report" data-top="system" data-subtype="made_first_lifestyle_report" data-stage="3">Made First Lifestyle Report</button>
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <button class="button zume alt-color systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-stage="3">Coach Request</button><br>
                                            <button class="button zume alt-color systemset_profile" data-top="system" data-subtype="set_profile" data-stage="3">Set Profile</button><br>
                                            <button class="button zume alt-color systemcompleted_3_month_plan" data-top="system" data-subtype="completed_3_month_plan" data-stage="3">Completed 3-Month Plan</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>Stage 1 - Partial Practitioner (4)</h2>
                                        </div>
                                        <div class="cell small-8">
                                            <button class="button zume systemcompleted_mawl" data-top="system" data-subtype="completed_mawl" data-stage="4">Full MAWL Skills</button>
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <button class="button zume alt-color systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-stage="4">Coach Request</button><br>
                                            <button class="button zume alt-color systemsubmitted_church_report" data-top="system" data-subtype="submitted_progress_report" data-stage="4">Report Churches</button><br>
                                            <button class="button zume alt-color systemjoined_affinity_hub" data-top="system" data-subtype="joined_affinity_hub" data-stage="4">Join Affinity Hub</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>

                                        <div class="cell">
                                            <h2>Stage 2 - Completed Practitioner (5)</h2>
                                        </div>
                                        <div class="cell small-8">
                                           <button class="button zume systemseeing_generational_fruit" data-top="system" data-subtype="seeing_generational_fruit" data-stage="5">Seeing Generational Fruit</button>
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <button class="button zume alt-color systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-stage="5">Coach Request</button><br>
                                            <button class="button zume alt-color systemsubmitted_church_report" data-top="system" data-subtype="submitted_progress_report" data-stage="5">Report Churches</button><br>
                                            <button class="button zume alt-color systemjoined_affinity_hub" data-top="system" data-subtype="joined_affinity_hub" data-stage="5">Join Affinity Hub</button>
                                        </div>

                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>Stage 3 - Multiplying Practitioner (6)</h2>
                                        </div>
                                        <div class="cell small-8">
                                        </div>
                                        <div class="cell small-4 left-border">
                                            <button class="button zume alt-color systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-stage="6">Coach Request</button><br>
                                            <button class="button zume alt-color systemsubmitted_church_report" data-top="system" data-subtype="submitted_progress_report" data-stage="6">Report Churches</button><br>
                                            <button class="button zume alt-color systemcoaching_others" data-top="system" data-subtype="coaching_others" data-stage="6">Coach Others</button><br>
                                            <button class="button zume alt-color systemjoined_affinity_hub" data-top="system" data-subtype="joined_affinity_hub" data-stage="6">Join Affinity Hub</button><br>
                                            <button class="button zume alt-color systemproviding_hub_leadership" data-top="system" data-subtype="providing_hub_leadership" data-stage="6">Provide Hub Leadership</button>
                                        </div>

                                         <div class="cell">
                                            <hr>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="cell small-6">
                                <h2>Encouragement Action</h2>
                                <div id="report"></div>
                            </div>
                        </div>
                    </div>
                    `)


                jQuery('.button.zume').on('click', function(event) {
                    jQuery('.loading-spinner').addClass('active')
                    let button = jQuery(this)
                    let type = button.data('top')

                    let subtype = button.data('subtype')


                    jQuery('#report').html(  'Current Stage: ' + button.data('stage') + '<br>Last Action: ' + type + ' | ' + subtype + '<hr><div id="ctas" class="grid-x"></div><hr><div id="date-list" class="grid-x"></div>' )

                    // Priority CTAs
                    let ctas = ['Get a Coach', 'Complete Profile', 'Invite Friends', 'Make a Plan']
                    let ctaList = '<div class="cell"><h4>PRIORITY SITE CTAs</h4></div>'
                    jQuery.each(ctas, function(ih, vh ) {
                        ctaList += `<div class="cell small-6">${vh}</div><div class="cell small-6">Priority CTA</div>`
                    })
                    jQuery('#ctas').append(ctaList)

                    // Email Schedule
                    let time = ['1 Day', '2 Days', '3 Days', '4 Days', '5 Days', '6 Days', '7 Days', '2 Weeks', '3 Weeks', '4 Weeks', '2 Months', '3 Months']
                    let list = '<div class="cell"><h4>EMAIL SCHEDULE</h4></div>'
                    jQuery.each(time, function(ih, vh ) {
                        list += `<div class="cell small-6">${vh}</div><div class="cell small-6"> Action to be taken</div>`
                    })
                    jQuery('#date-list').append(list)

                    jQuery('.loading-spinner').removeClass('active')
                })

                jQuery('.loading-spinner').removeClass('active')
            })
        </script>
        <?php
    }
}
new Zume_Simulator_Next_Step();
