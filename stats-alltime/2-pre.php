<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'zume_all_time_stats', function( $stats, $filter  ) {
    if ( ! ( 'none' === $filter || 'pre' === $filter ) ) {
        return $stats;
    }

    $stats[] = [
        'key' => 'zume_visitors',
        'label' => 'Visitors',
        'description' => '',
        'value' => 0,
        'category' => 'pre',
        'type' => 'number',
        'public' => true,
    ];


    return $stats;
}, 20, 2 );
