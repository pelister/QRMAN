 function login_message( message, msgdiv ) {
		if ( msgdiv == 0 ) {
			$( '#auth' ).hide( ).html( message ).fadeIn( 100 ); 
			$( '#auth' ).html( message ).fadeOut( 3000 ); 
		}
		else {
			$( '#qrmsg' ).hide( ).html( message ).fadeIn( 100 ); 
			$( '#qrmsg' ).html( message ).fadeOut( 3000 ); 
		}		
}

function set_options( val ) {
	var  str = '';
	switch ( val ) {
		case 'bookmarks':
			str += '<option value="url">Url</option>' ;
			str += '<option value="title">Title</option>' ; 
			break;
		case 'urls':
			str += '<option value="url">Url</option>' ; 
			break;
		case 'texts':
			str += '<option value="text">Text</option>' ; 
			break;
		case 'telephones':
			str += '<option value="telno">Tel No</option>' ; 
			break;
		case 'sms':
			str += '<option value="telno">Tel No</option>' ; 
			str += '<option value="sms_text">Sms Txt</option>' ; 
			break;
		case 'emails':
			str += '<option value="email">email</option>' ; 
			break;
		case 'emsg':
			str += '<option value="email">email</option>' ;
			str += '<option value="subject">subject</option>' ;
			str += '<option value="body">body</option>' ;
			break;
		case 'vcard':
			str += '<option value="hphno">Hphone</option>' ;
			str += '<option value="org">Org</option>' ;
			str += '<option value="title">Title</option>' ;
			str += '<option value="hstreet">Hstreet</option>' ;
			str += '<option value="hcity">Hcity</option>' ;
			str += '<option value="hstate">Hstate</option>' ;
			str += '<option value="hzip">Hzip</option>' ;
			str += '<option value="hcountry">Hcountry</option>' ;
		case 'mecard':
			str += '<option value="firstname">firstname</option>' ;
			str += '<option value="lastname">lastname</option>' ;
			str += '<option value="wphno">phone</option>' ;
			str += '<option value="email">email</option>' ;
			str += '<option value="url">url</option>' ;
			str += '<option value="bday">birthday</option>' ;
			str += '<option value="note">note</option>' ;
			str += '<option value="wstreet">street</option>' ;
			str += '<option value="wcity">city</option>' ;
			str += '<option value="wstate">state</option>' ;
			str += '<option value="wzip">zip</option>' ;
			str += '<option value="wcountry">country</option>' ;
			break;
		case 'user':
			str +=  '<option value="user_login">login</option>'
			str +=  '<option value="user_email">email</option>'
			str +=  '<option value="display_name">display</option>'
			break;
		case 'geo':
			str +=  '<option value="latitude">latitude</option>'
			str +=  '<option value="longitude">longitude</option>'
			str +=  '<option value="altitude">altitude</option>'
			
	}
	return str;
}

 $( document ).ready(function( ) {
	
	getdata( 0 );
	
//change event for select box in qrmanager filter area.	
$( "#qroption" ).change( function( ) { 
		var options = set_options( $(this).val( ));
		$( "#searchin" ).html( options );
		getdata( 0 );
				
});
	 
	 $(document).on( 'click', 'a.details' ,function(){
		 var target = $(this).attr('id').replace('more_', 'details_');
		 $('#'+target).toggle();
		 return false;	
	 });
	
	$( document ).on( 'click', 'ul.qrcodetabs li a', function() {
		var ref = $(this).attr( 'href' ).replace( '#', '' ); // 'qr_typ_qrcode'
		var divcl = ref.split('_')[1]; // 'typ'
		var divid = ref.split('_')[2]; // 'qrcode'
		$( 'div.'+divcl ).css( 'display', 'none' );
		$( 'div#'+divid ).css( 'display', 'block' );
		$( 'ul.qrcodetabs li a').removeClass( 'selected' );
		$( 'ul.qrcodetabs li a[href="#' + ref + '"]' ).addClass( 'selected' ).css( 'outline', 'none' ).blur();
		return false;
	});
	
//hover for li elements	
$( "div.menu #menu_select li" ).hover( function ( ) {
		var  mytext = $( this ).text( );
		$( "#breadcrumb" ).hide().html( "<div id='menumsg'>" + mytext + "</div>" ).fadeIn( 0 );	
});

	
// click event for li elements that generates the action like add, edit and delete.		
$( document ).on('click', "#menu_select li",  function( ) {
		
		var myid = $( this ).attr( 'id' );
		var myclass = $( this ).attr( 'class' );
		var mytext = $( this ).text( );
		var myindex = $( this ).index( );
		var mytitle = $( this ).attr( 'title' );
		
	//alert( "Class: " + myclass + " Id: " + myid + " Text: " + mytext + " Index: " + myindex + "Title: " + mytitle );
	if ( myclass == 'delete' ) {
			if ( confirm('Really delete?')) {
								$( '#qrform' ).fadeOut( 100 );
								$( '#qrcode' ).fadeOut( 100 ).html( ' ' );
								$( '#trkcode' ).fadeOut( 100 ).html( ' ' );
			} else 
				return;
	}
	
	if ( myclass == 'edit' ) {	
			var  img = $( '#qrimg' + myid ).attr( "src" ); 
			var  trimg = $( '#trkimg' + myid ).attr( "src" );	
				$.post( imginfo ,  "image=" + img + "&trimage=" + trimg, 
						function ( imgdata ) {							
							if ( imgdata.status == 'success' ) {
								//alert( imgdata.trcstatus );
									insert_image( imgdata.trcstatus, imgdata.html, imgdata.trhtml );
									if ( imgdata.width > 220 ) 		
										$( "div#qrcode img#qrimage" ).css( "width", "220px" ).css( "height", "220px" );
									if( imgdata.trwidth > 220 )
										$( "div#trkcode img#trimage" ).css( "width", "220px" ).css( "height", "220px" );
							}
						}, "json");
	}
		// form generator ajax
		$('#qrform').html( '<div id="ajaxload"><p><br /><img src="' + loadimg + 'submit.gif" /></p></div>' ); 
		$.post( frmgenurl ,  { "action": myclass, "type": mytext, "id" : myid },
				    
						function ( data ) {
								
							if ( myclass == 'delete' ) {	
									login_message( data, 1 );
									$( "#gradient-style tr#" + myid ).fadeOut( 500, function( ) {
									$( "#gradient-style tr#" + myid ).remove( );
								});
							}
							else {
								$( '#qrform' ).hide( ).html( data ).fadeIn( 150 ); 
								$( '#bday' ).Zebra_DatePicker();
							}
							if ( myid.split('_')[0] == 'geo' || myid == 'geo' ) {
								
								var lat = $('#latbox').val();
								var lng = $('#lngbox').val();
								$('#map_canvas').remove();
								$('#gmap').append( '<div id="map_canvas" style="width: 540px; height: 400px"> </div>' );		
								
								if( lat && lng )
								myLatlng = new google.maps.LatLng( parseFloat( lat ), parseFloat( lng ) ); // 13.0366,80.2523
								else
								myLatlng = new google.maps.LatLng( 13.0366,80.2523 ); // 13.0366,80.2523
								initialize( );
								$('#map_canvas').show();	
								
							}
							else {
								$('#map_canvas').remove();	
								marker.setMap(null);
							}
				}); 	
});
	
//form submit event 	
$( document ).on( 'submit', '#qrgen', function ( e ) {
        
			e.preventDefault( ); 
			var  form = $( this );
			
			var bclicked = $( this ).find( "input[type='submit']" );
			var  clickedbutton = bclicked.attr( "value" );
			var clickedid	= bclicked.attr( "id" ); 
			 bclicked.hide();
			 $('#fsub').html( '<div id="ajaxload"><p><img src="' + loadimg + 'ajax-loader.gif" /></p></div>' ); 
			//alert( 'submit ' + clickedid );
		$.post( qrgenurl  , "action=" + clickedid + "&" + form.serialize( ),
							function ( data ) {
							//alert(data);
								if ( data.status == 'success' ) {					
										//$( '#qrform' ).fadeOut( 100 );
										$( '#qrform' ).remove(".ssbuttons");
										$( '#qrform' ).html( data.share );
										$( '#map_canvas' ).hide();
										insert_image( data.trcstatus, data.html, data.trhtml );
										
									if ( data.width > 220 ) 
										$( "div#qrcode img#qrimage" ).css( "width", "220px" ).css( "height", "220px" );
									if ( clickedid == 'edit' ) 
											login_message( data.okmsg, 1 );
									$( '#qroption' ).val( data.qrtype ).change( );
									
								} else {
									$( '#qrform' ).fadeOut( 100 );
									$( '#qrcode' ).hide( ).html( data.html ).fadeIn( 100 ); 
								}
								
							}, "json" ); 
		
});
	
	$( 'ul.qrcodetabs li a:first' ).click();

	 $('#historical_clicks li:odd').css('background', '#DCDCDC');
	
		// tooltip for Mecard and Vcard
		$( document ).on( 'mouseover', '#mecinfo img', function( e ) {
	
			var titleText = $(this ).attr( 'title' );
			$(this ).data( 'tiptext', titleText).removeAttr( 'title' ).attr( 'title', ''); ;
		 
			$( '<p class="tooltip"></p>' )
			.text( titleText )
			.appendTo( 'body' )
			.css( 'top', ( e.pageY - 10 ) + 'px' )
			.css( 'left', ( e.pageX + 20 ) + 'px' )
			.fadeIn( 'slow' );
		 
		 }).on( 'mouseout', '#mecinfo img', function( e ) { // Hover off event
		 
				$(this ).attr( 'title', $(this ).data( 'tiptext' ));
				$( '.tooltip' ).remove( );
		 
		 }).on( 'mousemove', '#mecinfo img',  function( e ) { // Mouse move event
				$( '.tooltip' ).css( 'top', ( e.pageY - 10) + 'px' ).css( 'left', ( e.pageX + 20) + 'px' );
		});	
		// tooltip end
	 
});

 $( document ).on( 'submit', '#tblfilter', function ( filtr ) {
	
		filtr.preventDefault( );
		getdata( 0 );
});

function qrtab_select( num ) {
	$( 'ul.qrcodetabs li a').removeClass( 'selected' );
	if( num == 1 ) {
		$( 'ul.qrcodetabs li a[href="#qr_typ_qrcode"]' ).addClass( 'selected' );
		$( '#trkcode' ).hide( );
	}
	if( num == 2 )	
		$( 'ul.qrcodetabs li a[href="#qr_typ_trkcode"]' ).addClass( 'selected' );
}

function insert_image( status, qrhtm, trhtm ) {

	$( 'ul.qrcodetabs li a').removeClass( 'selected' );
	if ( status == 1 ) {
		$( '#trkcode' ).hide( ).html( qrhtm ).fadeIn( 100 ); 
		$( '#qrcode' ).hide( ).html( ' ' );
		$( 'ul.qrcodetabs li a[href="#qr_typ_trkcode"]' ).addClass( 'selected' );
	}
	if ( status == 2 ) {
		$( '#qrcode' ).hide( ).html( qrhtm ).fadeIn( 100 ); 
		$( '#trkcode' ).hide( ).html( ' ' );
		$( 'ul.qrcodetabs li a[href="#qr_typ_qrcode"]' ).addClass( 'selected' );
	}
	if ( status == 3 ) {
		$( '#qrcode' ).hide( ).html( qrhtm ).fadeIn( 100 ); 
		$( '#trkcode' ).hide( ).html( trhtm ).fadeIn( 100 ); 
		$( '#trkcode' ).hide( );
		$( 'ul.qrcodetabs li a[href="#qr_typ_qrcode"]' ).addClass( 'selected' );
	}
}

function getdata( pageno ) {                     
	
	var str = $( "#tblfilter" ).serialize( );
	var targetURL = qrmanurl + "?start=" + pageno + "&" + str;  
	//alert( pageno );
	 $('#qrmanager').html( '<div id="ajaxload"><p><img src="' + loadimg + 'ajax-loader.gif" /></p></div>' );  
	$( '#qrmanager' ).load( targetURL, function( ) {
			$( this ).hide( ).fadeIn( 500 ); 
			$( "#qrmanager table" ).tablesort( );
	});

}

function initialize( ) {
	var myOptions = {
		zoom: 8,
		center: myLatlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}	
	map = new google.maps.Map( document.getElementById("map_canvas"), myOptions); 
	google.maps.event.trigger( map, 'resize');	
	
	marker = new google.maps.Marker({
		action: 'clear',
		draggable: true,
		position: myLatlng, 
		map: map,
		title: "Your location"
	});

	google.maps.event.addListener(marker, 'click', function (event) {
		$( "#latbox" ).val( event.latLng.lat() );
		$( "#lngbox" ).val( event.latLng.lng() );
	}); 
			
	google.maps.event.addListener(marker, 'dragend', function (event) {
		$( "#latbox" ).val( this.getPosition().lat() );
		$( "#lngbox" ).val( this.getPosition().lng() );
	});
	
	$('#map_canvas').hide();
}