<?php

class qrTable
{
    private $data;
	private $start;
	private $qrtype;
	private $colid; 
	
    function set_data( $data, $arindex, $ttrows, $perpage, $site, $datatypes, $qrtype ) {
		
        $this->data = $data;
        $this->arindex = $arindex;
        $this->perpage = $perpage;
    
		$this->all = $ttrows;
        $this->col = count( $this->arindex );
		$this->types = $datatypes;
		$this->qrtype = $qrtype;
		$this->navlinks_perpage = 10;
			
        if( isset( $_GET[ 'start' ] ) ) 
            $this->start = $_GET[ 'start' ]; 
        else 
            $this->start = 0; 
        $this->site = $site;
    }
	
    // display data from array
    function build_table( ) {
		
    	$data = $this->data;
        $tbl = '<table id="gradient-style"><thead>';
        $tbl .= '<tr align="center">';
		$col = ( $this->qrtype === 'user' ) ? 'userID' : 'keyword';
			for ( $i = 0; $i < $this->col; $i++ )
			{
				if ( $i == 0 )
					$tbl .= '<th data-sort="'. $this->types[ $i ]. '" class="awesome">' .$this->arindex[ $i ]. '</th>';
				else 
					$tbl .= '<th data-sort="'. $this->types[ $i ]. '">' .$this->arindex[ $i ]. '</th>';
			}
			$tbl .= '<th>Actions</th>';
			$tbl .= '</tr></thead><tbody>';
		
		$zebra = 1;
		
			foreach( $data as $k => $v )
			{
				if( ( $zebra % 2 )	== 0 )
						$tbl .= '<tr id="' . $this->qrtype . '_' . $data[ $k ][ $col ] . '" class="even">';
				else 
						$tbl .=  '<tr id="' . $this->qrtype . '_' . $data[ $k ][ $col ] . '" class="odd">';
			
				for( $i = 0; $i < $this->col; $i++ )
				{
					if( $i == ( $this->col - 1))
						$tbl .= '<td class="crdate">'.date( "M d, Y H:i", $data[ $k ][ $this->arindex[ $i ] ] ).'</td>';
					elseif( $i == ( $this->col - 2 ) && !( $this->qrtype === 'user' )) {
						$ntrimg = $data[ $k ][ $this->arindex[ $i ] ] ? $data[ $k ][ $this->arindex[ $i ] ] : QRGEN_SITE . '/images/noimg.png';
						$tbl .= "<td class='imgcenter'><span>NT</span><a href='" . $ntrimg . "' class='button'><img id='qrimg" . $this->qrtype . '_' . $data[ $k ][ $col ] .  "' src='" . $ntrimg . "' height='35' width='35'></img></a>";
						$trimg = $data[ $k ][ 'trimage' ] ? $data[ $k ][ 'trimage' ] : QRGEN_SITE . '/images/noimg.png';
						$tbl .= "<span>T</span><a href='" . $trimg . "' class='button'><img id='trkimg" . $this->qrtype . '_' . $data[ $k ][ $col ] .  "' src='" . $trimg . "' height='35' width='35'></img></a> </td>";
					}
					else	
						$tbl .= '<td>'.$data[ $k ][ $this->arindex[ $i ] ] .'</td>';
				}
				$tbl .= '<td><div id="actions"><ul id="menu_select">';
				if( !( $this->qrtype === 'user' )) {
					$tbl .= '<li class="stat"><a href="' . QRGEN_SITE . '/stat_' . $data[ $k ][ $col ] . '"><img src="' . QRGEN_SITE . '/images/stats.png" /></a></li>';
					$tbl .= '<li class="surl"><a href="'. QRGEN_SITE . '/' . $data[ $k][ $col ] . '"><img src="'. QRGEN_SITE . '/images/surl.png" /></a></li>';
				}
				$tbl .= '<li class="edit" id="' . $this->qrtype . '_' . $data[ $k ][ $col ] . '" title="'. $this->qrtype . '"><img src="' . QRGEN_SITE . '/images/edit.png" /><p>' . origtext( $this->qrtype ) . '</p></li>';
				$tbl .= '<li class="delete" id="' . $this->qrtype . '_'  . $data[ $k ][ $col ] . '" title="'. $this->qrtype . '"><img src="' . QRGEN_SITE . '/images/delete.png" /><p>' . origtext( $this->qrtype ) . '</p></li>';
				$tbl .= '</ul></div></td></tr>';
			
				$zebra++;
			}
        $tbl .= '</tbody></table>';
		
		return $tbl;
    }
	
    // generate & display pagination
   function display_pagin( )   {
		
		$curpag = $this->start;
		$this->pages = ceil( $this->all / $this->perpage );
		if ( $this->navlinks_perpage > $this->pages ) {
			$this->navlinks_perpage = $this->pages;
		}
		if ( $curpag > $this->pages || $curpag <= 0) {
			$curpag = 1;
		}
		$batch = ceil( $curpag / $this->navlinks_perpage );
		$end = $batch * $this->navlinks_perpage;
		
		if ( $end > $this->pages) {
			$end = $this->pages;
		}
		$nstart = $end - $this->navlinks_perpage + 1;
		$prev = ( $curpag == 1 ) ? 1 : $curpag - 1;
		$next = ( $curpag == $this->pages ) ? $curpag : $curpag + 1;
		
		//$pagin = "<p>perpage $this->perpage | curpg $curpag | start  $this->start | pages $this->pages | all $this->all | batch $batch | end $end | nstart $nstart</p>";
		$pagin = '<p><div id="navig" style="font-family: arial; font-size: 12px; font-weight: normal;">'; 
			
		for( $i = $nstart; $i <= $end; $i ++) {
			
			if( $i == 1 ) { 
				$pagin .= "<div id='navfirst'><a href='javascript:void(0);' OnClick='getdata(" . $i . ")' style='text-decoration: none;'>First</a></div>";
				$pagin .= "<div class='navnum'><a href='javascript:void(0);' OnClick='getdata(" . $prev  . ")' style='text-decoration: none;'> &lt;&lt; </a></div>";	
			}
			if( $i == $curpag )
				$pagin .= "<div id='curpage' id='navnum'><a href='javascript:void(0);' OnClick='getdata(" . $i . ")' style='text-decoration: none;'>" . $i . " </a></div>"; 
			else	
				$pagin .= "<div class='navnum'><a href='javascript:void(0);' OnClick='getdata(" . $i  . ")' style='text-decoration: none;'>" . $i . " </a></div>";
		}
	
		$pagin .= "<div class='navnum'><a href='javascript:void(0);' OnClick='getdata(" . $next  . ")' style='text-decoration: none;'> &gt;&gt; </a></div>";
		$pagin .= "<div id='navlast'><a href='javascript:void(0);' OnClick='getdata(" . ( $this->pages ) . ")' style='text-decoration: none;'>Last</a></div>";
		
		$pagin .= '</div></p>';
		return $pagin;
    }   

}
