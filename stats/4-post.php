<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'zume_range_stats', function( $stats, $request_range  ) {
    if ( ! ( 'none' === $request_range['filter'] || 'post' === $request_range['filter'] ) ) {
        return $stats;
    }


    $stats[] = [
        'key' => 'active_3month_plans',
        'label' => 'Active 3 Month Plans',
        'description' => '',
        'value' => 0,
        'goal' => 0,
        'trend' => 0,
        'category' => 'post_training',
        'type' => 'number',
        'public' => true,
    ];


    return $stats;
}, 40, 2 );
