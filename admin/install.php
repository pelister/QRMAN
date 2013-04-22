<?php

define( 'QRGEN_INSTALLING', true );

require_once(  dirname( dirname( __FILE__ ))  . '/qr-loader.php' );
require_once(  QRGEN_INC  . '/functions-install.php' );

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;

function display_setup_head() {
	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>QR Manager Config Setup</title>
		<link rel="stylesheet" href="css/install.css?ver=<?php echo QRGEN_VERSION; ?>" type="text/css" />
	</head>
	<body>
		<h1 id="logo"><img alt="QR Manager" src="images/logo.png" /></h1>
		<?php
}

function qrgen_setup_form( $error = null ) {
	
	global $qrdb;
		
	$qrgen_title = isset( $_POST['qrgen_title'] ) ? trim( stripslashes( $_POST['qrgen_title'] ) ) : '';
	$user_name = isset($_POST['user_name']) ? trim( stripslashes( $_POST['user_name'] ) ) : 'admin';
	$admin_password = isset($_POST['admin_password']) ? trim( stripslashes( $_POST['admin_password'] ) ) : '';
	$admin_email  = isset( $_POST['admin_email']  ) ? trim( stripslashes( $_POST['admin_email'] ) ) : '';
	
	if ( ! is_null( $error ) ) {
	?>
	<p class="message"><?php printf( '<strong>ERROR</strong>: %s' , $error ); ?></p>
<?php } ?>
		<form id="setup" method="post" action="install.php?step=2">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="qrgen_title"><?php echo 'Site Title' ; ?></label></th>
					<td><input name="qrgen_title" type="text" id="qrgen_title" size="25" value="<?php echo $qrdb->escape( $qrgen_title ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="user_name"><?php echo 'Username'; ?></label></th>
					<td>
						<input name="user_name" type="text" id="user_login" size="25" value="<?php echo $qrdb->escape( $user_name ); ?>" />
						<p><?php echo 'Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods and the @ symbol.' ; ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="admin_password"><?php echo 'Password, twice'; ?></label>
						<p> </p>
					</th>
					<td>
						<input name="admin_password" type="password" id="pass1" size="25" value="" />
						<p><input name="admin_password2" type="password" id="pass2" size="25" value="" /></p>
						<div id="pass-strength-result"><?php echo 'Strength indicator'; ?></div>
						<p><?php echo 'Hint: The password should be at least six characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).' ; ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="admin_email"><?php echo 'Your E-mail' ; ?></label></th>
					<td><input name="admin_email" type="text" id="admin_email" size="25" value="<?php echo $qrdb->escape( $admin_email ); ?>" />
					<p><?php echo 'Double-check your email address before continuing.' ; ?></p></td>
				</tr>
			</table>
			<p class="step"><input type="submit" name="Submit" value="<?php echo 'Install QR Generator' ; ?>" class="button" /></p>
		</form>
		<script type='text/javascript'>
			/* <![CDATA[ */
			var pwsL10n = {"empty":"Strength indicator","short":"Very weak","bad":"Weak","good":"Medium","strong":"Strong","mismatch":"Mismatch"};
			/* ]]> */
		</script>
		<script type='text/javascript' src='<?php echo QRGEN_SITE . '/js/jquery-1.8.3.min.js' ?>'></script>
		<script type='text/javascript' src='js/password-strength-meter.js'></script>
		<script type='text/javascript' src='js/user-profile.js'></script>
		<?php
}
	
if ( is_qrgen_installed() && check_tables()) {
	display_setup_head();
	if( !file_exists( QRGEN_ABSPATH.'/.htaccess' ) ) {
		qrgen_create_htaccess();
	}
	die( '<h1>Already Installed</h1><p>You have already installed QR Manager. To reinstall please clear your old database tables first.</p><p class="step"><a href="../index.php" class="button"> Log In </a></p></body></html>' );
}

$curr_php_version    = phpversion();
$curr_mysql_version  = $qrdb->db_version();

$php_compat     = version_compare( $curr_php_version, $php_version, '>=' );
$mysql_compat   = version_compare( $curr_mysql_version, $mysql_version, '>=' );

if ( !$mysql_compat && !$php_compat )
	$compat = sprintf( 'You cannot install because QR Manager %1$s requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.' , QRGEN_VERSION, $php_version, $mysql_version, $curr_php_version, $curr_mysql_version );
elseif ( !$php_compat )
	$compat = sprintf( 'You cannot install because QR Manager %1$s requires PHP version %2$s or higher. You are running version %3$s.' , QRGEN_VERSION, $php_version, $curr_php_version );
elseif ( !$mysql_compat )
	$compat = sprintf( 'You cannot install because QR Manager %1$s requires MySQL version %2$s or higher. You are running version %3$s.' , QRGEN_VERSION, $mysql_version, $curr_mysql_version );
		
if ( !$mysql_compat || !$php_compat ) {
		display_setup_head();
		die( '<h1> Insufficient Requirements</h1><p>' . $compat . '</p></body></html>' );
}

switch($step) {
		case 0: // Step 1
		case 1: // Step 1, direct link.
			display_setup_head();
?>
		<h1><?php echo 'QR Manager'; ?></h1>
		<p><?php echo 'Welcome to QR Manager installation process!' ; ?></p>
		
		<h1><?php echo 'Information needed'; ?></h1>
		<p><?php echo 'Please provide the following information. Don&#8217;t worry, you can always change these settings later.' ; ?></p>
		
		<?php
			qrgen_setup_form();
			break;
		case 2:

		display_setup_head();
		
		$qrgen_title = isset( $_POST['qrgen_title'] ) ? trim( stripslashes( $_POST['qrgen_title'] ) ) : '';
		$user_name = isset($_POST['user_name']) ? trim( stripslashes( $_POST['user_name'] ) ) : 'admin';
		$admin_password = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
		$admin_password_check = isset($_POST['admin_password2']) ? $_POST['admin_password2'] : '';
		$admin_email  = isset( $_POST['admin_email']  ) ?trim( stripslashes( $_POST['admin_email'] ) ) : '';
		$error = false;
		
		if ( empty( $user_name ) ) {
			qrgen_setup_form( 'you must provide a valid username.' );
			$error = true;
		} elseif ( $user_name != sanitize_user( $user_name )) {
			qrgen_setup_form( 'the username you provided has invalid characters.' );
			$error = true;
		} elseif ( !$admin_password ||  !$admin_password_check ) {
			qrgen_setup_form( 'Passwords cannot be blank. Please try again'  );
			$error = true;
		} elseif ( $admin_password != $admin_password_check ) {
			qrgen_setup_form( 'your passwords do not match. Please try again'  );
			$error = true;
		} elseif( strlen( $admin_password ) < 6 ) {
			qrgen_setup_form( 'password must be alteast 6 characters'  );
			$error = true;
		} else if ( empty( $admin_email ) ){
			qrgen_setup_form(  'you must provide an e-mail address.'  );
			$error = true;
		} elseif ( ! is_valid_email( $admin_email ) ) {
			qrgen_setup_form( 'that isn&#8217;t a valid e-mail address. E-mail addresses look like: <code>username@example.com</code>' );
			$error = true;
		}
		
			if ( $error === false ) {
				$qrdb->show_errors();
				$result = qrgen_install( $qrgen_title, $user_name, $admin_email, $admin_password );
			
			
?>	
			<h1><?php echo  'Success!'; ?></h1>
			
			<p><?php echo 'QR Manager has been installed.' ; ?></p>	
			<table class="form-table install-success">
			<tr>
				<th><?php echo 'Username'; ?></th>
				<td><?php echo sanitize_user( $result['user'] ); ?></td>
			</tr>
			<tr>
				<th><?php echo 'Password' ; ?></th>
				<td><?php
					//if ( ! empty( $password ) && empty( $admin_password_check ))
						echo '<code>'. $result['pass'] .'</code><br />';
				?>
				</td>
			</tr>
			</table>
			<p class="step"><a href="../index.php" class="button"><?php echo 'Log In'; ?></a></p>
<?php
			}
		break;
		
		}
	?>
</body>
</html>
				



		
