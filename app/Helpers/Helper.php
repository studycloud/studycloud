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
	*
	* Pass in an associative array, such as array('A'=>5, 'B'=>45, 'C'=>50)
	* An array like this means that "A" has a 5% chance of being selected, "B" 45%, and "C" 50%.
	* The return value is the array key, A, B, or C in this case.  Note that the values assigned
	* do not have to be percentages.  The values are simply relative to each other.  If one value
	* weight was 2, and the other weight of 1, the value with the weight of 2 has about a 66%
	* chance of being selected.
	* Note that the order greatly influences the execution speed: biggest weights first yield the maximum speed, while lowest weights first yield the worst performance.
	*
	* The scale can be used to control how much deference is given to the weights.
	* A scale of 0 makes wrand() essentially ignore the weights, while a scale of 1 tells it
	* to use the weights as they've been provided. A scale larger than 1 will exaggerate the
	* effect of the weights. Negative scale values will flip your weights around their mean,
	* inverting their effect.
	* Note that the algorithm is faster when the the scale is between -1 and 1.
	* For more information about how the scale affects your weights, see https://www.desmos.com/calculator/pt3h97veu3
	* 
	* @param Illuminate\Support\Collection|array	$weighted_values	an array of (value, probability) key-value pairs
	* @param double									$scale				a number (typically from 0 to 1) denoting how important the weights are
	* @return mixed														a random key in $weighted_values chosen according to a discrete probability distribution
	*/
	function wrand($weighted_values, float $scale=1)
	{
		// if the input is an array, use a collection instead
		if (is_array($weighted_values))
		{
			$weighted_values = collect($weighted_values);
		}
		// make sure the input is of an acceptable type
		if (!($weighted_values instanceof Illuminate\Support\Collection))
		{
			throw new Exception('Input must be array or Collection.');
		}

		// make sure our algorithm is quick if the scale is 0
		if ($scale == 0)
		{
			return $weighted_values->keys()->random();
		}

		// calculate the sum and avg of the weights
		$total_sum = $weighted_values->sum();
		$avg = $total_sum/count($weighted_values);

		// transform the values when the scale is larger than 1 or smaller than -1
		// uses an exponential model to exaggerate changes to the weights without making them less than 0
		if (abs($scale) > 1)
		{
			$weighted_values = $weighted_values->map(
				function ($weight, $value) use ($scale, $avg)
				{
					// apply the exponential transformation
					// see the docstring for detailed explanation
					$extreme = $weight + pow(abs($scale), $weight - $avg) - 1;
					if ($scale > 1)
					{
						return $extreme;
					}
					return -$extreme + 2*$avg;
				}
			);
			// important to recalculate the sum here!
			$total_sum = $weighted_values->sum();
		}

		// now, run the algorithm to get the random key
		// first, we choose a random number between 0 and $total_sum
		$rand = mt_rand() / mt_getrandmax() * $total_sum;
		// then, we gradually subtract the weights from that number until we pass 0
		foreach ($weighted_values as $value => $weight)
		{
			// if the scale is between -1 and 1
			if (abs($scale) <= 1)
			{
				// apply the scaling factor
				$rand -= $avg + $scale*($weight - $avg);
			}
			else
			{
				// the scale has already been applied when we transformed the weights
				$rand -= $weight;
			}
			// return the key that made us pass 0
			if ($rand <= 0)
			{
				return $value;
			}
		}
	}
}