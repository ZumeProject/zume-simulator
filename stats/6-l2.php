<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'zume_range_stats', function( $stats, $request_range  ) {
    if ( ! ( 'none' === $request_range['filter'] || 'l2' === $request_range['filter'] ) ) {
        return $stats;
    }

    $stats[] = [
        'key' => 's2_practitioners',
        'label' => 'S2 (Complete)s',
        'description' => '',
        'value' => 0,
        'goal' => 0,
        'trend' => 0,
        'category' => 'l2',
        'type' => 'number',
        'public' => true,
    ];


    return $stats;
}, 60, 2 );
