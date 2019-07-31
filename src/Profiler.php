<?php

namespace SmartProfiler;

class Profiler
{
	/**
	 * @var ProfilerInstance
	 */
	private static $_instance;

	/**
	 * @return ProfilerInstance
	 */
	private static function instance() { return self::$_instance ?: (self::$_instance = new ProfilerInstance(0)); }
	
	/**
	 * @return int
	 */
	public static function getMmaxNestingLevel() { return self::instance()->maxNestingLevel; }
	
	public static function begin(string $name, string $subname=null) : void
	{
		self::instance()->begin($name, $subname);
	}
	
	static function end() : void
	{
		self::instance()->end();
	}
    
    static function measure(string $name, ?string $subname, callable $f)
    {
       return self::instance()->measure($name, $subname, $f);
    }

	public static function getResults(bool $traceNested=false, bool $traceCallStack=false, int $width=120, float $minDT=0.0, string $filterTo=null, string $filterFrom=null) : string
	{
		return self::instance()->getResults($traceNested, $traceCallStack, $width, $minDT, $filterTo, $filterFrom);
	}
	
	public static function traceResults(bool $traceNested=false, bool $traceCallStack=false, int $width=120, float $minDT=0.0, string $filterTo=null, string $filterFrom=null) : void
	{
		self::trace(self::getResults($traceNested, $traceCallStack, $width, $minDT, $filterTo, $filterFrom));
	}
	
	/**
	 * @return Result[]
	 */
	static function getNestedResults() : array
	{
		return self::instance()->getNestedResults();
	}
	
	/**
	 * @return Result[]
	 */
	static function getSummaryResults() : array
	{
		return self::instance()->getSummaryResults();
	}

    /**
     * @param float $minTimeMS
     * @param string $filterTo
     * @param string $filterFrom
     * @return Result[]
     */
	public static function getCallStackResults(float $minTimeMS=0.0, string $filterTo=null, string $filterFrom=null) : array
	{
		return self::instance()->getCallStackResults($minTimeMS, $filterTo, $filterFrom);
	}
	
	public static function getCallStackResultsAsText(float $minTimeMS=0.0, string $filterTo=null, string $filterFrom=null) : string
	{
		return self::instance()->getCallStackResultsAsText($minTimeMS, $filterTo, $filterFrom);
	}
	
	public function getCallStack(float $minTotalTimeMS=0.0, float $minMeasureTimeMS=0.0)
	{
		return self::instance()->getCallStack($minTotalTimeMS, $minMeasureTimeMS);
	}
    
    public static function getCallStackAsJson(float $minTotalTimeMS=0.0, float $minMeasureTimeMS=0.0) : string
    {
		return self::instance()->getCallStackAsJson($minTotalTimeMS, $minMeasureTimeMS);
    }
	
	public static function getSummaryGistogram(int $width=120) : string
	{
		return self::instance()->getSummaryGistogram($width);
	}
	
	public static function getNestedGistogram(int $width=120) : string
	{
		return self::instance()->getNestedGistogram($width);
	}
	
	public static function getCallStackGistogram(int $width=120) : string
	{
		return self::instance()->getCallStackGistogram($width);
	}
	
	public static function reset(int $maxNestingLevel=null) : void
	{
		self::instance()->reset($maxNestingLevel);
	}
	
    static function trace(string $s) : void
    {
        echo "$s\n";
    }
}
	