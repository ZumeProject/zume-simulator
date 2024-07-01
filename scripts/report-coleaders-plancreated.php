<?php
// Builds a balanced states view of the world.
include('con.php');
include('./location-grid-geocoder.php');
$geocoder = new Location_Grid_Geocoder();

print 'BEGIN' . PHP_EOL;

$query_raw = mysqli_query( $con,
    "
        SELECT u.ID as user_id, u.user_email, um.meta_value as contact_id, lgm.lng, lgm.lat, lgm.level, lgm.label, lgm.grid_id, c.payload, c.time
        FROM coleaders c
        JOIN zume_users u ON u.user_email=c.user_email
        JOIN zume_usermeta um ON u.ID=um.user_id AND um.meta_key = 'zume_corresponds_to_contact'
        LEFT JOIN zume_dt_location_grid_meta lgm ON lgm.post_id=um.meta_value;
     " );
if ( empty( $query_raw ) ) {
    print_r( $con );
    die();
}
$query = mysqli_fetch_all( $query_raw, MYSQLI_ASSOC );
print count($query) . PHP_EOL;

foreach( $query as $result ) {
    print 'Starting ' . $result['user_id'] . PHP_EOL;

    $user_id = $result['user_id'];
    $contact_id = $result['contact_id'];

    $payload = $result['payload'];
    $group = unserialize( $payload );
//    print_r( $group );

    if ( ! empty( $result['lng'] ) ) {
        $lng = $result['lng'];
        $lat = $result['lat'];
        $level = $result['level'];
        $label = mysqli_real_escape_string( $con, $result['label'] );
        $grid_id = $result['grid_id'];
    }
    else if ( ! empty( $group['lng'] ) ) {
        $geocode_result = $geocoder->get_grid_id_by_lnglat( $group['lng'], $group['lat'] );
        $lng = $group['lng'];
        $lat = $group['lat'];
        $level = $geocode_result['level_name'];
        $label = mysqli_real_escape_string( $con, _full_name( $geocode_result ) );
        $grid_id = $geocode_result['grid_id'];
    }
    else if ( ! empty( $group['ip_address'] ) ) {
        // set IP address and API access key
        $ip = $group['ip_address'];
        $access_key = 'd3a276e9bcd8bb03c0963acd3f8ae522';

        // Initialize CURL:
        $ch = curl_init('https://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $api_result = json_decode($json, true);
        print_r($api_result) . PHP_EOL;

        $lgm = convert_ip_result_to_location_grid_meta( $api_result );

        if ( empty( $lgm['lng'] ) ) {
            $lng = '';
            $lat = '';
            $level = '';
            $label = '';
            $grid_id = '';
            print 'NO LOCATION FOUND'. PHP_EOL;
            continue;
        } else {
            $lng = $lgm['lng'];
            $lat = $lgm['lat'];
            $level = $lgm['level'];
            $label = mysqli_real_escape_string( $con, $lgm['label'] );
            $grid_id = $lgm['grid_id'];
        }
    } else {
        print "NO LOCATION MATCH" . PHP_EOL;
        continue;
    }
//    break;

    $time_end = strtotime( $group['created_date'] );
    if ( empty( $time_end ) ) {
        $time_end = strtotime( $group['last_modified'] );
    }
    if ( empty( $time_end ) ) {
        $time_end = strtotime( $result['user_registered'] );
    }
    $timestamp = strtotime( $group['created_date'] );
    if ( empty( $timestamp ) ) {
        $timestamp = strtotime( $group['last_modified'] );
    }
    if ( empty( $timestamp ) ) {
        $timestamp = strtotime( $result['user_registered'] );
    }


    $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'system', 'plan_created', 1, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
    print $sql. PHP_EOL;
    mysqli_query( $con, $sql);

    $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'stage', 'current_level', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
    print $sql. PHP_EOL;
    mysqli_query( $con, $sql);

}

print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
