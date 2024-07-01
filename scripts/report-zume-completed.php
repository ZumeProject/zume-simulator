<?php
// Builds a balanced states view of the world.
include('con.php');
include('./location-grid-geocoder.php');
$geocoder = new Location_Grid_Geocoder();

print 'BEGIN' . PHP_EOL;

$query_raw = mysqli_query( $con,
    "
        SELECT *
        FROM zume_dt_reports r
        WHERE r.subtype = 'training_complete';
     " );
if ( empty( $query_raw ) ) {
    print_r( $con );
    die();
}
$query = mysqli_fetch_all( $query_raw, MYSQLI_ASSOC );
print count($query) . PHP_EOL;

$dup_list = [];
$count = 0;
foreach( $query as $result ) {
    extract($result);
    if ( in_array( $user_id, $dup_list ) ) {
        continue;
    }
    $dup_list[] = $user_id;

    $label = mysqli_real_escape_string( $con, $label );

    $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$post_id', 'zume', 'stage', 'current_level', 3, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";

    print $sql . PHP_EOL;
    $count++;

//    continue;

    if ( ! mysqli_query( $con, $sql) ) {
        print mysqli_error($con);
    };

}

print 'Processed: ' . $count . PHP_EOL;
print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
