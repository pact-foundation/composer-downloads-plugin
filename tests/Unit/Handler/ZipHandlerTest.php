<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use LastCall\DownloadsPlugin\Handler\ZipHandler;

class ZipHandlerTest extends ArchiveHandlerTestCase
{
    protected function getHandlerClass(): string
    {
        return ZipHandler::class;
    }

    protected function getSubpackageType(): string
    {
        return 'zip';
    }

    protected function getChecksum(): string
    {
        return 'dee14fc8dcfc413b334ff218be5732736b5f9c07c3988bb8742d80f74f40ff0a';
    }
}
