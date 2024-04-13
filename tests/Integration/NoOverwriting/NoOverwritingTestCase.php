<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\NoOverwriting;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

abstract class NoOverwritingTestCase extends CommandTestCase
{
    public function testDownload(): void
    {
        $this->runComposerCommandAndAssert(['install']);
    }

    protected function assertComposerOutput(string $output): void
    {
        $this->assertStringContainsString($this->getInfoMessage(), $output);
    }

    abstract protected function getInfoMessage(): string;
}
