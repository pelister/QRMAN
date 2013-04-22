<?php 
require_once(  dirname( __FILE__ )  . '/qr-loader.php' );	
require_once(  dirname( __FILE__ ) . '/includes/auth.php');

$html = '';
if( isset( $statistics ) && $statistics == TRUE && $qrdb->is_logged_in ( )) {
	$keyword = sanitize_string( $keyword );
	$html['html'] = display_statistics( $keyword, $type );
	qrgen_html_head( '', TRUE );
	//echo $html ;
	//exit;
}	
else
	qrgen_html_head( );
	qrgen_html_logo( );


if( isset( $keyword ) && isset( $table )) {

		if( $type === 'mecard' )
			$sql = "SELECT  b.firstname, b.lastname, b.wphno, b.email, b.url, b.bday, b.note, b.wstreet, b.wcity, b.wstate, b.wzip, b.wcountry, a.keyword, a.qrimage, a.trimage, a.created FROM $table a, ". $qrdb->prefix . "address b WHERE BINARY a.keyword = b.keyword and BINARY a.keyword='$keyword' LIMIT 1"; 
		elseif( $type === 'vcard' )
			$sql = "SELECT b.*,  a.keyword, a.qrimage, a.trimage, a.created FROM $table a, ". $qrdb->prefix . "address b WHERE BINARY a.keyword = b.keyword and BINARY a.keyword='$keyword' LIMIT 1"; 
		else
			$sql = "SELECT * from $table WHERE BINARY `keyword`='$keyword' LIMIT 1";
			
		$res = $qrdb->get_row( $sql, ARRAY_A );
		//$qrdb->debug();
		$update_clicks = update_clicks( $keyword );
		$log_redirect = log_redirect( $keyword );
		$html = html_type( $res, $type );
}
	if ( $qrdb->is_logged_in ( ))
		qrgen_html_interface( true, $html ) ;
	elseif( isset( $html ))
		qrgen_html_interface( false, $html );
	else 
		qrgen_html_interface( false );
		
		qrgen_show_content( );
		qrgen_footer( );
		
echo $msg;		
		

//$qrdb->show_session_data( );



