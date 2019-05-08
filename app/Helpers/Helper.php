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
	* Utility function for getting random values with weighting. Adapted from https://stackoverflow.com/a/11872928
	* Pass in an associative array, such as array('A'=>5, 'B'=>45, 'C'=>50)
	* An array like this means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
	* The return value is the array key, A, B, or C in this case.  Note that the values assigned
	* do not have to be percentages.  The values are simply relative to each other.  If one value
	* weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66%
	* chance of being selected.
	* Note that the order greatly influences the execution speed: biggest weights first yield the maximum speed, while lowest weights first yield the worst performance.
	*
	* For more information about how the scale affects your weights, see https://www.desmos.com/calculator/nwbaoddtix
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
		$n = count($weighted_values);
		$avg = $total_sum/$n;
		// TODO: find a constant that can be used to normalize the values so that their sum is still $total_sum
		if (abs($scale) > 1)
		{
			$norm = $total_sum - $n;
			if ($scale < -1)
			{
				$norm = -$norm + 2*$n*$avg;
			}
			$norm = $norm / $n;
		}

		// now, run the algorithm to get the random key
		$rand = mt_rand() / mt_getrandmax() * $total_sum;
		foreach ($weighted_values as $key => $value)
		{
			$distance = $value - $avg;
			if (abs($scale) <= 1)
			{
				$rand -= $avg + $scale*$distance;
			}
			else
			{
				$extreme = $value + pow(abs($scale), $distance) - 1;
				if ($scale > 1)
				{
					$rand -= $extreme;
				}
				else
				{
					$rand -= -$extreme + 2*$avg;
				}
			}
			if ($rand <= 0)
			{
				return $key;
			}
		}
	}
}