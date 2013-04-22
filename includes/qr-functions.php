<?php 

include_once( "sql_pdo.php" );

/* Class that manages user data and login, registration and profile edit operations */
	class qrClass extends ezSQL_pdo {
		
				private $user_session;
				private $min_pass_length;
				private $error;
				
					function __construct( ) {
							
							$this->prefix = QRGEN_DB_PREFIX;
							$this->user_session = USERSESSION;
							$this->min_pass_length = MINPASSLENGTH;
							
					}
					
					private function set_error( $msg ){
								$this->error .= $msg."\r\n";
					}
					
					private function get_error( ) {
								return $this->error;
								$this->error = '';
					}
				
					public function show_login_form( $post_to ) {
								
								$str = "<form action='".$post_to."?action=login"."' method='post'>
											<div id='labels'>Username:<br /><input type='text' name='login'></div>
											<div id='labels'>Password:<br /><input type='password' name='password'></div>
											<div id='labels'>Remember me <input type='checkbox' name='remember' value='1'></div>
											<div id='labels'><input type='submit' name='submit' value='Login'></div>
										</form>";
								return $str;
					}
					
					public function process_login( $login, $pass, $remember = false ) {
										
										$sql	= "select userID, user_login from " .$this->prefix. "user where user_login='" .$this->escape( $login ). "' and user_pass='" . $this->escape( $pass ). "'LIMIT 1";
																				
										$userdata = $this->get_row( $sql ); 
											
											if ( $this->num_rows >  0 ) {
													
														if ( $remember ) {
															
															$cookie_auth = $this->rand_string( 10 ) . $userdata->user_login;
															$auth_key = $this->session_encrypt( $cookie_auth );
															$sql = "UPDATE ". $this->prefix."user SET auth_key = '" . $this->escape( $auth_key ) . "' WHERE user_login = '" . $this->escape( $userdata->user_login ) . "' ";
															$res = $this->query( $sql );
															// setcookie( "auth_key", $auth_key, time( ) + 60 * 60 * 24 * 7, "/", QRGEN_SITE , false, false );
															//setcookie( "auth_key", $auth_key, false, "/", false );
															$time = time( ) + QRGEN_COOKIE_LIFE;
															setcookie( "auth_key", $auth_key, $time, '/', parse_url( QRGEN_SITE, 1 ), false, true );
															
														}
																												
														$userinfo = $this->user_info( $userdata->userID );
														session_regenerate_id( true );
														
			 											$_SESSION[$this->user_session]['userID'] = $userinfo['userID'];
														$_SESSION[$this->user_session]['user_login'] = $userinfo['user_login'];
														$_SESSION[$this->user_session]['is_admin'] = $userinfo['is_admin'];
														$_SESSION[$this->user_session]['user_lastactive'] = time( ); 
																										
														return true;
											
											} else {
													return false;
											}
					}
					
					function authorize( )	{
 							
						if( defined( 'QRGEN_INSTALLING' ) && QRGEN_INSTALLING )
							return true;
						
							if( isset ( $_COOKIE[ 'auth_key' ] ) ) {
									
									$auth_key = $this->escape( $_COOKIE[ 'auth_key' ] );
									
									if( !$this->is_logged_in( ) ) {																				
									
										$sql = "SELECT user_login, user_pass FROM ". $this->prefix."user WHERE auth_key = '" . $auth_key . "' LIMIT 1" ;
										$res = $this->query( $sql );
										
											if( $this->num_rows == 0 ) {													
													$time = time( ) - 3600;
													setcookie( "auth_key", "" , $time, '/', parse_url( QRGEN_SITE, 1 ), false, true );
													return false;
											} else {													
													$u = $this->get_row( $sql, ARRAY_A );													
													$this->process_login( $u['user_login'], $u['user_pass'], true );
													return true;
											}
									} 
							}
							else 
								return false;
					}
 
					
					function rand_string( $length ) {
							return generate_rand_pass( $length, true, true );
					}
					
					function session_encrypt( $string ) {
							
							$salt = $this->rand_string( 5 );
							$string = md5( $salt . $string );
							
							return $string;
					}
					
					public function show_registration_form( $post_to ) {
										
								$str = "<form action='".$post_to."?action=register"."' method='post'>
											<fieldset><table>
											<tr><td><p>Login</p></td><td><span class='text'><input type='text' name='login'></span></td></tr>
											<tr><td><p>Email</p></td><td><span class='text'><input type='text' name='email'></span></td></tr>
											<tr><td><p>Password</p></td><td><span class='text'><input type='password' name='password'></span></td></tr>
											<tr><td><p>Confirm Password</p></td><td><span class='text'><input type='password' name='conf_password'></span></td></tr>
											<tr><td></td><td><div id='fsub'><input type='submit' name='submit' value='Register'></div></td></tr>
											</fieldset>
											</form>";
									return $str;
								
					}
					
					public function show_forgotpass( $post_to ) {
						$str = "A new password will be generated for you and sent to the email address
						associated with your account, enter your email <br />
						<form action='$post_to?action=forgotpass' method='post'>
						<fieldset>
						<div id='labels'>email <span class='text'><input type='text' name='email'></span></div>
						<div id='fsub'><input type='submit' name='submit' value='Send email'></div>
						</fieldset></form>";
						return $str;
					}
					
					public function proc_forgotpass( $email ) {
							
							if( $this->email_exists( $email ) ) {
									$user = $this->get_userlogin( $email );
									$reset_pass = $this->rand_string( 8 );
										if( $this->send_newpass( $user, $email, $reset_pass )) {
												$reset_pass = $this->encrypt_passwd( $reset_pass, $user );
												$this->update_userfield( $user, 'user_pass', $reset_pass );
												return true;
										}
										else return "Couldnt send Email";
							} else {
									return "Email not found";
							}
					}
					
					public function get_userlogin( $email ) {
								
							$sql = "select user_login from ".$this->prefix."user where user_email='".$this->escape( $email )."'";
							$res = $this->get_row( $sql, ARRAY_A );
							if( $res )
								return $res['user_login'];
							else
								return false;
					}
					
					function update_userfield( $user, $field, $value ) {
							$sql = "UPDATE " . $this->prefix . "user SET " . $field . " = '$value' WHERE user_login = '$user'";
							return $this->query( $sql );
					}
							
					public function send_newpass( $user, $email, $pass ) {
									$email_from_addr = get_option( 'admin_email' );
									$email_from_name = get_option( 'siteurl' );
									
									$from = "From: " . $email_from_name ." <". $email_from_addr .">";
									$subject = "Your new password on " . QRGEN_SITE ;
									$body = $user.",\n\n"
									."A new password is generated for you on your "
									."request, you can use this new password with your "
									."username to log in to " . QRGEN_SITE . "\n\n"
									."Username: " . $user . "\n"
									."New Password: " . $pass . "\n\n"
									."It is recommended that you change your password "
									."to something that is easier to remember, which "
									."can be done by going to your Account page "
									."after signing in.\n\n";
									
									return mail( $email, $subject, $body, $from );
					}
					
					public function send_welcome( $user, $email, $pass, $api_key ) {
							$email_from_addr = get_option( 'admin_email' );
							$email_from_name = get_option( 'siteurl' );
							
							$from = "From: ". $email_from_name ." <". $email_from_addr .">";
							$subject = "Welcome to " . QRGEN_SITE;
							$body = $user.",\n\n"
							."You've just registered at " . QRGEN_SITE 
							." with the following information:\n\n"
							."Username: ".$user."\n"
							."Password: ".$pass."\n\n"
							."API Key: ". $api_key. "\n\n"
							."If you ever lose or forget your password, a new "
							."password will be generated for you and sent to this "
							."email address, if you would like to change your "
							."email address you can do so by going to the "
							."My Account page after signing in.\n\n";
								 
							return mail( $email, $subject, $body, $from );
					}
					
					function encrypt_passwd( $pass, $login ) {
						$password = str_split( $pass,( strlen( $pass ) / 2 ) + 1 );
						$hash = hash( 'md5', $login . $password[0] . QRGEN_HASH_SALT . $password[1] );
						return $hash;
					}
					
					public function process_registration( $login, $email, $pass, $confpass, $admin = 0 ) {
										
									if( strlen( $email ) >4 ) {
									
											if( strlen( $confpass ) >= $this->min_pass_length ) {
																	
													if( $pass == $confpass ) { 
													
														if( $login || strlen( $login = trim( $login )) != 0) {
				
															if ( !$this->login_exists( $login ) ) {
					
																	if ( !$this->email_exists( $email ) ) {
																	
																		//generate api key for users
																			$passwd = $this->encrypt_passwd( $pass, $login );
																			$api_key = $this->encrypt_passwd( $this->rand_string( 8 ), $login );
																			//everything is fine, lets insert the user into the DB
																			$sql="insert into ".$this->prefix ."user values (
																			'',
																			'".$this->escape( htmlentities( $login, ENT_QUOTES ))."',
																			'".$this->escape( $passwd )."',
																			'".$this->escape( $email )."',
																			'',
																			$admin,
																			".time().",
																			'0',
																			'" . $this->escape( $api_key ) ."')";
																			$res = $this->query( $sql );
																			if( EMAIL_WELCOME )
																				$this->send_welcome( $login, $email, $pass, $api_key );
																			return true;
																	} else {
																			return "email address already registered with another account.";
																	}
															} else {
																	return "Login already exists"	;
															}
														} else {
															return "Login cannot be blank";
														}
													} else {
														return "You didn't type the same password twice.";
													}
											} else {
												
												return strlen( $confpass ) . " Your password must be longer than ".$this->min_pass_length." characters.";
											}
									} else {
											return "A valid email address is required.";
									}
					}
					
					public function delete_account( $userID ) {
								//delete the given account
								if( $this->is_admin( ) ) {
										$sql = "delete from ".$this->prefix."user where userID=".$userID;
											if( $rs = $this->query( $sql ))
											{ 
													return true;
											} else { 
												$this->set_error( "Couldn't delete that record from users table." );
												return false;
											}
								}
					}
	
					public function login_exists ( $login ) {
						
						$sql = "select userID from ".$this->prefix."user where user_login = '".$this->escape( htmlentities( $login, ENT_QUOTES ))."'" ;
						$res = $this->query( $sql );
		
						if ( $this->num_rows > 0 )
								return true;
						else 
								return false;
					}
					
					public function email_exists( $email ){
							//return true if the given email address exists in the system
							$sql="select userID from ".$this->prefix."user where user_email='".$this->escape( $email )."'";
							$res = $this->query( $sql );
							
							if( $this->num_rows > 0 ) 
										return true;
							else
									return false;
					}
					
					public function get_userID( ) {
								//return the userID of the currently logged in user
							return $_SESSION[$this->user_session]['userID'];
					}
					
					public function get_user_login( ) {
								//return the userID of the currently logged in user
							return $_SESSION[$this->user_session]['user_login'];
					}
					
					public function user_info( $userID ) {
							//return all info for the given user
							$sql = "select * from ".$this->prefix."user where userID=".$userID;
							$rs = $this->get_row( $sql, ARRAY_A );
							return $rs;
					}
					
					public function is_logged_in( ) {
							//return true or false
							if( isset( $_SESSION[$this->user_session]['userID'] ) and $_SESSION[$this->user_session]['userID'] > 0 )
								return true;
							else 
								return false;
					}
					
					public function is_admin( ) {
							//return true or false
						if( isset( $_SESSION[$this->user_session]['is_admin'] ) and $_SESSION[$this->user_session]['is_admin'] == 1 )
								return true;
						else
								return false;
					}
						
					public function show_session_data( ) {
			
							echo "Session data<br>";
							echo "User ID: ".$_SESSION[$this->user_session]['userID']."<br>";
							echo "User login: ".$_SESSION[$this->user_session]['user_login']."<br>";
							echo "Last Active: ". date ( "d M Y",  $_SESSION[$this->user_session]['user_lastactive'] )."<br>";
							print_r ( $_COOKIE );
								
					}
					
					public function all_users( ) {
							//return an array of all user's info
						$sql = "select * from " .$this->prefix. "user";
						$r = $this->get_results( $sql, ARRAY_A );
						return $r;
					}
					
					public function log_out( ) {
						
						//log out the current user
						if ( $this->is_logged_in( )) {
								
								$username = $_SESSION[$this->user_session]['user_login'];
								setcookie( "auth_key", "", time( ) - 3600 );
								$sql = "UPDATE ". $this->prefix."user SET auth_key = 0 WHERE user_login = '" . $this->escape( $username ) . "'";
								$this->query( $sql );
						
						// If auth key is deleted from database proceed to unset all session variables
								unset( $_SESSION[$this->user_session] );
								$_SESSION[ $this->user_session ] = array( );
								session_destroy( );
								return true;						
						}
						else 
							return false;
					}
					
					public function show_profile_edit_form( $post_to, $userID = 0, $user = array ( ) ) {
							
								if( $userID == 0)
										$userID = $this->get_userID( );
										
									//make sure the user is logged in
									if( $this->is_logged_in( )) {								
											$goahead = false;
											if( $userID == $_SESSION[ $this->user_session ]['userID'] ) {
														$goahead = true;
											} else {
													if( $this->is_admin( ) ) {
															$goahead = true;
													} else {
															$goahead = false;
													}
											}
										//if it all checks out, do this
										if( $goahead ) {
													
											if( count( $user ) == 0 )
												$user = $this->user_info( $userID );
														
												$str = "<div id='accinfo'>This account was created on " .date("n-j-Y h:ia",$user['user_registered']). "<br />
												Your API Key : " . $user['api_key'] . "</div>
												<form action='".$post_to."?action=edit" ."' method='post'><fieldset>														
												<input type='hidden' name='userID' value='".$userID."'>
												<input type='hidden' name='login' value='".$user['user_login']."'>		
												<div id='labels'><p>Display Name</p>
												<span class='text'>
												<input type='text' name='dispname' value='".$user['display_name']."'>
												</span>
												</div>
												<div id='labels'><p>Email</p>
												<span class='text'>
												<input type='text' name='email' value='".$user['user_email']."'>
												</span>
												</div>";
														
												//if the current user is an administrator, let them give this person admin privileges
											if( $this->is_admin( ) ){
												$str .= "<div id='labels'><p>Administrator</p>
												<input type='checkbox' name='is_admin' value='true' ".( $user['is_admin'] == 1 ? "checked" : "" )."></div> ";
											}
												$str .= "<div id='profmsg'><h5>Leave the following blank if you do not want to change your password<h5></div>
												<div id='labels'><p>New Password</p>
												<span class='text'>
												<input type='password' name='new_password'>
												</span>
												</div>
												<div id='labels'><p>Confirm Password</p>
												<span class='text'>
												<input type='password' name='conf_password'>
												</span>
												</div>
												<div id='fsub'><br /><br />
												<input type='submit' name='submit' value='Save'>
												</div>
												</fieldset>
												</form>";
														
												return $str;
										} else {
												$this->set_error( "You are not allowed to do that" );
												return false;
										}
									} else {
											$this->set_error( "You are not logged in" );
											return false;
									}
					}
					
					public function process_profile_edit( $userID, $login, $dispname, $email, $is_admin, $newpass, $confpass ) {
								
							if( $userID == $this->get_userID( ) or ( $userID != $this->get_userID( ) and $this->is_admin( ) ) ) {
																
								if( strlen ( $email ) >4 ) {												
									$info = $this->user_info( $userID );
																				
									if( !$this->email_exists( $email ) or $email === $info[ 'user_email' ] ) {																				
											$sql = "update ". $this->prefix."user set
													user_email='". $this->escape( $email )."',
													display_name='". $this->escape( htmlentities( $dispname,ENT_QUOTES ) )."'
													".( $this->is_admin( ) ? ", is_admin='".( $is_admin == true ? "1" : "0" ). "'":"" )."
													where userID=".$userID. "";
												
												$rs=$this->query( $sql );
																			
												if( $userID == $this->get_userID( ) ) {
																							
														$userinfo = $this->user_info( $this->get_userID( ) );
														$_SESSION[$this->user_session] = array( );
														$_SESSION[$this->user_session]['userID'] = $userinfo['userID'];
														$_SESSION[$this->user_session]['user_login'] = $userinfo['user_login'];
														$_SESSION[$this->user_session]['is_admin'] = $userinfo['is_admin'];
														$_SESSION[$this->user_session]['user_lastactive'] = time( ); 
												}
									} else {
										return "That email address already exists in our system. Please choose a different one.";
									}
								} else {
										return "A valid email address is required.";
								}
									if( strlen ( $confpass ) >= $this->min_pass_length and strlen( $confpass) > 0 ) {
											if( $newpass == $confpass ) {
													$pass = $this->encrypt_passwd( $newpass, $login );
													$sql="update ".$this->prefix."user set
															user_pass='".$this->escape( $pass )."'
															where userID=".$userID."";
													$rs = $this->query( $sql );
													return true;
											} else {
													return "You didn't type the same password twice.";
											}
									} elseif ( strlen( $confpass )  < $this->min_pass_length ) {
											return "Your password must be longer than ".$this->min_pass_length." characters.";
									}
							} else {
									$this->set_error( "You are not allowed to do that!" );
									return false;
							}
						return true;
					}
							
	}
	
	