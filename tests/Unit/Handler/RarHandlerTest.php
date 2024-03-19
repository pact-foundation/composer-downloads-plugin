<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use LastCall\DownloadsPlugin\Handler\RarHandler;

class RarHandlerTest extends ArchiveHandlerTestCase
{
    protected function getHandlerClass(): string
    {
        return RarHandler::class;
    }

    protected function getSubpackageType(): string
    {
        return 'rar';
    }

    protected function getChecksum(): string
    {
        return 'c040d81f1f697b06cef6db7e915786b649dd7e2f9ae106e97889cf37bcbc613d';
    }
}
