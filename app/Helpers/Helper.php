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
	* For more information about how the scale affects your weights, see https://www.desmos.com/calculator/f98qouj5ub
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

		// transform the values
		// uses an exponential model to exaggerate changes to the weights without making them less than 0
		$weighted_values = $weighted_values->map(
			function ($weight, $value) use ($scale)
			{
				// apply the exponential transformation
				// see the docstring for detailed explanation
				return pow($weight, $scale);
			}
		);

		// calculate the sum here
		$total_sum = $weighted_values->sum();

		// now, run the algorithm to get the random key
		// first, we choose a random decimal between 0 and $total_sum
		// retain precision by multiplying by a big number and then dividing
		$rand = mt_rand(0, $total_sum*10000000000) / 10000000000;
		// then, we gradually subtract the weights from that number until we pass 0
		foreach ($weighted_values as $value => $weight)
		{
			$rand -= $weight;
			// return the key that made us pass 0
			if ($rand <= 0)
			{
				return $value;
			}
		}
	}
}

if (!function_exists('tinker'))
{
	/**
	 * Call this function inside your tests to have tinker get activated
	 */
	function tinker()
	{
		eval(\Psy\sh());
	}
}
