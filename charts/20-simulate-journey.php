<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Test_Journey extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'test_journey'; // lowercase
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
        $this->base_title = __( 'simulate journey', 'disciple_tools' );

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
        </style>

        <script>
            window.site_url = '<?php echo site_url() ?>' + '/wp-json/zume_simulator/v1/'
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

                            <div class="cell small-5">
                                <h2>KEY STEPS</h2>
                            </div>
                            <div class="cell small-5 left-border">
                                <h2>CTAs</h2>
                            </div>
                            <div class="cell small-2">

                            </div>
                            <div class="cell">
                                <hr>
                            </div>


                             <div class="cell">
                                <h2>Anonymous</h2>
                            </div>
                            <div class="cell small-5">
                                <button class="button large registration" data-key="registration" data-stage="0">Registered</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button large alt-color requested_a_coach" data-key="requested_a_coach" data-stage="0">Coach Request</button>
                                <button class="button large alt-color joined_online_training" data-key="joined_online_training" data-stage="0">Joined Online Training</button>
                            </div>
                            <div class="cell small-2">

                            </div>
                             <div class="cell">
                                <hr>
                            </div>

                            <div class="cell">
                                <h2>Registrant</h2>
                            </div>
                            <div class="cell small-5">
                               <button class="button large made_a_plan" data-key="made_a_plan" data-stage="1">Made a Plan</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button large alt-color requested_a_coach" data-key="requested_a_coach" data-stage="1">Coach Request</button>
                                <button class="button large alt-color set_profile" data-key="set_profile" data-stage="1">Set Profile</button>
                                <button class="button large alt-color invited_friends" data-key="invited_friends" data-stage="1">Invited Friends</button>
                                <button class="button large alt-color joined_online_training" data-key="joined_online_training" data-stage="1">Joined Online Training</button>
                            </div>
                            <div class="cell small-2">

                            </div>
                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Active Training Trainee</h2>
                            </div>
                            <div class="cell small-5">
                               <button class="button large completed_training" data-key="completed_training" data-stage="2">Completed 25 of 32 Training Points</button><br>
                               <button class="button large training_item_01" data-key="training_item_01" data-stage="2">1</button>
                               <button class="button large training_item_02" data-key="training_item_02" data-stage="2">2</button>
                               <button class="button large training_item_03" data-key="training_item_03" data-stage="2">3</button>
                               <button class="button large training_item_04" data-key="training_item_04" data-stage="2">4</button>
                               <button class="button large training_item_05" data-key="training_item_05" data-stage="2">5</button>
                               <button class="button large training_item_06" data-key="training_item_06" data-stage="2">6</button>
                               <button class="button large training_item_07" data-key="training_item_07" data-stage="2">7</button>
                               <button class="button large training_item_08" data-key="training_item_08" data-stage="2">8</button>
                               <button class="button large training_item_09" data-key="training_item_09" data-stage="2">9</button>
                               <button class="button large training_item_10" data-key="training_item_10" data-stage="2">10</button>
                               <button class="button large training_item_11" data-key="training_item_11" data-stage="2">11</button>
                               <button class="button large training_item_12" data-key="training_item_12" data-stage="2">12</button>
                               <button class="button large training_item_13" data-key="training_item_13" data-stage="2">13</button>
                               <button class="button large training_item_14" data-key="training_item_14" data-stage="2">14</button>
                               <button class="button large training_item_15" data-key="training_item_15" data-stage="2">15</button>
                               <button class="button large training_item_16" data-key="training_item_16" data-stage="2">16</button>
                               <button class="button large training_item_17" data-key="training_item_17" data-stage="2">17</button>
                               <button class="button large training_item_18" data-key="training_item_18" data-stage="2">18</button>
                               <button class="button large training_item_19" data-key="training_item_19" data-stage="2">19</button>
                               <button class="button large training_item_20" data-key="training_item_20" data-stage="2">20</button>
                               <button class="button large training_item_21" data-key="training_item_21" data-stage="2">21</button>
                               <button class="button large training_item_22" data-key="training_item_22" data-stage="2">22</button>
                               <button class="button large training_item_23" data-key="training_item_23" data-stage="2">23</button>
                               <button class="button large training_item_24" data-key="training_item_24" data-stage="2">24</button>
                               <button class="button large training_item_25" data-key="training_item_25" data-stage="2">25</button>
                               <button class="button large training_item_26" data-key="training_item_26" data-stage="2">26</button>
                               <button class="button large training_item_27" data-key="training_item_27" data-stage="2">27</button>
                               <button class="button large training_item_28" data-key="training_item_28" data-stage="2">28</button>
                               <button class="button large training_item_29" data-key="training_item_29" data-stage="2">29</button>
                               <button class="button large training_item_30" data-key="training_item_30" data-stage="2">30</button>
                               <button class="button large training_item_31" data-key="training_item_31" data-stage="2">31</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button large alt-color requested_a_coach" data-key="requested_a_coach" data-stage="2">Coach Request</button>
                                <button class="button large alt-color set_profile" data-key="set_profile" data-stage="2">Set Profile</button>
                                <button class="button large alt-color invited_friends" data-key="invited_friends" data-stage="2">Invited Friends</button>
                                <button class="button large alt-color made_3_month_plan" data-key="made_3_month_plan" data-stage="2">Made 3-Month Plan</button>
                            </div>
                            <div class="cell small-2">

                            </div>
                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Post-Training Trainee</h2>
                            </div>
                            <div class="cell small-5">
                               <button class="button large made_first_lifestyle_report" data-key="made_first_lifestyle_report" data-stage="1">Made First Lifestyle Report</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button large alt-color requested_a_coach" data-key="requested_a_coach" data-stage="3">Coach Request</button>
                                <button class="button large alt-color set_profile" data-key="set_profile" data-stage="3">Set Profile</button>
                                <button class="button large alt-color completed_3_month_plan" data-key="completed_3_month_plan" data-stage="3">Completed 3-Month Plan</button>
                            </div>
                            <div class="cell small-2">

                            </div>
                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Stage 1 - Partial Practitioner</h2>
                            </div>
                            <div class="cell small-5">
                               <button class="button large completed_mawl_of_checklist" data-key="completed_mawl_of_checklist" data-stage="4">Completed MAWL of Checklist</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button large alt-color requested_a_coach" data-key="requested_a_coach" data-stage="4">Coach Request</button>
                                <button class="button large alt-color submitted_church_report" data-key="submitted_church_report" data-stage="4">Report Churches</button>
                                <button class="button large alt-color joined_affinity_hub" data-key="joined_affinity_hub" data-stage="4">Join Affinity Hub</button>
                            </div>
                            <div class="cell small-2">

                            </div>
                             <div class="cell">
                                <hr>
                            </div>

                            <div class="cell">
                                <h2>Stage 2 - Completed Practitioner</h2>
                            </div>
                            <div class="cell small-5">
                               <button class="button large seeing_generational_friuit" data-key="seeing_generational_friuit" data-stage="5">Seeing Generational Fruit</button>
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button large alt-color requested_a_coach" data-key="requested_a_coach" data-stage="5">Coach Request</button>
                                <button class="button large alt-color submitted_church_report" data-key="submitted_church_report" data-stage="5">Report Churches</button>
                                <button class="button large alt-color joined_affinity_hub" data-key="joined_affinity_hub" data-stage="5">Join Affinity Hub</button>
                            </div>
                            <div class="cell small-2">

                            </div>
                             <div class="cell">
                                <hr>
                            </div>


                            <div class="cell">
                                <h2>Stage 3 - Multiplying Practitioner</h2>
                            </div>
                            <div class="cell small-5">
                            </div>
                            <div class="cell small-5 left-border">
                                <button class="button large alt-color requested_a_coach" data-key="requested_a_coach" data-stage="6">Coach Request</button>
                                <button class="button large alt-color submitted_church_report" data-key="submitted_church_report" data-stage="6">Report Churches</button>
                                <button class="button large alt-color coaching_others" data-key="coaching_others" data-stage="6">Coach Others</button>
                                <button class="button large alt-color joined_affinity_hub" data-key="joined_affinity_hub" data-stage="6">Join Affinity Hub</button>
                                <button class="button large alt-color providing_hub_leadership" data-key="providing_hub_leadership" data-stage="6">Provide Hub Leadership</button>
                            </div>
                            <div class="cell small-2">

                            </div>
                             <div class="cell">
                                <hr>
                            </div>

                        </div>
                    </div>
                    `)

                jQuery('.button').on('click', function(event) {
                    event.preventDefault()
                    jQuery('.loading-spinner').addClass('active')
                    let data = {
                        "user_id": jQuery('#user_id').val(),
                        "days_ago": jQuery('#days_ago').val(),
                        "location": jQuery('#location').val(),
                        "stage": jQuery(this).data('stage'),
                        "key": jQuery(this).data('key'),
                    }
                    jQuery(this).addClass('done')
                    makePostRequest('POST', 'log', data )
                        .then((response) => {
                            console.log(response)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                        .catch((error) => {
                            console.log(error)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                })

                function get_user_progress( user_id ) {
                    makePostRequest('POST', 'user_progress', {user_id: user_id} )
                        .then((response) => {
                            console.log(response)
                            if (response.length == 0) {
                                jQuery('.loading-spinner').removeClass('active')
                                jQuery('.button').removeClass('done')
                                return
                            }
                            jQuery.each(response, function(index, value) {
                                jQuery('.'+value.subtype).addClass('done')
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



                function makePostRequest(type, url, data, base = "zume_simulator/v1/") {
                    //make sure base has a trailing slash if url does not start with one
                    if ( !base.endsWith('/') && !url.startsWith('/')){
                        base += '/'
                    }
                    const options = {
                        type: type,
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        url: url.startsWith("http") ? url : `${wpApiShare.root}${base}${url}`,
                        beforeSend: (xhr) => {
                            xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
                        },
                    };

                    if (data && !window.lodash.isEmpty(data)) {
                        options.data = type === "GET" ? data : JSON.stringify(data);
                    }

                    return jQuery.ajax(options);
                }

                jQuery("#user_id option:first").attr('selected','selected');
                get_user_progress( jQuery('#user_id option:first').val() )
                jQuery('.loading-spinner').removeClass('active')
            })
        </script>
        <?php
    }
}
new Zume_Simulator_Test_Journey();
