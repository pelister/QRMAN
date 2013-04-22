<?php
define( 'QRGEN_ADMIN', true );
require_once( dirname( dirname( __FILE__ )) . '/qr-loader.php' );	
require_once(  QRGEN_INC . '/auth.php');

if( !$qrdb->is_admin()) {
	qrgen_add_funcs( 'before_die', 'login_form' );
	qrgen_die( "You must be an Administrator to view this page" );
}

	qrgen_html_head( "Admin - Shorturl and QR Code Generator" );
	qrgen_html_logo( );

	qrgen_html_interface( true ) ;
	
	qrgen_show_content( );
	qrgen_footer( );
		
echo $msg;		
		
