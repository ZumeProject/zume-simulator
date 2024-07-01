<?php
include('con.php');
include('./location-grid-geocoder.php');
$geocoder = new Location_Grid_Geocoder();

print 'BEGIN' . PHP_EOL;


$query_raw = mysqli_query( $con,
    "
            SELECT um.user_id, um.meta_value, um1.meta_value as contact_id
            FROM zume_usermeta um
            JOIN zume_usermeta um1 ON um1.user_id=um.user_id AND um1.meta_key = 'zume_corresponds_to_contact'
            WHERE um.meta_key LIKE 'zume_group%'
            " );
if ( empty( $query_raw ) ) {
    print_r( $con );
    die();
}
$groups = mysqli_fetch_all( $query_raw, MYSQLI_ASSOC );

foreach( $groups as $row ) {
    $group = unserialize( $row['meta_value'] );
    if ( empty($group['owner'] ) ) {
       continue;
    }

    $title = $group['group_name'];
    $user_id = $row['user_id'];
    $contact_id = $row['contact_id'];

    $creation_time = strtotime( $group['created_date'] );
    $set_a_01 = $creation_time + 604800;
    $set_a_02 = $creation_time + ( 604800 * 2 );
    $set_a_03 = $creation_time + ( 604800 * 3 );
    $set_a_04 = $creation_time + ( 604800 * 4 );
    $set_a_05 = $creation_time + ( 604800 * 5 );
    $set_a_06 = $creation_time + ( 604800 * 6 );
    $set_a_07 = $creation_time + ( 604800 * 7 );
    $set_a_08 = $creation_time + ( 604800 * 8 );
    $set_a_09 = $creation_time + ( 604800 * 9 );
    $set_a_10 = $creation_time + ( 604800 * 10 );

    $fields = [
        'title' => $title,
        'assigned_to' => $user_id,
        'set_type' => 'set_a',
        'visibility' => 'private',
        'created_date' => $creation_time,
        'set_a_01' => $set_a_01,
        'set_a_02' => $set_a_02,
        'set_a_03' => $set_a_03,
        'set_a_04' => $set_a_04,
        'set_a_05' => $set_a_05,
        'set_a_06' => $set_a_06,
        'set_a_07' => $set_a_07,
        'set_a_08' => $set_a_08,
        'set_a_09' => $set_a_09,
        'set_a_10' => $set_a_10,
        'participants' => [
            'values' => [
                [ 'value' => $contact_id ]
            ]
        ]
    ];

    // if coleaders
    if ( ! empty( $group['coleaders'] ) ) {
        $coleaders = $group['coleaders'];

        $coleader_contact_ids = [];
        foreach( $coleaders as $coleader_email ) {
            $cl_raw = mysqli_query( $con,
                "
                        SELECT um.meta_value as contact_id
                        FROM zume_users u
                        JOIN zume_usermeta um ON um.user_id=u.ID AND um.meta_key = 'zume_corresponds_to_contact'
                        WHERE u.user_email = '$coleader_email'
                     " );
            if ( empty( $cl_raw ) ) {
                print_r( $con );
                die();
            }
            $u_result = mysqli_fetch_array( $cl_raw );
            if ( empty($u_result['contact_id'] ) ) {
                continue;
            }
            $cid = $u_result['contact_id'];// lookup user_id from user_email

            $coleader_contact_ids[] = $cid;
        }

        if ( ! empty( $coleader_contact_ids ) ) {
            foreach( $coleader_contact_ids as $coleader_contact_id ) {
                $fields['participants']['values'][] = [ 'value' => $coleader_contact_id ];
            }
        }
    }


    print_r($fields);
}


