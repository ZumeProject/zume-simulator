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
                let user_selector = `<?php echo zume_simulator_selectors() ?>`
                let time_selector = `<?php echo zume_time_selector() ?>`
                window.training_items = [<?php echo json_encode( zume_training_items() ) ?>][0]
                let host_buttons_html = ''
                let mawl_buttons_html = ''
                jQuery.each( window.training_items, function(i,v){
                    host_buttons_html += `<a class="button  button-grey expanded clear" style="white-space:nowrap; overflow: hidden;margin-bottom:0;">(${i}) ${v.title}</a><div class="primary button-group expanded no-gaps" >`
                    jQuery.each(v.host, function(ih, vh ) {
                        // if ( 'Heard' === vh.label ) {
                        host_buttons_html += `<button class="button zume ${vh.type}_${vh.subtype}" data-top="${vh.type}"  data-subtype="${vh.subtype}" data-set="set1" data-stage="2">${vh.short_label}</button>`
                        // }
                    })
                    host_buttons_html += `</div>`

                    if ( v.mawl.length === 0 ) {
                        return
                    }
                    mawl_buttons_html += `<a class="button  button-grey expanded clear" style="white-space:nowrap; overflow: hidden;margin-bottom:0;">(${i}) ${v.title}</a><div class="primary button-group expanded no-gaps">`
                    jQuery.each(v.mawl, function(ih, vh ) {
                        mawl_buttons_html += `<button class="button zume ${vh.type}_${vh.subtype}" data-top="${vh.type}"  data-subtype="${vh.subtype}" data-set="set1" data-stage="2">${vh.short_label}</button>`
                    })
                    mawl_buttons_html += `</div>`
                })

                chart.empty().html(`
                        <div class="grid-x">
                            <div class="cell medium-6">
                                Configure: :
                                ${user_selector} ${time_selector}
                                <span class="loading-spinner active"></span>
                                <span class="loading-spinner active"></span>
                            </div>
                            <div class="cell medium-6 right">
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
                                            <button class="button zume  expanded system_registered" data-top="system" data-subtype="registered" data-set="set2" data-stage="0">Registered</button>
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
                                           <button class="button zume expanded system_plan_created" data-top="system" data-subtype="plan_created" data-set="set3" data-stage="1">Made a Plan</button>
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
                                            ${host_buttons_html}
                                            <button class="button zume alt-color expanded system_made_3_month_plan" data-top="system" data-subtype="made_3_month_plan" data-set="set5"  data-stage="2">Create 3-Month Plan</button>
                                        </div>
                                        <div class="cell small-6">
                                            <button class="button zume expanded system_training_completed" data-top="system" data-subtype="training_completed" data-set="set4" data-stage="2">Training Completed</button>
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
                                            <button class="button zume alt-color expanded system_completed_3_month_plan" data-top="system" data-subtype="completed_3_month_plan" data-set="set1"  data-stage="3">Complete 3-Month Plan</button>
                                        </div>
                                        <div class="cell small-6">
                                           <button class="button zume expanded system_first_practitioner_report" data-top="system" data-subtype="first_practitioner_report" data-set="set1"  data-stage="3">Made First Report</button>
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
                                            <button class="button zume expanded system_mawl_completed" data-top="system" data-subtype="mawl_completed" data-set="set1"  data-stage="4">Full Launch MAWL Skills</button>
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
                                           <button class="button zume expanded system_seeing_generational_fruit" data-top="system" data-subtype="seeing_generational_fruit" data-set="set1"  data-stage="5">Seeing Generational Fruit</button>
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
                                <div class="center"><h2>CTAS</h2><br></div>

                                <div style=" padding: 1em; height:${window.innerHeight - 300}px; border: 1px solid lightgrey; overflow: hidden scroll;">
                                    <div class="grid-x grid-padding-x">

                                            <div class="cell">
                                                <button class="button zume alt-color expanded system_requested_a_coach" data-top="system" data-subtype="requested_a_coach" data-set="set5"  data-stage="0">Requested a Coach</button>
                                                <button class="button zume alt-color expanded system_joined_online_training" data-top="system" data-subtype="joined_online_training" data-set="set1"  data-stage="0">Joined Online Training</button>
                                                <button class="button zume alt-color expanded system_set_profile" data-top="system" data-subtype="set_profile" data-set="set5"  data-stage="1">Set Profile</button>
                                                <button class="button zume alt-color expanded system_invited_friends" data-top="system" data-subtype="invited_friends" data-set="set5"  data-stage="1">Invited Friends</button>
                                            </div>

                                            <div class="cell">
                                                <h2><hr></h2>
                                            </div>
                                            <div class="cell small-5">
                                            </div>
                                            <div class="cell">
                                                <button class="button zume alt-color expanded reports_practitioner_report" data-top="reports" data-subtype="practitioner_report" data-set="set1"  data-stage="4">Submit Practitioner Report</button>
                                                <button class="button zume alt-color expanded system_joined_affinity_hub" data-top="system" data-subtype="joined_affinity_hub" data-set="set1"  data-stage="4">Join Affinity Hub</button>
                                                <button class="button zume alt-color expanded system_hub_checkin" data-top="system" data-subtype="hub_checkin" data-set="set1"  data-stage="4">Hub Checkin</button>
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
                                        <div id="user_profile"></div>
                                    </div>
                                    <div class="cell medium-6">
                                        <div id="encouragement_plan"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    `)

                jQuery("#user_id option:first").attr('selected','selected');
                let user_id = jQuery('#user_id').val()
                window.set = ''



                /* user location */
                window.get_user_location = ( user_id ) => {
                    makeRequest('POST', 'user_location', { user_id: user_id }, window.site_info.system_root ).done( function( data ) {
                        console.log('user_location')
                        console.log(data)
                        window.user_location = data
                    })
                }
                window.get_user_location( user_id )
                /* end user location */




                /* user state */
                window.get_user_profile = ( user_id ) => {
                    makeRequest('POST', 'user_profile', {user_id: user_id} , window.site_info.system_root ).done( function( data ) {
                        console.log('user_profile')
                        console.log(data)
                        window.user_profile = data

                        if (data.length == 0) {
                            jQuery('.loading-spinner').removeClass('active')
                            jQuery('.button').removeClass('done')
                            return
                        }

                        // set funnel buttons
                        jQuery.each(data.completions, function(index, value) {
                            jQuery('.'+index).addClass('done')
                        })

                        // set user state column
                        let stateList = '<div class="cell"><h2>USER STATE</h2><BR></div>'
                        jQuery.each(data.state, function(ih, vh ) {
                            stateList += `<span style="text-transform:uppercase;">${ih} </span>: ${vh}<br>`
                        })
                        jQuery('#user_profile').html(stateList)


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
                /* end user state */





                /* user encouragement */
                window.get_user_encouragement = ( user_id ) => {
                    makeRequest('POST', 'user_encouragement', { user_id: user_id }, window.site_info.system_root ).done( function( data ) {
                        console.log('user_encouragement')
                        console.log(data)
                        window.user_encourangement = data

                        let encouragement_plan = jQuery('#encouragement_plan')
                        encouragement_plan.html( `
                            <h2>ENCOURAGEMENT PLAN</h2><br>
                            <div id="ctas-list" class="grid-x"></div><br>
                            <div id="plan-list" class="grid-x"></div><br>
                            <div id="reset-list" class="grid-x"></div>
                        `)

                        let ctaList = '<div class="cell"><h4>WEBSITE CTA(s)</h4></div>'
                        jQuery.each(data.cta, function(ih, vh ) {
                            ctaList += `<div class="cell small-8">${vh}</div><div class="cell small-4"></div>`
                        })
                        jQuery('#ctas-list').append(ctaList)

                        let emailList = '<div class="cell"><h4>EMAIL PLAN</h4></div>'
                        jQuery.each(data.plan, function(ih, vh ) {
                            emailList += `<div class="cell small-8">${vh}</div><div class="cell small-4"></div>`
                        })
                        jQuery('#plan-list').append(emailList)

                        let resetdList = '<div class="cell"><h4>RESET ACTION</h4></div>'
                        jQuery.each(data.reset, function(ih, vh ) {
                            resetdList += `<div class="cell small-8">${vh}</div><div class="cell small-4"></div>`
                        })
                        jQuery('#reset-list').append(resetdList)

                    })
                }
                window.get_user_encouragement( user_id )
                /* end user encouragement */


                /* log activity */
                jQuery('.button.zume').on('click', function(event) {
                    jQuery('.loading-spinner').addClass('active')
                    let button = jQuery(this)
                    let type = button.data('top')
                    let subtype = button.data('subtype')
                    let user_id =  jQuery('#user_id').val()
                    window.set = button.data('set')

                    let last_action = jQuery('#last_action')
                    last_action.html(`
                        <div class=""><h2>LAST ACTION</h2>${type} | <strong>${subtype}</strong><br><br>
                    `)

                    let data = {
                        "user_id": user_id,
                        "days_ago": jQuery('#days_ago').val(),
                        "lng": window.user_profile.profile.location.lng,
                        "lat": window.user_profile.profile.location.lat,
                        "level": window.user_profile.profile.location.level,
                        "label": window.user_profile.profile.location.label,
                        "grid_id": window.user_profile.profile.location.grid_id,
                        "type": type,
                        "subtype": subtype,
                        "value": button.data('stage')
                    }
                    console.log('log activity')
                    console.log(data)

                    jQuery(this).addClass('done')
                    makeRequest('POST', 'log', data, window.site_info.system_root ).done( function( data ) {
                        window.get_user_profile( user_id )
                        window.get_user_encouragement( user_id )
                    })

                    if ( type === 'coaching' ) {

                        if ( subtype.match(/.launching/) ) {

                            data.subtype = subtype.replace(/.launching/, '_watching')
                            jQuery('.'+type+data.subtype).addClass('done')
                            makeRequest('POST', 'log', data, window.site_info.system_root )

                            data.subtype = subtype.replace(/.launching/, '_assisting')
                            jQuery('.'+type+data.subtype).addClass('done')
                            makeRequest('POST', 'log', data, window.site_info.system_root )

                            data.subtype = subtype.replace(/.launching/, '_modeling')
                            jQuery('.'+type+data.subtype).addClass('done')
                            makeRequest('POST', 'log', data, window.site_info.system_root )

                        }
                        else if ( subtype.match(/.watching/) ) {

                            data.subtype = subtype.replace(/.watching/, '_assisting')
                            jQuery('.'+type+data.subtype).addClass('done')
                            makeRequest('POST', 'log', data, window.site_info.system_root )

                            data.subtype = subtype.replace(/.watching/, '_modeling')
                            jQuery('.'+type+data.subtype).addClass('done')
                            makeRequest('POST', 'log', data, window.site_info.system_root )
                        }
                        else if ( subtype.match(/.assisting/) ) {

                            data.subtype = subtype.replace(/.assisting/, '_modeling')
                            jQuery('.'+type+data.subtype).addClass('done')
                            makeRequest('POST', 'log', data, window.site_info.system_root )

                        }

                    }
                })
                /* end log activity */

                jQuery('.loading-spinner').removeClass('active')
            })
        </script>
        <?php
    }
}
new Zume_Simulate_Funnel();
