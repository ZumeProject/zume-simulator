<?php
// Extend PHP limits for large processing
ini_set('memory_limit', '50000M');

// define and create output directories
$o = [
    'output' => getcwd() . '/output/',
];
foreach ( $o as $dirname ) {
    if ( ! is_dir( $dirname ) ) {
        mkdir($dirname, 0755, true);
    }
}

// define live folders
$f = [
    'root' =>  '../scripts/',
];

// define table names
$t = [
    'rd' => 'zume_dt_reports_dev',
    'temp' => 'temp_master_users',
    'p' => 'zume_posts',
    'pm' => 'zume_postmeta',
    'u' => 'zume_users',
    'um' => 'zume_usermeta',
];

// define database connection
if ( ! file_exists( 'connect_params.json') ) {
    $content = '{"host": "","username": "","password": "","database": ""}';
    file_put_contents( 'connect_params.json', $content );
}
$params = json_decode( file_get_contents( "connect_params.json" ), true );
if ( empty( $params['host'] ) ) {
    print 'You have just created the connect_params.json file, but you still need to add database connection information.
Please, open the connect_params.json file and add host, username, password, and database information.' . PHP_EOL;
    die();
}
$con = mysqli_connect( $params['host'], $params['username'], $params['password'],$params['database']);
if (!$con) {
    echo 'mysqli Connection FAILED. Check parameters inside connect_params.json file.' . PHP_EOL;
    die();
}

$ipstack_key = 'd3a276e9bcd8bb03c0963acd3f8ae522';

function _full_name( $row ) {
    $label = '';

    if ( 1 === $row['grid_id'] ) {
        $label = 'World';
    }

    if ( ! empty( $row['admin0_name'] ) ) {
        $label = $row['admin0_name'];
    }
    if ( ! empty( $row['admin1_name'] ) ) {
        $label = $row['admin1_name']  . ', ' . $row['admin0_name'];
    }
    if ( ! empty( $row['admin2_name'] ) ) {
        $label = $row['admin2_name'] . ', ' . $row['admin1_name']  . ', ' . $row['admin0_name'];
    }
    if ( ! empty( $row['admin3_name'] ) ) {
        $label = $row['admin3_name'] . ', ' . $row['admin2_name'] . ', '  . $row['admin1_name']  . ', ' . $row['admin0_name'];
    }

    return $label;
}

function convert_ip_result_to_location_grid_meta( $ip_result ) {
    print __METHOD__ . PHP_EOL;
    if ( empty( $ip_result['longitude'] ) ) {
        return false;
    }
    require_once('./location-grid-geocoder.php');
    $geocoder = new Location_Grid_Geocoder();


    // prioritize the smallest unit
    if ( !empty( $ip_result['city'] ) ) {
        $label = $ip_result['city'] . ', ' . $ip_result['region_name'] . ', ' . $ip_result['country_name'];
        $level = 'district';
    } elseif ( !empty( $ip_result['region_name'] ) ) {
        $label = $ip_result['region_name'] . ', ' . $ip_result['country_name'];
        $level = 'region';
    } elseif ( !empty( $ip_result['country_name'] ) ) {
        $label = $ip_result['country_name'];
        $level = 'country';
    } elseif ( !empty( $ip_result['continent_name'] ) ) {
        $label = $ip_result['continent_name'];
        $level = 'world';
    } else {
        $label = '';
        $level = '';
    }

    $grid_id = $geocoder->get_grid_id_by_lnglat( $ip_result['longitude'], $ip_result['latitude'] );
    if ( empty( $label ) ) {
        $label = $geocoder->_format_full_name( $grid_id );
    }

    $location_grid_meta = [
        'lng' => $ip_result['longitude'] ?? '',
        'lat' => $ip_result['latitude'] ?? '',
        'level' => $level,
        'label' => $label,
        'source' => 'ip',
        'grid_id' => $grid_id['grid_id'] ?? '',
    ];

//    Location_Grid_Meta::validate_location_grid_meta( $location_grid_meta );

    return $location_grid_meta;
}
