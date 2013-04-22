<?php

require_once(  dirname( dirname( __FILE__ ) )  . '/qr-loader.php' );

		$imgfile = $_POST[ 'image' ];
		$trimgfile = $_POST[ 'trimage' ];
						
		$content = image_html( $imgfile, 'qrimage' );
		$trcont = image_html( $trimgfile, 'trimage' );
		$trcont['trwidth'] = $trcont['width'];
		if( $content && $trcont ) {
			$content['trhtml'] = $trcont['html'];
			$content['trwidth'] = $trcont['width'];
			$content['trcstatus'] = 3;
			echo json_encode( $content );
		}
		elseif( $content && !$trcont ) { 
			$content['trcstatus'] = 2;
			echo json_encode( $content );
		}
		else{
			$trcont['trcstatus'] = 1;
			echo json_encode( $trcont );
		}
			

function image_html( $image, $qrctyp ) {
		
				if( empty( $image ))
					return false;
					
				$parts = explode( "/", $image );
				$img = $parts[ count( $parts ) - 1 ];
				$userdir = $parts[ count( $parts ) - 2 ];
				
				$imgfile = QRIMAGES_PATH . '/' . $userdir . '/' . $img; 
				$imgname  = QRIMAGES_URL . '/' . $userdir. '/' . $img ;

				if ( file_exists( $imgfile )) {
					list( $width, $height, $type, $attr ) = getimagesize( $imgfile );
					$t = time( );
					$str['html'] = "<img id='" . $qrctyp . "' src='" . $imgname . "?" . $t . "'" . $attr . "/>" ;
					$str['html'] .= "<div id='imginfo'> .png  ";
					$str['html'] .= "<span> ".$width."px </span> X";
					$str['html'] .= "<span> ".$height."px</span><br />";
					$str['html'] .= "<a href='".$imgname."' class='button'><img src='" . QRGEN_SITE . "/images/download.png' width='140' height='31' /></a>";
					$str['html'] .= "</div>";
					$str['width'] = $width;
					$str['status'] = 'success';
					return $str;
				}
				else 
					return false;
}				
?>