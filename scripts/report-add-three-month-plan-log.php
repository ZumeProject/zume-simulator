<?php
// Builds a balanced states view of the world.
include('con.php');
include('./location-grid-geocoder.php');
$geocoder = new Location_Grid_Geocoder();

print 'BEGIN' . PHP_EOL;

$query_raw = mysqli_query( $con,
    "
        SELECT um.user_id, um1.meta_value as contact_id, um.meta_value as plan, lgm.*, u.user_registered
        FROM zume_usermeta um
        LEFT JOIN zume_usermeta um1 ON um1.user_id=um.user_id AND um1.meta_key = 'zume_corresponds_to_contact'
        LEFT JOIN zume_dt_location_grid_meta lgm ON lgm.post_id=um1.meta_value
        LEFT JOIN zume_users u ON u.ID=um.user_id
        WHERE um.meta_key = 'three_month_plan';
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

    print $user_id . PHP_EOL;

    $plan = unserialize( $plan );
    $plan['user_id'] = $user_id;
    $group_key = $plan['group_key'];
    if ( empty( $group_key ) ) {
        continue;
    }
    $group_raw = mysqli_query( $con,
        "
                SELECT um.meta_value as payload
                FROM zume_usermeta um
                WHERE um.meta_key = '$group_key';
                " );
    $group_serial = mysqli_fetch_array( $group_raw, MYSQLI_ASSOC );
    $group = unserialize( $group_serial['payload'] );
//    print_r( $group );

    $time  = '';
    if ( empty( $time ) ) {
        $time = strtotime( $group['created_date'] );
    }
    if ( empty( $time ) ) {
        $time = strtotime( $result['user_registered'] );
    }

    $label = mysqli_real_escape_string( $con, $label );

    $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'system', 'made_post_training_plan', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time', '$time' )";
    print $sql. PHP_EOL;

    mysqli_query( $con, $sql);

    $count++;

}

print 'Processed: ' . $count . PHP_EOL;
print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
