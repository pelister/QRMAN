<?php 
require_once(  dirname( dirname( __FILE__ ))  . '/qr-loader.php' );	
//require_once(  dirname( __FILE__ ) . '/includes/auth.php');

qrgen_add_funcs( 'page_scripts', 'features_page' );
$html = '';
	qrgen_html_head( "Shorturl and QR Code Generator" );
	qrgen_html_logo( );
?>
<div class="inside">
		
			<div id="features">
				<h1>QRMAN - QR Code and Shorturl Generator	</h1>
				<p>
				   QRMAN written entirely in PHP with Ajax interface using jQuery is a simple QR Code and Shorturl Generator.
				   Now you can run your own QR Code Generation and Shorturl service from your domain.
				<p>
		
				<h2>QRMAN Features</h2>
								
				<ul>											
					<li>Open Source software - released under GPL License.</li>
					<li>Generates 10 types of QR Codes.</li>
					<li>Generates Shorturl for every QR Code.</li>
					<li>Generates Tracking Code and Non Tracking Code.</li>
					<li>Create, Edit and Delete QR codes.</li>
					<li>Url Redirection for Urls and Bookmarks - similar to shorturl services.</li>
					<li>Excellent statistics: click reports, referrers tracking, visitors geo-location, platform and browsers.</li>
					<li>Clean Ajax interface.</li>
					<li>API for QR Code Generation, Store QR Codes and Fetch statistics.</li>
					<li>API output in JSON, JSONP and XML.</li>
					<li>Easy Installation - Inspired by Wordpress.</li>
					<li>User Management feature for Admin.</li>
					<li>Registration, Password reset feature for users.</li>
				</ul>	
				
				<h2>Download</h2>
					
					<p>Download QRMAN from techlister.com</p>
			</div>					
</div>
<?php
	//qrgen_show_content( );
	qrgen_footer( );

function features_page( ) {
		
	//echo '<link rel="stylesheet" href="' . qrgen_site_url( false ) . '/docs/css/layout.css" type="text/css" charset="utf-8" />';
	echo "<link rel='stylesheet' href='" . qrgen_site_url( false ) . "/docs/css/istokweb.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='" . qrgen_site_url( false ) . "/docs/css/stylesheet.css' type='text/css' />\n";
	echo "<style> H1, H2 { font-family: 'courgetteregular'; } #features { padding-left: 20px }  #features H1 { padding-left : 0px } </style>\n";
}