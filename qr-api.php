<?php 
header('Access-Control-Allow-Origin: * ' );
header('Access-Control-Allow-Methods: POST, GET, OPTIONS' );
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type');

require_once( dirname( __FILE__ ) . '/qr-loader.php' );
require_once( dirname( __FILE__ ) . '/includes/qr-transact.php' );
require_once( dirname( __FILE__ ) . '/includes/functions-api.php' );


$method = $_SERVER['REQUEST_METHOD'] == 'POST' ? '_POST' : '_GET' ;
$query = $$method;

qrgen_api( $query );