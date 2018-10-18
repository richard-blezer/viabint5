<?php

/**
 * Returns given array with key from subarray
 * @param unknown $inputArray
 * @param unknown $key
 */
if (!function_exists('get_array_with_keys')) {
	function get_array_with_keys($inputArray, $key) {
		$tmp = array();
		foreach ($inputArray as $array) {
			$tmp[$array[$key]] = $array;
		}
	
		return $tmp;
	}
}

if (!function_exists('get_key_value_array')) {
	function get_key_value_array($inputArray, $key, $value) {
		$tmp = array();
		foreach ($inputArray as $array) {
			$tmp[$array[$key]] = $array[$value];
		}

		return $tmp;
	}
}