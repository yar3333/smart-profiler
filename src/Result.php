<?php

namespace SmartProfiler;

class Result
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var float
	 */
	public $dt;

	/**
	 * @var int
	 */
	public $count;
	
	function __construct(string $name, float $dt, int $count)
	{
		$this->name = $name;
		$this->dt = $dt;
		$this->count = $count;
	}
}
