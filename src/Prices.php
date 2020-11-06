<?php
namespace andrewdanilov\helpers;

class Prices
{
	public static function calcInCurrency($summ, $currencyValue, $format=false)
	{
		$result = round($summ / $currencyValue, 2);
		if ($format) {
			$result = number_format($result, 2, '.', ' ');
		}
		$result = preg_replace('/\.0+$/', '', $result);
		return $result;
	}
}