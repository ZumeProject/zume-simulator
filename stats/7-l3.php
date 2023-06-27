<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'zume_range_stats', function( $stats, $request_range ) {
    if ( ! ( 'none' === $request_range['filter'] || 'l3' === $request_range['filter'] ) ) {
        return $stats;
    }

    $stats[] = [
        'key' => 's3_practitioners',
        'label' => 'S3 (Multiplying)s',
        'description' => '',
        'value' => 0,
        'goal' => 0,
        'trend' => 0,
        'category' => 'l3',
        'type' => 'number',
        'public' => true,
    ];

    return $stats;
}, 70, 2 );
