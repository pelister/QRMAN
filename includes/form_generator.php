<?php
	
	require_once(  dirname( dirname( __FILE__ ) )  . '/qr-loader.php' );	
	
	$action = isset( $_POST['action'] ) ? $_POST['action'] : '' ;
	$qrtype = isset( $_POST['type'] ) ? $_POST['type'] : '';
	$id = isset( $_POST['id'] ) ? $_POST['id'] : '';
	
	$tbl = array( 
	'Bookmark' => 'bookmarks', 
	'Url' => 'urls' ,
	'Text' => 'texts',
	'Telephone' => 'telephones',
	'Sms' => 'sms',
	'Email Id' => 'emails',
	'Email Message' => 'emsg',
	'Mecard' => 'mecard',
	'Vcard' => 'vcard',
	'User' => 'user',
	'Geo' => 'geo'
	);

	if( !$qrdb->is_logged_in( ) && !from_regform( $qrtype)) {
		echo "<div id='notlogged'>You must be logged in to generate QR codes.</div>";
		exit;
	}
	
	switch( $action )
	{
		case 'add':
			echo show_form( $action, $qrtype, array( ) );
			break;
			
		case 'delete':
			
			$type = $tbl[ $qrtype ];
			$tmpid = explode( "_", $id );
			$keyword = $qrdb->escape( $tmpid[ 1 ] );
			$table = $qrdb->prefix . $type ;
			if( $type === 'user' )
				$sql = "DELETE from `$table` WHERE `userID` = $keyword" ;
			else	
				$sql = "DELETE from `$table` WHERE BINARY `keyword` = '$keyword'" ;
				if ( !( $type === 'user' )) {
				$imgqry = "SELECT qrimage, trimage from `$table` WHERE BINARY `keyword` = '$keyword' LIMIT 1";
				$row = $qrdb->get_row( $imgqry, ARRAY_A );
				
				if( $qrdb->num_rows > 0 ) {

					if( $imgfile = parse_qrimage( $row[ 'qrimage' ] ) )
						if ( file_exists( $imgfile )) 
							unlink( $imgfile );
					if( $imgfile = parse_qrimage( $row[ 'trimage' ] ) )
						if ( file_exists( $imgfile )) 
							unlink( $imgfile );			
				}
			}	
			if( $qrdb->query( $sql )) {
				$delshurl = "DELETE from `" . $qrdb->prefix . "shorturl` WHERE BINARY `keyword` = '$keyword'" ;
				$qrdb->query( $delshurl );
				
				$dellog = "DELETE from `" . $qrdb->prefix . "log` WHERE BINARY `keyword` = '$keyword'" ;
				$qrdb->query( $dellog );
				
				echo "Record deleted permanently from " . $type ;
				if( $type === "mecard" || $type == "vcard" ) {
					$sql = "DELETE from `" . $qrdb->prefix . "address` WHERE BINARY `keyword` = '$keyword'" ;
					$qrdb->query( $sql );
				}
			}	
			else
				echo "Could not delete from " . $type ; 
					
			break;
		
		case 'edit':
					
			$type = $tbl[ $qrtype ];
			$col = ( $type === "user" ) ? 'userID' : 'keyword' ;
			$tmpid = explode( "_", $id );
			$keyword = $qrdb->escape( $tmpid[ 1 ] );
			$table = $qrdb->prefix . $type ;
			
			if ( $type === "mecard" )
				$sql = "SELECT b.firstname, b.lastname, b.wphno, b.email, b.url, b.bday, b.note, b.wstreet, b.wcity, b.wstate, b.wzip, b.wcountry, a.keyword, a.qrimage, a.trimage, a.created FROM $table a, ". $qrdb->prefix . "address b WHERE BINARY a.keyword = '$keyword' AND  a.keyword =  b.keyword LIMIT 1 ";
			elseif ( $type === "vcard" )
				$sql = "SELECT b.* , a.keyword, a.qrimage, a.trimage, a.created FROM $table a, ". $qrdb->prefix . "address b WHERE BINARY a.keyword = '$keyword' AND  a.keyword =  b.keyword LIMIT 1 ";
			elseif ( $type == "user" )
				$sql = "SELECT * from `$table` WHERE `userID` = $keyword LIMIT 1" ;
			else
				$sql = "SELECT * from `$table` WHERE BINARY `keyword` = '$keyword' LIMIT 1" ;
				
			$row = $qrdb->get_row( $sql, ARRAY_A );
			
			if ( $qrdb->num_rows > 0 ) {
				echo show_form( $action, $qrtype, $row );
			}
			else {
				echo "Could not fetch data from the Database";
			}
			break;	
			
		case 'login':
			
			if ( $qrtype === 'Login' ) {
				
				$str = '<div id="logform">';
				$str .= $qrdb->show_login_form( 'index.php' ) ;
				$str .= '</div>';
				echo $str;
			}
			if ( $qrtype === 'Register' ) {
				$str = '<div id="logform">';
				$str .= $qrdb->show_registration_form( 'index.php'  );
				$str .= '</div>';
				echo $str;
			}
			if( $qrtype === 'Forgot Password?' ) {
				$str = '<div id="logform">';
				$str .= $qrdb->show_forgotpass(  'index.php' );
				$str .= '</div>';
				echo $str;
			}
			if( $id === 'edit' ) {
				$str = '<div id="logform">';
				$str .= $qrdb->show_profile_edit_form(  'index.php'  );
				$str .= '</div>';
				echo $str;					
			}
			
			break;
		
	}
	
	function show_form( $action, $type, $data ) {
		global $qrdb;
	
		if ( count( $data ) > 0 ) 
				$sval = true;
		else
				$sval = false;
				
		$qrform = '<div id="qrgenform"><form action="" method="post" id="qrgen">';
	
		$nonce_field = qrgen_nonce_field( $action );
		$qrform .= '<fieldset>';
		
		if ( $type === "Bookmark" ) {
			
			$qrform .= '<div><strong>Bookmark a Website</strong></div>
			<div id="labels"><p>Title</p>
			<span class="text">
			<input type="text" name="title"' ;
			if ( $sval ) $qrform .= ' value="' . $data[ 'title' ] . '"';
			$qrform .= ' size="100">
			</span>
			</div>
			<div id="labels"><p>Url</p>
			<span class="text">
			<input type="text" name="url"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'url' ] . '"';
			$qrform .= ' size="100"></span>	</div><br />';
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype"  value="bookmarks" />' .  $nonce_field ;
		}
		
		if ( $type === "Url" ) {
			
			$qrform .= '<div><strong>Website Url</strong></div>
			<div id="labels"><p>Url</p>
			<span class="text">
			<input type="text" name="url"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'url' ] . '"';
			$qrform .= ' size="100"></span></div><br />';
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="urls" />' .  $nonce_field ;
		}
		if ( $type === "Text" ) {
			
			$qrform .= '<div><strong>Free Formatted Text</strong></div>
			<div id="labels"><p>Text</p>
			<span class="txtarea">
			<textarea  class="qrtxt" name="simptxt" rows="5" cols="50">';
			if ( $sval ) $qrform .=  $data[ 'text' ]  ;
			$qrform .='</textarea></span></div><br />';
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="texts" />' .  $nonce_field ;
		}	
		if ( $type === "Telephone" ) {
			$qrform .= '<div><strong>Telephone</strong></div>
			<div id="labels"><p>Tel/Mobile</p>
	  		<span class="text">
			<input type="text" name="phno"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'telno' ] . '"';
			$qrform .= '></span></div><br />';
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="telephones" />' .  $nonce_field ;
		}
		if ( $type === "Sms" ) {
			$qrform .= '<div><strong>Send an SMS</strong></div>
			<div><p>Phone</p>
			<span class="text">
			<input type="text" name="phno"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'telno' ] . '"';
			$qrform .= '></span></div><br />
			<div><p>Message</p>
			<span class="txtarea">
			<textarea  class="qrtxt" name="smstxt" rows="5" cols="35">';
			if ( $sval ) $qrform .=  $data[ 'sms_text' ] ;
			$qrform .= '</textarea></span></div><br />';
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="sms" />' .  $nonce_field ;
		}
		if ( $type === "Email Id" ) {
			$qrform .= '<div><strong>Email Address</strong></div>
			<div id="labels"><p>Email</p>
			<span class="text">
			<input type="text" name="email"';
			if ( $sval ) $qrform .=  ' value="' . $data[ 'email' ]  . '"';
			$qrform .= '></span></div><br />';
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="emails" />' .  $nonce_field ;
		}
			
		if ( $type === "Email Message" ) {
			$qrform .= '<div><strong>Send an email</strong></div>
			<div ><p>Email</p>
			<span class="text">
			<input type="text" name="email"';
			if ( $sval ) $qrform .=  ' value="' . $data[ 'email' ]  . '"';
			$qrform .= '></span></div><br />';
			$qrform .= '<div><p>Subject</p>
			<span class="text">
			<input type="text" name="subject"';
			if ( $sval ) $qrform .=  ' value="' . $data[ 'subject' ]  . '"';
			$qrform .= '></span></div><br />';
			$qrform .= '<div ><p>Body</p>
			<span class="txtarea">
			<textarea  class="qrtxt" name="body" rows="5" cols="50">';
			if ( $sval ) $qrform .=  $data[ 'body' ] ;
			$qrform .= '</textarea></span></div><br />';
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="emsg" />' .  $nonce_field ;
		}
		if( $type === "Mecard" ) {
			$qrform .= '<div id="personal"><div><strong>Create a meCard</strong></div>
			<div id="labels"><p>First name</p>
			<span class="text">
			<input type="text" name="fname"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'firstname' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Last name</p>
			<span class="text">
			<input type="text" name="lname"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'lastname' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Phone</p>
			<span class="text">
			<input type="text" name="phno"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wphno' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Email</p>
			<span class="text">
			<input type="text" name="email"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'email' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Website</p>
			<span class="text">
			<input type="text" name="url"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'url' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Note</p>
			<span class="text">
			<input type="text" name="note"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'note' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Birthday</p>
			<span class="text">
			<input type="text" name="bday" id="bday"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'bday' ] . '"';
			$qrform .= '></span></div></div><br />
			<div id="adrfield"><div><strong>Address</strong></div>
			<div id="labels"><p>Street</p>
			<span class="text">
			<input type="text" name="street"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wstreet' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>City</p>
			<span class="text">
			<input type="text" name="city"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wcity' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>State</p>
			<span class="text">
			<input type="text" name="state"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wstate' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Zip</p>
			<span class="text">
			<input type="text" name="zip"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wzip' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Country</p>
			<span class="text">
			<input type="text" name="country"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wcountry' ] . '"';
			$qrform .= '></span></div></div>' ;
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="mecard" />' .  $nonce_field ;
		}	
		if( $type === "Vcard" ) {
			$qrform .= '<div id="personal"><div><strong>Create a vCard</strong></div>
			<div id="labels"><p>vCard Version</p>
				<select name="vcver">
				<option value="2.1" selected="selected">2.1</option>
				<option value="3.0">3.0</option>
				<option value="4.0">4.0</option>
				</select>
			</div>
			<div id="labels"><p>First name</p>
			<span class="text">
			<input type="text" name="fname"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'firstname' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Last name</p>
			<span class="text">
			<input type="text" name="lname"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'lastname' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Birthday</p>
			<span class="text">
			<input type="text" name="bday" id="bday"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'bday' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Email</p>
			<span class="text">
			<input type="text" name="email"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'email' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Photo ( url )</p>
			<span class="text">
			<input type="text" name="photo"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'photo' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Organization</p>
			<span class="text">
			<input type="text" name="org"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'org' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Title</p>
			<span class="text">
			<input type="text" name="title"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'title' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Website</p>
			<span class="text">
			<input type="text" name="url"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'url' ] . '"';
			$qrform .= '></span></div>			
			<div id="labels"><p>Work Phone</p>
			<span class="text">
			<input type="text" name="wphno"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wphno' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Home Phone</p>
			<span class="text">
			<input type="text" name="hphno"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'hphno' ] . '"';
			$qrform .= '></span></div></div><br />
			<div id="adrfield"><div><strong>Work Address</strong></div>
			<div id="labels"><p>Street</p>
			<span class="text">
			<input type="text" name="wstreet"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wstreet' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>City</p>
			<span class="text">
			<input type="text" name="wcity"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wcity' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>State</p>
			<span class="text">
			<input type="text" name="wstate"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wstate' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Zip</p>
			<span class="text">
			<input type="text" name="wzip"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wzip' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Country</p>
			<span class="text">
			<input type="text" name="wcountry"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'wcountry' ] . '"';
			$qrform .= '></span></div></div><br />
			<div id="adrfield"><div><strong>Home Address</strong></div>
			<div id="labels"><p>Street</p>
			<span class="text">
			<input type="text" name="hstreet"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'hstreet' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>City</p>
			<span class="text">
			<input type="text" name="hcity"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'hcity' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>State</p>
			<span class="text">
			<input type="text" name="hstate"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'hstate' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Zip</p>
			<span class="text">
			<input type="text" name="hzip"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'hzip' ] . '"';
			$qrform .= '></span></div>
			<div id="labels"><p>Country</p>
			<span class="text">
			<input type="text" name="hcountry"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'hcountry' ] . '"';
			$qrform .= '></span></div></div>' ;
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="vcard" />' .  $nonce_field ;
			
		}
		if( $type === 'Geo' ) {
			$qrform .= '<div><strong>Geo Location (drag or click the marker on map to get Latitude and Longitude)</strong></div>
			<div id="labels"><p>Latitude</p>
			<span class="text">
			<input type="text" id="latbox" name="lat"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'latitude' ] . '"';
			$qrform .= '></span></div>' ;
			$qrform .= '<div id="labels"><p>Longitude</p>
			<span class="text">
			<input type="text" id="lngbox" name="lng"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'longitude' ] . '"';
			$qrform .= '></span></div>' ;
			$qrform .= '<div id="labels"><p>Altitude</p>
			<span class="text">
			<input type="text" name="altitude"';
			if ( $sval ) $qrform .= ' value="' . $data[ 'altitude' ] . '"';
			$qrform .= '></span></div>' ;
			if ( $sval ) { 
				$qrform .= '<input type="hidden" name="qrimage"  value="' . $data[ 'qrimage' ] . '" />';
				$qrform .= '<input type="hidden" name="trimage"  value="' . $data[ 'trimage' ] . '" />';
				$qrform .= '<input type="hidden" name="id"  value="' . $data[ 'keyword' ] . '" />';
			}
			$qrform .= '<input type="hidden" name="qrtype" value="geo" />' .  $nonce_field ;
		}

		$qrform .= '<p></p><div id="errset"><span>Error Correction</span>
				<select name="errc">
				<option value="L">L - Recovery of 7% Data loss</option>
				<option value="M" selected="selected" >M - Recovery of 15% Data loss</option>
				<option value="Q">Q - Recovery of 25% Data loss</option>
				<option value="H">H - Recovery of 30% Data loss</option>
				</select></div>
				<div id="sizeset">
			<span>QR image size</span>
			<select name="size">
				<option value="2">Smallest</option>
				<option value="3">Smaller</option>
				<option value="4">Small</option>
				<option value="5" selected="selected" >Normal</option>
				<option value="6">Medium</option>
				<option value="7">Large</option>
				<option value="8">Very Large</option>
				<option value="9">Extra Large</option>
				<option value="10">Largest</option>
			</select></div>
			<div id="typeset">
			<span>Type of QR Code</span>
			<select name="trackable">
			<option value="1" selected="selected">Trackable</option>
			<option value="2">Non Trackable</option>
			<option value="3">Both</option>
			</select>
			</div>
			<br />
			<p></p>			
			<div id="fsub">';
			
		if ( $action === "add" ) 
				$qrform .= "<br /><input type='submit' id='" . $action. "' value='Generate'></fieldset></form></div></div>";
				
		if ( $action === "edit" )
				$qrform .= "<br /><input type='submit' id='" . $action. "' value='Save'></fieldset></form></div></div>";
		
		if ( $type === "User" && $action === "add" )			
				$qrform = $qrdb->show_registration_form( 'index.php' ) ;
			
		if ( $type === "User" && $action === "edit" ) 
				$qrform = $qrdb->show_profile_edit_form( 'index.php'  , $data[ 'userID' ] , $data );
					
		return $qrform;
}
		
function from_regform( $type ) {
		$arr = array( "Register", "Forgot Password?" );
		return in_array( $type, $arr );
}