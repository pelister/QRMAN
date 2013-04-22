<?php

	//SET ALL OF THE FOLLOWING VALUES before running anything else

/* MySQL hostname */
define('QRGEN_DB_HOST', 'localhost');	

/*	MySQL database user */
define('QRGEN_DB_USER', 'root');

/* MySQL database password */
define('QRGEN_DB_PASS', 'db password');

/* The name of the database for QRGen */
define('QRGEN_DB_NAME', 'db name');

/* MySQL tables prefix */
define('QRGEN_DB_PREFIX', 'qrm_');

/* QRGen installation URL -- all lowercase and with no trailing slash.
 ** If you define it to "http://site.com", don't use "http://www.site.com" in your browser (and vice-versa) */

define('QRGEN_SITE', 'http://localhost/qrman');

/**
 * Email Settings for the the from field in the emails
 * sent to users, and set EMAIL WELCOME to true or false 
 * to send a welcome email to newly registered users.
 */
//set this false if you do not want your users to receive a welcome Email after registration
define('EMAIL_WELCOME', true); 


define( 'QRGEN_DB_DSN', 'mysql:dbname=' . QRGEN_DB_NAME . ';host=' . QRGEN_DB_HOST );
 
define( 'QRGEN_ABSPATH', str_replace( '\\', '/', dirname( __FILE__ ) ) );
 
define( 'QRIMAGES_DIR', 'qrimages' );

define( 'QRGEN_INC', QRGEN_ABSPATH . '/includes' );
 
define( 'QRIMAGES_URL', QRGEN_SITE .'/'. QRIMAGES_DIR );
 
define( 'QRIMAGES_PATH', QRGEN_ABSPATH .'/' .QRIMAGES_DIR );
 
	if ( !file_exists( QRIMAGES_PATH ))
			mkdir( QRIMAGES_PATH );
	
	// replace with your own random string atleast 15 characters in length
define('QRGEN_COOKIEKEY', 'put some random string here');  

define('QRGEN_HASH_SALT', 'random string here');

//( 3600 )seconds - 1 hour.
define( 'QRGEN_FORM_LIFE', 3600 ); 

 //userID will be $_SESSION['user']['userID']
define( 'USERSESSION','qrmanager' );

//the minimum required length for password
define( "MINPASSLENGTH", 6 );

// life span of an auth cookie in seconds (60*60*24*7 = 7 days)
if( !defined( 'QRGEN_COOKIE_LIFE' ) )
	define( 'QRGEN_COOKIE_LIFE', 60*60*24*7 );

/*
 * Short URL character set
 * 36: generates all lowercase keywords (ie: 13jkm)
 * 62: generates mixed case keywords (ie: 13jKm or 13JKm)
 */
define( 'URL_CHARSET', 62 );

// minimum delay in seconds before a same IP can process another qrcode data. Note: logged in users are not throttled down.
if( !defined( 'IP_FLOOD_DELAY_SECONDS' ) )
	define( 'IP_FLOOD_DELAY_SECONDS', 5 );

// comma separated list of IPs that can bypass flood check.	
if( !defined( 'IP_FLOOD_WHITELIST' ) )
	define( 'IP_FLOOD_WHITELIST', '' );	
	
define( 'NOSTATS', false );
define( 'HOURS_OFFSET', 0 ); 
