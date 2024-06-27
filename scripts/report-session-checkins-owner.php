<?php
// Builds a balanced states view of the world.
include('con.php');
include('./location-grid-geocoder.php');
$geocoder = new Location_Grid_Geocoder();

print 'BEGIN' . PHP_EOL;

$query_raw = mysqli_query( $con,
    "
        SELECT
	        um.*, um1.meta_value as contact_id, lgm.*, u.user_registered
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
print count($query) . PHP_EOL;

$user_ids = [];
foreach( $query as $result ) {
    print 'Starting ' . $result['umeta_id'] . PHP_EOL;

    $user_id = $result['user_id'];
    if ( in_array( $user_id, $user_ids ) ) {
        continue;
    }
    $user_ids[] = $user_id;
    $contact_id = $result['contact_id'];

    $payload = $result['meta_value'];
    $group = unserialize( $payload );
//    print_r( $group );

    if ( ! empty( $group['lng'] ) ) {
        $geocode_result = $geocoder->get_grid_id_by_lnglat( $group['lng'], $group['lat'] );

        $lng = $group['lng'];
        $lat = $group['lat'];
        $level = $geocode_result['level_name'];
        $label = mysqli_real_escape_string( $con, _full_name( $geocode_result ) );
        $grid_id = $geocode_result['grid_id'];
    } else if ( ! empty( $result['lng'] ) ) {
        $lng = $result['lng'];
        $lat = $result['lat'];
        $level = $result['level'];
        $label = mysqli_real_escape_string( $con, $result['label'] );
        $grid_id = $result['grid_id'];
    } else if ( ! empty( $group['ip_address'] ) ) {
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


    // sessions
    $complete = $group['session_1_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_01';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_2_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_02';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_3_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_03';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_4_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_04';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_5_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_05';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_6_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_06';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_7_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_07';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_8_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_08';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_9_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_09';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }
    $complete = $group['session_10_complete'];
    if ( ! empty( $complete ) ) {
        $checkin = 'set_a_10';
        print $checkin . PHP_EOL;
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '$checkin', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
        if ( ! mysqli_query( $con, $sql) ) {
            print mysqli_error($con);
        };
    }


}

print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
