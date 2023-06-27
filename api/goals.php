<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Zume_Simulator_Goals {
    public static function get() {
        // Goals need to be divided by 365 to get the daily goal.
        return [
            'visitors' => 365 / 2,
            'anonymous' => 365 / 2,
            'registered' => 365 / 2,
            'registrants' => 365 / 2,
            'active_trainees' => 365 / 2,
            'post_training_trainees' => 365 / 2,
            's1_practitioners' => 365 / 6,
            's2_practitioners' => 365 / 6,
            's3_practitioners' => 365 / 6,
        ];
    }
}
