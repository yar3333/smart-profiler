<?php

namespace SmartProfiler;

class Opened
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var float
	 */
	public $start;

	/**
	 * @var float
	 */
	public $stop;

	function __construct(string $name)
	{
		$this->name = $name;
		$this->start = microtime(true);
	}

	public function getSeconds() : float { return $this->stop - $this->start; }
}
