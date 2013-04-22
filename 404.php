<?php 
require_once(  dirname( __FILE__ )  . '/qr-loader.php' );	
//require_once(  dirname( __FILE__ ) . '/includes/auth.php');

qrgen_add_funcs( 'page_scripts', 'error_page' );
$html = '';
	qrgen_html_head( "Shorturl and QR Code Generator" );
	qrgen_html_logo( );
?>
<div class="inside">
		
			<div id="features">
				<div id="errpg">404</div>
				<h1>Page Not Found</h1>
				<p>
				   The Page you are looking for doesn't exist or an other error occurred. Go back, or head over to <a href="<?php echo QRGEN_SITE; ?>"> <?php echo QRGEN_SITE; ?> </a> choose a new direction.
				<p>
						
			</div>					
</div>
<?php
	//qrgen_show_content( );
	qrgen_footer( );

function error_page( ) {
		
	//echo '<link rel="stylesheet" href="' . qrgen_site_url( false ) . '/docs/css/layout.css" type="text/css" charset="utf-8" />';
	echo "<link rel='stylesheet' href='" . qrgen_site_url( false ) . "/docs/css/istokweb.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='" . qrgen_site_url( false ) . "/docs/css/stylesheet.css' type='text/css' />\n";
	echo "<style> H1, H2 { font-family: 'courgetteregular'; } #features { margin: 70px auto 0; width: 400px; padding-left: 20px; text-align: center; }  #features H1 { padding-left : 0px } #errpg { margin: 70px auto 0; width: 180px; font-size: 100px } </style>\n";
}