<?php

function qrgen_api( $query ) {
 
$action =  isset( $query['action'] ) ? $query['action'] : null ;
$api_key =  isset( $query['apk'] ) ?  $query['apk'] : null ; 
$format = isset( $query['format'] ) ? $query['format'] : 'xml';
$errc = !empty( $query['ec'] ) ? $query['ec'] : 'M';
$size = !empty( $query['size'] ) ? $query['size'] : 160;

	switch( $action ) {
		
		case 'qr':
			$url = QRGEN_SITE;
			$size = strtolower( $size ); // possible uppercase 'X' in string
			
			if( strstr( $size, 'x' )) {
					$tmp = explode( 'x', $size );
					$size = $tmp[0];
			}
			
			$qrsiz = ( $size < 200 ) ? 3 : 10;
			$data = !empty( $query['data'] ) ? $query['data'] : $url;
			$t = time();
			
			//create qr image
			$arr = create_qrimage( $data, $errc , $qrsiz, 'api', $t );
	
			$seconds_to_cache = 86400;
			$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
			header("Expires: $ts");
			header("Pragma: cache"); 
			header( "Cache-Control: public, max-age=86400" );
			header( "x-content-type-options: nosniff" );
			header( "x-frame-options: ALLOWALL");
			header( "x-xss-protection: 1; mode=block");
			
			$ajax = array_key_exists('ajax', $query) && $query['ajax'] == 'ajax';
									
			if( $arr['width'] != $size ) {
				
				$orig = imagecreatefrompng( $arr['qrpath'] );
				$copy = imagecreatetruecolor( $size, $size ); 
				imagecopyresized( $copy, $orig, 0, 0, 0, 0, $size, $size, $arr['width'], $arr['width'] );
				imagepng( $copy, 'rsizqrcode.png' );
				
				$filesiz = filesize( 'rsizqrcode.png' );
				header( "Content-Length: $filesiz" );
				header( "Content-Type: image/png" );
				header("Content-Disposition: inline; filename=qrcode.png");							
				if ( $ajax ) {
					//header_remove( "Content-Length: $filesiz" );
					$img =  base64_encode( file_get_contents( 'rsizqrcode.png' ) );
					header( "Content-Length: " . strlen( $img ));
					echo $img;
				}
				else 					
					readfile( 'rsizqrcode.png' );
				
			} else {
				
				$filesiz = filesize( $arr['qrpath'] );
				header( "Content-Length: $filesiz" );
				header( "Content-Type: image/png" );
				header( "Content-Disposition: inline; filename=qrcode.png" );
				if ( $ajax ) {
					//header_remove( "Content-Length: $filesiz" );
					$img =  base64_encode( file_get_contents( 'rsizqrcode.png' ) );
					header( "Content-Length: " . strlen( $img ));
					echo $img;
				}
				else 
					readfile( $arr['qrpath'] );
			}
		break;
			
		case 'save':
			
			
				$type = !empty( $query['qt'] ) ? $query['qt'] : ' ';
				$tr = empty( $query['tr'] ) ? 1 : $query['tr'];
	
				$type = trim( $type );
							
				$ip = get_IP( );
				if( !check_IP_flood( $ip )) {
					$output['error'] = "Warning: " . QRGEN_SITE . " Trying to flood the system. Slow down." ;					
					qrgen_api_output( $format, $output );
					exit;
				}
				if( strlen( $api_key ) == 0 ) {
					$output['error'] = "Error: No Api Key - Get a valid api key from "  . QRGEN_SITE ;					
					qrgen_api_output( $format, $output );
					exit;
				} 
				elseif(( $user = api_user( $api_key )) == false ) {
					$output['error'] = "Error: API Key Not Found - You must provide a valid api key obtained from "  . QRGEN_SITE ;					
					qrgen_api_output( $format, $output );
					exit;
				}
				elseif( !qrtypes_api( $type )) {
					$output['error'] = "Error: Not a Valid QR Code Type. ";					
					qrgen_api_output( $format, $output );
					exit;
				}
				else {
					$qrsiz = 7;
					$query['qrtype'] = $query['qt'];
					$data = qrgen_encoder( $query, $qrdata );
					$t = time();
					$id = get_next_decimal( );
					$qrdata['keyword'] = int2string( $id ); 
					$qrdata['ipaddr'] = $ip;
					$shorturl = QRGEN_SITE . '/' . $qrdata['keyword'];
		
					$username = $user['user_login'];
					$userid = $user['userID'];
		
					switch( (int)$tr ) {
			
						case 1:
							$content = create_qrimage( $shorturl, $errc , $qrsiz, $username, $t, TRUE );
							$qrdata[ 'trimg' ] = $content[ 'trkimg' ];
							$content[ 'trimg' ] = $content[ 'trkimg' ];
							$qrdata[ 'qrimg' ] = '';
							$content[ 'trcstatus' ] = 1;
							break;
						case 2:
							$content = create_qrimage( $data, $errc , $qrsiz, $username, $t );
							$qrdata[ 'qrimg' ] = $content [ 'qrimg' ];
							$qrdata[ 'trimg' ] = '';
							$content[ 'trcstatus' ] = 2;
							break;
						case 3:
							$trcont = create_qrimage( $shorturl, $errc , $qrsiz, $username, $t, TRUE );
							$content = create_qrimage( $data, $errc , $qrsiz, $username, $t );
							$qrdata[ 'trimg' ] = $trcont[ 'trkimg' ];
							$qrdata[ 'qrimg' ] = $content [ 'qrimg' ];							
							$content[ 'trwidth' ] = $trcont[ 'width' ];
							$content[ 'trimg' ] = $trcont[ 'trkimg' ];
							$content[ 'trcstatus' ] = 3;
							break;
					}
					$action = "add";
					if ( store_qrcode( $qrdata, $type, $action, $t, $userid ) ) {
						$id++;
						update_next_decimal( $id );
						$content[ 'qrtype' ] = $type;
						$content[ 'shorturl' ] = $shorturl;
			
						unset( $content['html'] );
						unset( $content['trkpath'] );
						unset( $content['qrpath'] );
						unset( $content['trkimg'] );
						
						if( isset( $query['callback'] ) && $format === 'jsonp' )
							$content['callback'] = $query['callback'];
			
						qrgen_api_output( $format, $content );
				
					} else {	
						$output['error'] = "Error: Failed to store into DB at " . QRGEN_SITE;
						qrgen_api_output( $format, $output );
						exit;
					} 
		
				}
		break;
		
		case 'stat':
			
			if( isset( $query['callback'] ) && $format === 'jsonp' )
				$output['callback'] = $query['callback'];
				
			$keyword = isset( $query['kw'] ) ? $query['kw'] : '';
			
			if( $keyword )
					$keyword = str_replace( QRGEN_SITE . '/' , '', $keyword );
			else {
				$output['error'] = "Error: Keyword not provided " ;
				qrgen_api_output( $format, $output );
				exit;
			}
				
			if( strlen( $api_key ) == 0 ) {
				$output['error'] = "Error: No Api Key - Get a valid api key from "  . QRGEN_SITE ;
				qrgen_api_output( $format, $output );
				exit;
			} 
			elseif(( $user = api_user( $api_key )) == false ) {
				$output['error'] = "Error: API Key Not Found - You must provide a valid api key obtained from "  . QRGEN_SITE ;
				qrgen_api_output( $format, $output );
				exit;
			}
			elseif(( $res = check_keyword( $keyword )) == false ) {
				$output['error'] = "Error: Keyword not fount at " . QRGEN_SITE;
				qrgen_api_output( $format, $output );
				exit;
			}
			elseif( !has_access( $user, $res )) {
				$output['error'] = "Error: You do not have access to resources created by other users";
				qrgen_api_output( $format, $output );
				exit;	
			} else {
				$res['shorturl'] = QRGEN_SITE . '/' . $keyword;
				if( $countries = get_countries_visited( $keyword ))
					$res['countries'] = $countries;
				
				if( isset( $query['callback'] ) && $format === 'jsonp' )
					$res['callback'] = $query['callback'];
				qrgen_api_output( $format, $res );
				exit;
			}
			break;
					
	}

}

function api_user( $api_key ) {
	
	global $qrdb;
	$sql = "SELECT userID, user_login from " . $qrdb->prefix . "user where api_key='" . $qrdb->escape( $api_key ) . "' LIMIT 1";
	$res = $qrdb->get_row( $sql, ARRAY_A );
	
	if( $qrdb->num_rows > 0 ) 
		return $res;
	else
		return false;
}

function qrgen_api_output( $format, $output ) {
		
		switch( $format ) {
				case 'json':
					header( 'Content-type: application/json' );
					echo json_encode( $output );
					break;
				
				case 'jsonp':
					header( 'Content-type: application/javascript' );
					echo $output['callback'] . '(' . json_encode( $output ) . ')';
					break;
					
				case 'xml':
					header( 'Content-type: application/xml' );
					echo qrgen_xml_encode( $output );
					break;
		}
}
	
function qrgen_xml_encode( $array ) {
	require_once( dirname( __FILE__ ) . '/xml-generator.php' );
	$conv = new qrgen_arraytoxml;
	return $conv->arraytoxml( $array );
}