<?php
// Builds a balanced states view of the world.
include('con.php');
include('./location-grid-geocoder.php');
$geocoder = new Location_Grid_Geocoder();

print 'BEGIN' . PHP_EOL;

$query_raw = mysqli_query( $con,
    "
        SELECT
            um.user_id,
            um1.meta_value as contact_id,
            um.meta_value as groups,
            lgm.grid_id,
            lgm.lng,
            lgm.lat,
            lgm.level,
            lgm.label,
            u.user_registered
        FROM zume_usermeta um
        LEFT JOIN zume_usermeta um1 ON um1.user_id=um.user_id AND um1.meta_key = 'zume_corresponds_to_contact'
        LEFT JOIN zume_dt_location_grid_meta lgm ON lgm.post_id=um1.meta_value AND lgm.post_type = 'contacts'
        LEFT JOIN zume_users u ON u.ID=um.user_id
        WHERE um.meta_key LIKE 'zume_group%'
     " );
if ( empty( $query_raw ) ) {
    print_r( $con );
    die();
}
$query = mysqli_fetch_all( $query_raw, MYSQLI_ASSOC );

$count = 0;
foreach( $query as $result ) {
//    extract(['user_id', 'contact_id', 'payload', 'group', 'owner_id', 'key', '$plan_sql', 'plan_sql_result', 'plan', 'plan_post_id', 'assigned_to_sql' ] );
//    print 'Starting ' . $result['umeta_id'] . PHP_EOL;

    $user_id = $result['user_id'];
    $contact_id = $result['contact_id'];

    $payload = $result['groups'];
    $group = unserialize( $payload );

    $owner_id = $group['owner'];
    $key = $group['key'];

    $plan_sql = "
        SELECT *
        FROM zume_postmeta
        WHERE meta_value = '$key' AND meta_key = 'join_key';
    ";
     $plan_sql_result = mysqli_query(  $con, $plan_sql );
     $plan = mysqli_fetch_all( $plan_sql_result, MYSQLI_ASSOC );

    $plan_post_id = $plan[0]['post_id'];
    if ( $plan_post_id ) {

        $assigned_to_sql = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'assigned_to', 'user-$user_id' )";
        print $assigned_to_sql . PHP_EOL;
        mysqli_query( $con, $assigned_to_sql );

        if ( ! empty( $group['session_1_complete'] ) ) {
            $time = strtotime( $group['session_1_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_01_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_2_complete'] ) ) {
            $time = strtotime( $group['session_2_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_02_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_3_complete'] ) ) {
            $time = strtotime( $group['session_3_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_03_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_4_complete'] ) ) {
            $time = strtotime( $group['session_4_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_04_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_5_complete'] ) ) {
            $time = strtotime( $group['session_5_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_05_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_6_complete'] ) ) {
            $time = strtotime( $group['session_6_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_06_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_7_complete'] ) ) {
            $time = strtotime( $group['session_7_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_07_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_8_complete'] ) ) {
            $time = strtotime( $group['session_8_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_08_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_9_complete'] ) ) {
            $time = strtotime( $group['session_9_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_09_completed', '$time' )";
            mysqli_query( $con, $set );
        }
        if ( ! empty( $group['session_10_complete'] ) ) {
            $time = strtotime( $group['session_10_complete'] );
            $set = "INSERT INTO zume_postmeta (post_id, meta_key, meta_value) VALUES ( '$plan_post_id', 'set_a_10_completed', '$time' )";
            mysqli_query( $con, $set );
        }
    }

    if ( ! $plan_post_id ) {
        print $key . PHP_EOL;
    }

}

print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
