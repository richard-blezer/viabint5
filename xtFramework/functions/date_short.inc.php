<?php
/*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

defined('_VALID_CALL') or die('Direct Access is not allowed.');

function date_short($data, $format_str=''){
	// added optional parameter format

	if(($data=='0000-00-00 00:00:00') || !is_data($data)) return false;

	// in case there is a format defined
	switch ($format_str) {
		case 'yyyy-mm-dd':
			return date("Y-m-d", strtotime($data));
			break;
			
		case 'mm/dd/yyyy':
			return date("m/d/Y", strtotime($data));
			break;
			
		default:
			// former only return
			return date("d.m.Y", strtotime($data));
			break;
	}
}

function date_long($data){

	if(($data=='0000-00-00 00:00:00') || !is_data($data)) return false;

	return date("d.m.Y H:i:s",strtotime($data));
}

/**
 * Format unix timestamp readable string
 *
 * @param int $timestamp
 * @param string $format
 * @return string time
 */
function format_timestamp($timestamp,$format = 'date'){

	if(!is_data($timestamp)) return false;

	($format=='date' ? $format="%d.%m.%Y" : '');
	($format=='datetime' ? $format="%d.%m.%Y %H:%M:%S" : '');
	($format=='mysql-date' ? $format="%Y-%m-%d" : '');
	($format=='mysql-datetime' ? $format="%Y-%m-%d %H:%M:%S" : '');
	return strftime($format, $timestamp);
}

/**
 * format 0000-00-00 00:00:00 as unix timestamp
 *
 * @param datetime $datetime
 * @return timestamp
 */
function datetime_to_timestamp($datetime) {
	return strtotime($datetime);
}

/**
 * add x Days to given date
 *
 * @param timestamp $timestamp
 * @param int $days
 * @return timestamp
 */

function vtn_date_add($timestamp,$days=0) {	
	return $timestamp + ($days * 24 * 3600);
	//return mktime(date("G",$timestamp), date("i",$timestamp), date("s",$timestamp), date("m",$timestamp)  , date("d",$timestamp)+$days, date("Y",$timestamp));
}

/**
 * add x hours to given date
 *
 * @param timestamp $timestamp
 * @param int $hours
 */
function date_add_hours($timestamp,$hours=0) {
	return $timestamp + ($hours * 60);
//	return mktime(date("G",$timestamp)+$hours, date("i",$timestamp), date("s",$timestamp), date("m",$timestamp)  , date("d",$timestamp), date("Y",$timestamp));
}

function current_age($dob) {
	return floor((date("Ymd") - date("Ymd", strtotime($dob))) / 10000);
}
?>