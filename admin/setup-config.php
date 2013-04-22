<?php

define( 'ABSPATH', dirname( dirname( __FILE__ )) . '/' );

require(ABSPATH . '/includes/qr-helpers.php');
require(ABSPATH . '/includes/version.php');
require(ABSPATH . '/includes/functions-install.php');

check_php_mysql_versions();

if ( ! file_exists( ABSPATH . 'qr-config-sample.php' ))
	qrgen_die( 'Sorry, qr-config-sample.php is needed to setup the configurations. Please re-upload this file from your QR Manager installation.' );

$config_file = file( ABSPATH . 'qr-config-sample.php' );	

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

switch( $step ) {
		case 0:
		display_setup_head();
?>
		<p><?php echo "Welcome to QR Manager, the following information about the the database is required to setup the config."; ?> </p>
		<ol>
			<li><?php echo 'Database name' ; ?></li>
			<li><?php echo 'Database username' ; ?></li>
			<li><?php echo 'Database password' ; ?></li>
			<li><?php echo 'Database host' ; ?></li>
			<li><?php echo 'Table prefix, if you want to run more than one QR Manager in a single database' ; ?></li>
		</ol>
		<p><strong><?php echo "If this step doenst work for you simply open the <code>qr-config-sample.php</code> in a text editor and fill the required information, and save it as <code>qr-config.php</code>." ; ?></strong></p>
	<p class="step"><a href="setup-config.php?step=1" class="button"><?php echo 'Continue' ; ?></a></p>
<?php	
		break;
	
		case 1:
			display_setup_head();
		
		?>
		<form method="post" action="setup-config.php?step=2">
			<p><?php echo "enter your database connection details. contact your hosting provider if you dont know what it is." ; ?></p>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="dbname"><?php echo 'Database Name'; ?></label></th>
					<td><input name="dbname" id="dbname" type="text" size="25" value="qrman" /></td>
					<td><?php echo 'The name of the database you want to run QR Manager in.' ; ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="uname"><?php echo 'User Name' ; ?></label></th>
					<td><input name="uname" id="uname" type="text" size="25" value="db user name" /></td>
					<td><?php echo 'Your MySQL DB username' ; ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="pwd"><?php echo 'Password' ; ?></label></th>
					<td><input name="pwd" id="pwd" type="text" size="25" value="db password" /></td>
					<td><?php  echo 'Your MySQL password.' ; ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="dbhost"><?php echo 'Database Host' ; ?></label></th>
					<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
					<td><?php echo 'You should be able to get this info from your web host, if <code>localhost</code> does not work.' ; ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="prefix"><?php echo 'Table Prefix' ; ?></label></th>
					<td><input name="prefix" id="prefix" type="text" value="qrm_" size="25" /></td>
					<td><?php echo 'If you want to run multiple QR Manager installations in a single database, change this.' ; ?></td>
				</tr>
			</table>
			<p class="step"><input name="submit" type="submit" value="<?php echo 'Submit' ; ?>" class="button" /></p>
		</form>
		<?php
		break;
		
		case 2:
		
			foreach ( array( 'dbname', 'uname', 'pwd', 'dbhost', 'prefix' ) as $key )
				$$key = trim( stripslashes( $_POST[ $key ] ) );
			
			$tryagain_link = '</p><p class="step"><a href="setup-config.php?step=1" onclick="javascript:history.go(-1);return false;" class="button">' .  'Try Again'  . '</a>';	
			if ( empty( $prefix ) )
				qrgen_die( '<strong>ERROR</strong>: "Table Prefix" must not be empty.' . $tryagain_link );
				
			if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
				qrgen_die( '<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.' . $tryagain_link );

			define('QRGEN_DB_NAME', $dbname);
			define('QRGEN_DB_USER', $uname);
			define('QRGEN_DB_PASS', $pwd);
			define('QRGEN_DB_HOST', $dbhost);
			define('QRGEN_DB_PREFIX', $prefix);
			define( 'QRGEN_DB_DSN', 'mysql:dbname=' . QRGEN_DB_NAME . ';host=' . QRGEN_DB_HOST );
			
			require_qrgen_db();
			if ( $qrdb->last_error )
				qrgen_die( $qrdb->last_error . ' ' . $tryagain_link);
			
			for ( $i = 0; $i < 2; $i++ ) 
				$salts_keys[] = generate_rand_pass( 64, true, false );
			
			foreach ( $salts_keys as $k => $v ) 
				$salts_keys[$k] = substr( $v, 5, 20 );
			
			$key = 0;
			foreach ( $config_file as &$line ) {		
				
				if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) )
					continue;
				
				$constant = $match[1];
				$padding  = $match[2];
								
				switch ( $constant ) {
					case 'QRGEN_DB_NAME' :
					case 'QRGEN_DB_USER' :
					case 'QRGEN_DB_PASS' :
					case 'QRGEN_DB_HOST' :
					case 'QRGEN_DB_PREFIX' :
						$line = "define('" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "');\r\n";
						break;
					case 'QRGEN_SITE' : 	
						$line = "define('" . $constant . "'," . $padding . "'" . addcslashes( qrgen_guess_url(), "\\'" ) . "');\r\n";
						break;
					case 'QRGEN_COOKIEKEY'         :
					case 'QRGEN_HASH_SALT'  :
						$line = "define('" . $constant . "'," . $padding . "'" . $salts_keys[$key++] . "');\r\n";
						break;
				}
				
			}
			unset( $line );
		if ( ! is_writable(ABSPATH)) {
					display_setup_head();
					
				?>
				<p><?php echo "Sorry, can't write the <code>qr-config.php</code> file."; ?></p>
				<p><?php echo 'You can create the <code>qr-config.php</code> manually and paste the following text into it.'; ?></p>
				<textarea cols="98" rows="15" class="code">
					<?php
					foreach( $config_file as $line ) {
						echo htmlentities( $line, ENT_COMPAT, 'UTF-8' );
					}
					?>
				</textarea>
				<p><?php echo 'After that, click "Run the install."' ; ?></p>
				<p class="step"><a href="install.php" class="button"><?php echo 'Run the install' ; ?></a></p>
				<?php
		} else {
				$handle = fopen( ABSPATH . 'qr-config.php', 'w' );
				foreach( $config_file as $line ) {
					fwrite( $handle, $line );
				}
				fclose( $handle );
				chmod( ABSPATH . 'qr-config.php', 0666 );
				display_setup_head();
				?>
				<p><?php echo "All set!. QR Manager can now connect with your database. " ; ?></p>
				
				<p class="step"><a href="install.php" class="button"><?php echo 'Run the install' ; ?></a></p>
				<?php
		}
			break;				
}
	?>
</body>
</html>