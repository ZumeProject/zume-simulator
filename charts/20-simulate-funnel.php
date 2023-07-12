<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Test_Journey extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'simulate_funnel'; // lowercase
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
        $this->base_title = __( 'simulate funnel', 'disciple_tools' );

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
                background-color: #39ea29;
            }
            .button.zume.done:hover {
                background-color: #25951b;
            }
        </style>

        <script>
            window.user_id = '<?php echo get_current_user_id() ?>'


            //  0 = Anonymous
            //  1 = Registrant
            //  2 = Active Training
            //  3 = Post-Training
            //  4 = S1 (Partial)
            //  5 = S2 (Complete)
            //  6 = S3 (Multiplying)


            jQuery(document).ready(function(){
                "use strict";
                let chart = jQuery('#chart')
                let selectors = `<?php echo zume_simulator_selectors() ?>`
                window.training_items = [<?php echo json_encode( zume_training_items() ) ?>][0]
                let host_buttons_html = ''
                let mawl_buttons_html = ''
                jQuery.each( window.training_items, function(i,v){
                    host_buttons_html += `<div class="primary button-group expanded no-gaps"><a class="button zume button-grey clear">${v.title}</a>`
                    jQuery.each(v.host, function(ih, vh ) {
                        host_buttons_html += `<button class="button zume ${vh.type}${v.subtype}" data-top="${vh.type}"  data-subtype="${vh.subtype}" data-stage="2">${vh.label}</button>`
                    })
                    host_buttons_html += `</div><br>`

                    mawl_buttons_html += `<div class="primary button-group expanded no-gaps"><button class="button zume button-grey clear">${v.title}</button>`
                    jQuery.each(v.mawl, function(ih, vh ) {
                        mawl_buttons_html += `<button class="button zume ${vh.type}${v.subtype}" data-top="${vh.type}"  data-subtype="${vh.subtype}" data-stage="2">${vh.label}</button>`
                    })
                    mawl_buttons_html += `</div><br>`
                })

                chart.empty().html(`
                        <div class="grid-x">
                            <div class="cell medium-9">
                                Configure: :
                                ${selectors}
                                <span class="loading-spinner active"></span>
                            </div>
                            <div class="cell medium-3 right">
                                <h2>Log a User Journey</h2>
                            </div>
                            <div class="cell">
                                <hr>
                            </div>
                        </div>


                        <div class="grid-x grid-padding-x">

                            <div class="cell small-7">
                                <h2>KEY FUNNEL STEPS</h2>
                            </div>
                            <div class="cell small-5 left-border">
                                <h2>CTAs</h2>
                            </div>
                            <div class="cell">
                                <hr>
                            </div>


                             <div class="cell">
                                <h2>Anonymous (0)</h2>
                            </div>
                            <div class="cell small-7">
                                <button class="button zume zume_systemregistered" data-top="zume_system" data-subtype="registered" data-stage="0">Registered</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button zume alt-color zume_systemrequested_a_coach" data-top="zume_system" data-subtype="requested_a_coach" data-stage="0">Coach Request</button><br>
                                <button class="button zume alt-color zume_systemjoined_online_training" data-top="zume_system" data-subtype="joined_online_training" data-stage="0">Joined Online Training</button>
                            </div>
                             <div class="cell">
                                <hr>
                            </div>

                            <div class="cell">
                                <h2>Registrant (1)</h2>
                            </div>
                            <div class="cell small-7">
                               <button class="button zume zume_systemmade_a_plan" data-top="zume_system" data-subtype="made_a_plan" data-stage="1">Made a Plan</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button zume alt-color zume_systemrequested_a_coach" data-top="zume_system" data-subtype="requested_a_coach" data-stage="1">Coach Request</button><br>
                                <button class="button zume alt-color zume_systemset_profile" data-top="zume_system" data-subtype="set_profile" data-stage="1">Set Profile</button><br>
                                <button class="button zume alt-color zume_systeminvited_friends" data-top="zume_system" data-subtype="invited_friends" data-stage="1">Invited Friends</button><br>
                                <button class="button zume alt-color zume_systemjoined_online_training" data-top="zume_system" data-subtype="joined_online_training" data-stage="1">Joined Online Training</button>
                            </div>
                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Active Training Trainee (2)</h2>
                            </div>
                            <div class="cell small-7">
                                ${host_buttons_html}
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button zume alt-color zume_systemrequested_a_coach" data-top="zume_system" data-subtype="requested_a_coach" data-stage="2">Coach Request</button><br>
                                <button class="button zume alt-color zume_systemset_profile" data-top="zume_system" data-subtype="set_profile" data-stage="2">Set Profile</button><br>
                                <button class="button zume alt-color zume_systeminvited_friends" data-top="zume_system" data-subtype="invited_friends" data-stage="2">Invited Friends</button><br>
                                <button class="button zume alt-color zume_systemmade_3_month_plan" data-top="zume_system" data-subtype="made_3_month_plan" data-stage="2">Made 3-Month Plan</button>
                            </div>
                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Post-Training Trainee (3)</h2>
                            </div>
                            <div class="cell small-7">
                               <button class="button zume zume_systemmade_first_lifestyle_report" data-top="zume_system" data-subtype="made_first_lifestyle_report" data-stage="3">Made First Lifestyle Report</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button zume alt-color zume_systemrequested_a_coach" data-top="zume_system" data-subtype="requested_a_coach" data-stage="3">Coach Request</button><br>
                                <button class="button zume alt-color zume_systemset_profile" data-top="zume_system" data-subtype="set_profile" data-stage="3">Set Profile</button><br>
                                <button class="button zume alt-color zume_systemcompleted_3_month_plan" data-top="zume_system" data-subtype="completed_3_month_plan" data-stage="3">Completed 3-Month Plan</button>
                            </div>
                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Stage 1 - Partial Practitioner (4)</h2>
                            </div>
                            <div class="cell small-7">
                                ${mawl_buttons_html}
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button zume alt-color zume_systemrequested_a_coach" data-top="zume_system" data-subtype="requested_a_coach" data-stage="4">Coach Request</button><br>
                                <button class="button zume alt-color zume_systemsubmitted_church_report" data-top="zume_system" data-subtype="submitted_church_report" data-stage="4">Report Churches</button><br>
                                <button class="button zume alt-color zume_systemjoined_affinity_hub" data-top="zume_system" data-subtype="joined_affinity_hub" data-stage="4">Join Affinity Hub</button>
                            </div>
                             <div class="cell">
                                <hr>
                            </div>

                            <div class="cell">
                                <h2>Stage 2 - Completed Practitioner (5)</h2>
                            </div>
                            <div class="cell small-7">
                               <button class="button zume seeing_generational_fruit" data-top="zume_system" data-subtype="seeing_generational_friuit" data-stage="5">Seeing Generational Fruit</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button zume alt-color zume_systemrequested_a_coach" data-top="zume_system" data-subtype="requested_a_coach" data-stage="5">Coach Request</button><br>
                                <button class="button zume alt-color zume_systemsubmitted_church_report" data-top="zume_system" data-subtype="submitted_church_report" data-stage="5">Report Churches</button><br>
                                <button class="button zume alt-color zume_systemjoined_affinity_hub" data-top="zume_system" data-subtype="joined_affinity_hub" data-stage="5">Join Affinity Hub</button>
                            </div>

                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Stage 3 - Multiplying Practitioner (6)</h2>
                            </div>
                            <div class="cell small-5">
                            </div>
                            <div class="cell small-7 left-border">
                                <button class="button zume alt-color zume_systemrequested_a_coach" data-top="zume_system" data-subtype="requested_a_coach" data-stage="6">Coach Request</button><br>
                                <button class="button zume alt-color zume_systemsubmitted_church_report" data-top="zume_system" data-subtype="submitted_church_report" data-stage="6">Report Churches</button><br>
                                <button class="button zume alt-color zume_systemcoaching_others" data-top="zume_system" data-subtype="coaching_others" data-stage="6">Coach Others</button><br>
                                <button class="button zume alt-color zume_systemjoined_affinity_hub" data-top="zume_system" data-subtype="joined_affinity_hub" data-stage="6">Join Affinity Hub</button><br>
                                <button class="button zume alt-color zume_systemproviding_hub_leadership" data-top="zume_system" data-subtype="providing_hub_leadership" data-stage="6">Provide Hub Leadership</button>
                            </div>

                             <div class="cell">
                                <hr>
                            </div>

                        </div>
                    </div>
                    `)

                jQuery('.button.zume').on('click', function(event) {
                    jQuery('.loading-spinner').addClass('active')
                    let button = jQuery(this)
                    let type = button.data('top')
                    if ( ! type ) {
                        type = 'zume'
                    }
                    let data = {
                        "user_id": jQuery('#user_id').val(),
                        "days_ago": jQuery('#days_ago').val(),
                        "location": jQuery('#location').val(),
                        "type": type,
                        "subtype": button.data('subtype'),
                        "stage": button.data('stage')
                    }
                    console.log(data)
                    jQuery(this).addClass('done')
                    makeRequest('POST', 'log', data, window.site_info.system_root ).done( function( data ) {
                            console.log(data)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                        .catch((error) => {
                            console.log(error)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                })

                function get_user_progress( user_id ) {
                    makeRequest('POST', 'user_progress', {user_id: user_id} , window.site_info.rest_root ).done( function( response ) {
                            console.log(response)
                            if (response.length == 0) {
                                    jQuery('.loading-spinner').removeClass('active')
                                    jQuery('.button').removeClass('done')
                                    return
                            }
                            jQuery.each(response, function(index, value) {
                                jQuery('.'+value.type+value.subtype).addClass('done')
                            })
                            jQuery('#location').val(response[0].label)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                        .catch((error) => {
                            console.log(error)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                }
                jQuery('#user_id').on('change', function(event) {
                    event.preventDefault()
                    jQuery('.loading-spinner').addClass('active')
                    jQuery('.button').removeClass('done')
                    get_user_progress( jQuery(this).val() )
                })

                makeRequest('POST', 'user_location', [], window.site_info.system_root ).done( function( data ) {
                    console.log(data)
                    jQuery('.loading-spinner').removeClass('active')
                })
                .catch((error) => {
                    console.log(error)
                    jQuery('.loading-spinner').removeClass('active')
                })

                jQuery("#user_id option:first").attr('selected','selected');
                get_user_progress( jQuery('#user_id option:first').val() )
                jQuery('.loading-spinner').removeClass('active')
            })
        </script>
        <?php
    }
}
new Zume_Simulator_Test_Journey();
