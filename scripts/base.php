<?php
// Builds a balanced states view of the world.
include('con.php');

print 'BEGIN' . PHP_EOL;

$query_raw = mysqli_query( $con,
    "
        SELECT COUNT(*)
        FROM zume_users
     " );
if ( empty( $query_raw ) ) {
    print_r( $con );
    die();
}
$query = mysqli_fetch_all( $query_raw, MYSQLI_ASSOC );

print_r($query);




print PHP_EOL . 'END' . PHP_EOL;
