<?php

/* This is the QR Code Generator - Generates and stores data - based on type and action. */

require_once(  dirname( dirname( __FILE__ ) )  . '/qr-loader.php' );
require_once(  dirname( __FILE__ )   . '/qr-transact.php' );

global $qrdb;

$ip = get_IP( );
if( check_IP_flood( $ip )) {

$qrdata = array( );

	$errc = $_POST['errc'];
	$size = $_POST['size'];
	$trackable = $_POST['trackable'];

	$type = $_POST['qrtype'];
	$action = $_POST['action'];
	$nonce = $_POST['nonce'];
	
	$str = array( );
		
require_once( dirname( __FILE__ )   . '/qr-transact.php' );

	 if( qrgen_verify_nonce( $action )  && isset( $_POST['nonce'] )) {
	 
		$user = $qrdb->get_user_login( );
		
		$encoded = qrgen_encoder( $_POST, $qrdata );
	
		switch( $action ) {
		
			case 'add':
				
				$t = time();
				$id = get_next_decimal( );
				$qrdata['keyword'] = int2string( $id ); 
				$qrdata['ipaddr'] = $ip;
				$shorturl = QRGEN_SITE . '/' . $qrdata['keyword'];
				
					if( strstr( $encoded, "Blank" ) == false ) {
					
						switch( ( int )$trackable ) {
							case 1:
								$content = create_qrimage( $shorturl, $errc , $size, $user, $t, TRUE );
								$qrdata[ 'trimg' ] = $content[ 'trkimg' ];
								$qrdata[ 'qrimg' ] = '';
								$content[ 'trcstatus' ] = 1;
								break;
							case 2:
								$content = create_qrimage( $encoded, $errc , $size, $user, $t );
								$qrdata[ 'qrimg' ] = $content [ 'qrimg' ];
								$qrdata[ 'trimg' ] = '';
								$content[ 'trcstatus' ] = 2;
								break;
							case 3:
								$trcont = create_qrimage( $shorturl, $errc , $size, $user, $t, TRUE );
								$content = create_qrimage( $encoded, $errc , $size, $user, $t );
								$qrdata[ 'trimg' ] = $trcont[ 'trkimg' ];
								$qrdata[ 'qrimg' ] = $content [ 'qrimg' ];
								$content[ 'trhtml' ] = $trcont[ 'html' ];
								$content[ 'trwidth' ] = $trcont[ 'width' ];
								$content[ 'trimg' ] = $trcont[ 'trkimg' ];
								$content[ 'trcstatus' ] = 3;
								break;
						}
						
						if ( store_qrcode( $qrdata, $type, $action, $t ) ) {
								$id++;
								update_next_decimal( $id );
								$content[ 'qrtype' ] = $type;
								$content[ 'share' ] = generate_share_code( $shorturl, $type, $qrdata );
								echo json_encode( $content );  
						} else {
									
							$str['html'] = "<div id='qrcode'><div id='flood'>Failed to store into DB</div></div>";
							$str['status'] = 'success';
							echo json_encode( $str );  
						} 
					} else {
						
						$str['html'] = "<div id='qrcode'><div id='flood'>Sorry! empty fileds.</div></div>";
						$str['status'] = 'fail';
						echo json_encode( $str );  
					}
		
				break;
			
			case 'edit':
							
				$t = time();
				$qrdata[ 'keyword' ] = $_POST[ 'id' ];
				$qrdata[ 'qrimage' ] = $_POST[ 'qrimage' ];
				$qrdata[ 'trimage' ] = $_POST[ 'trimage' ];
				$shorturl = QRGEN_SITE . '/' . $qrdata['keyword'];
					
				switch( ( int )$trackable ) {
					case 1:
							if( $imgfile = parse_qrimage( $qrdata[ 'trimage' ] ) )
								if ( file_exists( $imgfile )) 
									unlink( $imgfile );
							$content = create_qrimage( $shorturl, $errc , $size, $user, $t, TRUE );
							$qrdata[ 'trimg' ] = $content[ 'trkimg' ];
							$qrdata[ 'qrimg' ] = $qrdata[ 'qrimage' ];
							$content[ 'trcstatus' ] = 1;
							break;
					case 2:
							if( $imgfile = parse_qrimage( $qrdata[ 'qrimage' ] ) )
								if ( file_exists( $imgfile )) 
									unlink( $imgfile );	
							$content = create_qrimage( $encoded, $errc , $size, $user, $t );
								$qrdata[ 'qrimg' ] = $content [ 'qrimg' ];
								$qrdata[ 'trimg' ] = $qrdata[ 'trimage' ];
								$content[ 'trcstatus' ] = 2;	
							break;
					case 3:
							if( $imgfile = parse_qrimage( $qrdata[ 'trimage' ] ) )
								if ( file_exists( $imgfile )) 
									unlink( $imgfile );	
							if( $imgfile = parse_qrimage( $qrdata[ 'qrimage' ] ) )
								if ( file_exists( $imgfile )) 
									unlink( $imgfile );	
							$trcont = create_qrimage( $shorturl, $errc , $size, $user, $t, TRUE );		
							$content = create_qrimage( $encoded, $errc , $size, $user, $t );
							$qrdata[ 'trimg' ] = $trcont[ 'trkimg' ];
							$qrdata[ 'qrimg' ] = $content [ 'qrimg' ];
							$content[ 'trhtml' ] = $trcont[ 'html' ];
							$content[ 'trwidth' ] = $trcont[ 'width' ];
							$content[ 'trimg' ] = $trcont[ 'trkimg' ];
							$content[ 'trcstatus' ] = 3;
							break;
				}
				
				$content[ 'qrtype' ] = $type;
				if ( store_qrcode( $qrdata, $type, $action, $t )) 
					$content[ 'okmsg' ] = 'saved successfully' ;
				else	
					$content[ 'okmsg' ] = 'ERRQR: There is a problem with updating the '. $type ;
					echo json_encode( $content ); 
				
				break;
				
		}

	} else {
		$str['html'] = "<div id='qrcode'><div id='flood'> Form Expired </div></div>";
		$str['status'] = 'success';
		echo json_encode( $str );
	}  
} else {
						$str['html'] = "<div id='qrcode'><div id='flood'>Slow down partner! give me some time to process</div></div>";
						$str['status'] = 'fail';
						echo json_encode( $str );  				
}


