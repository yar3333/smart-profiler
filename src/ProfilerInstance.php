<?php

namespace SmartProfiler;

class ProfilerInstance
{
	/**
	 * @var int
	 */
	public $maxNestingLevel = 0;
	
	/**
     * @var Block[]
     */
	public $blocks;

    /**
     * @var Opened[]
     */
    public $opened;

    /**
     * @var InternalCallTree
     */
    public $callTree;

	function __construct(int $maxNestingLevel)
	{
		$this->reset($maxNestingLevel);
	}
	
	public function begin(string $name, string $subname=null) : void
	{
		if ($this->maxNestingLevel <= 0) return;

		if ($this->maxNestingLevel > 1)
		{
			$subCallTree = new InternalCallTree($name, $subname, [], $this->callTree, 0);
			$this->callTree->children[] = $subCallTree;
			$this->callTree = $subCallTree;
		}
		
		if (count($this->opened) > 0)
		{
			$name = $this->opened[count($this->opened) - 1]->name . '-' . $name;
		}
		$this->opened[] = new Opened($name);
	}
	
	function end() : void
	{
		if ($this->maxNestingLevel <= 0) return;

		if (count($this->opened) === 0)
		{
			throw new \Exception("Profiler::end() called but there are no open blocks.");
		}
		
		$b = $this->opened[count($this->opened) - 1];
		$b->stop = microtime(true);
		array_pop($this->opened);
		$dt = $b->getSeconds();
		
		if (!array_key_exists($b->name, $this->blocks))
		{
			$this->blocks[$b->name] = new Block(1, $dt);
		}
		else
		{
			$this->blocks[$b->name]->count++;
			$this->blocks[$b->name]->dt += $dt;
		}
		
		if ($this->maxNestingLevel > 1)
		{
			$this->callTree->dt = $dt;
			$this->callTree = $this->callTree->parent;
		}
	}
	
	function measure(string $name, ?string $subname, callable $f)
	{
		$this->begin($name, $subname);
		try { return $f(); }
		finally { $this->end(); }
	}

	public function getResults(bool $traceNested=false, bool $traceCallStack=false, int $width=120, float $minDT=0.0, string $filterTo=null, string $filterFrom=null) : string
	{
		$r = "";
		
		if ($this->maxNestingLevel > 0)
		{
			if (count($this->opened) > 0)
			{
				foreach ($this->opened as $b)
				{
					$r .= "PROFILER WARNING: Block '" . $b->name . 	"' is not ended" . "\n";
				}
			}
			
			$r .= "PROFILER Summary:\n" . Gistogram::generate($this->getSummaryResults(), $width) . "\n";
			
			if ($traceNested)
			{
				$r .= "PROFILER Nested:\n" . Gistogram::generate($this->getNestedResults(), $width) . "\n";
			}
			
			if ($traceCallStack && $this->maxNestingLevel > 1)
			{
				$r .= "PROFILER Calls:\n" . Gistogram::generate($this->getCallStackResults($minDT, $filterTo, $filterFrom), $width) . "\n";
			}
		}
		
		return rtrim($r);
	}
	
	/**
	 * @return Result[]
	 */
	function getSummaryResults() : array
	{
		if ($this->maxNestingLevel <= 0) return [];
		
		$results = [];
		
		foreach ($this->blocks as $name => $block)
		{
			$nameParts = explode('-', $name);
			
			$name2 = $nameParts[$nameParts->Length - 1];
			if (!array_key_exists($name2, $results))
			{
				$results[$name2] = new Result($name2, 0.0, 0);
			}
			$results[$name2]->dt += $block->dt;
			$results[$name2]->count += $block->count;
		}
		
		$r = array_values($results);
		usort($r, function($a, $b) { return $a->dt - $b->dt; });
		return $r;
	}
	
	/**
	 * @return Result[]
	 */
	function getNestedResults() : array
	{
		/** @var Result[] $r */
		$r = [];
		
		if ($this->maxNestingLevel < 1) return $r;
		
		foreach ($this->blocks as $name => $block)
		{
			$r[] = new Result($name, $block->dt, $block->count);
		}
		
		usort($r, function($a, $b)
        {
			$ai = $a->name->split('-');
			$bi = $b->name->split('-');
			
			for ($i=0; $i<min($ai->Length, $bi->Length); $i++)
			{
				if ($ai[$i] !== $bi[$i])
				{
					return $ai[$i] < $bi[$i] ? -1 : ($ai[$i] > $bi[$i] ? 1 : 0);
				}
			}
			
			return round(($b->dt - $a->dt) * 1000);
		});
		
		return $r;
	}

    /**
     * @param float $minTimeMS
     * @param string $filterTo
     * @param string $filterFrom
     * @return Result[]
     */
	public function getCallStackResults(float $minTimeMS=0.0, string $filterTo=null, string $filterFrom=null) : array
	{
		return $this->maxNestingLevel > 1 ? $this->callStackToResults($minTimeMS, $this->callTree, 0, $filterTo, $filterFrom) : [];
	}
	
	public function getCallStackResultsAsText(float $minTimeMS=0.0, string $filterTo=null, string $filterFrom=null) : string
	{
		$results = $this->getCallStackResults($minTimeMS, $filterTo, $filterFrom);
		
		$maxNameLen = 0;
		foreach ($results as $result)
		{
			if (strlen($result->name) > $maxNameLen)
			{
				$maxNameLen = strlen($result->name);
			}
		}
		
		return join("\n",
            array_map(
                function($e) use($maxNameLen) { return str_pad(TimeToString::run($e->dt), 4, '0', STR_PAD_LEFT) . " | " . str_pad($e->name, $maxNameLen - strlen($e->name)); },
                $results
            )
        );
	}
	
	public function getCallStack(float $minTotalTimeMS=0.0, float $minMeasureTimeMS=0.0)
	{
		if ($minTotalTimeMS > 0)
		{
			if ($this->callTree->dt !== null)
			{
				if ($this->callTree->dt * 1000 < $minTotalTimeMS) return "";
			}
			else
			{
				$dt = 0.0;
				if ($this->callTree->children !== null)
                {
                    foreach ($this->callTree->children as $t)
                    {
                        $dt += $t->dt !== null ? $t->dt : 0.0;
                    }
                }
				if ($dt * 1000 < $minTotalTimeMS) return "";
			}
		}
		$r = $this->toPublicCallTree($this->callTree, $minMeasureTimeMS);
		if (is_string($r)) return $r;
		return $r->children;
	}
	
	public function getCallStackAsJson(float $minTotalTimeMS=0.0, float $minMeasureTimeMS=0.0) : string
	{
		$results = $this->getCallStack($minTotalTimeMS, $minMeasureTimeMS);
		if (is_string($results) && trim($results) != "" || is_array($results) && count($results) > 0)
		{
			return json_encode($results);
		}
		return "";
	}
	
	public function getSummaryGistogram(int $width=120) : string
	{
		return Gistogram::generate($this->getSummaryResults(), $width);
	}
	
	public function getNestedGistogram(int $width=120) : string
	{
		return Gistogram::generate($this->getNestedResults(), $width);
	}
	
	public function getCallStackGistogram(int $width=120) : string
	{
		return Gistogram::generate($this->getCallStackResults(), $width);
	}
	
	public function reset(?int $maxNestingLevel) : void
	{
		if ($maxNestingLevel !== null) $this->maxNestingLevel = $maxNestingLevel;
		
		if ($this->maxNestingLevel > 0)
		{
			$this->blocks = [];
			/** @var Opened[] $opened */
			$this->opened = [];
			if ($this->maxNestingLevel > 1)
			{
				$this->callTree = new InternalCallTree("", null, [], null, null);
			}
		}
	}
	
	function toPublicCallTree(InternalCallTree $c, float $minTimeMS)
	{
		$dt = $c->dt !== null ? str_pad(TimeToString::run($c->dt), 4, STR_PAD_LEFT) :  "";
		$name = $dt . " " . $c->name . ($c->subname != null ? " / " . $c->subname : "");
		$stack = $c->children !== null ? array_filter($c->children, function($e) use($minTimeMS) { return round($e->dt * 1000) >= $minTimeMS; }) : [];
		if (count($stack) > 0)
		{
			return new CallTree($name, array_map(function($e) use($minTimeMS) { return $this->toPublicCallTree($e, $minTimeMS); }, $stack));
		}
		return $name;
	}

    /**
     * @param float $minTimeMS
     * @param InternalCallTree $call
     * @param int $indent
     * @param string $filterTo
     * @param string $filterFrom
     * @return Result[]
     */
	function callStackToResults(float $minTimeMS, InternalCallTree $call, int $indent, ?string $filterTo, ?string $filterFrom) : array
	{
		/** @var Result[] $r */
		$r = [];
		foreach ($call->children as $c)
		{
			if (($c->dt === null || $c->dt >= $minTimeMS)
			 && $this->callStackThisOrChildrenHasName($c, $filterTo)
			 && ($this->callStackThisOrParentsHasName($c, $filterFrom) || $this->callStackThisOrChildrenHasName($c, $filterFrom))
			)
			{
				$prefix = str_pad("", $indent * 2);
				$r[] = new Result($prefix . $c->name . ($c->subname !== null ? " / " . $c->subname : ""), $c->dt, 1);
				$r = array_merge($r, $this->callStackToResults($minTimeMS, $c, $indent + 2, $filterTo, $filterFrom));
			}
		}
		return $r;
	}
	
	function callStackThisOrChildrenHasName(InternalCallTree $call, ?string $filter) : bool
	{
		if ($filter === null || $filter === "") return true;
		if ($call->name . ($call->subname !== null ? " / " . $call->subname : "") === $filter) return true;
		foreach ($call->children as $c)
		{
		    if ($this->callStackThisOrChildrenHasName($c, $filter)) return true;
		}
		return false;
	}
	
	function callStackThisOrParentsHasName(?InternalCallTree $call, ?string $filter) : bool
	{
		if ($filter === null || $filter === "") return true;
		if ($call === null || $call->name === null) return false;
		if ($call->name . ($call->subname !== null ? " / " . $call->subname : 	"") === $filter) return true;
		return $this->callStackThisOrParentsHasName($call->parent, $filter);
	}
}
