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
        $this->base_title = __( 'simulate encouragement system', 'disciple_tools' );

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
                /*margin-bottom: 0 !important;*/
            }
            .button.button-grey {
                background-color: lightgrey;
                color: black;
                cursor: default;
            }
            .button.button-grey:hover {
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
                    host_buttons_html += `<a class="button  button-grey expanded clear" style="white-space:nowrap; overflow: hidden;margin-bottom:0;">(${i}) ${v.title}</a><div class="primary button-group expanded no-gaps" >`
                    jQuery.each(v.host, function(ih, vh ) {
                        // if ( 'Heard' === vh.label ) {
                            host_buttons_html += `<button class="button zume ${vh.type}${vh.subtype}" data-top="${vh.type}"  data-subtype="${vh.subtype}" data-set="set1" data-stage="2">${vh.label}</button>`
                        // }
                    })
                    host_buttons_html += `</div>`

                    if ( v.mawl.length === 0 ) {
                        return
                    }
                    mawl_buttons_html += `<a class="button  button-grey expanded clear" style="white-space:nowrap; overflow: hidden;margin-bottom:0;">(${i}) ${v.title}</a><div class="primary button-group expanded no-gaps">`
                    jQuery.each(v.mawl, function(ih, vh ) {
                        mawl_buttons_html += `<button class="button zume ${vh.type}${vh.subtype}" data-top="${vh.type}"  data-subtype="${vh.subtype}" data-set="set1" data-stage="2">${vh.label}</button>`
                    })
                    mawl_buttons_html += `</div>`
                })

                chart.empty().html(`
                        <div class="grid-x">
                            <div class="cell medium-6">
                                <span class="loading-spinner active"></span>
                            </div>
                            <div class="cell medium-6 right">
                                <h2>SIMULATE ENCOURAGEMENT SYSTEM</h2>
                            </div>
                            <div class="cell">
                                <hr>
                            </div>
                        </div>

                        <div class="grid-x grid-padding-x">
                            <div class="cell medium-2">
                                <div id="funnel" style="height:${window.innerHeight - 225}px; padding: 1em; overflow: hidden scroll; border: 1px solid lightgrey;">
                                    <div class="grid-x grid-padding-x">

                                        <div class="cell ">
                                            <h2>FUNNEL</h2>
                                        </div>

                                        <div class="cell">
                                            <hr>
                                        </div>

                                         <div class="cell">
                                            <h2>(0) Anonymous</h2>
                                        </div>
                                        <div class="cell ">
                                            <button class="button zume  expanded systemregistered" data-top="system" data-subtype="registered" data-set="set2" data-stage="0">Registered</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>

                                        <div class="cell">
                                            <h2>(1) Registrant</h2>
                                        </div>
                                        <div class="cell ">
                                           <button class="button zume expanded systemplan_created" data-top="system" data-subtype="plan_created" data-set="set3" data-stage="1">Made a Plan</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>(2) Active Training</h2>
                                        </div>
                                        <div class="cell ">
                                            <button class="button zume expanded trainingcompleted" data-top="system" data-subtype="training_completed" data-set="set4" data-stage="2">Training Completed</button>
                                        </div>
                                        <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>(3) Post-Training </h2>
                                        </div>
                                        <div class="cell ">
                                           <button class="button zume expanded systempractitioner_report" data-top="system" data-subtype="first_practitioner_report" data-set="set1"  data-stage="3">Made First Report</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>(4) Partial Practitioner</h2>
                                        </div>
                                        <div class="cell ">
                                            <button class="button zume expanded systemcompleted_mawl" data-top="system" data-subtype="mawl_completed" data-set="set1"  data-stage="4">Full Launch MAWL Skills</button>
                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>

                                        <div class="cell">
                                            <h2>(5) Practitioner</h2>
                                        </div>
                                        <div class="cell ">
                                           <button class="button zume expanded systemseeing_generational_fruit" data-top="system" data-subtype="seeing_generational_fruit" data-set="set1"  data-stage="5">Seeing Generational Fruit</button>
                                        </div>

                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell">
                                            <h2>(6) Multiplying Practitioner</h2>
                                        </div>
                                        <div class="cell ">
                                        </div>

                                         <div class="cell">
                                            <hr>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="cell medium-3">
                                <div style=" padding: 1em; height:${window.innerHeight - 225}px; border: 1px solid lightgrey; overflow: hidden scroll;">
                                    <div class="grid-x grid-padding-x">

                                            <div class="cell ">
                                                <h2>TRAINING</h2>
                                            </div>

                                            <div class="cell">
                                                <hr>
                                            </div>

                                            <div class="cell">
                                                <h2>Training Elements</h2>
                                            </div>
                                            <div class="cell small-5">
                                            </div>
                                            <div class="cell">
                                                ${host_buttons_html}
                                            </div>

                                        </div>
                                    </div>
                            </div>
                            <div class="cell medium-3">
                                <div style=" padding: 1em; height:${window.innerHeight - 225}px; border: 1px solid lightgrey; overflow: hidden scroll;">
                                    <div class="grid-x grid-padding-x">

                                            <div class="cell ">
                                                <h2>COACHING</h2>
                                            </div>

                                            <div class="cell">
                                                <hr>
                                            </div>

                                            <div class="cell">
                                                <h2>MAWL Elements</h2>
                                            </div>
                                            <div class="cell small-5">
                                            </div>
                                            <div class="cell">
                                                ${mawl_buttons_html}
                                            </div>

                                        </div>
                                    </div>
                            </div>

                            <div class="cell medium-2">
                                <div style=" padding: 1em; height:${window.innerHeight - 225}px; border: 1px solid lightgrey; overflow: hidden scroll;">
                                    <div class="grid-x grid-padding-x">

                                            <div class="cell ">
                                                <h2>CTAS</h2>
                                            </div>

                                            <div class="cell">
                                                <hr>
                                            </div>


                                            <div class="cell">
                                                <button class="button zume alt-color expanded systemrequested_a_coach" data-top="system" data-subtype="requested_a_coach" data-set="set5"  data-stage="0">Requested a Coach</button>
                                                <button class="button zume alt-color expanded systemjoined_online_training" data-top="system" data-subtype="joined_online_training" data-set="set1"  data-stage="0">Joined Online Training</button>
                                                <button class="button zume alt-color expanded systemset_profile" data-top="system" data-subtype="set_profile" data-set="set5"  data-stage="1">Set Profile</button>
                                                <button class="button zume alt-color expanded systeminvited_friends" data-top="system" data-subtype="invited_friends" data-set="set5"  data-stage="1">Invited Friends</button>
                                                <button class="button zume alt-color expanded systemmade_3_month_plan" data-top="system" data-subtype="made_3_month_plan" data-set="set5"  data-stage="2">Create 3-Month Plan</button>
                                                <button class="button zume alt-color expanded systemcompleted_3_month_plan" data-top="system" data-subtype="completed_3_month_plan" data-set="set1"  data-stage="3">Complete 3-Month Plan</button>
                                            </div>

                                            <div class="cell">
                                                <h2><hr></h2>
                                            </div>
                                            <div class="cell small-5">
                                            </div>
                                            <div class="cell">
                                                <button class="button zume alt-color expanded reportspractitioner_report" data-top="reports" data-subtype="practitioner_report" data-set="set1"  data-stage="4">Submit Practitioner Report</button>
                                                <button class="button zume alt-color expanded systemjoined_affinity_hub" data-top="system" data-subtype="joined_affinity_hub" data-set="set1"  data-stage="4">Join Affinity Hub</button>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="cell medium-2">
                                <div id="report"></div>
                            </div>
                        </div>
                    </div>
                    `)

                window.sets = {
                    'set1': {
                        'cta': [ '{{Not Configured}}' ],
                        'time': [ '{{Not Configured}}' ],
                        'reset': [ '{{Not Configured}}' ],
                    },
                    'set2': {
                        'cta': [
                            'Make a Plan',
                            'Request Coach',
                            'Join Online Training'
                        ],
                        'time': [
                            '1 day after event',
                            '2 days after event',
                            '3 days after event',
                            '4 days after event',
                            '5 days after event',
                            '6 days after event',
                            '7 days after event',
                            '2 weeks after event',
                            '3 weeks after event',
                            '4 weeks after event',
                            '2 months after event',
                            '3 months after event'
                        ],
                        'reset': [
                            'Plan created'
                        ],
                    },
                    'set3': {
                        'cta': [
                            'Request Coach',
                            'Set Profile',
                            'Invite Friends'
                        ],
                        'time': [
                            '1 day before planned training',
                            '1 days after planned training',
                            '2 weeks after event with no checkin',
                            '3 weeks after event with no checkin',
                            '4 weeks after event with no checkin',
                            '5 weeks after event with no checkin',
                            '6 weeks after event with no checkin',
                        ],
                        'reset': [
                            'Training checkins'
                        ],
                    },
                    'set4': {
                        'cta': [
                            'Request Coach',
                            'Set Profile',
                            'Completed 3-Month Plan',
                            'Report as Practitioner'
                        ],
                        'time': [
                            '1 week after completed training',
                            '2 weeks after completed training',
                            '3 weeks after completed training',
                            '4 weeks after completed training',
                            '5 weeks after completed training',
                            '6 weeks after completed training',
                            '7 weeks after completed training',
                            '8 weeks after completed training',
                            '9 weeks after completed training',
                            '10 weeks after completed training',
                            '11 weeks after completed training',
                            '12 weeks after completed training',
                        ],
                        'reset': [
                            'Completed 3-Month Plan',
                            'Makes first practitioner report',

                        ],
                    },
                    'set5': {
                        'cta': [
                            'Set Profile',
                        ],
                        'time': [
                            'Immediately after event, coach notification',
                            'Immediately after event, challenge to set profile',
                            '1 day after request ??',
                            '2 day after request ?? ',
                            '3 day after request ??',
                        ],
                        'reset': [
                            'Coach establishes communication'
                        ],
                    },
                }


                jQuery('.button.zume').on('click', function(event) {
                    jQuery('.loading-spinner').addClass('active')
                    let button = jQuery(this)
                    button.addClass('done')
                    let type = button.data('top')
                    let subtype = button.data('subtype')
                    let set = button.data('set')

                    jQuery('#report').html( `
                            <h2>LAST ACTION</h2>${type} | <strong>${subtype}</strong>
                            <hr>
                            <h2>ENCOURAGEMENT PLAN</h2><br>
                            <div id="ctas-list" class="grid-x"></div><br>
                            <div id="time-list" class="grid-x"></div><br>
                            <div id="reset-list" class="grid-x"></div><hr>
                    `)

                    let ctaList = '<div class="cell"><h4>WEBSITE CTA(s)</h4></div>'
                    jQuery.each(window.sets[set].cta, function(ih, vh ) {
                        ctaList += `<div class="cell small-8">${vh}</div><div class="cell small-4"></div>`
                    })
                    jQuery('#ctas-list').append(ctaList)

                    let emailList = '<div class="cell"><h4>EMAIL PLAN</h4></div>'
                    jQuery.each(window.sets[set].time, function(ih, vh ) {
                        emailList += `<div class="cell small-8">${vh}</div><div class="cell small-4"></div>`
                    })
                    jQuery('#time-list').append(emailList)

                    let resetdList = '<div class="cell"><h4>RESET ACTION</h4></div>'
                    jQuery.each(window.sets[set].reset, function(ih, vh ) {
                        resetdList += `<div class="cell small-8">${vh}</div><div class="cell small-4"></div>`
                    })
                    jQuery('#reset-list').append(resetdList)

                    jQuery('.loading-spinner').removeClass('active')
                })

                jQuery('.loading-spinner').removeClass('active')
            })
        </script>
        <?php
    }
}
new Zume_Simulator_Next_Step();
