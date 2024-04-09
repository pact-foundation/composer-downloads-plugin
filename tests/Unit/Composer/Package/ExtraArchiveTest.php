<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Package;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraArchive;
use LastCall\DownloadsPlugin\Enum\Type;
use LastCall\DownloadsPlugin\Model\Hash;

class ExtraArchiveTest extends ExtraDownloadTest
{
    private array $ignore = [
        'dir/*',
        '!dir/file1',
    ];
    protected Type $type = Type::RAR;

    public function testGetTrackingChecksum(): void
    {
        $this->assertSame('da040863fb9a0360909cc295d483dd8bdcc6578f66834b3793cc91bf6869814c', $this->extraDownload->getTrackingChecksum());
    }

    protected function createExtraDownload(PackageInterface $parent, ?Hash $hash, ?array $ignore = null): void
    {
        $this->extraDownload = new ExtraArchive(
            $parent,
            $this->name,
            $this->version,
            $hash,
            Type::RAR,
            $this->executable,
            $this->url,
            $this->path,
            $ignore ?? $this->ignore
        );
    }
}
