<?php

namespace SmartProfiler;

class Block
{
	/**
	 * @var int
	 */
	public $count;
	
	/**
	 * @var float
	 */
	public $dt;
	
	function __construct(int $count, float $dt)
	{
		$this->count = $count;
		$this->dt = $dt;
	}
}
