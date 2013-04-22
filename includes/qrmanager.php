<?php
	
		require_once(  dirname( dirname( __FILE__ ) )  . '/qr-loader.php' );	
		
		$type = isset( $_GET[ 'fltype' ] ) ? $_GET[ 'fltype' ] : 'bookmarks' ;
		$findcol = isset( $_GET[ 'searchcol' ] ) ? $_GET[ 'searchcol' ] : 'url' ;
		$start = isset( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0 ;
		$nrows = isset( $_GET[ 'nrows' ] ) ? $_GET[ 'nrows' ] : 10 ;
		
		$nrows = ( $nrows == 0 ) ? 10 : $nrows;
		$start = ( $start == 0 ) ? $start : $start - 1;
		$start = $nrows * $start;	
		
			$tbcol = array (
						'bookmarks' => array( 'title', 'url', 'qrimage', 'created' ),
						'urls' => array( 'url', 'qrimage', 'created' ),
						'texts' => array( 'text', 'qrimage', 'created' ),
						'telephones' => array( 'telno', 'qrimage', 'created' ),
						'sms' => array( 'telno', 'sms_text', 'qrimage', 'created' ),
						'emails' => array( 'email', 'qrimage', 'created' ),
						'emsg' => array( 'email', 'subject', 'body', 'qrimage', 'created' ),
						'mecard' => array( 'name', 'info', 'qrimage', 'created'  ),
						'vcard' => array( 'name', 'info', 'qrimage', 'created'  ),
						'geo' => array( 'latitude', 'longitude', 'altitude', 'qrimage', 'created'  ),
						'user' => array( 'user_login', 'user_email', 'display_name', 'is_admin', 'user_registered' )
			);
			
			//$qrid = array ( 'bookmarks' => 'bmid', 'urls' => 'urlid' , 'texts' => 'txid' , 'telephones' => 'telid' , 'sms' => 'smsid', 'emails' => 'emid', 'emsg' => 'emsid', 'mecard' => 'mecid' );
			//$colid = getcol_id( $type );
	if ( $qrdb->is_logged_in( ) ) {
		
		$totalrows = 0;
		$userID = $qrdb->get_userID( );
		$col = ( $type === "user" ) ? 'userID' : 'keyword' ;
		$orderby = ( $type === "user" ) ? 'userID' : 'created' ;
		$table = $qrdb->prefix . $type;
		
				if( $qrdb->is_admin( )  && empty( $_GET[ 'qrfind' ] ) ) {
				
						$sqlcount = "SELECT COUNT( $col ) as ttrows FROM `$table`";
						$totals = $qrdb->get_row( $sqlcount );
						$totalrows = $totals->ttrows;
						//echo "Total Rows : $totalrows";
						
						if ( $type === "mecard" )
							$sql = "SELECT  b.firstname, b.lastname, b.wphno, b.email, b.url, b.bday, b.note, b.wstreet, b.wcity, b.wstate, b.wzip, b.wcountry, a.keyword, a.qrimage, a.trimage, a.created FROM $table a, ". $qrdb->prefix . "address b WHERE a.keyword = b.keyword ORDER BY a.created DESC LIMIT $start,  $nrows ";
						elseif ( $type === "vcard" )							
							$sql = "SELECT  b.* , a.keyword, a.qrimage, a.trimage, a.created FROM $table a, ". $qrdb->prefix . "address b WHERE a.keyword = b.keyword ORDER BY a.created DESC LIMIT $start,  $nrows ";
						else		
							$sql = "SELECT * FROM `$table` ORDER BY `$orderby` DESC LIMIT $start,  $nrows";
				}
				elseif( isset( $_GET[ 'qrfind' ] ) && !empty( $_GET[ 'qrfind' ] )) {
					$qrfind = $_GET[ 'qrfind' ];

						if( $qrdb->is_admin( ) ) {
							if ( $type === "mecard" ) {
								$sql = "SELECT  b.firstname, b.lastname, b.wphno, b.email, b.url, b.bday, b.note, b.wstreet, b.wcity, b.wstate, b.wzip, b.wcountry, a.keyword, a.qrimage, a.trimage, a.created  FROM $table a, ". $qrdb->prefix . "address b WHERE a.keyword = b.keyword AND " . $findcol . " LIKE ('%" . $qrfind . "%') ORDER BY a.created DESC LIMIT $start,  $nrows ";
							} elseif ( $type ==="vcard" ) {
								$sql = "SELECT  b.*,	a.keyword, a.qrimage, a.trimage, a.created  FROM $table a, ". $qrdb->prefix . "address b WHERE a.keyword = b.keyword AND " . $findcol . " LIKE ('%" . $qrfind . "%') ORDER BY a.created DESC LIMIT $start,  $nrows ";
							} else {
								$sql = 	"SELECT * from `$table` WHERE `". $findcol . "` LIKE ('%" . $qrfind . "%')  ORDER BY `$orderby` DESC LIMIT $start,  $nrows ";
							}	
						}
						else {	
							if ( $type === "mecard" ) {
								$sql = "SELECT  b.firstname, b.lastname, b.wphno, b.email, b.url, b.bday, b.note, b.wstreet, b.wcity, b.wstate, b.wzip, b.wcountry, a.keyword, a.qrimage, a.trimage, a.created,  FROM $table a, ". $qrdb->prefix . "address b WHERE a.uid = " . $userID . " and a.keyword = b.keyword AND " . $findcol . " LIKE ('%" . $qrfind . "%') ORDER BY a.created DESC LIMIT $start,  $nrows ";
							} elseif ( $type ==="vcard" ) {
								$sql = "SELECT  b.*, a.keyword, a.qrimage, a.trimage, a.created,  FROM $table a, ". $qrdb->prefix . "address b WHERE a.uid = " . $userID . " and a.keyword = b.keyword AND " . $findcol . " LIKE ('%" . $qrfind . "%') ORDER BY a.created DESC LIMIT $start,  $nrows ";
							} else {
								$sql = 	"SELECT * from `$table` WHERE 1=1 AND `" . $findcol . "` LIKE ('%" . $qrfind . "%') AND `uid` in ( SELECT uid from  `$table` WHERE `uid`=" . $userID ." )  ORDER BY `$orderby` DESC LIMIT $start,  $nrows ";
							}
						}
				}
				else {	
				
					$sqlcount = "SELECT COUNT( $col ) as ttrows FROM `$table` WHERE `uid`=" . $userID ;
					$totals = $qrdb->get_row( $sqlcount );
					$totalrows = $totals->ttrows;
					//echo "Total ". origtext( $type ) . "s you have created is : $totalrows";
					
						if ( $type === "mecard" ) {
							$sql = "SELECT  b.firstname, b.lastname, b.wphno, b.email, b.url, b.bday, b.note, b.wstreet, b.wcity, b.wstate, b.wzip, b.wcountry, a.keyword, a.qrimage, a.trimage, a.created from $table a, ". $qrdb->prefix . "address b where a.uid = " . $userID . " and a.keyword = b.keyword ORDER BY a.created DESC LIMIT $start,  $nrows ";
						} elseif ( $type ==="vcard" ) {
							$sql = "SELECT  b.*, a.keyword, a.qrimage, a.trimage, a.created from $table a, ". $qrdb->prefix . "address b where a.uid = " . $userID . " and a.keyword = b.keyword ORDER BY a.created DESC LIMIT $start,  $nrows ";	
						} else {
							$sql = "SELECT * from `$table` WHERE `uid`=" . $userID ." ORDER BY `$orderby` DESC LIMIT $start,  $nrows ";
						}
				}
			
			$data = $qrdb->get_results( $sql, ARRAY_A );
			
			if( !empty( $qrdb->num_rows ) &&  $qrdb->num_rows > 0 ) {
				
				$totalrows = ( $totalrows > 0 ) ? $totalrows : $qrdb->num_rows;
				//$totalrows =  $qrdb->num_rows;
				
				if( $type === "mecard" || $type === "vcard" ) {
					$data = data_mecard( $data, $type );
					$qrtable->set_data( $data, $tbcol[ $type ], $totalrows, $nrows, 'index.php', array( "string", "string", "string", "string", "string", "string" ), $type ); 
					echo $qrtable->build_table( ); 			
				}
				else {
					$qrtable->set_data( $data, $tbcol[ $type ], $totalrows, $nrows, 'index.php', array( "string", "string", "string", "string", "string", "string" ), $type ); 
					echo $qrtable->build_table( ); 
				}
				echo $qrtable->display_pagin( ); // display pagination & option 1 or 0 - show count sites 
			}
			else {
										
				if ( !empty( $_GET[ 'qrfind' ] ) ) {
					$nomatch = "<div id='nodata' ><h3>Could not find `". $_GET[ 'qrfind' ] . "` under `" . $findcol. "` in ". ucfirst( $type) ;
					$nomatch .= "</h3></div>";
				}
				else {
						$nomatch = "<div id='nodata' ><h3>No records found in ". ucfirst( $type) . ".</h3></div>";
				}
				echo $nomatch;
			}
	}

function data_mecard( $data, $type ) {
		
		foreach ( $data as $key => $value ) 
				 $return[ $key ] = build_mecdata( $value, $type );  
		return $return;
}

function build_mecdata( $data, $type ) {
	
		$vcdata = ( $type === "vcard" ) ? true : false;
		$imgsiz = $vcdata ? 20 : 25;
		$return['keyword'] = $data['keyword'];
		$return['name'] = $data['firstname'] . " " . $data['lastname'];
		$return['info'] = '<div id="mecinfo"><img title="Phone: ' . $data['wphno'] . '" src="' . QRGEN_SITE . '/images/mcphone.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		if( $vcdata ) 
			$return['info'] .= '<img title="Home Phone: ' . $data['hphno'] . '" src="' . QRGEN_SITE . '/images/mcphone.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
			
		$return['info'] .= '<img title="Email: ' . $data['email'] . '" src="' . QRGEN_SITE . '/images/mcmail.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		$return['info'] .='<img title="Url: ' . $data['url'] . '" src="' . QRGEN_SITE . '/images/mcweb.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		$return['info'] .='<img title="Birthday: ' . $data['bday'] . '" src="' . QRGEN_SITE . '/images/mcbday.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		
		if( $vcdata )
			$return['info'] .='<img title="Org & Title: ' . $data['org'] . '-' . $data['title'] . '" src="' . QRGEN_SITE . '/images/mcnote.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		else	
			$return['info'] .='<img title="Note: ' . $data['note'] . '" src="' . QRGEN_SITE . '/images/mcnote.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		
		$address = $data['wstreet'] . ", " . $data['wcity'] . ", " . $data['wstate'] . ", " . $data['wzip'] . ", " . $data['wcountry'] ;
		$return['info'] .='<img title="Address: ' . $address . '" src="' . QRGEN_SITE . '/images/mcaddr.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		
		if ( $vcdata ) {
			$haddress = $data['hstreet'] . ", " . $data['hcity'] . ", " . $data['hstate'] . ", " . $data['hzip'] . ", " . $data['hcountry'] ;
			$return['info'] .='<img title="Home Address: ' . $haddress . '" src="' . QRGEN_SITE . '/images/mcaddr.png" alt="" width="' . $imgsiz . '" height="' . $imgsiz . '" />';
		}
		$return['info'] .= '</div>';
		$return['qrimage'] = $data['qrimage'];
		$return['trimage'] = $data['trimage'];
		$return['created'] = $data['created'];
			
		return $return;
 }
		
	
	
?>