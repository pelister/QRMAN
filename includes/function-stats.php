<?php

//require_once(  dirname( dirname( __FILE__ ) )  . '/qr-loader.php' );


$aggregate = false;

$dates = array();
$list_of_days = array();
$list_of_months = array();
$list_of_years = array();
$last_24h = array();

function get_referrer( $keyword ) {
	
	global $qrdb;
	$referrers = array();
	$direct = $notdirect = 0;
	
	$table = $qrdb->prefix . 'log' ;	
	$query = "SELECT `referrer`, COUNT(*) AS `count` FROM `$table` WHERE BINARY `keyword`='$keyword'  GROUP BY `referrer`;";
	$rows = $qrdb->get_results( $query );
	//$qrdb->debug( );
	
	foreach( (array)$rows as $row ) {
		if ( $row->referrer == 'direct' ) {
			$direct = $row->count;
			continue;
		}
		
		$host = get_domain( $row->referrer );
		if( !array_key_exists( $host, $referrers ) )
		$referrers[$host] = array( );
		if( !array_key_exists( $row->referrer, $referrers[$host] ) ) {
			$referrers[$host][$row->referrer] = $row->count;
			$notdirect += $row->count;			
		} else {
			$referrers[$host][$row->referrer] += $row->count;
			$notdirect += $row->count;				
		}
	}
	arsort( $referrers );
	return array( 'direct' => $direct, 'notdirect' => $notdirect, 'referrers' => $referrers );
}

function sort_referrers( $referrers, $number_of_sites ) {
		
	$referrer_sort = array();
	
	foreach( $referrers as $site => $urls ) {
		if( count($urls) >= 1 || $number_of_sites == 1 )
			$referrer_sort[$site] = array_sum( $urls );
	}
	arsort( $referrer_sort );
	return $referrer_sort;
}

function get_domain( $url, $include_scheme = false ) {
	$parse = @parse_url( $url ); // Hiding errors coming out of badly formed referrer URLs
	
	// Get host & scheme. Fall back to path if not found.
	$host = isset( $parse['host'] ) ? $parse['host'] : '';
	$scheme = isset( $parse['scheme'] ) ? $parse['scheme'] : '';
	$path = isset( $parse['path'] ) ? $parse['path'] : '';
	if( !$host )
	$host = $path;	
	
	if ( $include_scheme && $scheme )
	$host = $scheme.'://'.$host;
	
	return $host;
}

function get_countries_visited( $keyword ) {
	
	global $qrdb;
	$table = $qrdb->prefix . 'log' ;	
	$countries = array();
	$query = "SELECT `country_code`, COUNT(*) AS `count` FROM `$table` WHERE BINARY `keyword`='$keyword'  GROUP BY `country_code`;";
	$rows = $qrdb->get_results( $query );	

	foreach( (array)$rows as $row ) {
			if ( "$row->country_code" )
			$countries["$row->country_code"] = $row->count;
		}
	return $countries;
}

function get_dates( $keyword ) {
	// *** Dates : array of $dates - returned as assoc array list_of_years, list_of_months, list_of_days ***
	global $qrdb;
	$table = $qrdb->prefix . 'log';
	$dates = array( );
	
	$query = "SELECT 
	DATE_FORMAT(`click_time`, '%Y') AS `year`, 
	DATE_FORMAT(`click_time`, '%m') AS `month`, 
	DATE_FORMAT(`click_time`, '%d') AS `day`, 
	COUNT(*) AS `count` 
	FROM `$table`
	WHERE BINARY `keyword`='$keyword' 
	GROUP BY `year`, `month`, `day`;";
	
	$rows = $qrdb->get_results( $query );
	//$qrdb->debug();
	foreach( (array)$rows as $row ) {
		if( !array_key_exists($row->year, $dates ) )
		$dates[$row->year] = array();
		if( !array_key_exists( $row->month, $dates[$row->year] ) )
		$dates[$row->year][$row->month] = array();
		if( !array_key_exists( $row->day, $dates[$row->year][$row->month] ) )
		$dates[$row->year][$row->month][$row->day] = $row->count;
		else
		$dates[$row->year][$row->month][$row->day] += $row->count;
	}	
	
	ksort( $dates );
	foreach( $dates as $year=>$months ) {
		ksort( $dates[$year] );
		foreach( $months as $month=>$day ) {
			ksort( $dates[$year][$month] );
		}
	}
	
	reset( $dates );
	if( $dates ) {
			return $dates;
	} else
		return false;
}

function get_last_24h( $keyword ) {
	
	// *** Last 24 hours : array of $last_24h[ $hour ] = number of click ***
	global $qrdb;
	$table = $qrdb->prefix . 'log';
	$query = "SELECT
	DATE_FORMAT(`click_time`, '%H %p') AS `time`,
	COUNT(*) AS `count`
	FROM `$table`
	WHERE BINARY `keyword`='$keyword' AND `click_time` > (CURRENT_TIMESTAMP - INTERVAL 1 DAY)
	GROUP BY `time`;";
	$rows = $qrdb->get_results( $query );
	//$db->debug();
	$_last_24h = array();
	foreach( (array)$rows as $row ) {
		if ( $row->time )
		$_last_24h[ "$row->time" ] = $row->count;
	}
	
	
	$now = intval( date('U') );
	for ( $i = 23; $i >= 0; $i-- ) {
		$h = date( 'H A', $now - ( $i * 60 * 60 ) );
		// If the $last_24h doesn't have all the hours, insert missing hours with value 0
		$last_24h[ $h ] = array_key_exists( $h, $_last_24h ) ? $_last_24h[ $h ] : 0 ;
	}
	unset( $_last_24h );
	return $last_24h;
}

function get_total_useragents( $keyword ) {
	global $qrdb;
	$table = $qrdb->prefix . 'log' ;	
	
	$query = "SELECT `user_agent` FROM `$table` WHERE BINARY `keyword`='$keyword' ;";
	$uagents = $qrdb->get_results( $query );	
	
	$platform = $browser = array();
	
	foreach( (array)$uagents as $ua ) {
		
		$brdata = getBrowser( $ua->user_agent );
		$browser[ $brdata['name' ] ] = !isset( $browser[ $brdata['name' ] ] ) ? 1 :  $browser[ $brdata['name' ] ] + 1 ;
		$platform[ $brdata['platform' ] ] = !isset( $platform[ $brdata['platform' ] ] ) ? 1 :  $platform[ $brdata['platform' ] ] + 1 ;
	}
	return array( 'browser' => $browser, 'platform' => $platform );
}

function build_list_of_days( $dates ) {
	
	if( !$dates )
	return array();
	
	// Get first & last years from our range. In our example: 2009 & 2009
	$first_year = key( $dates );
	$endyr = array_keys( $dates );
	$last_year  = end( $endyr );
	reset( $dates );
	
	// Get first & last months from our range. In our example: 08 & 09
	$first_month = key( $dates[ $first_year ] );
	$lstmnth = array_keys( $dates[ $last_year ] );
	$last_month  = end( $lstmnth );
	reset( $dates );
	
	// Get first & last days from our range. In our example: 29 & 05
	$first_day = key( $dates[ $first_year ][ $first_month ] );
	$lstday = array_keys( $dates[ $last_year ][ $last_month ] );
	$last_day  = end( $lstday );
	
	// Now build a list of all years (2009), month (08 & 09) and days (all from 2009-08-29 to 2009-09-05)
	$list_of_years  = array();
	$list_of_months = array();
	$list_of_days   = array();
	for ( $year = $first_year; $year <= $last_year; $year++ ) {
		$_year = sprintf( '%04d', $year );
		$list_of_years[ $_year ] = $_year;
		$current_first_month = ( $year == $first_year ? $first_month : '01' );
		$current_last_month  = ( $year == $last_year ? $last_month : '12' );
		for ( $month = $current_first_month; $month <= $current_last_month; $month++ ) {
			$_month = sprintf( '%02d', $month );
			$list_of_months[ $_month ] = $_month;
			$current_first_day = ( $year == $first_year && $month == $first_month ? $first_day : '01' );
			$current_last_day  = ( $year == $last_year && $month == $last_month ? $last_day : qr_days_in_month( $month, $year) );
			for ( $day = $current_first_day; $day <= $current_last_day; $day++ ) {
				$day = sprintf( '%02d', $day );
				$key = date( 'M d, Y', mktime( 0, 0, 0, $_month, $day, $_year ) );
				$list_of_days[ $key ] = isset( $dates[$_year][$_month][$day] ) ? $dates[$_year][$_month][$day] : 0;
			}
		}
	}
	
	return array( 'list_of_days'   => $list_of_days, 'list_of_months' => $list_of_months, 'list_of_years'  => $list_of_years );
}

function getBrowser( $agent = NULL )
{
    $u_agent = $agent ? $agent : $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";
	
    //First get the platform?
	if( !strstr( strtolower( $u_agent ), 'mobile')) {
    if (preg_match( '/linux/i', $u_agent )) 
        $platform = 'linux';
    elseif (preg_match( '/macintosh|mac os x/i', $u_agent ))
        $platform = 'mac';
    elseif ( preg_match( '/windows|win32/i', $u_agent ))
        $platform = 'windows';
	elseif ( preg_match( '/alexa/i', $u_agent ))	
		$platform = 'alexa bot';
	elseif ( preg_match( '/Tweetme/i', $u_agent ))	
		$platform = 'Tweetme Bot(Twitter)';
	elseif( preg_match( '/Twitterbot/i', $u_agent ))	
		$platform = 'Twitter Bot';		
	else 	
		$platform = 'unknown';
	}
	else {
		/* must be mobile */
		$pattern = '/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i'; 
		preg_match( $pattern, $u_agent , $device );
		if( $device )
			$platform = $device [ 0 ];
		else
			$platform = 'Unknown';
		// $platform = 'other OS';
	}
    // Next get the name of the useragent yes seperately and for good reason
	$ub = ' ';
    if( preg_match( '/MSIE/i',$u_agent ) && !preg_match( '/Opera/i', $u_agent )) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif( preg_match( '/Firefox/i', $u_agent )) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif( preg_match( '/Chrome/i', $u_agent )) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif( preg_match( '/Safari/i', $u_agent )) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif( preg_match( '/Opera/i', $u_agent )) {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif( preg_match( '/Netscape/i', $u_agent )) {
        $bname = 'Netscape';
        $ub = "Netscape";
    }
	elseif( preg_match( '/Konqueror/i', $u_agent )) {
        $bname = 'Konqueror';
        $ub = "	Konqueror";
    }
	elseif( preg_match( '/ia_archiver/i', $u_agent )) {
		$bname = 'Alexa Bot';
        $ub = "Alexa bot";
	}
	else {
		$bname = 'unknown';
        $ub = "unknown";
	}
    // finally get the correct version number
    $known = array( 'Version', $ub, 'other' );
    $pattern = '#(?<browser>' . join('|', $known) .  ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all( $pattern, $u_agent, $matches )) {
        // we have no matching number just continue
    }
	
    // see how many we have
	
	if( !empty( $matches )) {
			//echo "<pre>"; print_r ( $matches ); echo "</pre>";
		$i = count( $matches['browser'] );
			if ( $i != 1  && $i != 0 ) {
				//we will have two since we are not using 'other' argument yet
				//see if version is before or after the name
				if ( strripos( $u_agent,"Version" ) < strripos($u_agent, $ub)) {
					$version= $matches['version'][0];
				}
				else {
					$version= $matches['version'][1];
				}
			}
		else {
			//$version= $matches['version'][0];
			$version = 0;
		}
	}
	
    // check if we have a number
    if ( $version==null || $version=="" ) { $version="?"; }
	
    return array(
	'name'      => $bname,
	'version'   => $version,
	'platform'  => $platform
    );
} 


function display_statistics( $keyword, $type ) {
	
	$qrcode_type = origtext( $type );
	$timestamp = get_keyword_timestamp( $keyword );
	$tmpref = get_referrer( $keyword );
	$direct = $tmpref[ 'direct' ];
	$notdirect = $tmpref[ 'notdirect' ];
	$referrers = $tmpref[ 'referrers' ];
	$number_of_sites = count( array_keys( $referrers ) );
	$referrer_sort = sort_referrers( $referrers, $number_of_sites );
	$countries = get_countries_visited( $keyword );
	//print_r( $countries );
	$useragents = get_total_useragents( $keyword );
	$browser = $useragents['browser'];
	$platform = $useragents['platform'];
	$dates = get_dates( $keyword );
	$last_24h = get_last_24h( $keyword );
	
	$stat_content = '<div id="sttabs">
	<div class="status_tabs">
	<ul id="tabqrtyp" class="qrcodetabs">
	<li><a href="#qr_sttyp_stats"><strong>Daily</strong></a></li>
	<li><a href="#qr_sttyp_overall"><strong>Overall</strong></a></li>
	<li><a href="#qr_sttyp_location"><strong>Location</strong></a></li>
	<li><a href="#qr_sttyp_demog"><strong>Map</strong></a></li>
	<li><a href="#qr_sttyp_sources"><strong>Sources</strong></a></li>
	<li><a href="#qr_sttyp_browser"><strong>Browser</strong></a></li>
	<li><a href="#qr_sttyp_platform"><strong>OS</strong></a></li>
	</ul>
	</div></div>';
	
	if( $dates )
		extract( build_list_of_days( $dates ) );
		
	 if ( !empty( $list_of_days )) { 
		 $graphs = array( '24' => 'Last 24 hours', '7'  => 'Last 7 days', '30' => 'Last 30 days', 'all'=> 'All time' );
	
	$do_all = $do_30 = $do_7 = $do_24 = false;
	$hits_all = array_sum( $list_of_days );
	$hits_30  = array_sum( array_slice( $list_of_days, -30 ) );
	
	$hits_7   = array_sum( array_slice( $list_of_days, -7 ) );
	$hits_24  = array_sum( $last_24h );
		if( $hits_all > 0 )
			$do_all = true; // line graph for all days
		 if( $hits_30 > 0 && count( array_slice( $list_of_days, -30 ) ) == 30 )
			$do_30 = true; // line graph for the last 30 days
		 if( $hits_7 > 0 && count( array_slice( $list_of_days, -7 ) ) == 7 )
			$do_7 = true; // line graph for the last 7 days
		 if( $hits_24 > 0 )
			$do_24 = true; // line graph for the last 24 hours
			
	$display_all = $display_30 = $display_7 = $display_24 = false;
			if( $do_24 )
				$display_24 = true;
			elseif ( $do_7 )
				$display_7 = true;
			elseif ( $do_30 )
				$display_30 = true;
			elseif ( $do_all )
				$display_all = true;

	$stat_content .= '<div id="stats" class="sttyp">
		<table border="0" cellspacing="2">
		 <tr><td>
		 <ul id="stats_lines" class="qrcodetabs">';
		 
		 if( $do_24 == true )
			$stat_content .= '<li><a href="#qr_line_st24">Last 24 hours</a></li>';
		 if( $do_7 == true )
			$stat_content .= '<li><a href="#qr_line_st7">Last 7 days</a></li>';
		 if( $do_30 == true )
			$stat_content .= '<li><a href="#qr_line_st30">Last 30 days</a></li>';
		 if( $do_all == true )
			$stat_content .= '<li><a href="#qr_line_stall">All time</a></li>';
		
	$stat_content .= '</ul>';
	
		 foreach( $graphs as $graph => $graphtitle ) {
			 if( ${'do_'.$graph} == true ) {
				 $display = ( ${'display_'.$graph} === true ? 'display:block' : 'display:none' );
				 $stat_content .= "<div id='st$graph' class='line' style='$display'>";
				 $stat_content  .= "<h5>Number of hits : $graphtitle</h5>";
				 switch( $graph ) {
					 case '24':
					 $stat_content .= stats_line( $last_24h, "st$graph" );
					 break;
					 
					 case '7':
					 case '30':
					 $slice = array_slice( $list_of_days, intval( $graph ) * -1 );
					 $stat_content .= stats_line( $slice, "st$graph" );
					 unset( $slice );
					 break;
					 
					 case 'all':
					 $stat_content .= stats_line( $list_of_days, "st$graph" );
					 break;
				 }
				 $stat_content .= "</div>";
			 }			
		 }
		
		$stat_content .= '</td></tr></table></div>';
		
		$stat_content .= '<div id="overall" class="sttyp">
		 <table>
		 <tr>
		 <td>
		 <h5>Overall Click Status</h5>';
		 
		 $ago = round( (date('U') - strtotime( $timestamp )) / (24* 60 * 60 ) );
		 if( $ago <= 1 )
			 $daysago = '';
		 else
			 $daysago = '(about '.$ago .' '.qr_plural( ' day', $ago ).' ago)';
		 
		$stat_content .= '<p><strong>' . $qrcode_type . '</strong> Created on ' . date( "F j, Y @ g:i a", ( strtotime( $timestamp ) + HOURS_OFFSET * 3600 ) ) . $daysago . '</p>';
		$stat_content .= '<div class="wrap_unfloat">';
		$stat_content .= '<ul class="stat_line" id="historical_clicks">';
		 
			 foreach( $graphs as $graph => $graphtitle ) {
				 if ( ${'do_'.$graph} ) {
					 $link = "<a href='#qr_line_st$graph'>$graphtitle</a>";
				 } else {
					 $link = $graphtitle;
				 }
				 $stat = '';
				 if( ${'do_'.$graph} ) {
					 switch( $graph ) {
						 case '7':
						 case '30':
						 $stat = round( ( ${'hits_'.$graph} / intval( $graph ) ) * 100 ) / 100 . ' per day';
						 break;
						 case '24':
						 $stat = round( ( ${'hits_'.$graph} / 24 ) * 100 ) / 100 . ' per hour';
						 break;
						 case 'all':
						 if( $ago > 0 )
						 $stat = round( ( ${'hits_'.$graph} / $ago ) * 100 ) / 100 . ' per day';
					 }
				 }
				 $hits = qr_plural( 'hit', ${'hits_'.$graph} );
				 $stat_content .= "<li><span class='historical_link'>$link</span> <span class='historical_count'>${'hits_'.$graph} $hits</span> $stat</li>";
			 }

		 $stat_content .= '</ul></div>';
		 $stat_content .= '<h5>Best day</h5>';
		 
		 $best = stats_get_best_day( $list_of_days );
		 $best_time['day']   = date("d", strtotime( $best['day'] ) );
		 $best_time['month'] = date("m", strtotime( $best['day'] ) );
		 $best_time['year']  = date("Y", strtotime( $best['day'] ) );
		 
		 $stat_content .= '<p><strong>' . $best['max'] . '</strong>' . qr_plural( 'hit', $best['max'] ) . ' on ' . date("F j, Y", strtotime( $best['day'] ) ) . '.'; 
		 $stat_content .= '<a href="" class="details hide-if-no-js" id="more_clicks">Click for more details</a></p>';
		 $stat_content .= '<ul id="details_clicks" style="display:none">';
		 /*echo "<pre>";
		 print_r( $dates );
		 echo "</pre>"; */
		 foreach( $dates as $year => $months ) {
			 $css_year = ( $year == $best_time['year'] ? 'best_year' : '' );
			 if( count( $list_of_years ) > 1 ) {
				 $li = "<a href='' class='details' id='more_year$year'>Year $year</a>";
				 $display = 'none';
			 } else {
				 $li = "Year $year";
				 $display = 'block';
			 }
			 $stat_content .= "<li><span class='$css_year'>$li</span></li>";
			 $stat_content .= "<ul style='display:$display' id='details_year$year'>";
			 foreach( $months as $month=>$days ) {
				 $css_month = ( ( $month == $best_time['month'] && ( $css_year == 'best_year' ) ) ? 'best_month' : '' );
				 $monthname = date("F", mktime(0, 0, 0, $month,1));
				 if( count( $list_of_months ) > 1 ) {
					 $li = "<a href='' class='details' id='more_month$year$month'>$monthname</a>";
					 $display = 'none';
				 } else {
					 $li = "$monthname";
					 $display = 'block';
				 }
				 $stat_content .= "<li><span class='$css_month'>$li</span></li>";
				 $stat_content .= "<ul style='display:$display' id='details_month$year$month'>";
				 foreach( $days as $day=>$hits ) {
					 $class = ( $hits == $best['max'] ? 'class="bestday"' : '' );
					 $stat_content .= "<li $class>$day: $hits". qr_plural( ' hit', $hits) ."</li>";
				 }
				 $stat_content .= "</ul>";
			 }
			 $stat_content .= "</ul>";
		 }
		 $stat_content .= 	'</ul></td></tr></table></div>';
		 
	 } else {
		 $stat_content .= '<div id="stats" class="sttyp"><p>No traffic yet. Get some clicks first!</p></div>';
	 }
	 
	 if ( $countries ) { 
		 $stat_content .= '<div id="location" class="sttyp">
		 <h5>Traffic location - Top 10 Countries</h5>
		 <table border="0" cellspacing="2">
		 <tr> <td valign="top">';
		 
		 $stat_content .= qr_stats_pie( $countries, 10, '530x300', 'location_pie' ); 
		 $stat_content .= '<p><a href="" class="details hide-if-no-js" id="more_countries">Click for more details</a></p>
		 <ul id="details_countries" style="display:none" class="no_bullet">';
		 foreach( $countries as $code=>$count ) {
			 $stat_content .= "<li><img src='". geo_get_flag( $code )."' /> $code (". geo_countrycode_to_countryname( $code ).") : $count ".qr_plural('hit', $count)."</li>";
		 }		
		 $stat_content .= '</ul></td></tr></table></div>';
		 
		 $stat_content .= '<div id="demog" class="sttyp">
		 <table>
		 <tr>
		 <td valign="top">
		 <div id="mytest">
		 <h5>World traffic</h5>';
		 $stat_content .= stats_countries_map( $countries, 'location_map' ); 
		 $stat_content .= '</div></td></tr></table></div>';
	 } else { 
		$stat_content .= '<div id="location" class="sttyp"><p>No country data.</p></div>';
	} 
	 
	
	if ( $referrers ) { 
		$stat_content .= '<div id="sources" class="sttyp">
		<h5>Traffic Sources & Referrals</h5>
		<table border="0" cellspacing="2">
		<tr><td valign="top">';
		if ( $number_of_sites > 1 )
			$referrer_sort['Others'] = count( $referrers );
			$stat_content .= qr_stats_pie( $referrer_sort, 5, '530x300', 'stat_tab_source_ref' );
			unset( $referrer_sort['Others'] );
		$stat_content .= '<h5>Referring Sites</h5><ul class="no_bullet">';
		$i = 0;
		foreach( $referrer_sort as $site => $count ) {
			$i++;
			$favicon = get_favicon_url( $site );
			$stat_content .= "<li class='sites_list'><img src='$favicon' class='fix_images'/> $site: <strong>$count</strong> <a href='' class='details hide-if-no-js' id='more_url$i'>(details)</a></li>";
			$stat_content .= "<ul id='details_url$i' style='display:none'>";
			foreach( $referrers[$site] as $url => $count ) {
				$stat_content .= "<li>"; $stat_content .= build_html_link( $url ); $stat_content .= ": <strong>$count</strong></li>";
			}
			$stat_content .= "</ul>";
			unset( $referrers[$site] );
		}
		if ( $referrers ) {
			$stat_content .= "<li id='sites_various'>Various: <strong>". count( $referrers ). "</strong> <a href='' class='details hide-if-no-js' id='more_various'>(details)</a></li>";
			$stat_content .= "<ul id='details_various' style='display:none'>";
			foreach( $referrers as $url ) {
				$stat_content .= "<li>"; $stat_content .= build_html_link( key( $url ) ); $stat_content .= ": 1</li>";	
			}
			$stat_content .= "</ul>";
		}
		$stat_content .= '</ul></td></tr></table></div>';
	}
	 else {
		$stat_content .= '<div id="sources" class="sttyp"><p>No referrer data.</p></div>';
	} 
	
	if ( $browser ) { 
		$stat_content .= '<div id="browser" class="sttyp">
		<table border="0" cellspacing="2">
		<tr>
		<td valign="top">
		<h5>Browser Statistics</h5>';
		$stat_content .= qr_stats_pie( $browser, 10, '530x300', 'browser' );
		$stat_content .='<p><a href="" class="details hide-if-no-js" id="more_browsers">Click for more details</a></p>
		<ul id="details_browsers" style="display:none" class="no_bullet">';
		foreach(  $browser as $br => $count ) { 
				$stat_content .= "<li><strong>$br</strong>: $count</li>";
		}		
		$stat_content .= '</ul></td></tr></table></div>';
		
		$stat_content .= '<div id="platform" class="sttyp">
		<table border="0" cellspacing="2">
		<tr>
		<td valign="top">
		<h5>Operating Systems</h5>';
		
		$stat_content .= qr_stats_pie( $platform, 10, '530x300', 'os' );
		$stat_content .= '</td></tr></table></div>';
	} else {
		$stat_content .= '<div id="browser" class="sttyp"><p>No Browser or OS data.</p></div>';
	} 

	return $stat_content;
}


function stats_line( $values, $id = null ) {
		
		// if $id is null then assign a random string
		if( $id === null )
		$id = uniqid ( 'qrgen_stats_line_' );
		
		// If we have only 1 day of data, prepend a fake day with 0 hits for a prettier graph
		if ( count( $values ) == 1 )
		array_unshift( $values, 0 );
		
		// Keep only a subset of values to keep graph smooth
		$values = qr_array_granularity( $values, 30 );
		
		$data = array_merge( array( 'Time' => 'Hits' ), $values );
		$data = google_array_to_data_table( $data );
		
		$options = array(
		"legend"      => "none",
		"pointSize"   => "3",
		"theme"       => "maximized",
		"curveType"   => "function",
		"width"       => 535,
		"height"	  => 200,
		"hAxis"       => "{minTextSpacing: 80, maxTextLines: 1, maxAlternation: 1}",
		"vAxis"       => "{minValue: 0, format: '#'}",
		"backgroundColor" => "none",
		"colors"	  => "['#0C538E','#2a85b3']",
		);
		
		$lineChart = google_viz_code( 'LineChart', $data, $options, $id );
		
		return $lineChart;
}

function qr_stats_pie( $data, $limit = 10, $size = '340x220', $id = null ) {
		
		
		// if $id is null then assign a random string
		if( $id === null )
		$id = uniqid ( 'qr_pie_' );
		
		// Trim array: $limit first item + the sum of all others
		if ( count( $data ) > $limit ) {
			$i = 0;
			$trim_data = array( 'Others' => 0 );
			foreach( $data as $item=>$value ) {
				$i++;
				if( $i <= $limit ) {
					$trim_data[$item] = $value;
				} else {
					$trim_data['Others'] += $value;
				}
			}
			$data = $trim_data;
		}
		
		// Scale items
		$_data = qr_scale_data( $data );
		
		list($width, $height) = explode( 'x', $size );
		
		$options = array(
		'backgroundColor' => 'none',
		//'theme'  => 'maximized',
		'is3D' => 'true',
		'width'   => $width,
		'height'   => $height,
		'colors'    => "['589CE4','37C15A','DD422A','606FD4','F37D06','EB64E0','540E17','19167C','3A5E10','ADD762', 'F99C8E' ]",
		//'legend'     => 'none',
		'chartArea'   => '{top: "2%", height: "95%"}',
		'pieSliceText' => 'label',
		//'pieSliceTextStyle' => '{color: "DCDCDC"}',
		);
		
		
		$script_data = array_merge( array( 'Country' => 'Value' ), $_data );
		$script_data = google_array_to_data_table( $script_data );
		
		$pie = google_viz_code( 'PieChart', $script_data, $options, $id );
		
	return  $pie;
}

function stats_countries_map( $countries, $id = null ) {
		
		// if $id is null then assign a random string
		if( $id === null )
		$id = uniqid ( 'qrgen_stats_map_' );
		
		$data = array_merge( array( 'Country' => 'Hits' ), $countries );
		$data = google_array_to_data_table( $data );
		
		$options = array(
		'backgroundColor' => "white",
		'colorAxis'       => "{colors:['96CFED','96EDB8','EDE296','EDAB96','A096ED','E59FE3','F18F56','3E7DAE','2E72A5','1F669C']}",
		'width'           => "535",
		'height'          => "350",
		'theme'           => 'maximized'
		);
		
		$map = google_viz_code( 'GeoChart', $data, $options, $id );
		
		return $map;
}

function qr_scale_data( $data ) {
	$max = max( $data );
	if( $max > 100 ) {
		foreach( $data as $k=>$v ) {
			$data[$k] = intval( $v / $max * 100 );
		}
	}
	return $data;
}


	
function qr_array_granularity( $array, $grain = 100, $preserve_max = true ) {
		if ( count( $array ) > $grain ) {
			$max = max( $array );
			$step = intval( count( $array ) / $grain );
			$i = 0;
			// Loop through each item and unset except every $step (optional preserve the max value)
			foreach( $array as $k=>$v ) {
				$i++;
				if ( $i % $step != 0 ) {
					if ( $preserve_max == false ) {
						unset( $array[$k] );
					} else {
						if ( $v < $max )
						unset( $array[$k] );
					}
				}
			}
		}
		return $array;
}
	
function google_array_to_data_table( $data ){
		$str  = "var data = google.visualization.arrayToDataTable([\n";
		foreach( $data as $label => $values ){
			if( !is_array( $values ) ) {
				$values = array( $values );
			}
			$str .= "\t['$label',"; 
			foreach( $values as $value ){
				if( !is_numeric( $value ) && strpos( $value, '[' ) !== 0 && strpos( $value, '{' ) !== 0 ) { 
					$value = "'$value'";
				}
				$str .= "$value";
			}		
			$str .= "],\n";
		}
		$str = substr( $str, 0, -2 ) . "\n"; // remove the trailing comma/return, reappend the return
		$str .= "]);\n"; // wrap it up	
		return $str;
}
	
function google_viz_code( $graph_type, $data, $options, $id ) {
		$function_name = 'qrgen_graph' . $id;
		$code  = "\n<script id=\"$function_name\" type=\"text/javascript\">\n";
		$code .= "function $function_name() { \n";
		
		$code .= "$data\n";
		
		$code .= "var options = {\n";
		foreach( $options as $field => $value ) {
			if( !is_numeric( $value ) && strpos( $value, '[' ) !== 0 && strpos( $value, '{' ) !== 0 ) { 
				$value = "\"$value\"";
			}
			$code .= "\t'$field': $value,\n";
		}
		$code  = substr( $code, 0, -2 ) . "\n"; // remove the trailing comma/return, reappend the return
		$code .= "\t}\n";
		
		$code .= "new google.visualization.$graph_type( document.getElementById('visualization_$id') ).draw( data, options );";
		$code .= "}\n";
		$code .= "google.setOnLoadCallback( $function_name );\n";
		$code .= "</script>\n";
		$code .= "<div id=\"visualization_$id\"></div>\n";
		
		return $code;
}

function stats_get_best_day( $list_of_days ) {
		$max = 0; $day = 0;
		$max = max( $list_of_days );
		foreach( $list_of_days as $k=>$v ) {
			if ( $v == $max )
			return array( 'day' => $k, 'max' => $max );
		}
}

function qr_days_in_month( $month, $year ) {
	// calculate number of days in a month
	return $month == 2 ? ( $year % 4 ? 28 : ( $year % 100 ? 29 : ( $year % 400 ? 28 : 29 ) ) ) : ( ( $month - 1 ) % 7 % 2 ? 30 : 31 );
}	

function qr_plural( $word, $count=1 ) {
	return $word . ($count > 1 ? 's' : '');
}
	
?>