<?php

namespace SmartProfiler;

class TimeToString
{
	static function run(float $dt) : string
	{
		return (string)round($dt * 1000);
	}
}
