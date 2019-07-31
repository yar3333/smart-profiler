<?php

namespace SmartProfiler;

class Gistogram
{
	static function generate($results, int $width) : string
	{
		$maxLen = 0;
		$maxDT = 0.0;
		$maxCount = 0;

		foreach ($results as $result)
		{
			$maxLen = max($maxLen, $result->name->Length);
			$maxDT = max($maxDT, $result->dt);
			$maxCount = max($maxCount, $result->count);
		}
		
		$countLen = $maxCount > 1 ? $maxCount->toString()->Length : 0;
		
		$maxW = $width - $maxLen - $countLen;
		if ($maxW < 1) $maxW = 1;
		
		$r = "";
		// ReSharper disable once PossibleMultipleEnumeration
		foreach ($results as $result)
		{
			$r .= str_pad(TimeToString::run($result->dt), strlen(TimeToString::run($maxDT)), '0', STR_PAD_LEFT) . " | ";
			$r .= str_pad(str_pad("", round($result->dt / $maxDT * $maxW), '*'), $maxW, ' ') . " | ";
			$r .= str_pad($result->name, $maxLen, ' ');
			if ($countLen > 0)
			{
				$r .= " [" . str_pad((string)$result->count, $countLen, ' ') . " time(s)]";
			}
			$r .= "\n";
		}
		return $r;
	}
}
