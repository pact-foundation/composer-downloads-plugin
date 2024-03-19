<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use LastCall\DownloadsPlugin\Handler\TarHandler;

class TarHandlerTest extends ArchiveHandlerTestCase
{
    protected function getHandlerClass(): string
    {
        return TarHandler::class;
    }

    protected function getSubpackageType(): string
    {
        return 'tar';
    }

    protected function getChecksum(): string
    {
        return '96cf608fbcfb78ac663ca97cd28ed7f2120630bd2fb87cfc1e95dd79a54d5052';
    }
}
