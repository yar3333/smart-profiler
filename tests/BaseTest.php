<?php

namespace SmartProfilerTests;

/** @noinspection PhpIncludeInspection */
require_once 'autoload.php'; 

use PHPUnit\Framework\TestCase;
use SmartProfiler\Profiler;

class BaseTest extends TestCase
{
    public function testSuccess(): void
    {
        Profiler::reset(10);

        Profiler::begin("SectionA");
        usleep(500 * 1000);
        Profiler::end();

        $text = Profiler::getCallStackResultsAsText();

        self::assertEquals("0500 | SectionA", $text);
    }
}
