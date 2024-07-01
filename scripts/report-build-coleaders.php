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

foreach( $query as $result ) {
    print 'Starting ' . $result['umeta_id'] . PHP_EOL;

    $user_id = $result['user_id'];
    $contact_id = $result['contact_id'];

    $payload = $result['meta_value'];
    $group = unserialize( $payload );

    $new_group = mysqli_real_escape_string( $con, serialize($group) );

    $time = strtotime( $group['created_date'] );

//    print_r( $group );

    if ( ! empty( $group['coleaders'] ) ) {
        foreach( $group['coleaders'] as $coleaders) {

            $sql = "INSERT INTO coleaders (user_email, payload, time )
            VALUES ('$coleaders', '$new_group', '$time')";
            if ( ! mysqli_query( $con, $sql) ) {
                print mysqli_error($con);
            };

        }
    }


}

print count($query) . PHP_EOL;


print PHP_EOL . 'END' . PHP_EOL;
