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

if (!function_exists('wrand'))
{
	/**
	* wrand()
	* Utility function for getting random values with weighting.
	* Pass in an associative array, such as array('A'=>5, 'B'=>45, 'C'=>50)
	* An array like this means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
	* The return value is the array key, A, B, or C in this case.  Note that the values assigned
	* do not have to be percentages.  The values are simply relative to each other.  If one value
	* weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66%
	* chance of being selected.  Also note that weights should be integers.
	* Note that the order greatly influences the execution speed: biggest weights first yield the maximum speed, while lowest weights first yield the worst performance.
	* 
	* @param Illuminate\Support\Collection|array	$weighted_values
	* @param double									$scale				a number from 0 to 1 denoting how important the weights are (so if $scale = 0, it is equivalent to using no weights at all)
	*/
	function wrand($weighted_values, float $scale=1)
	{
		// first, get the sum and avg of the weights
		if ($weighted_values instanceof Illuminate\Support\Collection)
		{
			$total_sum = $weighted_values->sum();
		}
		elseif (is_array($weighted_values))
		{
			$total_sum = array_sum($weighted_values);
		}
		else
		{
			throw new Exception('Input must be array or Collection.');
		}
		$avg = $total_sum/count($weighted_values);

		// now, run the algorithm to get the random key
		$rand = mt_rand(1, (int) $total_sum);
		foreach ($weighted_values as $key => $value)
		{
			$rand -= $avg + $scale*($value-$avg);
			if ($rand <= 0)
			{
				return $key;
			}
		}
	}
}