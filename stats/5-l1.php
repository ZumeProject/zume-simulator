<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'zume_range_stats', function( $stats, $request_range  ) {
    if ( ! ( 'none' === $request_range['filter'] || 'l1' === $request_range['filter'] ) ) {
        return $stats;
    }

    $stats[] = [
        'key' => 's1_practitioners',
        'label' => 'S1 (Partial)s',
        'description' => '',
        'value' => 0,
        'goal' => 0,
        'trend' => 0,
        'category' => 'l1',
        'type' => 'number',
        'public' => true,
    ];


    return $stats;
}, 50, 2 );
