<?php
/* User Authorization is done here for login, register and editing of profiles. */

$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
global $qrdb;
global $msg;

$msg = ' ';

	switch( $action ) {
		
			case 'login':
			
					if( ! $qrdb->is_logged_in( ) and isset( $_POST['login'] )) {
									$login = $_POST['login'];
									$pass = $_POST['password'];
									
									$remember = isset( $_POST['remember'] ) && $_POST['remember']  ? true : false ;
									$pass = $qrdb->encrypt_passwd( $pass, $login );
									
									$returned = $qrdb->process_login( $login, $pass , $remember );
							if( $returned ) {
									$msg = "<script>login_message( 'You are logged in', 0 );</script>";
							}
							else {
									 $msg = "<script>login_message( 'Invalid Username or Password', 0 );</script>";
							} 
					
				}
				break;
			
			case 'logout':
			
				if ( $qrdb->log_out( ))
							$msg = "<script>login_message ( 'Logged out! ', 0 ); </script>";		
				break;	
				
			case 'register':
						
						if( !$qrdb->is_logged_in( ) && isset( $_POST['email'] ) ||  $qrdb->is_admin( ) && isset( $_POST['email'] )) {
								$login = $_POST['login'];
								$email = $_POST['email'];
								$pass = $_POST['password'];
								$confpass = $_POST['conf_password'];
								//process the user registration
								$returned = $qrdb->process_registration( $login, $email, $pass, $confpass );
										if( $returned === true ) {
												$msg = "<script>login_message ( 'Registration complete!', 0 ); </script>";
										} else {
												$msg = "<script>login_message ( '$returned', 0 ); </script>";
										}
				} 
					break;
					
			case 'edit':
							
							if( $qrdb->is_logged_in( ) and isset( $_POST['email'] )) {
									
									$userid = $_POST['userID'];							
									$dispname = $_POST['dispname'];
									$email = $_POST['email'];
									$login = $_POST['login'];
									
										 $is_admin = isset ( $_POST['is_admin'] ) ? $_POST['is_admin'] : 0;
																			
									$newpass = $_POST['new_password'];
									$confpass = $_POST['conf_password'];
																
									//process the profile edit
									$returned = $qrdb->process_profile_edit( $userid, $login, $dispname, $email, $is_admin, $newpass, $confpass );
									
									if ( $returned ) {
										$msg =  "<script> login_message( 'Profile Saved', 0 ); </script>";
									}
									
							}
						
					break;		
	
			case 'forgotpass':
				if( isset( $_POST['email'])) {
						$email = $_POST['email'];
						
						if( ( $fpmsg = $qrdb->proc_forgotpass( $email )) === true ) {
							$msg =  "<script> login_message( 'Password sent to your email', 0 ); </script>";
						}
						else {
							$msg =  "<script> login_message( '$fpmsg', 0 ); </script>";
						}
				}
	}
				