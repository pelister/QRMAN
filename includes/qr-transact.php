<?php
require_once(  dirname( __FILE__ )   . '/phpqrcode/qrlib.php' );

function store_qrcode( $qrdata, $type, $action, $t, $userID = 0 ) {
				
				global $qrdb;
				
				$userID = $userID ? $userID : $qrdb->get_userID( );
				
				//echo "$action | $type ";
				
				//initiate according to the type of qrcode and action.
				if( $action === 'add' ) {
						$sql = "INSERT into " . $qrdb->prefix . $type . " values(" ;
					// build the remaining query
					switch ( $type ) {
					
						case 'bookmarks':
						
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'".$qrdb->escape( htmlentities( $qrdata[ 'title' ], ENT_QUOTES ))."',
							'".$qrdb->escape( htmlentities( $qrdata[ 'url' ], ENT_QUOTES ))."',
							'".$qrdb->escape( $qrdata[ 'qrimg' ] )."',
							'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
							" . $t . ")" ;
							break;
					
						case 'urls':
					
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'".$qrdb->escape( htmlentities( $qrdata[ 'url' ], ENT_QUOTES ))."',
							'".$qrdb->escape( $qrdata[ 'qrimg' ] )."',
							'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
							" . $t .")" ;
							break;
							
						case 'texts':
						
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'" . $qrdb->escape( htmlentities( $qrdata[ 'simptxt' ], ENT_QUOTES )). "',
							'".$qrdb->escape( $qrdata[ 'qrimg' ] )."',
							'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
							" . $t .")" ;
							break;	
						
						case 'telephones':
							
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'" . $qrdb->escape( $qrdata['telno'] ) . "',
							'".$qrdb->escape( $qrdata[ 'qrimg' ] )."',
							'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
							" . $t . ")" ;
							break;
							
						case 'sms':
								
								$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
								'" . $qrdb->escape( $qrdata['telno'] ) . "',
								'".$qrdb->escape( $qrdata[ 'sms_text' ] )."',
								'".$qrdb->escape( $qrdata[ 'qrimg' ] )."',
								'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
								" . $t .")" ;
							break;		
							
						case 'emails':
							
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'" . $qrdb->escape( $qrdata['email'] ) . "',
							'".$qrdb->escape( $qrdata[ 'qrimg' ] )."',
							'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
							" . $t .")" ;
							break;
							
						case 'emsg':
							
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'" . $qrdb->escape( $qrdata['email'] ) . "',
							'".$qrdb->escape( $qrdata[ 'subject' ] )."',
							'".$qrdb->escape( $qrdata[ 'body' ] )."',
							'".$qrdb->escape( $qrdata[ 'qrimg' ] )."',
							'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
							" . $t .")" ;
							break;
							
						case 'mecard':
							
							//$t = time();
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'" . $qrdb->escape( $qrdata['qrimg'] ) . "',
							'" . $qrdb->escape( $qrdata[ 'trimg' ] ). "',
							" . $t .")" ;
							break;
							
						case 'vcard':
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'" . $qrdb->escape( $qrdata['qrimg'] ) . "',
							'" . $qrdb->escape( $qrdata[ 'trimg' ] ). "',
							" . $t .")" ;
							break;
						
						case 'geo':
							$sql .= $userID . ", '". $qrdb->escape( $qrdata[ 'keyword' ] ) . "' ,
							'".$qrdb->escape( $qrdata[ 'latitude' ] )."',
							'".$qrdb->escape( $qrdata[ 'longitude' ] )."',
							'".$qrdb->escape( $qrdata[ 'altitude' ] )."',
							'".$qrdb->escape( $qrdata['qrimg'] )."',
							'".$qrdb->escape( $qrdata[ 'trimg' ] )."',
							" . $t .")" ;
							break;
					} 
				}
				if( $action === 'edit' ) {
					$sql = "UPDATE " . $qrdb->prefix . $type . " set " ;
					
					switch( $type ) {
						
						case 'bookmarks':
							
							$sql .= " title='" . $qrdb->escape( htmlentities( $qrdata[ 'title' ], ENT_QUOTES )) ."',
								url='" . $qrdb->escape( htmlentities( $qrdata[ 'url' ], ENT_QUOTES )) ."',
								qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
								trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;
							
						case 'urls':
							
							$sql .= " url='" . $qrdb->escape( htmlentities( $qrdata[ 'url' ], ENT_QUOTES )) ."',
								qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
								trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;
						
						case 'texts':
							
							$sql .= " text='" . $qrdb->escape( htmlentities( $qrdata[ 'simptxt' ], ENT_QUOTES )) ."',
								qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
								trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;
					
						case 'telephones':
							
							$sql .= " telno='" . $qrdb->escape( $qrdata[ 'telno' ] ) ."',
								qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
								trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;
							
						case 'sms':
						
							$sql .= " telno='" . $qrdb->escape( $qrdata[ 'telno' ] ) ."',
								sms_text='" . $qrdb->escape( $qrdata[ 'sms_text' ] ) ."',
								qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
								trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;		
					
						case 'emails':
							$sql .= " email='" . $qrdb->escape( $qrdata[ 'email' ] ) ."',
							qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
							trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
						break;
					
						case 'emsg':
							$sql .= " email='" . $qrdb->escape( $qrdata[ 'email' ] ) ."',
							subject='" . $qrdb->escape( $qrdata[ 'subject' ] ) ."',
							body='" . $qrdb->escape( $qrdata[ 'body' ] ) ."',
							qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
							trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;
						
						case 'mecard':	
						case 'vcard':
							$sql .= " qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
							trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;
							
						case 'geo':
							$sql .= " latitude='" . $qrdb->escape( $qrdata[ 'latitude' ] ) ."',
							longitude='" . $qrdb->escape( $qrdata[ 'longitude' ] ) ."',
							altitude='" . $qrdb->escape( $qrdata[ 'altitude' ] ) ."',
							qrimage='" . $qrdb->escape( $qrdata[ 'qrimg' ] ) ."',
							trimage='" . $qrdb->escape( $qrdata[ 'trimg' ] ) ."' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
							break;
						
					}
				}
				//insert the data into db
				if ( $qrdb->query( $sql )) {
					$adrquery = '';
					if( check_iscard( $type ) && ( strcmp( $action, "add" ) == 0 ) ) {
					
						//$sqladr = "SELECT mecid from `" . $qrdb->prefix . "mecard` WHERE `created`='". $t ."'" ;
						//$mecardid = $qrdb->get_row( $sqladr, ARRAY_A );
						//$qrdb->debug();
						
						$adrquery = "INSERT into `" . $qrdb->prefix .  "address` values( '', '" . $qrdb->escape( $qrdata[ 'keyword' ] ) . "',
						'" . $qrdb->escape( $qrdata['fname'] ). "',
						'". $qrdb->escape( $qrdata['lname'] ) . "',
						'" . $qrdb->escape( $qrdata['hphno'] ) . "',
						'" . $qrdb->escape( $qrdata['wphno'] ) . "',
						'" . $qrdb->escape( $qrdata['email'] ). "',
						'" . $qrdb->escape( $qrdata['url'] ). "',
						'" . $qrdb->escape( $qrdata['bday'] ) . "',
						'" . $qrdb->escape( $qrdata['note'] ) . "',
						'" . $qrdb->escape( $qrdata['org'] ) . "',
						'" . $qrdb->escape( $qrdata['title'] ) . "',
						'" . $qrdb->escape( $qrdata['photo'] ) . "',
						'" . $qrdb->escape( $qrdata['hstreet'] ) . "',
						'" . $qrdb->escape( $qrdata['hcity'] ) . "',
						'" . $qrdb->escape( $qrdata['hstate'] ) . "',
						'" . $qrdb->escape( $qrdata['hzip'] ) . "',
						'" . $qrdb->escape( $qrdata['hcountry'] ) . "',
						'" . $qrdb->escape( $qrdata['wstreet'] ). "',
						'" . $qrdb->escape( $qrdata['wcity'] ). "',
						'" . $qrdb->escape( $qrdata['wstate'] ). "',
						'" . $qrdb->escape( $qrdata['wzip'] ). "',
						'" . $qrdb->escape( $qrdata['wcountry'] ). "' ) "; 
							
					}
					if( check_iscard( $type ) && ( strcmp( $action, "edit" ) == 0 ) ) {
						
							$adrquery = "UPDATE `" . $qrdb->prefix . "address` set firstname='" . $qrdb->escape( $qrdata['fname'] ). "',
								lastname='" . $qrdb->escape( $qrdata['lname'] ) . "',
								hphno='" . $qrdb->escape( $qrdata['hphno'] ) . "',
								wphno='" . $qrdb->escape( $qrdata['wphno'] ) . "',
								email='" . $qrdb->escape( $qrdata['email'] ) . "',
								url='" . $qrdb->escape( $qrdata['url'] ) . "',
								bday='"  . $qrdb->escape( $qrdata['bday'] ) . "',
								note='" . $qrdb->escape( $qrdata['note'] ) . "',
								org='" . $qrdb->escape( $qrdata['org'] ) . "',
								title='" . $qrdb->escape( $qrdata['title'] ) . "',
								photo='" . $qrdb->escape( $qrdata['photo'] ) . "',
								hstreet='" . $qrdb->escape( $qrdata['hstreet'] ) . "',
								hcity='" . $qrdb->escape( $qrdata['hcity'] ) . "',
								hstate='" . $qrdb->escape( $qrdata['hstate'] ) . "',
								hzip='" . $qrdb->escape( $qrdata['hzip'] ) . "',
								hcountry='" . $qrdb->escape( $qrdata['hcountry'] ) . "',
								wstreet='" . $qrdb->escape( $qrdata['wstreet'] ) . "',
								wcity='" . $qrdb->escape( $qrdata['wcity'] ). "',
								wstate='" . $qrdb->escape( $qrdata['wstate'] ) . "',
								wzip='" . $qrdb->escape( $qrdata['wzip'] ) . "',
								wcountry='" . $qrdb->escape( $qrdata['wcountry'] ). "' WHERE BINARY keyword='" . $qrdb->escape( $qrdata['keyword'] ) ."'";
					}
						
					if ( strlen( $adrquery ) > 0 ) {
							 $qrdb->query( $adrquery ); 
					}
					
					if( $action === "add" ) {
						$timestamp = date( 'Y-m-d H:i:s' );
						$table = $qrdb->prefix . 'shorturl';
						$ip = $qrdata['ipaddr'];
						$keyword = $qrdata['keyword'];
						$sqlsurl = "INSERT INTO `$table` ( `keyword`,`type`, `timestamp`, `ip`, `clicks` ) VALUES( '$keyword', '$type', '$timestamp', '$ip', 0 );" ;
						$qrdb->query( $sqlsurl );
						return true;
					}
					return true;
				}
				else {
					//echo "Main query";
					return false;		
				}
}


function create_qrimage( $data, $errc , $size, $user, $t, $tracking = false ) {
			
		global $qrdb;
	
		//Build image absolute path.
		$imgdir = QRIMAGES_PATH . '/' . $user ; 
		
		if ( !file_exists( $imgdir ))
				mkdir( $imgdir );
		//$t = time( );
		
		//Build image url		
		$imgpath = QRIMAGES_URL . '/' . $user ;
		
		
		if( $tracking ) {
			$filename = $imgdir. "/trackcode" . $t . ".png";
			$imgname = $imgpath ."/trackcode". $t . ".png";
		}
		else {
			$filename = $imgdir. "/qrcode" . $t . ".png";
			$imgname = $imgpath ."/qrcode". $t . ".png";
		}
		QRcode::png( $data , $filename , $errc , $size , 3 );

		list( $width, $height, $type, $attr ) = getimagesize( $filename );
	
		if( $tracking )
			$str['html'] = "<div id='gtrkcode' class='share'><img id='trimage' src='" . $imgname . "?" . $t . "'" . $attr . "/>" ;
		else
			$str['html'] = "<div id='gqrcode' class='share'><img id='qrimage' src='" . $imgname . "?" . $t . "'" . $attr . "/>" ;
		
		$str['html'] .= "<div id='imginfo'> .png ";
		$str['html'] .= "<span> ".$width."px</span> X";
		$str['html'] .= "<span> ".$height."px</span><br />";
		$str['html'] .= "<a href='".$imgname."' class='downlb'><img src='" . QRGEN_SITE . "/images/download.png' width='140' height='31' /></a>";
		$str['html'] .= "</div></div>";
		
		if( $tracking ) {
			$str['trkimg'] = $imgname;
			$str['trkpath'] = $filename;
			$str['qrimg'] = '';
		}
		else {
			$str['qrimg'] = $imgname;
			$str['qrpath'] = $filename;
			$str['trkimg'] = '';
		}
			
		$str['width'] = $width;
		$str['status'] = 'success';
		
		return $str;
}

function check_iscard( $type ) {
	if( $type === 'mecard' || $type === 'vcard' )
			return true;
	else 
			return false;
}

function vcard_encode( $vcdata ) {
	
	extract( $vcdata );
	
	if ( strcmp( $vcver, "2.1" ) == 0 )
	{
		$data  = "BEGIN:VCARD\r\nVERSION:2.1\r\n";
		$data .= "N:" . $lname . ";" . $fname . ";;;\r\n";
		$data .= "FN:" . $fname . " " . $lname . "\r\n";
		$data .= "ORG:" . $org . ";\r\n";
		$data .= "TITLE:" . $title . "\r\n";
		$data .= "BDAY;value=" . $bday. "\r\n";
		$data .= "TEL;WORK:". $wphno . "\r\n";
		$data .= "TEL;HOME:" . $hphno . "\r\n";
		$data .= "ADR;WORK:;;" . $wstreet . ";" . $wcity . ";" . $wstate . ";" . $wzip . ";" . $wcountry . "\r\n";
		$data .= "ADR;HOME:;;" . $hstreet . ";" . $hcity . ";" . $hstate . ";" . $hzip . ";" . $hcountry . "\r\n";
		$data .= "EMAIL;PREF;INTERNET:" . $email . "\r\n";
		
		if ( !empty ( $photo ) &&  ( $arr = get_photo_data( $photo )) ) {
			list( $pwidth, $pheight, $ptype, $imgform ) = $arr;
			$data .= "PHOTO;VALUE=URL;TYPE=" . $imgform . ":" . $photo . "\r\n";
		}
		$data .= "URL;WORK:" . $url . "\r\n";
		$data .= "END:VCARD";
	}
	
	if ( strcmp ( $vcver, "3.0" ) == 0 )
	{
		$data  = "BEGIN:VCARD\r\nVERSION:3.0\r\n";
		$data .= "N:" . $lname . ";" . $fname . ";;;\r\n";
		$data .= "FN:" . $fname. " " . $lname . "\r\n";
		$data .= "ORG:" . $org . ";\r\n";
		$data .= "TITLE:" . $title . "\r\n";
		
		if ( !empty ( $photo )  && ( $arr = get_photo_data( $photo )) ) {
			list( $pwidth, $pheight, $ptype, $imgform ) = $arr;
			$data .= "PHOTO;VALUE=URL;TYPE=" . $imgform . ":" . $photo . "\r\n";
		}
		
		$data .= "BDAY;value=" . $bday . "\r\n";
		
		$data .= "TEL;TYPE=WORK:" . $wphno . "\r\n";
		$data .= "TEL;TYPE=HOME:" . $hphno . "\r\n";
		$data .= "ADR;TYPE=WORK:;;" . $wstreet . ";" . $wcity . ";" . $wstate . ";" . $wzip . ";" . $wcountry . "\r\n";
		$data .= "ADR;TYPE=HOME:;;" . $hstreet . ";" . $hcity . ";" . $hstate . ";" . $hzip . ";" . $hcountry . "\r\n";
		$data .= "EMAIL;TYPE=PREF;INTERNET:" . $email . "\r\n";
		$data .= "item3.URL;type=pref:" . $url . "\r\n";
		$data .= 'item3.X-ABLabel:_$!<HomePage>!$_' . "\r\n";
		$data .= "END:VCARD";
	}
	
	if ( strcmp ( $vcver, "4.0" ) == 0 )
	{
		$data  = "BEGIN:VCARD\r\nVERSION:4.0\r\n";
		$data .= "N:" . $lname . ";" . $fname . ";;;\r\n";
		$data .= "FN:" . $fname . " " . $lname . "\r\n";
		$data .= "ORG:" . $org . "\r\n";
		$data .= "TITLE:" . $title . "\r\n";
		
		if ( !empty ( $photo ) && ( $arr = get_photo_data( $photo ))) {
			list( $pwidth, $pheight, $ptype, $imgform ) = $arr;
			$data .= "PHOTO:" . $photo . "\r\n";
		}
		
		$data .= "BDAY;value=" . $bday . "\r\n";
		$data .= "TEL;TYPE=work:" . $wphno ."\r\n";
		$data .= "TEL;TYPE=home:" . $hphno . "\r\n";
		$data .= "ADR;TYPE=work:;;" . $wstreet . ";" . $wcity . ";" . $wstate . ";" . $wzip . ";" . $wcountry . "\r\n";
		$data .= "ADR;TYPE=home:;;" . $hstreet . ";" . $hcity . ";" . $hstate . ";" . $hzip . ";" . $hcountry . "\r\n";
		$data .= "URL;TYPE=work:". $url . "\r\n";
		$data .= "EMAIL:" . $email . "\r\n";
		$data .= "END:VCARD";
	}
	return $data;
	
}

function qrgen_encoder( $datarr, &$qrdata ) {
	
	switch( $datarr[ 'qrtype' ] ) {
		
		case 'bookmarks':
		
		$qrdata['title'] = !empty( $datarr['title'] ) ? $datarr['title'] : "Blank" ; 
		$qrdata['url'] =  !empty( $datarr['url'] ) ? $datarr['url'] : "Blank";
		
		if (preg_match( '/^http:\/\//', $qrdata[ 'url' ] ) || preg_match( '/^https:\/\//', $qrdata[ 'url' ] ))   
		$qrdata[ 'url' ];
		else  
		$qrdata[ 'url' ] = "http://".$qrdata[ 'url' ];  				
		
		if( strstr( $qrdata['url'], "Blank" ) == false ) {		
			if( $qrdata['title'] === "Blank" )
			$qrdata['title'] = get_remote_title( $qrdata['url'] );
		}				
		$encoded	=	"MEBKM:TITLE:" .$qrdata[ 'title' ]. ";URL:" . $qrdata[ 'url' ] . ";;"; 	
		break;
		
		case 'urls':
		
		$qrdata['url'] =  !empty( $datarr['url'] ) ? $datarr['url'] : "Blank";
		
		if (preg_match( '/^http:\/\//', $qrdata[ 'url' ] ) || preg_match( '/^https:\/\//', $qrdata[ 'url' ] ))   
		$encoded = $qrdata[ 'url' ];  
		else  
		$encoded = "http://".$qrdata[ 'url' ];  
		break;
		
		case 'texts':
		
		$qrdata['simptxt'] =  !empty( $datarr['simptxt'] ) ? $datarr['simptxt'] : "Blank";
		$encoded = $qrdata['simptxt'];
		break;
		
		case 'telephones':
		
		$qrdata['telno'] =  !empty( $datarr['phno'] ) ? $datarr['phno'] : "Blank";
		$encoded = "TEL:".$qrdata['telno']; 
		break;
		
		case 'sms':
		
		$qrdata['telno'] =  !empty( $datarr['phno'] ) ? $datarr['phno'] : "Blank";
		$qrdata['sms_text'] =  !empty( $datarr['smstxt'] ) ? $datarr['smstxt'] : "Blank";
		$encoded = "SMSTO:".$qrdata['telno'].":".$qrdata['sms_text'];
		break;
		
		case 'emails':
		
		$qrdata['email'] =  !empty( $datarr['email'] ) ? $datarr['email'] : "Blank";
		$encoded = "MAILTO:".$qrdata['email']; 
		break;
		
		case 'emsg':
		
		$qrdata['email'] =  !empty( $datarr['email'] ) ? $datarr['email'] : "Blank";
		$qrdata['subject'] = !empty( $datarr['subject'] ) ? $datarr['subject'] : "Blank";
		$qrdata['body'] =  !empty( $datarr['body'] ) ? $datarr['body'] : "Blank";
		$encoded = "MATMSG:TO:".$qrdata['email'].";SUB:".$qrdata['subject'].";BODY:".$qrdata['body'].";;" ;
		break;
		
		case 'mecard':
		
		$qrdata['fname'] = !empty( $datarr['fname'] ) ? $datarr['fname'] : "firstname";
		$qrdata['lname'] = !empty( $datarr['lname'] ) ? $datarr['lname'] : "lastname";
		$qrdata['wphno'] =  !empty( $datarr['phno'] ) ? $datarr['phno'] : "phone";
		$qrdata['email'] =  !empty( $datarr['email'] ) ? $datarr['email'] : "email";
		$qrdata['url'] =  !empty( $datarr['url'] ) ? $datarr['url'] : "url";
		$qrdata['note'] = !empty( $datarr['note'] ) ? $datarr['note'] : "note";
		$qrdata['bday'] = !empty( $datarr['bday'] )  ? $datarr['bday'] : "bday";
		$qrdata['wstreet'] = !empty( $datarr['street'] )  ? $datarr['street'] : "street";
		$qrdata['wcity'] = !empty( $datarr['city'] ) ? $datarr['city'] : "city";
		$qrdata['wstate'] = !empty( $datarr['state'] ) ? $datarr['state'] : "state";
		$qrdata['wzip'] = !empty( $datarr['zip'] ) ? $datarr['zip'] : "zip";
		$qrdata['wcountry'] = !empty( $datarr['country'] ) ? $datarr['country'] : "country";
		//set empty - those are vcard data.
		$qrdata['org'] = '0';
		$qrdata['title'] = '0';
		$qrdata['photo'] = '';
		$qrdata['hphno'] = '0';
		$qrdata['hstreet'] = '0';
		$qrdata['hcity'] = '0';
		$qrdata['hstate'] = '0';
		$qrdata['hzip'] = '0';
		$qrdata['hcountry'] = '0';
		
		if ( !empty ( $datarr['bday'] ))
		$bday  = explode( "-", $qrdata['bday'] );
		else
		$bday = array( "0", "0", "0" );
		
		$address = $qrdata['wstreet'] . "," . $qrdata['wcity'] . "," . $qrdata['wstate'] . "," . $qrdata['wzip'] . "," . $qrdata['wcountry'];
		$encoded = "MECARD:N:". $qrdata['fname'] .",". $qrdata['lname'] .";TEL:". $qrdata['wphno'] .";EMAIL:". $qrdata['email'] .";URL:". $qrdata['url'] .";NOTE:". $qrdata['note'] .";BDAY:". $bday[0].$bday[1].$bday[2] .";ADR:,,". $address .";;" ;
		break;
		
		case 'vcard':
		
		$qrdata['vcver'] = !empty( $datarr['vcver'] ) ? $datarr['vcver'] : "version";
		$qrdata['fname'] = !empty( $datarr['fname'] ) ? $datarr['fname'] : "firstname";
		$qrdata['lname'] = !empty( $datarr['lname'] ) ? $datarr['lname'] : "lastname";
		$qrdata['wphno'] =  !empty( $datarr['wphno'] ) ? $datarr['wphno'] : "phone";
		$qrdata['hphno'] =  !empty( $datarr['hphno'] ) ? $datarr['hphno'] : "phone";
		$qrdata['bday'] = !empty( $datarr['bday'] )  ? $datarr['bday'] : "bday";
		$qrdata['email'] =  !empty( $datarr['email'] ) ? $datarr['email'] : "email";
		$qrdata['photo'] =  !empty( $datarr['photo'] ) ? $datarr['photo'] : "";
		$qrdata['org'] =  !empty( $datarr['org'] ) ? $datarr['org'] : "org";
		$qrdata['title'] =  !empty( $datarr['title'] ) ? $datarr['title'] : "title";
		$qrdata['hstreet'] = !empty( $datarr['hstreet'] )  ? $datarr['hstreet'] : "street";
		$qrdata['hcity'] = !empty( $datarr['hcity'] ) ? $datarr['hcity'] : "city";
		$qrdata['hstate'] = !empty( $datarr['hstate'] ) ? $datarr['hstate'] : "state";
		$qrdata['hzip'] = !empty( $datarr['hzip'] ) ? $datarr['hzip'] : "zip";
		$qrdata['hcountry'] = !empty( $datarr['hcountry'] ) ? $datarr['hcountry'] : "country";
		$qrdata['wstreet'] = !empty( $datarr['wstreet'] )  ? $datarr['wstreet'] : "street";
		$qrdata['wcity'] = !empty( $datarr['wcity'] ) ? $datarr['wcity'] : "city";
		$qrdata['wstate'] = !empty( $datarr['wstate'] ) ? $datarr['wstate'] : "state";
		$qrdata['wzip'] = !empty( $datarr['wzip'] ) ? $datarr['wzip'] : "zip";
		$qrdata['wcountry'] = !empty( $datarr['wcountry'] ) ? $datarr['wcountry'] : "country";
		$qrdata['url'] =  !empty( $datarr['url'] ) ? $datarr['url'] : "url";
		$qrdata['note'] = '0';
		
		$encoded = vcard_encode( $qrdata );
		break;
		
		case 'geo':
		$qrdata['latitude'] = !empty( $datarr['lat'] ) ? $datarr['lat'] : 'Blank';
		$qrdata['longitude'] = !empty( $datarr['lng'] ) ? $datarr['lng'] : 'Blank';
		$qrdata['altitude'] = !empty( $datarr['altitude'] ) ? $datarr['altitude'] : 0;
		if ( $qrdata['altitude'] == 0  )
		$encoded = "GEO:" . $qrdata['latitude'] . "," . $qrdata['longitude'];
		else
		$encoded = "GEO:" . $qrdata['latitude'] . "," . $qrdata['longitude'] . "," . $qrdata['altitude'];
	}
	
	return $encoded;
}

function get_photo_data( $photo ) {
	
	if( $arr = @getimagesize( $photo ) ) {
		//list( $pwidth, $pheight, $ptype ) = getimagesize( $photo );
		list( $pwidth, $pheight, $ptype ) = $arr;
		$mimetype = image_type_to_mime_type ( $ptype );
		$imgfmt = explode ( "/", $mimetype );
		return array( $pwidth, $pheight, $ptype, $imgfmt[1] );
	}
	else 
		return false;
}