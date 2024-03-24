<?php

namespace LastCall\DownloadsPlugin\Tests\Integration\Valid;

use LastCall\DownloadsPlugin\Tests\Integration\CommandTestCase;

class InstallThenRemovePackageTest extends CommandTestCase
{
    private bool $isRemoved;

    /**
     * @testWith [["install"]]
     *           [["remove", "test/library"]]
     */
    public function testDownload(array $command): void
    {
        $this->isRemoved = $command === ['remove', 'test/library'];
        $this->runComposerCommandAndAssert($command);
    }

    protected function getFilesFromLibrary(): array
    {
        if ($this->isRemoved) {
            return array_fill_keys(array_keys(parent::getFilesFromLibrary()), false);
        } else {
            return parent::getFilesFromLibrary();
        }
    }

    protected function getExecutableFilesFromLibrary(): array
    {
        if ($this->isRemoved) {
            return [];
        } else {
            return parent::getExecutableFilesFromLibrary();
        }
    }

    protected function getMissingExecutableFiles(): array
    {
        if ($this->isRemoved) {
            return array_keys(parent::getExecutableFilesFromLibrary());
        } else {
            return parent::getMissingExecutableFiles();
        }
    }
}
