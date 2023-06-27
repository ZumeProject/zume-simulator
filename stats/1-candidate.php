<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'zume_range_stats', function( $stats, $request_range ) {
    if ( ! ( 'none' === $request_range['filter'] || 'anonymous' === $request_range['filter'] ) ) {
        return $stats;
    }

    $stats[] = [
        'key' => 'visitors',
        'label' => 'Visitors',
        'description' => 'Visitors to all Zume properties.',
        'value' => 0,
        'goal' => 0,
        'trend' => 0,
        'category' => 'anonymous',
        'type' => 'number',
        'public' => true,
    ];

    $stats[] = [
        'key' => 'registrations',
        'label' => 'Registrations',
        'description' => 'Registrations to all Zume properties.',
        'value' => 0,
        'goal' => 0,
        'trend' => 0,
        'category' => 'anonymous',
        'type' => 'number',
        'public' => true,
    ];


    return $stats;
}, 10, 2 );
