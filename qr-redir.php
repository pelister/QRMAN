<?php

require_once( dirname( __FILE__ ) . '/qr-loader.php' );

if ( '/robots.txt' == $_SERVER['REQUEST_URI'] ) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "User-agent: *\n";
	echo "Disallow:\n";
	exit;
}

$request = get_request( );
$pattern = qrgen_make_regexp_pattern( get_shorturl_charset( ) );

if( preg_match( "@^([$pattern]+)/?$@", $request, $matches ) ) {
	$keyword = isset( $matches[1] ) ? $matches[1] : '';
	
	if( $type = get_qrcode_type( $keyword )) {
		if( $type === "urls" || $type === "bookmarks" ) {
				$url = get_keyword_longurl( $type, $keyword );
				if( !empty( $url ) ) {
					// Update click count in main table
					$update_clicks = update_clicks( $keyword );
					// Update detailed log for stats
					$log_redirect = log_redirect( $keyword );
					url_redirect( $url, 301 );
					exit;
				}
		}
		elseif( find_qrtypes( $type ) ) {
			//$colid = getcol_id( $type );
			$table = $qrdb->prefix . $type;
			include( QRGEN_ABSPATH.'/index.php' );
			exit;
		}	
	}
	else {
		// URL not found. Either reserved, or page doesn't exist
		url_redirect( QRGEN_SITE . '/404.php', 404 );
		exit;
	}

}
elseif( preg_match( "/^stat_([$pattern]+)/", $request, $matches )) {
	//print_r( $matches );
	$keyword = isset( $matches[1] ) ? $matches[1] : '';
	$type = get_qrcode_type( $keyword );
	if( !$type ) {
		url_redirect( QRGEN_SITE . '/404.php', 404 );
		exit;
	}		
	$statistics = TRUE;
	include( QRGEN_ABSPATH.'/index.php' );
	exit;
}
else {
	// URL not found. Either reserved, or page doesn't exist
	url_redirect( QRGEN_SITE . '/404.php', 404 );
	exit;
}
