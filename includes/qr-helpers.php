<?php

function qr_db_connect( ) {
	global $qrdb;

	if (   !defined( 'QRGEN_DB_USER' )
		or !defined( 'QRGEN_DB_PASS' )
		or !defined( 'QRGEN_DB_NAME' )
		or !defined( 'QRGEN_DB_HOST' )
		or !class_exists( 'ezSQL_pdo' )
	) die ( 'DB config missing, or could not find DB class' );
	
	// Connect to the database
	$qrdb =  new qrClass( );	
	
		$qrdb->ezSQL_pdo( QRGEN_DB_DSN, QRGEN_DB_USER, QRGEN_DB_PASS );		
		if ( $qrdb->last_error )
			die( $qrdb->last_error );
	
	return $qrdb;
}

function qrgen_die( $message = '', $title = '', $header_code = 200 ) {
$url =  !defined('QRGEN_SITE') ? qrgen_guess_url()  : qrgen_site_url( false );
?>	
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php echo $title ?></title>
		<link rel="stylesheet" href="<?php echo $url;  ?>/css/die.css" type="text/css" >
	</head>
	<body id="config-error">
	<h1 id="logo"><a href="<?php echo $url; ?>"> <img alt="QRMAN" src="images/logo.png" /> </a></h1>
		
		<?php

			qrgen_call_funcs( 'before_die' );
			echo $message;

		?>
		
	</body>
</html>
<?php
	die( );
}

function qrgen_admin_url( $page = '' ) {
	$admin = QRGEN_SITE . '/admin/' . $page;
	if( qrgen_is_ssl( ) or qrgen_needs_ssl( ) )
		$admin = str_replace( 'http://', 'https://', $admin );
	return $admin;
}

function qrgen_site_url( $echo = true, $url = '' ) {
	$url = qrgen_get_relative_url( $url );
	$url = trim( QRGEN_SITE . '/' . $url, '/' );
	
	// Do not enforce (checking qrgen_need_ssl() ) but check current usage so it won't force SSL on non-admin pages
	if( qrgen_is_ssl( ) )
		$url = str_replace( 'http://', 'https://', $url );
	if( $echo )
		echo $url;
	
	return $url;
}

function qrgen_get_relative_url( $url, $strict = true ) {
	$url = sanitize_url( $url );
	
	// Remove protocols to make it easier
	$noproto_url  = str_replace( 'https:', 'http:', $url );
	$noproto_site = str_replace( 'https:', 'http:', QRGEN_SITE );
	
	// Trim URL from QRGEN root URL : if no modification made, URL wasn't relative
	$_url = str_replace( $noproto_site . '/', '', $noproto_url );
	if( $_url == $noproto_url )
		$_url = ( $strict ? '' : $url );
	
	return $_url;
}

// Check if SSL is used.

function qrgen_is_ssl( ) {
	$is_ssl = false;
	if ( isset( $_SERVER['HTTPS'] ) ) {
		if ( 'on' == strtolower( $_SERVER['HTTPS'] ) )
			$is_ssl = true;
		if ( '1' == $_SERVER['HTTPS'] )
			$is_ssl = true;
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		$is_ssl = true;
	}
	return $is_ssl ;
}

function qrgen_guess_url() {
	if ( defined('QRGEN_SITE') && '' != QRGEN_SITE ) {
		$url = QRGEN_SITE;
	} else {
		$schema = qrgen_is_ssl() ? 'https://' : 'http://';
		$url = preg_replace('#/(admin/.*)#i', '', $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}
	return rtrim( $url, '/' );
}

function qrgen_needs_ssl( ) {
	if ( defined('QRGEN_ADMIN_SSL') && QRGEN_ADMIN_SSL == true )
		return true;
	return false;
}

function is_valid_email( $email ) {
	
	// Test for the minimum length the email can be
	if ( strlen( $email ) < 3 ) 
		return false;
	
	// Test for an @ character after the first position
	if ( strpos( $email, '@', 1 ) === false ) 
		return false;
	
	// Split out the local and domain parts
	list( $local, $domain ) = explode( '@', $email, 2 );
	
	// LOCAL PART
	// Test for invalid characters
	if ( !preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local ) ) 
		return false;
		
	// DOMAIN PART
	// Test for sequences of periods
	if ( preg_match( '/\.{2,}/', $domain ) ) 
		return false;
	
	// Test for leading and trailing periods and whitespace
	if ( trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) 
		return false;
	
	// Split the domain into subs
	$subs = explode( '.', $domain );
	
	// Assume the domain will have at least two subs
	if ( 2 > count( $subs ) ) 
		return false;
	
	// Loop through each sub
	foreach ( $subs as $sub ) {
		// Test for leading and trailing hyphens and whitespace
		if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub ) 
			return false;
		
		// Test for invalid characters
		if ( !preg_match('/^[a-z0-9-]+$/i', $sub ) ) 
			return false;
	}
	
	// Congratulations your email made it!
	return $email;
}

function qrman_status( $show = false ) {
		global $qrdb;
		
		$tbl = array ( 'bookmarks', 'urls' , 'texts', 'telephones', 'sms', 'emails', 'emsg', 'mecard', 'vcard', 'geo' );
		$tblcol = array ( 'Bookmark', 'Url' , 'Text', 'Telephone', 'Sms', 'Emails', 'Email Msg', 'Mecard', 'vCard', 'Geo' );
		
		$admin =  $qrdb->is_admin( );
		$uid = $qrdb->get_userID( );
		
		for( $i = 0; $i < count( $tbl ); $i++ ) {
			//$colid = getcol_id( $tbl[ $i ] );
			if ( $admin )
				$sqlcount = "SELECT COUNT( keyword ) as totalrec FROM `" . $qrdb->prefix . $tbl[ $i ] . "`";
			else	
				$sqlcount = "SELECT COUNT( keyword) as totalrec FROM `" . $qrdb->prefix . $tbl[ $i ] . "` WHERE `uid`=" . $uid ;
				$totals = $qrdb->get_row( $sqlcount );
				$tabl_count[ $tbl[ $i ] ] = $totals->totalrec; 
			if( $show ) {
				if ( $i == 0 ) {
					echo "<div class='statushead'>Total QR Codes</div>";
					echo "<table>";
				}
					echo "<tr> <td class='statcol'>"  .  $tblcol[ $i ] . " </td>  <td class='statcount'> " . $tabl_count[ $tbl[ $i ] ] . " </td> </tr>";
			}
		}
			if( $show ) 
				echo "</table>";
		return $tabl_count ;
}

function qrgen_favicon( $echo = true ) {
	
	$favicon = qrgen_site_url( false ) . '/images/favicon.ico';
	
	if( $echo )
	echo $favicon;
	
	return $favicon;
}

function qrgen_html_head( $title = '', $stat = false ) {
	?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<?php $title = $title ? $title : get_site_title() ?>
	<title><?php echo $title ?></title>
	<link rel="shortcut icon" href="<?php qrgen_favicon( ); ?>" /> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1" />
	<meta name="author" content="Pelister @ http://techlister.com/" />
	<meta name="generator" content="QRMAN <?php echo QRGEN_VERSION ?>" />
	<meta name="description" content="Url Shortener and Qr Code generator | <?php qrgen_site_url( ); ?>" />
	
	<link rel="stylesheet" href="<?php qrgen_site_url( ); ?>/css/style.css" type="text/css" >
	<link rel="stylesheet" href="<?php qrgen_site_url( ); ?>/css/datepicker_metallic.css" type="text/css" >
		
	<!--<script src="<?php qrgen_site_url( ); ?>/js/jquery-1.7.2.min.js" type="text/javascript"></script> -->
	<script src="http://code.jquery.com/jquery-1.8.3.min.js" type="text/javascript"></script>
	<!--<script src="http://code.jquery.com/jquery-1.7.2.min.js" type="text/javascript"></script> -->
	<script src="<?php qrgen_site_url( ); ?>/js/tablesort.min.js" type="text/javascript"></script>
	<script src="<?php qrgen_site_url( ); ?>/js/datepicker.js" type="text/javascript"></script>
	<script src="<?php qrgen_site_url( ); ?>/js/qrfunctions.js" type="text/javascript"></script>
	<script src="http://maps.googleapis.com/maps/api/js?sensor=true" type="text/javascript"></script>
	<?php qrgen_call_funcs( 'page_scripts' ); ?>
	
	<?php if( $stat ) { ?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<?php } ?>
	
	<script type="text/javascript">
	//<![CDATA[
		var frmgenurl  = '<?php qrgen_site_url( ) ; ?>/includes/form_generator.php' ;
		var qrgenurl    = '<?php qrgen_site_url( ); ?>/includes/qr-generator.php' ;
		var qrmanurl = '<?php qrgen_site_url( ); ?>/includes/qrmanager.php';
		var imginfo = '<?php qrgen_site_url( ); ?>/includes/imginfo.php';
		var filtopt = '<?php qrgen_site_url( ); ?>/includes/colfilters.php';
		var siteurl = '<?php qrgen_site_url( ); ?>';
		var loadimg = '<?php qrgen_site_url( ); ?>/images/';
		var map;
		var myLatlng;
		var marker;
	//]]>
	<?php if( $stat ) { ?>
	 google.load('visualization', '1.0', {'packages':['corechart', 'geochart']});
	 <?php } ?>
	</script>
	
</head>
<body id="page1" >
<div class="tail-top">
	<?php
}

function qrgen_html_logo( ) {
	global $qrdb;
	?>
	
		<header>
		<div class="container">
		<div id="logo">
			<h1>
			<a href="<?php echo qrgen_site_url( false, 'index.php' ) ?>" title="QRMAN"><img src="<?php qrgen_site_url( ); ?>/images/logo.png" alt="QRMAN" title="QRMAN" border="0" style="border: 0px;" /></a>
			</h1>
		</div>
				
				<?php
				if ( !$qrdb->is_logged_in( ) ) { ?>
				<div id="userlog">
				<form action="index.php?action=login" id="login-form" method="post">
				<fieldset>
					<span class="text">
						<input type="text" name="login" value='username' onFocus="if(this.value=='username'){this.value=''}" onBlur="if(this.value==''){this.value='username'}">
					</span>
					<span class="text">
						<input type="password" name="password" value='password' onFocus="if(this.value=='password'){this.value=''}" onBlur="if(this.value==''){this.value='password'}">
					</span>
					<span class="rem">
					<input type='checkbox' name='remember' value='1'>
					</span>
					<a href="#" class="login" onClick="document.getElementById('login-form').submit()"><span><span>Login</span></span></a>
					<br />
					<div id="registration">
					<ul id="menu_select">
					<li class='login' id='register'>Register</li>
					<li class='login' id='forgot'>Forgot Password?</li>
					</ul>
					</div>
				</fieldset>
			</form>
			</div>
			
		<?php	}  else { ?>
					<div id="userinfo">
					<?php echo "Howdy, " , $qrdb->is_admin( ) ? '<a href="' . QRGEN_SITE . '/admin">' . $qrdb->get_user_login( ) . "!</a>" : $qrdb->get_user_login( ); ?>
						<ul id="menu_select">							
							<li class='login' id='edit'><img title="Profile" src="<?php echo QRGEN_SITE; ?>/images/edit-profile.png" /></li>
							<?php if ( $qrdb->is_admin( ) && defined( 'QRGEN_ADMIN' )) { ?>
							<li class="login" id="setting"><a href='settings.php'><img title="Settings" src="<?php echo QRGEN_SITE; ?>/images/settings.png" /></a></li>
							<?php } ?>
							<li class='login' id='logout'><a href='?action=logout'><img title="Logout" src="<?php echo QRGEN_SITE; ?>/images/logout.png" /></a></li>
						</ul>
					</div>
			<?php } ?>
			<div class="clear"></div>
			<div class="left"></div>
			<div class="header-box">
						<nav>
							<ul>
								<li class="current"><a href="<?php qrgen_site_url(); ?>/index.php">Home</a></li>
								<li><a href="<?php qrgen_site_url(); ?>/pages/features.php">Features</a></li>
								<li><a href="<?php qrgen_site_url(); ?>/docs/index.html" target="_blank">Documentation</a></li>			
							</ul>
						</nav> 
			</div> 
			<div class="right"></div>
			
		</div>
	</header>
	<section id="content">
		<div class="qrinterface">
			<div class="inside">
	
<?php
}
	
function qrgen_html_interface( $show, $data = array() ) {
			global $qrdb;
			 ?>

				<div class="wrapper row-1">
					<div class="box col-2 maxheight">
						<div class="border-right maxheight">
							<div class="border-bot maxheight">
								<div class="border-left maxheight">
									<div class="left-top-corner maxheight">
										<div class="right-top-corner maxheight">
											<div class="right-bot-corner maxheight">
												<div class="left-bot-corner maxheight">
														<div class="inner">
																<h3>Select Type</h3>
																	
																<div id="breadcrumb"> </div>
																<div class="clear"></div>
																<div class="menu">
																<ul id="menu_select" >
																<li class="add" id="bmk" title="bookmark"><img src="<?php echo QRGEN_SITE; ?>/images/bookmark.png" /><p>Bookmark</p></li>
																<li class="add" id="url" title="url"><img src="<?php echo QRGEN_SITE; ?>/images/URL.png" /><p>Url</p></li>
																<li class="add" id="text" title="text"><img src="<?php echo QRGEN_SITE; ?>/images/text.png" /><p>Text</p></li>
																<li class="add" id="telep" title="telephone"><img src="<?php echo QRGEN_SITE; ?>/images/phone.png" /><p>Telephone</p></li>
																<li class="add" id="sms" title="sms"><img src="<?php echo QRGEN_SITE; ?>/images/sms.png" /><p>Sms</p></li>
																<li class="add" id="email" title="email"><img src="<?php echo QRGEN_SITE; ?>/images/Mail.png" /><p>Email Id</p></li>
																<li class="add" id="emmsg" title="email"><img src="<?php echo QRGEN_SITE; ?>/images/Email.png" /><p>Email Message</p></li>
																<li class="add" id="mecard" title="mecard"><img src="<?php echo QRGEN_SITE; ?>/images/mecard.png" /><p>Mecard</p></li>
																<li class="add" id="vcard" title="vcard"><img src="<?php echo QRGEN_SITE; ?>/images/vcard.png" /><p>Vcard</p></li>
																<li class="add" id="geo" title="geo"><img src="<?php echo QRGEN_SITE; ?>/images/geo.png" /><p>Geo</p></li>
																<?php if ( $qrdb->is_admin( ) && defined( 'QRGEN_ADMIN' )) { ?>
																	<li class="add" id="user" title="user"><img src="<?php echo QRGEN_SITE; ?>/images/User.png" /><p>User</p></li>
																<?php } ?>
																</ul>
																</div>
																<div class="clear"></div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="box col-3 maxheight">
						<div class="border-right maxheight">
							<div class="border-bot maxheight">
								<div class="border-left maxheight">
									<div class="left-top-corner maxheight">
										<div class="right-top-corner maxheight">
											<div class="right-bot-corner maxheight">
												<div class="left-bot-corner maxheight">
													<div class="inner">
														<h3>QR Data Sheet</h3>
															<div id="auth"></div>
																<div id="qrcontainer">
																	<div id="qrform">
																	<?php if( !empty( $data )) {
																					echo $data['html'];	
																	}?>
																	</div>
																</div>
															<div id="gmap" ></div>	
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="box col-4 maxheight">
						<div class="border-right maxheight">
							<div class="border-bot maxheight">
								<div class="border-left maxheight">
									<div class="left-top-corner maxheight">
										<div class="right-top-corner maxheight">
											<div class="right-bot-corner maxheight">
												<div class="left-bot-corner maxheight">
													<div class="inner">
														<h3>QR Code</h3>
															<div id="status">
																<div id="tabs">
																	<ul  id="tabqrtyp" class="qrcodetabs" >
																			<li><a href="#qr_typ_qrcode">Non - Trackable</a></li>
																			<li><a href="#qr_typ_trkcode">Trackable</a></li>
																	</ul>
																</div>
																		<?php if( !empty( $data )) {
																						$tab = 0;
																						if( !empty( $data['qrimage'] ) && ( $dim = image_info( $data['qrimage'] )) ) {
																							$sz = $dim['width'] > 210 ? 210 : $dim['width']; 
																							$tab = 1;
																						?>
																							<div id='qrcode' class='typ'><img id='qrimage' src='<?php echo $data['qrimage']; ?>' width='<?php echo $sz; ?>' height='<?php echo $sz; ?>'></div>																							
																		<?php		} else { ?> <div id='qrcode' class='typ'></div> <?php }
																						if ( !empty( $data['trimage'] ) && ( $dim = image_info( $data['trimage'] )) ) { 
																								$sz = $dim['width'] > 210 ? 210 : $dim['width'];	
																								$tab = ( $tab ) ? $tab : 2;
																						?>																							
																							<div id='trkcode' class='typ'><img id='trimage' src='<?php echo $data['trimage']; ?>' width='<?php echo $sz; ?>' height='<?php echo $sz; ?>'></div>
																		<?php		} else { ?> <div id='trkcode' class='typ'></div> <?php }
																						echo "<script> qrtab_select( $tab ); </script>";	
																					} else { ?>
																							<div id='qrcode' class='typ'><img id='qrimage' src='<?php qrgen_site_url( ); ?>/images/default.png' width='230' height='230'></div>  
																							<div id='trkcode' class='typ'></div>
																		<?php	}	?>					
																		
															</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>	
																	
	<?php

}

function qrgen_show_content( ) {
	global $qrdb;
	global $qrtable;
		?>
	
					<div class="inside1">
					<div class="wrap row-2">
						<div class="qrfilters">
							<div class="sthead" >
							<h3>Statistics</h3>
							</div>
							<ul class="filters">
								<li>
									<?php 
									if ( $qrdb->is_logged_in( ))
											qrman_status( true );
									?>
								</li>
								<li>
								<img src="images/qrcodes.png"><p>Quickly and easily create QR Codes</p><a href="#"><b>Read More</b></a>
								</li>
								<li><img src="images/management.png"><p>Manage your QR Codes any time, any where</p><a href="#"><b>Read More</b></a></li>
								
							</ul>
						</div>
						<div class="qrtable">
							
							<?php if( $qrdb->is_logged_in( ) ) { ?>
							<div class="qrmanhead">
							<h3>QR Code Manager</h3>
							</div>
							<div id="qrmsg"></div>
							<form id="tblfilter" action="" method="post">
								 
								<div id="filabls"><span>Filter By</span>
									<select id="qroption" name="fltype">
										<option value="bookmarks" selected="selected">Bookmarks</option>
										<option value="urls">Urls</option>
										<option value="texts">Texts</option>
										<option value="telephones">Telephones</option>
										<option value="sms">Sms</option>
										<option value="emails">Email Id</option>
										<option value="emsg">Email message</option>
										<option value="mecard">Mecard</option>
										<option value="vcard">Vcard</option>
										<option value="geo">Geo</option>
										<?php 
										if( $qrdb->is_admin( ) && defined( 'QRGEN_ADMIN' )) { ?>
											<option value="user">Users</option>
										<?php } ?>
									</select>
								</div>
								
								<div id="shrows">
									<span>Show </span><input type="text" name="nrows" size="2" ><span> rows</span>
								</div>
								<div id="qrsearch">
									<input type="text" name="qrfind">
										<span>Search In </span>
										<select id="searchin" name="searchcol">
											<option value="title">Title</option>
											<option value="url" selected="selected">Url</option>
										</select>
									
									<input type="submit" name="submit" value="Search">
								</div>
							</form>
							<?php } else { ?>
							<div class="qrmanhead">
							<h3>QR Code Manager</h3>
							</div>
							<div "defcont"> 
							<p>The QR code was invented in Japan by the Toyota subsidiary Denso Wave in 1994 to track vehicles during manufacture. It was designed to allow high-speed component scanning. It has since become one of the most popular types of two-dimensional barcodes
							QR Code management made easy. </p><p>Register and start creating qrcodes. </p>
							</div>
							<?php } ?>
							<div class="clear"></div>
							<div id="qrmanager">
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</div>
	
<?php
}

function qrgen_footer( ) {
?>
			</div>
		</div>
	</section>
</div>
<div class="aside">
	<div class="qrfooter">



							<div class="fcol1">
			<!--
								<a title="anim.me" href="http://anim.me" target="_blank">
									<img title="Anim.me" alt="Url Shortner" src="http://anim.me/images/animme.jpg" border="0" height="53" width="172">
								</a>															
				-->
							</div>
							<div class="fcol2">
			<!--
								<a title="techlister.com" href="http://techlister.com" target="_blank">
									<img alt="Web Design" src="http://techlister.com/wp-content/themes/trance/images/techlister_logo.jpg" height="53" width="234">
								</a>
				-->
							</div>
							<div class="fcol3">
			<!--
								<a href="http://animium.com/">
									<img alt="Free 3D Models for Maya and 3DS MAX" src="http://animium.com/wp-content/themes/logo.png" height="53" width="234">
								</a>
			-->
							</div>
							<div class="fcol4">
		
								Copyrights <a class="new_window" href="http://techlister.com/" target="_blank" rel="nofollow">http://techlister.com</a>

							</div>
							<div class="fcol5">
								
							</div>



	</div>
</div>
<!-- footer -->
<footer>
	<div class="copyr">
		<div class="inside">
			
		</div>
	</div>
</footer>

</body>
</html>

<?php 
}

function show_login_head( )	{
						
						global $qrdb;
						$str = "<ul id='type_select' >"; 
						if ( $qrdb->is_logged_in( ) ) {
								$str .= "<li class='login' id='edit'>Welcome " . $qrdb->get_user_login( ) . "! </li>";
								$str .= "<li class='login' id='logout'><a href='?action=logout' title='Logout'>Logout</a></li>";
								//echo "<li class='login' id='#edit'><a href='?action=edit' title='Edit'>Edit Profile</a></li>";
						}
						else{
								//$str .= "<li class='login' id='login'><a href='?action=login' title='Login'>Login</a></li>";
								$str .= "<li class='login' id='login'>Login</li>";
								$str .= "<li class='login' id='register'>Register</li>";
						}
		
					//echo "<li class='login' id='show'><a href='?action=show' title='Login'>Show Data</a></li>";
					$str .= "</ul>";
					return $str;
}

function qrgen_tick( ) {
	return ceil( time( ) / QRGEN_FORM_LIFE );
}

function qrgen_salt( $string ) {
	$salt = defined( 'QRGEN_COOKIEKEY') ? QRGEN_COOKIEKEY : md5( __FILE__ ) ;
	return md5 ( $string . $salt );
}


function qrgen_create_nonce( $action, $user = false ) {
	global $qrdb;
	if( false == $user )
		$user = $qrdb->is_logged_in( ) ? $qrdb->get_user_login( ) : '-1'; 
	$tick = qrgen_tick( );
	$str = substr( qrgen_salt( $tick . $action . $user ), 0, 10 );
	return $str;
}

// Create a nonce field for inclusion into a form
function qrgen_nonce_field( $action, $name = 'nonce', $user = false, $echo = false ) {
	$field = '<input type="hidden" id="'.$name.'" name="'.$name.'" value="' .qrgen_create_nonce( $action, $user ).'" />';
	if( $echo )
		echo $field."\n";
	return $field;
}

// Check validity of a nonce (ie time span, user and action match).
// Returns true if valid, dies otherwise.
// if $nonce is false or unspecified, it will use $_REQUEST['nonce']
function qrgen_verify_nonce( $action, $nonce = false, $user = false, $return = '' ) {
	global $qrdb;
	// get user
	if( false == $user )
		$user = $qrdb->get_user_login( );
	// get current nonce value
	if( false == $nonce && isset( $_REQUEST['nonce'] ) )
		$nonce = $_REQUEST['nonce'];

	// what nonce should be
	$valid = qrgen_create_nonce( $action, $user );
	
	if( $nonce == $valid ) {
		return true;
	} else {
		return false ;
	}
}

function generate_rand_pass( $length = 12, $special_chars = true, $extra_special_chars = false ) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	if ( $special_chars )
	$chars .= '!@#$%^&*()';
	if ( $extra_special_chars )
	$chars .= '-_ []{}<>~`+=,.;:/?|';
	
	$password = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$password .= substr( $chars, rand( 0, strlen($chars) - 1 ), 1);
	}
	return $password;
}

function origtext( $type ) {

$litext = array ( 
				'bookmarks' => 'Bookmark', 
				'urls'=> 'Url', 
				'texts' =>  'Text' ,
				'telephones' => 'Telephone',
				'sms' => 'Sms',
				'emails' => 'Email Id', 
				'emsg' => 'Email Message',
				'mecard' => 'Mecard',
				'vcard' => 'Vcard',
				'geo' => 'Geo',
				'user' => 'User'
			);
	return $litext[ $type ];
}

function getcol_id( $type ) {

	 $qrid = array ( 'bookmarks' => 'bmid', 'urls' => 'urlid', 'texts' => 'txid' , 'telephones' => 'telid' , 'sms' => 'smsid', 'emails' => 'emid', 'emsg' => 'emsid', 'mecard' => 'mecid', 'user' => 'userID' );
	 
	 return $qrid[ $type ];
}

function html_type( $data, $type ) {

	$title = origtext( $type );
	$return['html'] = "<div id='qrdatv'><table id='qrtblview'><thead><tr><th>";
	$return['html'] .= "$title created on " . date( "M d, Y H:i", $data[ 'created' ] ) ;
	$return['html'] .= "</th></tr></thead>";
	
		switch( $type ) {
					
			case 'emails':
					$return['html'] .= "<tr><td><span>Email</span>" . $data[ 'email' ] . "</td></tr>";
					break;
			case 'texts':
					$return['html'] .= "<tr><td><span>Text</span>" . $data[ 'text' ] . "</td></tr>";
					break;
			case 'emsg':
					$return['html'] .= "<tr><td><span>Email</span>" . $data[ 'email' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Subject</span>" . $data[ 'subject' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Body</span>" . $data[ 'body' ] . "</td></tr>";
					break;
			case 'sms':
					$return['html'] .= "<tr><td><span>Telephone</span>" . $data[ 'telno' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Message</span>" . $data[ 'sms_text' ] . "</td></tr>";
					break;
			case 'telephones':
					$return['html'] .= "<tr><td><span>Telephone</span>" . $data[ 'telno' ] . "</td></tr>";
					break;
			case 'geo':
					$return['html'] .= "<tr><td><span>Latitude</span>" . $data[ 'latitude' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Longitude</span>" . $data[ 'longitude' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Altitude</span>" . $data[ 'altitude' ] . "</td></tr>";
					break;		
			case 'mecard':
			case 'vcard':
					$return['html'] .= "<tr><td><span>Firstname</span>" . $data[ 'firstname' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Lastname</span>" . $data[ 'lastname' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Phone</span>" . $data[ 'wphno' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Email</span>" . $data[ 'email' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Website</span>" . $data[ 'url' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Birthday</span>" . $data[ 'bday' ] . "</td></tr>";
					$return['html'] .= "<tr><td><span>Note</span>" . $data[ 'note' ] . "</td></tr>";
					$return['html'] .= "<tr><td>";
					if( $type === 'vcard' ) {
						$return['html'] .= "<div><span>Home Address</span><br />";
						$return['html'] .= "<ul><li>" . $data[ 'hstreet' ] . "</li>";
						$return['html'] .= "<li>" . $data[ 'hcity' ] . "</li>";
						$return['html'] .= "<li>" . $data[ 'hstate' ] . "</li>";
						$return['html'] .= "<li>" . $data[ 'hzip' ] . "</li>";
						$return['html'] .= "<li>" . $data[ 'hcountry' ] . "</li>";
						$return['html'] .=	"</ul></div>";
					}						
					$return['html'] .= "<div><span>Office Address</span><br />";
					$return['html'] .= "<ul><li>" . $data[ 'wstreet' ] . "</li>";
					$return['html'] .= "<li>" . $data[ 'wcity' ] . "</li>";
					$return['html'] .= "<li>" . $data[ 'wstate' ] . "</li>";
					$return['html'] .= "<li>" . $data[ 'wzip' ] . "</li>";
					$return['html'] .= "<li>" . $data[ 'wcountry' ] . "</li>";
					$return['html'] .=	"</ul></div>";
					$return['html'] .= "</td></tr>";
		}
		
		$return['html'] .= "</table></div>";
		$return['qrimage'] = $data['qrimage'];
		$return['trimage'] = $data['trimage'];
	
	return $return;
}

function image_info( $image ) {
		
				if( empty( $image))
					return false;
					
				$parts = explode( "/", $image );
				$img = $parts[ count( $parts ) - 1 ];
				$userdir = $parts[ count( $parts ) - 2 ];
				
				$imgfile = QRIMAGES_PATH . '/' . $userdir . '/' . $img; 
				$imgname  = QRIMAGES_URL . '/' . $userdir. '/' . $img ;

				if ( file_exists( $imgfile )) {
					list( $width, $height, $type, $attr ) = getimagesize( $imgfile );
					$dim[ 'width' ] = $width;
					$dim[ 'height' ] = $height;
					$dim[ 'imgfile' ] = $imgfile;
					return $dim;
				}
				else 
					return false;
}

function parse_qrimage( $img ) {	
		if( empty( $img ))
			return false;
			
		$parts = explode( "/", $img );
		$img = $parts[ count( $parts ) - 1 ];
		$userdir = $parts[ count( $parts ) - 2 ];
		$imgfile = QRIMAGES_PATH . '/' . $userdir . '/' . $img; 
		return $imgfile;
}				

// the following functions provide simple plugin like interface to hook any additional data at specific points.

function qrgen_add_funcs( $tag, $function ) {
	global $qrgen_filters;
	$qrgen_filters[$tag][$function] = $function;
}

function qrgen_call_funcs( $exec_tag ) {
	global $qrgen_filters;
	
	if( empty( $qrgen_filters ))
		return;	
		
	foreach( $qrgen_filters as $key => $tag ) {
		
		if( $exec_tag === $key ) { 
			foreach( $tag as $func ) 			
				call_user_func( $func );
		}
	}
}

function get_site_title() {
	$title = isset( $_SESSION['site_title'] ) ? $_SESSION['site_title'] : get_option( 'site_title' );
	$_SESSION['site_title'] = $title;
	return $title;
}