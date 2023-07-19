<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Metrics_Base {

    public $base_slug = 'zume-simulator';
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_filter( 'desktop_navbar_menu_options', [ $this, 'add_navigation_links' ], 35 );
        add_filter( 'off_canvas_menu_options', [ $this, 'add_navigation_links' ], 35);

        $url_path = dt_get_url_path(true);



        // top
        if ( str_contains( $url_path, $this->base_slug ) !== false || dt_is_rest() ) {
            add_filter('dt_templates_for_urls', [$this, 'dt_templates_for_urls']);
            add_filter('dt_metrics_menu', [$this, 'dt_metrics_menu'], 99);

            require_once ('abstract.php');
            require_once ('01-register-user.php');
            require_once ('02-user-state.php');

            require_once ('20-simulate-funnel.php');
            require_once ('23-simulate-next-step.php');

            require_once ('100-reset-tracking.php');

        }
    }

    public function add_navigation_links( $tabs ) {
        if ( current_user_can( 'access_disciple_tools' ) ) {
            $tabs[] = [
                "link" => site_url( "/zume-simulator/" ), // the link where the user will be directed when they click
                "label" => __( "Simulator", "disciple_tools" )  // the label the user will see
            ];
        }
        return $tabs;
    }

    public function dt_metrics_menu( $content ) {
        return $content;
    }

    public function dt_templates_for_urls( $template_for_url ) {
        $template_for_url['zume-simulator'] = 'template-metrics.php';
        return $template_for_url;
    }
}
Zume_Simulator_Metrics_Base::instance();
