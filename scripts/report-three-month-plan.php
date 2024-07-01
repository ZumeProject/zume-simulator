<?php
// Builds a balanced states view of the world.
include('con.php');
include('./location-grid-geocoder.php');
$geocoder = new Location_Grid_Geocoder();

print 'BEGIN' . PHP_EOL;

$query_raw = mysqli_query( $con,
    "
        SELECT um.user_id, um.meta_value as plan, um1.meta_value as contact_id
        FROM zume_usermeta um
		LEFT JOIN zume_usermeta um1 ON um1.user_id=um.user_id AND um1.meta_key = 'zume_corresponds_to_contact'
        WHERE um.meta_key = 'three_month_plan'
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
    print_r( $group );

    $due_date = date( 'Y-m-d H:i:s', $group['last_modified_date'] );

    $list = [];
    foreach ( $plan as $i => $v ) {
        if ( empty( $v ) ) {
            continue;
        }
        if ( 'group_key' === $i ) {
            continue;
        }
        if ( 'user_id' === $i ) {
            continue;
        }

        $question = ucwords( str_replace('_', ' ', $i ) );

        $serial = serialize( [
            'note' => '',
            'question' => $question,
            'answer' => $v,
            'notification' => 'notification_sent',
            'status' => 'closed'
        ] );

        $sql = "INSERT INTO zume_dt_post_user_meta (user_id, post_id, meta_key, meta_value, date, category )
                VALUES ('$user_id','$contact_id', 'tasks', '$serial', '$due_date', 'post_training_plan' )";
        print $sql . PHP_EOL;

        mysqli_query( $con, $sql );

        $count++;

    }
}

print 'Processed: ' . $count . PHP_EOL;
print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
