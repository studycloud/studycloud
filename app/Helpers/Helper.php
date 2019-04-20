<?php

if (!function_exists('readable_array'))
{
	/**
	 * return a string containing the elements of $arr in a human readable format
	 * @param  array $arr the array whose elements you'd like in the string
	 * @return string     a string containing the elements of $arr, nicely formatted
	 */
	function readable_array($arr)
	{
		if (count($arr)>2)
		{
			$last = array_pop($arr);
			return implode(", ", $arr).", and ".$last;
		}
		elseif (count($arr) == 2)
		{
			return $arr[0]." and ".$arr[1];
		}
		elseif (count($arr) == 1)
		{
			return $arr[0];
		}
		else
		{
			return "";
		}
	}
}