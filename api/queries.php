<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Query {

    public static function sample( $params ) {
        $negative_stat = false;
        if ( isset( $params['negative_stat'] ) && $params['negative_stat'] ) {
            $negative_stat = $params['negative_stat'];
        }

        $value = rand(100, 1000);
        $goal = rand(500, 700);
        $trend = rand(500, 700);
        return [
            'key' => 'sample',
            'label' => 'Sample',
            'link' => 'sample',
            'description' => 'Sample description.',
            'value' => self::format_int( $value ),
            'valence' => self::get_valence( $value, $goal, $negative_stat ),
            'goal' => $goal,
            'goal_valence' => self::get_valence( $value, $goal, $negative_stat ),
            'goal_percent' => self::get_percent( $value, $goal ),
            'trend' => $trend,
            'trend_valence' => self::get_valence( $value, $trend, $negative_stat ),
            'trend_percent' => self::get_percent( $value, $trend ),
            'negative_stat' => $negative_stat,
        ];
    }

}
