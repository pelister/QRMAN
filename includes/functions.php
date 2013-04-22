<?php

function get_request( ) {
	
	// Ignore protocol & www. prefix
	$root = str_replace( array( 'https://', 'http://', 'https://www.', 'http://www.' ), '', QRGEN_SITE );
	// Case insensitive comparison of the QRGEN_SITE root to match both http://Qrg.en/foo and http://Qrg.en/foo
	$request = preg_replace( "!$root/!i", '', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 1 );

	// Unless request looks like a full URL (ie request is a simple keyword) strip query string
	if( !preg_match( "@^[a-zA-Z]+://.+@", $request ) ) 
		$request = current( explode( '?', $request ) );

	return $request ;
}

function qrgen_make_regexp_pattern( $string ) {
	$pattern = preg_quote( $string, '-' ); // add - as an escaped characters -- this is fixed in PHP 5.3
	return $pattern;
}

function get_shorturl_charset( ) {
	static $charset = null;
	if( $charset !== null )
		return $charset;
		
	if( !defined( 'URL_CHARSET' ) ) {
		$charset = '0123456789abcdefghijklmnopqrstuvwxyz';
	} else {
		switch( URL_CHARSET ) {
			case 36:
				$charset = '0123456789abcdefghijklmnopqrstuvwxyz';
				break;
			case 62:
			case 64: //failsafe, because some get it wrong in their qr-config.php
				$charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
		}
	}
	
	return $charset;
}

function sanitize_string( $string ) {
	// make a regexp pattern with the shorturl charset, and remove everything but this
	$pattern = qrgen_make_regexp_pattern( get_shorturl_charset( ) );
	$valid = substr( preg_replace( '![^'.$pattern.']!', '', $string ), 0, 199 );
	
	return $valid;
}

// Sanitize a page title. No HTML per W3C http://www.w3.org/TR/html401/struct/global.html#h-7.4.2
function sanitize_title( $title ) {
	$title = strip_tags( $title );
	// Remove extra white space
	$title = preg_replace( "/\s+/", ' ', trim( $title ) );
	return $title;
}

// A few sanity checks on the URL
function sanitize_url( $unsafe_url ) {
	// make sure there's only one 'http://' at the beginning (prevents pasting a URL right after the default 'http://')
	return  qrgen_esc_url( $unsafe_url, 'redirection' );	
}

function qrgen_esc_url( $url, $context = 'display', $protocols = array() ) {
	// make sure there's only one 'http://' at the beginning (prevents pasting a URL right after the default 'http://')
	$url = str_replace( 
	array( 'http://http://', 'http://https://' ),
	array( 'http://',        'https://'        ),
	$url
	);
	
	if ( '' == $url )
	return $url;
	
	// make sure there's a protocol, add http:// if not
	if ( ! qrgen_get_protocol( $url ) )
		$url = 'http://'.$url;
	
	// force scheme and domain to lowercase - see issue 591
	preg_match( '!^([a-zA-Z]+://([^/]+))(.*)$!', $url, $matches );
	if( isset( $matches[1] ) && isset( $matches[3] ) )
		$url = strtolower( $matches[1] ) . $matches[3];
	
	$original_url = $url;
	
	$url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url );
	
	// TODO: check if that was it too destructive
	$strip = array( '%0d', '%0a', '%0D', '%0A' );
	$url = deep_replace( $strip, $url );
	$url = str_replace( ';//', '://', $url );
			
	// Replace ampersands and single quotes only when displaying.
	if ( 'display' == $context ) {
		$url = qrgen_kses_normalize_entities( $url );
		$url = str_replace( '&amp;', '&#038;', $url );
		$url = str_replace( "'", '&#039;', $url );
	}
		
	if ( ! is_array( $protocols ) or ! $protocols ) {
		global $qrgen_allowedprotocols;
		$protocols =  $qrgen_allowedprotocols;
		// Note: $qrgen_allowedprotocols is also globally filterable in functions-kses.php/qrgen_kses_init()
	}

	if ( !qrgen_is_allowed_protocol( $url, $protocols ) )
		return '';
	
	$url = substr( $url, 0, 1999 );
					
	return $url;
}

function qrgen_get_protocol( $url ) {
	preg_match( '!^[a-zA-Z0-9\+\.-]+:(//)?!', $url, $matches );
	$protocol = ( isset( $matches[0] ) ? $matches[0] : '' );
	return $protocol;
}

function qrgen_is_allowed_protocol( $url, $protocols = array() ) {
	
	if( ! $protocols ) {
		global $qrgen_allowedprotocols;
		$protocols = $qrgen_allowedprotocols;
	}
	
	$protocol = qrgen_get_protocol( $url );
	return  in_array( $protocol, $protocols );
}

function sanitize_user( $username, $strict = false ) {
	$raw_username = $username;
	$username = strip_all_tags( $username );
	// Kill octets
	$username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
	$username = preg_replace( '/&.+?;/', '', $username ); // Kill entities
	
	// If strict, reduce to ASCII for max portability.
	if ( $strict )
	$username = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $username );
	
	$username = trim( $username );
	// Consolidate contiguous whitespace
	$username = preg_replace( '|\s+|', ' ', $username );
	
	return $username;
}

function strip_all_tags($string, $remove_breaks = false) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
	$string = strip_tags( $string );
	
	if ( $remove_breaks )
	$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
	
	return trim( $string );
}

function check_keyword( $keyword ) {
	global $qrdb;
	$keyword = $qrdb->escape( $keyword );
	$sql = "SELECT * from `" . $qrdb->prefix . "shorturl` WHERE BINARY `keyword` = '$keyword' LIMIT 1";
	if ( $result = $qrdb->get_row( $sql, ARRAY_A )) 
		return $result;
	else
		return false;
}

function has_access( $user, $res ) {
	global $qrdb;
	$sql = "SELECT * from `" . $qrdb->prefix . $res['type'] . "` WHERE uid=" . $user['userID'] . " and BINARY `keyword`='" . $res['keyword'] . "' LIMIT 1";
	if ( $result = $qrdb->get_row( $sql, ARRAY_A )) 
		return true;
	else
		return false;
}

function get_qrcode_type( $keyword ) {
	global $qrdb;
	$keyword = sanitize_string( $keyword );
	$sql = "SELECT type FROM `". $qrdb->prefix . "shorturl` WHERE BINARY `keyword` = '$keyword' LIMIT 1";
	if ( $result = $qrdb->get_row( $sql )) 
		return $result->type;
	else
		return false;
}
	
function get_keyword_longurl( $type, $keyword ) {
	global $qrdb;
	
	$keyword = sanitize_string( $keyword );
	$table = $qrdb->prefix . $type;
	if ( $result = $qrdb->get_var( "SELECT url FROM `$table` WHERE BINARY `keyword` = '$keyword'" )) 
		return $result;
	else
		return false;
}

function get_keyword_clicks( $keyword, $notfound = false ) {
	return get_keyword_info( $keyword, 'clicks', $notfound );
}

function get_keyword_timestamp( $keyword, $notfound = false ) {
	return get_keyword_info( $keyword, 'timestamp', $notfound );
}
	
function get_keyword_info( $keyword, $field, $notfound = false ) {

	$keyword = sanitize_string( $keyword );
	$infos = get_keyword_infos( $keyword );
	
	$return = $notfound;
	if ( isset( $infos[ $field ] ) && $infos[ $field ] !== false )
		$return = $infos[ $field ];

	return $return;	
}

function get_keyword_infos( $keyword, $use_cache = true ) {
	global $qrdb;
	$keyword = sanitize_string( $keyword );

	if( isset( $db->infos[ $keyword ] ) && $use_cache == true ) {
		return $db->infos[ $keyword ];
	}
		
	$table = $qrdb->prefix . 'shorturl';
	$infos = $qrdb->get_row( "SELECT * FROM `$table` WHERE BINARY `keyword` = '$keyword'" );
	if( $infos ) {
		$infos = (array)$infos;
		$db->infos[ $keyword ] = $infos;
	} else {
		$db->infos[ $keyword ] = false;
	}
		
	return $db->infos[ $keyword ];
}


function update_clicks( $keyword, $clicks = false ) {
	
	global $qrdb;
	$keyword = sanitize_string( $keyword );
	$table = $qrdb->prefix .'shorturl';
	if ( $clicks !== false && is_int( $clicks ) && $clicks >= 0 )
		$update = $qrdb->query( "UPDATE `$table` SET `clicks` = $clicks WHERE BINARY `keyword` = '$keyword'" );
	else
		$update = $qrdb->query( "UPDATE `$table` SET `clicks` = clicks + 1 WHERE BINARY `keyword` = '$keyword'" );
	
	return $update;
}	

function log_redirect( $keyword ) {
	
	global $qrdb;
	$table = $qrdb->prefix . 'log';
	
	$keyword = sanitize_string( $keyword );
	$referrer = ( isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_url( $_SERVER['HTTP_REFERER'] ) : 'direct' );
	$ua = get_user_agent( );
	$ip = get_IP( );
	$location = geo_ip_to_countrycode( $ip );
	
	return $qrdb->query( "INSERT INTO `$table` (click_time, keyword, referrer, user_agent, ip_address, country_code) VALUES (NOW(), '$keyword', '$referrer', '$ua', '$ip', '$location')" );
}

function url_redirect( $location, $code = 301 ) {

	// Redirect, either properly if possible, or via Javascript otherwise 
	if( !headers_sent( ) ) {
		status_header( $code );
		header( "Location: $location" );
	} else {
		redirect_javascript( $location );
	}
	die( );
}

function status_header( $code = 200 ) {
	if( headers_sent() )
		return;
		
	$protocol = $_SERVER["SERVER_PROTOCOL"];
	if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
		$protocol = 'HTTP/1.0';

	$code = intval( $code );
	$desc = get_HTTP_status( $code );

	@header ("$protocol $code $desc"); // This causes problems on IIS and some FastCGI setups
}

function get_HTTP_status( $code ) {
	$code = intval( $code );
	$headers_desc = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		226 => 'IM Used',

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Reserved',
		307 => 'Temporary Redirect',

		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		510 => 'Not Extended'
	);

	if ( isset( $headers_desc[$code] ) )
		return $headers_desc[$code];
	else
		return '';
}

function redirect_javascript( $location, $dontwait = true ) {
	
	if( $dontwait ) {
		echo <<<REDIR
		<script type="text/javascript">
		window.location="$location";
		</script>
		<small>(if you are not redirected after 10 seconds, please <a href="$location">click here</a>)</small>
REDIR;
	} else {
		echo <<<MANUAL
		<p>Please <a href="$location">click here</a></p>
MANUAL;
	}
	
}

function geo_ip_to_countrycode( $ip = '', $default = '' ) {

	if ( !file_exists ( dirname( __FILE__ ) . '/geo/GeoIP.dat' ) || !file_exists( dirname( __FILE__ ) . '/geo/geoip.inc' ) )
		return $default;

	if ( $ip == '' )
		$ip = get_IP( );
	
	require_once( dirname( __FILE__ ) . '/geo/geoip.inc' ) ;
	$gi = geoip_open( dirname( __FILE__ ) . '/geo/GeoIP.dat' , GEOIP_STANDARD );
	$location = geoip_country_code_by_addr( $gi, $ip );
	geoip_close($gi);

	return $location;
}


function geo_get_flag( $code ) {
	if( file_exists( dirname( __FILE__ ) . '/geo/flags/flag_' . strtolower( $code ).'.gif' ) ) {
		$img = QRGEN_SITE . '/includes/geo/flags/flag_' . ( strtolower( $code ) ) . '.gif' ;
	} else {
		$img = false;
	}
	return $img;
}

function geo_countrycode_to_countryname( $code ) {
	// Load the Geo class if not already done
	if( !class_exists( 'GeoIP' ) ) {
		$temp = geo_ip_to_countrycode( '127.0.0.1' );
	}
	
	if( class_exists( 'GeoIP' ) ) {
		$geo  = new GeoIP;
		$id   = $geo->GEOIP_COUNTRY_CODE_TO_NUMBER[ strtoupper( $code ) ];
		$long = $geo->GEOIP_COUNTRY_NAMES[ $id ];
		return $long;
	} else {
		return false;
	}
}

function get_favicon_url( $url ) {
	return 'http://www.google.com/s2/u/0/favicons?domain=' . get_domain( $url, false ) ;
}

function build_html_link( $href, $title = '', $element = '' ) {
	if( !$title )
	$title = $href;
	if( $element )
	$element = "id='$element'";
	return  "<a href='$href' $element>$title</a>" ;
}

function get_option( $option_name, $default = false ) {
	global $qrdb;
	$qrdb->hide_errors();
	if ( !isset( $qrdb->option[ $option_name ] ) ) {
		$table = $qrdb->prefix . 'options';
		$option_name = $qrdb->escape( $option_name );
		$row = $qrdb->get_row( "SELECT `option_value` FROM `$table` WHERE `option_name` = '$option_name' LIMIT 1" );
		if ( is_object( $row) ) { 
			$value = $row->option_value;
		} else { 
			$value = $default;
		}
		$qrdb->option[ $option_name ] = maybe_unserialize( $value );
		
	}
	return $qrdb->option[ $option_name ] ;
}

function form_option( $option ) {
	global $qrdb;
	echo $qrdb->escape( get_option( $option ) );
}

function is_serialized( $data ) {
	// if it isn't a string, it isn't serialized
	if ( !is_string( $data ) )
		return false;
	$data = trim( $data );
	if ( 'N;' == $data )
		return true;
	if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
		return false;
	switch ( $badions[1] ) {
		case 'a' :
		case 'O' :
		case 's' :
			if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
				return true;
			break;
		case 'b' :
		case 'i' :
		case 'd' :
			if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
				return true;
			break;
	}
	return false;
}

function maybe_serialize( $data ) {
	if ( is_array( $data ) || is_object( $data ) )
		return serialize( $data );

	if ( is_serialized( $data ) )
		return serialize( $data );

	return $data;
}

function maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) 
		return @unserialize( $original );
	return $original;
}

function get_remote_title( $url ) {
	
	$url = sanitize_url( $url );
	
	$title = $charset = false;

	$content = get_remote_content( $url );
	
	// If false, return url as title.
	if( false === $content )
		return $url;

	if( $content !== false ) {
		// look for <title>
		if ( preg_match('/<title>(.*?)<\/title>/is', $content, $found ) ) {
			$title = $found[1];
			unset( $found );
		}

		// look for charset
		// <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		if ( preg_match('/<meta[^>]*?charset=([^>]*?)\/?>/is', $content, $found ) ) {
			$charset = trim($found[1], '"\' ');
			unset( $found );
		}
	}
	
	// if title not found, guess if returned content was actually an error message
	if( $title == false && strpos( $content, 'Error' ) === 0 ) {
		$title = $content;
	}
	
	if( $title == false )
		$title = $url;
	
	// Charset conversion. We use @ to remove warnings (mb_ functions are easily bitching about illegal chars)
	if( function_exists( 'mb_convert_encoding' ) ) {
		if( $charset ) {
			$title = @mb_convert_encoding( $title, 'UTF-8', $charset );
		} else {
			$title = @mb_convert_encoding( $title, 'UTF-8' );
		}
	}
	
	// Remove HTML entities
	$title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
	
	// Strip out evil things
	$title = sanitize_title( $title );
	
	 return $title;
}

// Function to filter all invalid characters from a URL. 
function clean_url( $url ) {
	$url = preg_replace( '|[^a-z0-9-~+_.?\[\]\^#=!&;,/:%@$\|*`\'<>"()\\x80-\\xff\{\}]|i', '', $url );
	$strip = array( '%0d', '%0a', '%0D', '%0A' );
	$url = deep_replace( $strip, $url );
	$url = str_replace( ';//', '://', $url );
	$url = str_replace( '&amp;', '&', $url ); // Revert & not to break query strings
	
	return $url;
}

// Perform a replacement while a string is found, eg $subject = '%0%0%0DDD', $search ='%0D' -> $result =''
function deep_replace( $search, $subject ) {
	$found = true;
	while($found) {
		$found = false;
		foreach( (array) $search as $val ) {
			while( strpos( $subject, $val ) !== false ) {
				$found = true;
				$subject = str_replace( $val, '', $subject );
			}
		}
	}
	
	return $subject;
}

function get_IP( ) {
	// Precedence: if set, X-Forwarded-For > HTTP_X_FORWARDED_FOR > HTTP_CLIENT_IP > HTTP_VIA > REMOTE_ADDR
	$headers = array( 'X-Forwarded-For', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_VIA', 'REMOTE_ADDR' );
	foreach( $headers as $header ) {
		if ( !empty( $_SERVER[ $header ] ) ) {
			$ip = $_SERVER[ $header ];
			break;
		}
	}
	
	// headers can contain multiple IPs (X-Forwarded-For = client, proxy1, proxy2). Take first one.
	if ( strpos( $ip, ',' ) !== false )
		$ip = substr( $ip, 0, strpos( $ip, ',' ) );
	
	return sanitize_ip( $ip );
}


function sanitize_ip( $ip ) {
	return preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
}

function get_next_decimal( ) {
	return ( int )get_option( 'next_id' );
}

function update_next_decimal( $int = '' ) {
	
	$int = ( $int == '' ) ? get_next_decimal( ) + 1 : ( int )$int ;
	$update = update_option( 'next_id', $int );
	return $update;
}

function is_qrgen_installed( ) {
	return get_option( 'siteurl' );
}

function check_tables() {
	global $qrdb;
	$qr_tables = array( 'address', 'bookmarks', 'emails', 'emsg', 'geo', 'log', 'mecard', 'options', 'shorturl', 'sms', 'telephones', 'texts',
								  'urls', 'user', 'vcard' );
		$found = false;						  
		
		if( defined( 'QRGEN_DB_PREFIX' )) {
			foreach ( $qr_tables as $table ) {
				$table = QRGEN_DB_PREFIX . $table;
				
				$qrdb->hide_errors();
				if ( $qrdb->get_results( "DESCRIBE $table;" )) {
					$found = true;
					continue;
				}
				$found = false;
				$error = 'One or more database tables are unavailable. You may need to repair the database before proceeding' ;
				qrgen_die( $error );		
			}
		}
		return $found;		
}

function get_user_agent( ) {
	if ( !isset( $_SERVER['HTTP_USER_AGENT'] ) )
		return '-';
	
	//echo $_SERVER['HTTP_USER_AGENT'] . "<br />";
	$ua = strip_tags( html_entity_decode( $_SERVER['HTTP_USER_AGENT'] ));
	$ua = preg_replace('![^0-9a-zA-Z\':., /{}\(\)\[\]\+@&\!\?;_\-=~\*\#]!', '', $ua );
		
	return substr( $ua, 0, 254 );
}

function check_IP_flood( $ip = '' ) {

	if(	( defined( 'IP_FLOOD_DELAY_SECONDS' ) && IP_FLOOD_DELAY_SECONDS === 0 ) ||	!defined( 'IP_FLOOD_DELAY_SECONDS' )	)
			return true;

	$ip = ( $ip ? sanitize_ip( $ip ) : get_IP( ) );

	// Don't throttle whitelist IPs
	if( defined( 'IP_FLOOD_WHITELIST' ) && IP_FLOOD_WHITELIST ) {
		$whitelist_ips = explode( ',', IP_FLOOD_WHITELIST );
		foreach( (array)$whitelist_ips as $whitelist_ip ) {
			$whitelist_ip = trim( $whitelist_ip );
			if ( $whitelist_ip == $ip )
				return true;
		}
	}
	
	global $qrdb;
	// Don't throttle logged in users
	/*if( $qrdb->is_logged_in( ) )
				return true;
	*/
	
	$table = $qrdb->prefix . 'shorturl';
	//echo $table . " " . $ip;
	$lasttime = $qrdb->get_var( "SELECT `timestamp` FROM $table WHERE `ip` = '$ip' ORDER BY `timestamp` DESC LIMIT 1" );
	if( $lasttime ) {
		$now = date( 'U' );
		$then = date( 'U', strtotime( $lasttime ) );
		if( ( $now - $then ) <= IP_FLOOD_DELAY_SECONDS ) {
			// Flood!
			return false;
		}
	}
	
	return true;
}

function update_option( $option_name, $newvalue ) {
	global $qrdb;
	$table = $qrdb->prefix .'options';

	$safe_option_name = $qrdb->escape( $option_name );

	$oldvalue = get_option( $safe_option_name );

	// If the new and old values are the same, no need to update.
	if ( $newvalue === $oldvalue )
		return false;

	if ( false === $oldvalue ) {
		add_option( $option_name, $newvalue );
		return true;
	}

	$_newvalue = $qrdb->escape( maybe_serialize( $newvalue ) );
	
	$qrdb->query( "UPDATE `$table` SET `option_value` = '$_newvalue' WHERE `option_name` = '$option_name'" );

	if ( $qrdb->rows_affected == 1 ) {
		$qrdb->option[ $option_name ] = $newvalue;
		return true;
	}
	return false;
}

function add_option( $name, $value = '' ) {
	global $qrdb;
	$table = $qrdb->prefix . 'options';
	$safe_name = $qrdb->escape( $name );

	// Make sure the option doesn't already exist
	if ( false !== get_option( $safe_name ) )
		return;

	$_value = $qrdb->escape( maybe_serialize( $value ) );


	$qrdb->query( "INSERT INTO `$table` (`option_name`, `option_value`) VALUES ('$name', '$_value')" );
	$qrdb->option[ $name ] = $value;
	return;
}

function int2string( $num, $chars = null ) {
	if( $chars == null )
		$chars = get_shorturl_charset( );
	$string = '';
	$len = strlen( $chars );
	while( $num >= $len ) {
		$mod = bcmod( $num, $len );
		$num = bcdiv( $num, $len );
		$string = $chars[ $mod ] . $string;
	}
	$string = $chars[ (( int ) $num ) ] . $string;
	
	return  $string;
}

function find_qrtypes( $find ) {
	$qrtypes = array( 'emails', 'emsg', 'mecard', 'sms', 'telephones', 'texts', 'vcard', 'geo' );
	return in_array( $find, $qrtypes );
}

function qrtypes_api( $find ) {
	$qrtypes = array( 'emails', 'emsg', 'mecard', 'sms', 'telephones', 'texts', 'vcard', 'bookmarks', 'urls', 'geo' );
	return in_array( $find, $qrtypes );
}

function login_form() {
		global $qrdb;
		$str = '<div id="qrform">';
		$str .= $qrdb->show_login_form( "index.php" );
		$str .= '</div>';
		echo $str;
}

function generate_share_code( $shorturl, $type, $qrdata = array() ) {
	
	if( $type === 'bookmarks' )
		$title = !empty( $qrdata['title'] ) ? $qrdata['title'] : $type;
	else
		$title = $type;
		
	$link = $shorturl;
	$_link = rawurlencode ( $link );
	$share = rawurlencode ( $title . ' ' . $link );
	
	$arrButtonsCode[] = '<div class="socialbuttons sb-button-googleplus"><!-- Google Plus One--><div class="g-plusone" data-size="medium" data-href="'.$link.'"></div></div>';
	//$arrButtonsCode[] = '<div class="socialbuttons sb-button-fblike"><!-- Facebook like--><div id="fb-root"></div><div class="fb-like" data-href="'.$link.'" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false"></div></div>';
	$arrButtonsCode[] = '<div class="socialbuttons sb-button-twitter"><!-- Twitter--><a href="https://twitter.com/share" class="twitter-share-button" data-url="' . $link . '" data-text="' . $title . '"  rel="nofollow"></a></div>';
	$arrButtonsCode[] = '<div class="socialbuttons sb-button-fblike"><!-- Facebook like--><div id="fb-root"></div><div class="fb-like" data-href="' . $link . '" data-layout="button_count" data-send="false" data-show-faces="false" data-width="90"></div></div>';	
	
	$sb_buttonscode = '<div class="ssbuttons">'."\n";
	$sb_buttonscode .= '<p><h3>Share</h3></p>
	<span class="txtarea">
	<textarea class="qrtxt" rows="5" cols="50">' . $title . ' ' . $link . '</textarea></span><br />' . "\n";
	$sb_buttonscode .= implode("\n", $arrButtonsCode) . "\n";
	
	$sb_buttonscode .= '
	<script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script>
	<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
	<script type="text/javascript">
	if ($(".fb-like").length > 0) {
		if (typeof (FB) != "undefined") {
			FB.XFBML.parse();
		} else {
			$.getScript("http://connect.facebook.net/en_US/all.js#xfbml=1", function () {
			FB.init({ status: true, cookie: true, xfbml: true });
			});
		}
	}
	</script>';
	
	$sb_buttonscode .= '</div>'."\n";
	
	return $sb_buttonscode;
}