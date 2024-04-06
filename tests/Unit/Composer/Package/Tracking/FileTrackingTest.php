<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Package\Tracking;

use LastCall\DownloadsPlugin\Composer\Package\Tracking\FileTracking;
use LastCall\DownloadsPlugin\Composer\Package\Tracking\TrackingInterface;
use LastCall\DownloadsPlugin\Enum\Type;
use PHPUnit\Framework\TestCase;

class FileTrackingTest extends TestCase
{
    protected TrackingInterface $tracking;
    protected string $name = 'normal-file';
    protected string $url = 'http://example.com/file.zip';
    protected string $path = 'path/to/dir';
    protected Type $type = Type::ZIP;
    protected array $executable = [
        'file1',
        'path/to/file2',
    ];

    protected function setUp(): void
    {
        $this->tracking = new FileTracking($this->name, $this->url, $this->path, $this->type, $this->executable);
    }

    public function testGetChecksum(): void
    {
        $this->assertSame('d2ffb87c3fc709cc36cc08791b1b1dfb6292d0d403fc091a9f5584768c0f35fa', $this->tracking->getChecksum());
    }
}
