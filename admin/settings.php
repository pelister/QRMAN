<?php
define( 'QRGEN_ADMIN', true );
require_once( dirname( dirname( __FILE__ )) . '/qr-loader.php' );	
require_once(  QRGEN_INC . '/auth.php');


if( !$qrdb->is_admin()) {
	qrgen_add_funcs( 'before_die', 'login_form' );
	qrgen_die( "You must be an Administrator to view this page" );
}

	qrgen_html_head( );
	qrgen_html_logo( );

	$msg = '';
	if( isset( $_POST['action'] ) && $_POST['action'] === 'update' ) {
			$settings = array( 'siteurl', 'site_title', 'admin_email' );
			
			foreach( $settings as $set )
				update_option( $set, $_POST[ $set ] );
				
			$_SESSION['site_title'] = $_POST['site_title'];
			$msg = "Settings Saved";
	}	
	
?>
<div class="inside1">
		<div id="qrform">
		<div id="logform">
		<div id="auth"> </div>

		<h3>General Settings</h3><br />
		<form action="settings.php" method="post" >
		<fieldset>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="siteurl"><?php echo 'Site Address (URL)'; ?></label></th>
				<td><span class="text">
					<input name="siteurl" type="text" id="siteurl" value="<?php form_option('siteurl'); ?>" />
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="site_title"><?php echo 'Site Title'; ?></label></th>
				<td><span class="text">
					<input name="site_title" type="text" id="site_title" value="<?php form_option('site_title'); ?>" />
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="admin_email"><?php echo 'E-mail Address'; ?></label></th>
				<td><span class="text">
					<input name="admin_email" type="text" id="admin_email" value="<?php form_option('admin_email'); ?>" />
					</span><br />
				<p class="description"><?php echo 'This address is used for admin purposes, like new user notification.' ; ?></p></td>
			</tr>
		</table>
			<input type="hidden" name="action" value="update" />
			<div id="fsub">
			<input type="submit" name="submit" value="Save"/>
			</div>
			<br />
			</fieldset>
		</form>

		</div>
		</div>
</div>
		<script type="text/javascript">
			var msg = '<?php echo $msg; ?>';
			login_message( msg, 0 );
		</script>
<?php 		
	
qrgen_footer( );

		
