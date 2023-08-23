<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulate_Funnel extends Zume_Simulator_Chart_Base
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
                /*margin-bottom: 0 !important;*/
            }
            #chart .button {
                border-radius: 0 !important;
            }
            button.encouragement {
                border-radius: 0 !important;
            }
            .button.encouragement.done {
                background-color: #39ea29 !important;
            }
            .button.button-grey {
                border-radius: 0 !important;
                background-color: lightgrey;
                color: black;
                cursor: default;
            }
            .button.button-grey:hover {
                background-color: lightgrey;
                color: black;
            }
            .button.zume.done, .button.encouragement.done {
                background-color: #39ea29 !important;
            }
            .button.zume.done:hover {
                background-color: #25951b;
            }
            .button-grey.tool {
                border: 2px solid #3f729b;
                font-weight: bold;
            }
        </style>

        <script>
            window.user_id = '<?php echo get_current_user_id() ?>'

            jQuery(document).ready(function(){
                "use strict";
                let chart = jQuery('#chart')
                let user_selector = `<?php echo zume_simulator_selectors() ?>`
                let time_selector = `<?php echo zume_time_selector() ?>`
                window.training_items = [<?php echo json_encode( zume_training_items() ) ?>][0]
                let host_buttons_html = ''
                let mawl_buttons_html = ''
                jQuery.each( window.training_items, function(i,v){
                    host_buttons_html += `<a class="button  button-grey expanded clear ${v.type}" style="white-space:nowrap; overflow: hidden;margin-bottom:0;">(${v.key}) ${v.title}</a><div class="primary button-group expanded no-gaps" >`
                    jQuery.each(v.host, function(ih, vh ) {
                        host_buttons_html += `<button class="button zume ${vh.type}_${vh.subtype}" data-type="${vh.type}"  data-subtype="${vh.subtype}" data-set="set1" data-stage="2">${vh.short_label}</button>`
                    })
                    host_buttons_html += `</div>`

                    if ( v.mawl.length === 0 ) {
                        return
                    }
                    mawl_buttons_html += `<a class="button  button-grey expanded clear ${v.type}" style="white-space:nowrap; overflow: hidden;margin-bottom:0;">(${v.key}) ${v.title}</a><div class="primary button-group expanded no-gaps">`
                    jQuery.each(v.mawl, function(ih, vh ) {
                        mawl_buttons_html += `<button class="button zume ${vh.type}_${vh.subtype}" data-type="${vh.type}"  data-subtype="${vh.subtype}" data-set="set1" data-stage="2">${vh.short_label}</button>`
                    })
                    mawl_buttons_html += `</div>`
                })

                chart.empty().html(`
                        <div class="grid-x">
                            <div class="cell medium-8">
                                Configure: :
                                ${user_selector} ${time_selector}
                                <a href="" class="button primary small" id="switch_to">Switch to User</a>
                                <a href="" class="button primary small" target="_blank" id="coaching_record">Coaching Record</a>
                                <span class="loading-spinner active"></span>
                            </div>
                            <div class="cell medium-4 right">
                                <h2>SIMULATE FUNNEL</h2>
                            </div>
                            <div class="cell">
                                <hr>
                            </div>
                        </div>

                        <div class="grid-x grid-padding-x">
                            <div class="cell medium-5">
                                <div class="cell center">
                                    <h2>FUNNEL</h2>
                                </div>
                                <div class="cell">
                                    <div class="grid-x grid-padding-x">
                                         <div class="cell small-6 center">
                                            Major Funnel Step
                                        </div>
                                        <div class="cell small-6 center">
                                            Minor Funnel Steps
                                        </div>
                                    </div>
                                </div>
                                <div id="funnel" style="height:${window.innerHeight - 300}px; padding: 1em; overflow: hidden scroll; border: 1px solid lightgrey;">
                                    <div class="grid-x grid-padding-x">

                                         <div class="cell center">
                                            <h2>(0) Anonymous</h2>
                                        </div>
                                        <div class="cell small-6">
                                        </div>
                                        <div class="cell small-6 left-border">
                                                <button class="button zume expanded system_joined_online_training" data-type="system" data-subtype="joined_online_training" data-stage="">Joined Online Training</button>
                                                <button class="button expanded system_requested_a_coach" data-type="system" data-subtype="requested_a_coach" data-stage="">Requested a Coach</button>
                                        </div>
                                        <div class="cell small-6">
                                            <button class="button zume  expanded system_registered" data-type="system" data-subtype="registered" data-set="set2" data-stage="0">Registered</button>
                                        </div>
                                        <div class="cell small-6 left-border">

                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>

                                        <div class="cell center">
                                            <h2>(1) Registrant</h2>
                                        </div>
                                        <div class="cell small-6">
                                        </div>
                                        <div class="cell small-6 left-border">
                                                <button class="button zume expanded system_joined_online_training" data-type="system" data-subtype="joined_online_training" data-stage="">Joined Online Training</button>
                                                <button class="button expanded system_requested_a_coach" data-type="system" data-subtype="requested_a_coach" data-stage="">Requested a Coach</button>
                                                <button class="button zume expanded system_set_profile" data-type="system" data-subtype="set_profile"  data-stage="">Set Profile</button>
                                                <button class="button zume expanded system_invited_friends" data-type="system" data-subtype="invited_friends" data-stage="">Invited Friends</button>
                                        </div>
                                        <div class="cell small-6">
                                           <button class="button zume expanded system_plan_created" data-type="system" data-subtype="plan_created" data-set="set3" data-stage="1">Made a Plan</button>
                                        </div>
                                        <div class="cell small-6 left-border">

                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell center">
                                            <h2>(2) Active Training</h2>
                                        </div>
                                        <div class="cell small-6">
                                        </div>
                                        <div class="cell small-6 left-border">
                                            <button class="button zume  expanded training_set_a_01" data-type="training" data-subtype="set_a_01" data-stage="2">Session 1 Checkin</button>
                                            <button class="button zume  expanded training_set_a_02" data-type="training" data-subtype="set_a_02" data-stage="2">Session 2 Checkin</button>
                                            <button class="button zume  expanded training_set_a_03" data-type="training" data-subtype="set_a_03" data-stage="2">Session 3 Checkin</button>
                                            <button class="button zume  expanded training_set_a_04" data-type="training" data-subtype="set_a_04" data-stage="2">Session 4 Checkin</button>
                                            <button class="button zume  expanded training_set_a_05" data-type="training" data-subtype="set_a_05" data-stage="2">Session 5 Checkin</button>
                                            <button class="button zume  expanded training_set_a_06" data-type="training" data-subtype="set_a_06" data-stage="2">Session 6 Checkin</button>
                                            <button class="button zume  expanded training_set_a_07" data-type="training" data-subtype="set_a_07" data-stage="2">Session 7 Checkin</button>
                                            <button class="button zume  expanded training_set_a_08" data-type="training" data-subtype="set_a_08" data-stage="2">Session 8 Checkin</button>
                                            <button class="button zume  expanded training_set_a_09" data-type="training" data-subtype="set_a_09" data-stage="2">Session 9 Checkin</button>
                                            <button class="button zume  expanded training_set_a_10" data-type="training" data-subtype="set_a_10" data-stage="2">Session 10 Checkin</button>

                                            ${host_buttons_html}

                                            <button class="button zume alt-color expanded system_made_3_month_plan" data-type="system" data-subtype="made_3_month_plan" data-set="set5"  data-stage="2">Create Post Training Plan</button>
                                        </div>
                                        <div class="cell small-6">
                                            <button class="button zume expanded system_training_completed" data-type="system" data-subtype="training_completed" data-set="set4" data-stage="2">Training Completed</button>
                                        </div>
                                        <div class="cell small-6 left-border">

                                        </div>

                                        <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell center">
                                            <h2>(3) Post-Training </h2>
                                        </div>
                                        <div class="cell small-6">

                                        </div>
                                        <div class="cell small-6 left-border">
                                            <button class="button zume alt-color expanded system_completed_3_month_plan" data-type="system" data-subtype="completed_3_month_plan" data-set="set1"  data-stage="3">Complete Post Training Plan</button>
                                        </div>
                                        <div class="cell small-6">
                                           <button class="button zume expanded system_first_practitioner_report" data-type="system" data-subtype="first_practitioner_report" data-set="set1"  data-stage="3">Made First Report</button>
                                        </div>
                                        <div class="cell small-6 left-border">

                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell center">
                                            <h2>(4) Partial Practitioner</h2>
                                        </div>
                                        <div class="cell small-6">
                                        </div>
                                        <div class="cell small-6 left-border">
                                            ${mawl_buttons_html}
                                        </div>
                                        <div class="cell small-6">
                                            <button class="button zume expanded system_mawl_completed" data-type="system" data-subtype="mawl_completed" data-set="set1"  data-stage="4">Full Launch MAWL Skills</button>
                                        </div>
                                        <div class="cell small-6 left-border">

                                        </div>
                                         <div class="cell">
                                            <hr>
                                        </div>

                                        <div class="cell center">
                                            <h2>(5) Practitioner</h2>
                                        </div>
                                        <div class="cell small-6">
                                           <button class="button zume expanded system_seeing_generational_fruit" data-type="system" data-subtype="seeing_generational_fruit" data-set="set1"  data-stage="5">Seeing Generational Fruit</button>
                                        </div>
                                        <div class="cell small-6 left-border">

                                        </div>

                                         <div class="cell">
                                            <hr>
                                        </div>


                                        <div class="cell center">
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

                            <div class="cell medium-2">
                                <div class="center"><h2>ACTIONS</h2><br></div>

                                <div style=" padding: 1em; height:${window.innerHeight - 300}px; border: 1px solid lightgrey; overflow: hidden scroll;">
                                    <div class="grid-x grid-padding-x">
                                            <div class="cell center">
                                                <h3>General Actions</h3>
                                            </div>
                                            <div class="cell">
                                                <button class="button zume alt-color expanded system_login" data-type="system" data-subtype="login" data-stage="">Login</button>
                                            </div>
                                            <div class="cell center">
                                                <h3><hr>Training CTAs</h3>
                                            </div>
                                            <div class="cell">
                                                <button class="button zume alt-color expanded system_joined_online_training" data-type="system" data-subtype="joined_online_training" data-stage="">Joined Online Training</button>
                                                <button class="button alt-color expanded system_requested_a_coach" data-type="system" data-subtype="requested_a_coach" data-stage="">Requested a Coach</button>
                                                <button class="button zume alt-color expanded system_set_profile" data-type="system" data-subtype="set_profile"  data-stage="">Set Profile</button>
                                                <button class="button zume alt-color expanded system_invited_friends" data-type="system" data-subtype="invited_friends" data-stage="">Invited Friends</button>
                                            </div>
                                            <div class="cell center">
                                                <h3><hr>Practitioner CTAs</h3>
                                            </div>
                                            <div class="cell">
                                                <button class="button zume alt-color expanded reports_practitioner_report" data-type="reports" data-subtype="practitioner_report" data-stage="4">Submit Practitioner Report</button>
                                                <button class="button zume alt-color expanded system_joined_affinity_hub" data-type="system" data-subtype="joined_affinity_hub" data-stage="">Join Affinity Hub</button>
                                                <button class="button zume alt-color expanded system_hub_checkin" data-type="system" data-subtype="hub_checkin" data-stage="">Hub Checkin</button>
                                            </div>
                                            <div class="cell center">
                                                <h3><hr>Checkins</h3>
                                            </div>
                                            <div class="cell">
                                                <button class="button zume alt-color expanded training_checkin" data-type="training" data-subtype="checkin" data-set="set5" data-stage="">Training Checkin</button>
                                                <button class="button zume alt-color expanded coaching_checkin" data-type="coaching" data-subtype="checkin" data-set="set5" data-stage="">Coaching Checkin</button>
                                                <button class="button zume alt-color expanded system_checkin" data-type="system" data-subtype="checkin" data-set="set5" data-stage="">Practitioner Checkin</button>
                                                <button class="button zume alt-color expanded report_checkin" data-type="report" data-subtype="checkin" data-set="set5" data-stage="">Report Checkin</button>
                                            </div>

                                        </div>
                                    </div>
                            </div>

                            <div class="cell medium-5">
                                <div class="grid-x grid-padding-x">
                                    <div class="cell">
                                        <div id="last_action"></div>
                                    </div>
                                    <div class="cell medium-6">
                                        <h2>ENCOURAGEMENT PLAN</h2><br>
                                        <div id="plan-list" class="grid-x"></div>
                                        <div id="reset-list" class="grid-x"></div>
                                    </div>
                                    <div class="cell medium-6">
                                        <h2>STAGE</h2><br>
                                        <div id="stage-list" class="grid-x"></div>
                                        <hr>
                                        <h2>CTAs</h2><br>
                                        <div id="ctas-list" class="grid-x"></div>
                                        <hr>
                                        <h2>USER PROFILE</h2><BR>
                                        <div id="user_profile" class="grid-x"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    `)

                jQuery("#user_id option:first").attr('selected','selected');
                let user_id = jQuery('#user_id').val()


                /* user profile */
                window.get_user_profile = ( user_id ) => {
                    jQuery('.loading-spinner').addClass('active')
                    makeRequest('POST', 'user_profile', { user_id: user_id } , window.site_info.system_root ).done( function( data ) {
                        console.log('user_profile')
                        console.log(data)
                        window.user_profile = data

                        let user_profile = jQuery('#user_profile')
                        user_profile.empty()

                        // set user state column
                        let profileList = ''
                        jQuery.each(data.profile, function(ih, vh ) {
                            if ( typeof(vh) === 'string' ) {
                                profileList += `<div class="cell"><span style="text-transform:uppercase;">${ih} </span>: ${vh}</div>`
                            }
                            else if ( ih === 'language' ) {
                                profileList += `<div class="cell"><span style="text-transform:uppercase;">${ih} </span>: ${vh.name}</div>`
                            }
                            else if ( ih === 'location' ) {
                                profileList += `<div class="cell"><span style="text-transform:uppercase;">${ih} </span>: ${vh.label}</div>`
                            }
                            else {
                                profileList += `<div class="cell"><span style="text-transform:uppercase;">${ih} </span>: ${JSON.stringify(vh)}</div>`
                            }
                        })
                        user_profile.append(profileList)

                        // let stateList = '<hr>'
                        // jQuery.each(data.state, function(ih, vh ) {
                        //     stateList += `<div class="cell"><span style="text-transform:uppercase;">${ih} </span>: ${vh}</div>`
                        // })
                        // user_profile.append(stateList)

                        let stageList = ''
                        jQuery.each(data.stage, function(ih, vh ) {
                            stageList += `<div class="cell"><span style="text-transform:uppercase;">${ih} </span>: ${vh}</div>`
                        })
                        jQuery('#stage-list').empty().append(stageList)

                        jQuery('#switch_to').prop('href', `https://zume5.training/wp-admin/users.php?s=${user_id}&action=-1&new_role&paged=1&action2=-1&new_role2` )

                        jQuery('#coaching_record').prop('href', `https://zume5.training/coaching/contacts/${data.coaching_contact_id}` )

                        jQuery('.loading-spinner').removeClass('active')
                    })
                }
                jQuery('#user_id').on('change', function(event) {
                    event.preventDefault()
                    jQuery('.loading-spinner').addClass('active')
                    jQuery('.button').removeClass('done')
                    window.get_user_profile( jQuery(this).val() )
                })
                window.get_user_profile( user_id )
                window.get_user_completions = ( user_id ) => {
                    makeRequest('POST', 'user_completions', { user_id: user_id }, window.site_info.system_root ).done( function( data ) {
                        window.user_completions = data

                        jQuery.each(data, function(index, value) {
                            jQuery('.'+index).addClass('done')
                        })
                    })
                }
                window.get_user_completions( user_id )

                /* user ctas */
                window.get_user_ctas = ( user_id ) => {
                    makeRequest('POST', 'user_ctas', { user_id: user_id }, window.site_info.system_root ).done( function( data ) {
                        window.user_ctas = data

                        let ctaList = ''
                        jQuery.each(data, function(ih, vh ) {
                            ctaList += `<div class="cell">${vh.label}</div>`
                        })
                        jQuery('#ctas-list').empty().append(ctaList)
                    })
                }
                window.get_user_ctas( user_id )
                /* end user ctas */


                /* user encouragement */
                window.get_user_encouragement = ( user_id, type, subtype ) => {
                    makeRequest('POST', 'get_encouragement', { user_id, type, subtype }, window.site_info.system_root ).done( function( data ) {
                        // console.log('get_encouragement')
                        // console.log(data)
                        window.get_encouragement = data

                        let plan = jQuery('#plan-list')
                        plan.empty()

                        let sent = ''
                        let responded = ''

                        let emailList = ''
                        jQuery.each(data, function(ih, vh ) {
                            sent = ''
                            responded = ''
                            if ( vh.sent ) {
                                sent = 'done'
                            }
                            if ( vh.responded ) {
                                responded = 'done'
                            }
                            emailList = `<a class="button button-grey expanded clear " style="white-space:nowrap; overflow: hidden;margin-bottom:0;">${vh.subject}</a><div class="primary button-group expanded no-gaps">`
                            emailList += `<button type="button" class="button encouragement sent alt-color encouragement_sent_${vh.message_post_id} sent_${vh.message_post_id} ${sent}" data-type="encouragement" data-subtype="sent" data-message="${vh.message_post_id}">SEND</button>`
                            emailList += `<button type="button" class="button encouragement responded alt-color encouragement_responded_${vh.message_post_id} responded_${vh.message_post_id} ${responded}" data-type="encouragement" data-subtype="responded" data-message="${vh.message_post_id}">RESPONDED</button>`
                            emailList += `</div>`
                            plan.append(emailList)
                        })

                        jQuery('.encouragement').on('click', function (event) {
                            jQuery(this).addClass('done')
                            window.send_encouragement_log( event )
                        })
                    })
                }
                window.get_user_encouragement( user_id, 'no', 'trigger' )
                /* end user encouragement */


                /* log activity */
                jQuery('.button.zume').on('click', function(event) {
                    jQuery(this).addClass('done')
                    window.post_log( event )
                })
                /* end log activity */
                window.post_log = ( event ) => {
                    jQuery('.loading-spinner').addClass('active')

                    window.user_completions = false

                    let type = event.target.dataset.type
                    let subtype = event.target.dataset.subtype
                    let user_id = jQuery('#user_id').val()

                    jQuery('#last_action').html(`
                        <div class=""><h2>LAST ACTION</h2>${type} | <strong>${subtype}</strong><br><br>
                    `)

                    let data = {
                        "user_id": user_id,
                        "type": type,
                        "subtype": subtype,
                    }

                    makeRequest('POST', 'log', data, window.site_info.system_root ).done( function( data ) {
                        console.log('log')
                        window.get_user_profile( user_id )
                        window.get_user_ctas( user_id )
                        window.get_user_completions( user_id )
                    })
                    window.get_user_encouragement( user_id, type, subtype )

                }
                window.send_encouragement_log = ( event ) => {

                    let type = event.target.dataset.type
                    let subtype = event.target.dataset.subtype
                    let message_id = event.target.dataset.message
                    let user_id = jQuery('#user_id').val()

                    let data = {
                        "user_id": user_id,
                        "post_id": message_id,
                        "type": type,
                        "subtype": subtype,
                    }

                    makeRequest('POST', 'log', data, window.site_info.system_root ).done( function( data ) {
                        console.log(data)
                        window.get_user_profile( user_id )
                        window.get_user_ctas( user_id )
                        window.get_user_completions( user_id )
                    })
                    window.get_user_encouragement( user_id, type, subtype )
                }
                jQuery('.system_requested_a_coach').on('click', function(event) {
                    // console.log('system_requested_a_coach')
                    // console.log(data)
                    jQuery(this).addClass('done')

                    jQuery('.loading-spinner').addClass('active')

                    window.user_completions = false

                    let type = event.target.dataset.type
                    let subtype = event.target.dataset.subtype
                    let user_id = jQuery('#user_id').val()

                    jQuery('#last_action').html(`
                        <div class=""><h2>LAST ACTION</h2>${type} | <strong>${subtype}</strong><br><br>
                    `)

                    let data = {
                        "user_id": user_id,
                        "type": type,
                        "subtype": subtype,
                    }

                    makeRequest('POST', 'get_a_coach', data, window.site_info.system_root ).done( function( data ) {
                        console.log(data)
                        window.get_user_profile( user_id )
                        window.get_user_ctas( user_id )
                        window.get_user_completions( user_id )
                    })
                    window.get_user_encouragement( user_id, type, subtype )

                })

            })
        </script>
        <?php
    }
}
new Zume_Simulate_Funnel();


