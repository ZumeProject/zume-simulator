<?php

class Zume_System_Completions_API
{
    public $namespace = 'zume_system/v1';
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if (dt_is_rest()) {
            add_action('rest_api_init', [$this, 'add_api_routes']);
            add_filter('dt_allow_rest_access', [$this, 'authorize_url'], 10, 1);
        }
    }

    public function authorize_url($authorized)
    {
        if (isset($_SERVER['REQUEST_URI']) && strpos(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), $this->namespace) !== false) {
            $authorized = true;
        }
        return $authorized;
    }

    public function add_api_routes()
    {
        $namespace = $this->namespace;
        register_rest_route(
            $namespace, '/user_completions', [
                'methods' => ['GET', 'POST'],
                'callback' => [$this, 'request_sorter'],
                'permission_callback' => '__return_true'
            ]
        );
    }

    public function request_sorter(WP_REST_Request $request)
    {
        $params = dt_recursive_sanitize_array($request->get_params());

        if (is_user_logged_in()) {
            return $this->user($params);
        } else {
            return $this->guest($params);
        }
    }

    public function user($params)
    {
        $user_id = $params['user_id'] ?? get_current_user_id();

        global $wpdb;
        $sql = $wpdb->prepare( "SELECT CONCAT( r.type, '_', r.subtype ) as log_key, r.*
                FROM $wpdb->dt_reports r
                WHERE r.user_id = %s
                AND r.post_type = 'zume'
                ", $user_id );
        $results = $wpdb->get_results( $sql, ARRAY_A );

        $data = [];
        foreach( $results as $item ) {
            if ( 'encouragement' === $item['type'] ) {
                $data[$item['log_key'] . '_' . $item['parent_id']] = true;
            } else {
                $data[$item['log_key']] = true;
            }
        }

        return $data;
    }
    public static function _query_user_log( $user_id ) {

    }

    public function guest($params)
    {
        return [];
    }

}
Zume_System_Completions_API::instance();
