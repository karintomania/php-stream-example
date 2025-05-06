<?php

namespace Tests;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use StreamTest\StreamScanner;

class StreamScannerTest extends TestCase
{
    public function test_example() {
        // Mock HTTP stream with file stream
        $f = fopen(__DIR__ . '/data.json', 'r');
        $stream = Utils::streamFor($f);

        $handler = new StreamScanner();

        $counter = 1;
        foreach($handler->__invoke($stream) as $object) {
            if ($object !== null) {
                $this->assertEquals($counter, $object->id);
                $this->assertEquals(sprintf('Item %d', $counter), $object->name);
                $counter ++;
            }
        }
    }
}
