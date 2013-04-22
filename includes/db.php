<?php

function get_qrgen_tables( $prefix )  {
	
	$qr_tables = array( 'address', 'bookmarks', 'emails', 'emsg', 'geo', 'log', 'mecard', 'options', 'shorturl', 'sms', 'telephones', 'texts',
	'urls', 'user', 'vcard' );
	
	$create_tables = array();
	$create_tables[ $prefix . 'address' ] =
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'address` (
		`adrid` bigint(20) NOT NULL auto_increment,
		`keyword` varchar(200) NOT NULL,
		`firstname` varchar(25) NOT NULL,
		`lastname` varchar(25) NOT NULL,
		`hphno` varchar(20) default NULL,
		`wphno` varchar(20) NOT NULL,
		`email` varchar(25) NOT NULL,
		`url` varchar(200) NOT NULL,
		`bday` date NOT NULL,
		`note` varchar(255) NOT NULL,
		`org` varchar(100) default NULL,
		`title` varchar(20) default NULL,
		`photo` varchar(200) default NULL,
		`hstreet` varchar(50) default NULL,
		`hcity` varchar(20) default NULL,
		`hstate` varchar(20) default NULL,
		`hzip` varchar(15) default NULL,
		`hcountry` varchar(20) default NULL,
		`wstreet` varchar(50) NOT NULL,
		`wcity` varchar(20) NOT NULL,
		`wstate` varchar(20) NOT NULL,
		`wzip` varchar(15) NOT NULL,
		`wcountry` varchar(20) NOT NULL,
		PRIMARY KEY  (`adrid`),
		KEY `keyword` (`keyword`)
		) AUTO_INCREMENT=1;' ;
		
	$create_tables[ $prefix . 'bookmarks' ] =
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'bookmarks` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`title` varchar(255) NOT NULL,
		`url` varchar(255) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
	
	$create_tables[ $prefix . 'emails' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'emails` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`email` varchar(100) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
		
	$create_tables[ $prefix . 'emsg' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'emsg` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`email` varchar(100) NOT NULL,
		`subject` varchar(100) NOT NULL,
		`body` varchar(255) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
	
	$create_tables[ $prefix . 'geo' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'geo` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`latitude` varchar(50) NOT NULL,
		`longitude` varchar(50) NOT NULL,
		`altitude` varchar(50) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
	
	$create_tables[ $prefix . 'log' ] =
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'log` (
		`click_id` int(11) NOT NULL auto_increment,
		`click_time` datetime NOT NULL,
		`keyword` varchar(200) character set latin1 collate latin1_bin NOT NULL,
		`referrer` varchar(200) NOT NULL,
		`user_agent` varchar(255) NOT NULL,
		`ip_address` varchar(41) NOT NULL,
		`country_code` varchar(5) NOT NULL,
		PRIMARY KEY  (`click_id`),
		KEY `shorturl` (`keyword`)
		) AUTO_INCREMENT=1;';
	
	$create_tables[ $prefix . 'mecard' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'mecard` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';	
	
	$create_tables[ $prefix . 'options' ] =		
		"CREATE TABLE IF NOT EXISTS `" . $prefix . "options` (
		`option_id` bigint(20) unsigned NOT NULL auto_increment,
		`option_name` varchar(64) NOT NULL default '0',
		`option_value` longtext NOT NULL,
		PRIMARY KEY  (`option_id`,`option_name`),
		KEY `option_name` (`option_name`)
		) AUTO_INCREMENT=1;";
	
	$create_tables[ $prefix . 'shorturl' ] =
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'shorturl` (
		`keyword` varchar(200) character set latin1 collate latin1_bin NOT NULL,
		`type` varchar(25) NOT NULL,
		`timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
		`ip` varchar(50) NOT NULL,
		`clicks` int(12) NOT NULL,
		PRIMARY KEY  (`keyword`)
		);';
		
	$create_tables[ $prefix . 'sms' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'sms` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`telno` varchar(20) NOT NULL,
		`sms_text` varchar(160) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
	
	$create_tables[ $prefix . 'telephones' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix. 'telephones` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`telno` varchar(20) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
		
	$create_tables[ $prefix . 'texts' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'texts` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`text` varchar(512) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
	
	$create_tables[ $prefix . 'urls' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'urls` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`url` varchar(255) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
		
	$create_tables[ $prefix . 'user' ] =	
		"CREATE TABLE IF NOT EXISTS `" . $prefix. "user` (
		`userID` bigint(20) NOT NULL auto_increment,
		`user_login` varchar(100) default NULL,
		`user_pass` varchar(255) default NULL,
		`user_email` varchar(255) NOT NULL,
		`display_name` varchar(100) default NULL,
		`is_admin` tinyint(1) NOT NULL default '0',
		`user_registered` int(11) default NULL,
		`auth_key` varchar(255) default NULL,
		`api_key` varchar(64) NOT NULL,
		PRIMARY KEY  (`userID`),
		UNIQUE KEY `user_email` (`user_email`)
		) AUTO_INCREMENT=1;";
	
	$create_tables[ $prefix . 'vcard' ] =	
		'CREATE TABLE IF NOT EXISTS `' . $prefix . 'vcard` (
		`uid` bigint(20) NOT NULL,
		`keyword` varchar(200) NOT NULL,
		`qrimage` varchar(255) NOT NULL,
		`trimage` varchar(255) NOT NULL,
		`created` int(12) NOT NULL,
		KEY `keyword` (`keyword`)
		);';
	
	return $create_tables;
}
	
	
	
	