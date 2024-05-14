<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Parser;

use LastCall\DownloadsPlugin\Helper\MuslDetector;
use PHPUnit\Framework\TestCase;

class MuslDetectorTest extends TestCase
{
    public function testInvoke(): void
    {
        $detector = new MuslDetector();
        // @todo Test when the result is true
        $this->assertFalse($detector());
    }
}
