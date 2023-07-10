<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Evaluate extends Zume_Simulator_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'evaluate_user'; // lowercase
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
        $this->base_title = __( 'evaluate', 'disciple_tools' );

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
                display:none;
            }
        </style>

        <script>
            window.user_id = '<?php echo get_current_user_id() ?>'

            jQuery(document).ready(function(){
                "use strict";
                let chart = jQuery('#chart')
                let selectors = `<?php echo zume_simulator_selectors() ?>`

                chart.empty().html(`
                        <div class="grid-x">
                            <div class="cell medium-9">
                                ${selectors}
                                <button class="button" style="margin-top:10px;">Lookup</button>
                                <span class="loading-spinner active"></span>
                            </div>
                            <div class="cell medium-3 right">
                                <h2>Evaluate User</h2>
                            </div>
                            <div class="cell">
                                <hr>
                            </div>
                        </div>
                        <div class="grid-x grid-padding-x">
                            <div class="cell medium-6" id="list"></div>
                            <div class="cell medium-6" id="user_state" style="border-left: 1px solid lightgrey;"></div>
                        </div>
                    </div>
                    `)

                jQuery('.button').on('click', function(event) {
                    event.preventDefault()
                    jQuery('.loading-spinner').addClass('active')
                    let data = {
                        "user_id": jQuery('#user_id').val(),
                        "days_ago": jQuery('#days_ago').val(),
                    }
                    jQuery(this).addClass('done')
                    get_user_progress(data)
                    get_user_state(data)
                })

                function get_user_progress( data ) {
                    makeRequest('POST', 'lookup', data, window.site_info.rest_root ).done( function( data ) {
                        console.log(data)
                        jQuery('#list').html( list_template( data ) )
                        jQuery('.loading-spinner').removeClass('active')
                    })
                        .catch((error) => {
                            console.log(error)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                }
                function list_template( data ) {
                    let html = ''
                    data.forEach( function( item ) {
                        html += `
                            <div class="grid-x grid-padding-x">
                                <div class="cell medium-1">
                                    <h4>${item.value}</h4>
                                </div>
                                <div class="cell medium-4">
                                    <h4>${item.subtype}</h4>
                                </div>
                                <div class="cell medium-3">
                                    <h4>${item.label}</h4>
                                </div>
                                <div class="cell medium-3">
                                    <h4>${item.time_end}<br>${item.timestamp}</h4>
                                </div>
                            </div>
                        `
                    })
                    return html
                }
                function get_user_state(data) {
                    makeRequest('POST', 'user_state', data, window.site_info.rest_root ).done( function( data ) {
                        console.log(data)
                        jQuery('#user_state').empty()
                        jQuery.each( data, function(i,v) {
                            jQuery('#user_state').append(`<h2><span style="text-transform:uppercase">${i}</span>: ${v}</h2>`)
                        })
                        jQuery('.loading-spinner').removeClass('active')
                    })
                        .catch((error) => {
                            console.log(error)
                            jQuery('.loading-spinner').removeClass('active')
                        })
                }



                jQuery("#user_id option:first").attr('selected','selected');
                // get_user_progress( jQuery('#user_id option:first').val() )
                jQuery('.loading-spinner').removeClass('active')
            })
        </script>
        <?php
    }
}
new Zume_Simulator_Evaluate();
