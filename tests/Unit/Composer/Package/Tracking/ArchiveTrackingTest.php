<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Package\Tracking;

use LastCall\DownloadsPlugin\Composer\Package\Tracking\ArchiveTracking;

class ArchiveTrackingTest extends FileTrackingTest
{
    private array $ignore = [
        'dir/*',
        '!dir/file1',
    ];

    protected function setUp(): void
    {
        $this->tracking = new ArchiveTracking($this->name, $this->url, $this->path, $this->type, $this->executable, $this->ignore);
    }

    public function testGetChecksum(): void
    {
        $this->assertSame('f876342d677c28a6f3010c62ef052d9ec305c1d5e83ed0f4095dca2d2a79fda5', $this->tracking->getChecksum());
    }
}
