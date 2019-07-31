# smart-profiler

[![Build Status](https://travis-ci.org/yar3333/smart-profiler.svg?branch=master)](https://travis-ci.org/yar3333/smart-profiler)
[![Latest Stable Version](https://poser.pugx.org/smart-profiler/smart-profiler/version)](https://packagist.org/packages/smart-profiler/smart-profiler)
[![Total Downloads](https://poser.pugx.org/smart-profiler/smart-profiler/downloads)](https://packagist.org/packages/smart-profiler/smart-profiler)

Profiling library for PHP 7.1+

## Installation
```
composer require "smart-profiler/smart-profiler"
```

## Using
```php
<?php

use SmartProfiler\Profiler;

Profiler::reset(10); // specify max nesting level!

Profiler::begin("MainSection");
    sleep(1);
    Profiler::begin("SubSection");
        sleep(2);
    Profiler::end();
Profiler::end();

echo "Results:\n" . Profiler::getCallStackResultsAsText();
```

Output:
```
Results:
3003 | MainSection
2001 |     SubSection
```
Numbers like `3003` means 3.003 seconds.

## Measuring via begin() / end()
```php
Profiler::begin("myCodeA");
// code to measure duration
Profiler::end();
```

## Measuring via measure()
```php
Profiler::measure("myCodeA", null, function()
{
    // code to measure duration
});

$result = Profiler::measure("myCodeB", null, function()
{
    // code to measure duration
    return "abc"; // result
});
```

## Getting collected data ##
```php
// print summary
Profiler::traceResults();

// get all calls as flat array
$results = Profiler::getCallStackResults();

// get all calls as tree
$callTree = Profiler::getCallStack();
// it is very useful to generate human-readable json from this
echo json_encode([ 'name' => 'myApp', 'stack' => $callTree ]);

// or just use next
echo Profiler::getCallStackResultsAsText();
```