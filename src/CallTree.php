<?php

namespace SmartProfiler;

class CallTree
{
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var array
	 */
	public $children;
	
	function __construct(string $name, array $children)
	{
		$this->name = $name;
		$this->children = $children;
	}
}
