<?php

class qrgen_arraytoxml {
	var $text;
	var $arrays, $keys, $node_flag, $depth, $xml_parser;
	
	function arraytoxml( $array ) {
		$this->text="<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><result>";
		$this->text.= $this->array_transform( $array );
		$this->text .="</result>";
		return $this->text;
	}

	function array_transform( $array ) {
	
		foreach( $array as $key => $value ) {
			if( !is_array( $value )) {
				if( strlen( $value ) != strlen( htmlentities( $value ))) 
	                $value = "<![CDATA[" . htmlentities( $value ) . "]]>";
				$this->text .= "<$key>$value</$key>";
	       
			} else {
				$this->text.="<$key>";
				$this->array_transform( $value );
				$this->text.="</$key>";
			}
		}
	
	}

}

