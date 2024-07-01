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
    print 'Starting ' . $result['umeta_id'] . PHP_EOL;

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

    if ( ! mysqli_query( $con, $sql) ) {
        print mysqli_error($con);
    };

    $sql = "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'stage', 'current_level', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )";
    print $sql. PHP_EOL;

    if ( ! mysqli_query( $con, $sql) ) {
        print mysqli_error($con);
    };

    break;

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



    // sessions
    $complete = $group['session_1_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '1_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '2_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '3_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '4_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '5_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_2_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '6_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '7_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '8_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_3_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '9_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '10_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '11_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_4_complete'];
    if ( ! empty( $complete ) ) {


        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '12_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '13_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '14_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '15_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '16_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_5_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '17_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '18_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '19_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_6_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '20_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '21_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );

    }
    $complete = $group['session_7_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '22_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_8_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '23_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_9_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '24_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '25_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '26_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '27_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', 'training_complete', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }
    $complete = $group['session_10_complete'];
    if ( ! empty( $complete ) ) {
        $time_end = strtotime( $complete );
        $timestamp = strtotime( $complete );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '28_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '29_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '30_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '31_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', '32_heard', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
        mysqli_query( $con, "INSERT INTO zume_dt_reports_dev (user_id, post_id, post_type, type, subtype,  value, lng, lat, label, level, grid_id, time_end, timestamp)
            VALUES ('$user_id','$contact_id', 'zume', 'training', 'training_complete', 2, '$lng', '$lat', '$label', '$level', '$grid_id', '$time_end', '$timestamp' )" );
    }



}

print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
