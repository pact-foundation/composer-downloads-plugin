<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Factory;

use LastCall\DownloadsPlugin\Composer\Package\ExtraArchive;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Factory\ExtraArchiveFactory;
use LastCall\DownloadsPlugin\Factory\ExtraDownloadFactoryInterface;

class ExtraArchiveFactoryTest extends ExtraDownloadFactoryTest
{
    protected function createFactory(): ExtraDownloadFactoryInterface
    {
        return new ExtraArchiveFactory($this->attributeManager);
    }

    protected function getExtraDownloadClass(): string
    {
        return ExtraArchive::class;
    }

    protected function getAttributesMap(): array
    {
        return [
            ...parent::getAttributesMap(),
            [Attribute::IGNORE, [
                'dir/*',
                '!dir/file1',
            ]],
        ];
    }
}
