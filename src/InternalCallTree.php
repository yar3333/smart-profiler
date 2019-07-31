<?php

namespace SmartProfiler;

class InternalCallTree
{
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $subname;
	/**
	 * @var InternalCallTree[]
	 */
	public $children;
	/**
	 * @var InternalCallTree
	 */
	public $parent;

    /**
     * @var float|null
     */
	public $dt;

    /**
     * InternalCallTree constructor.
     * @param string $name
     * @param string $subname
     * @param InternalCallTree[] $children
     * @param InternalCallTree $parent
     * @param float|null $dt
     */
	public function __construct(string $name, ?string $subname, array $children, ?InternalCallTree $parent, ?float $dt)
	{
		$this->name = $name;
		$this->subname = $subname;
		$this->children = $children;
		$this->parent = $parent;
		$this->dt = $dt;
	}
}
