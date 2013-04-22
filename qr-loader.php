<?php

if( file_exists( dirname( __FILE__  ) . '/qr-config.php' ) ) {
	require_once( dirname( __FILE__  ) . '/qr-config.php' );
}  else {
	
	if ( strpos( $_SERVER['PHP_SELF'], 'admin' ) !== false )
		$path = 'setup-config.php';
	else
		$path = 'admin/setup-config.php';
	define( 'ABSPATH', dirname(__FILE__) . '/' );
	
	require_once( ABSPATH . '/includes/qr-helpers.php' );
	$die  = '<p>'. "Cannot find <code>qr-config.php</code> file. We need this before we can get started."  . '</p>';
	$die .= '<p class="error">Please read the <tt>readme.html</tt>to learn how to install QR generator</p>';
	$die .= '<p><a href="' . $path . '" class="button">  Create a Configuration File  </a>';
	
	qrgen_die( $die, "QR Manager Setup" );
}

if( !defined( 'QRGEN_DB_PREFIX' ) )
	die( '<p class="error">Your <tt>qr-config.php</tt> does not contain all the required constant definitions.</p>' );

if( !defined( 'QRGEN_ADMIN_SSL' ) )
	define( 'QRGEN_ADMIN_SSL', false );
	

require_once(  dirname( __FILE__ )  . '/includes/qr-functions.php' );
require_once(  dirname( __FILE__ )  . '/includes/qr-dbtable.php' );
require_once(  dirname( __FILE__ )  . '/includes/version.php' );	
require_once(  dirname( __FILE__ )  . '/includes/qr-helpers.php' );
require_once(  dirname( __FILE__ )  . '/includes/functions-kses.php' );
require_once(  dirname( __FILE__ )  . '/includes/functions-http.php' );	
require_once(  dirname( __FILE__ )  . '/includes/functions.php' );
require_once(  dirname( __FILE__ )  . '/includes/function-stats.php' );

global $qrdb;
qr_db_connect( );

global $qrtable;
$qrtable = new qrTable( );

session_start( );
$qrdb->authorize( );

