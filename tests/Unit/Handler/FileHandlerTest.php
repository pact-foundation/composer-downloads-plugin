<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use LastCall\DownloadsPlugin\Handler\FileHandler;

class FileHandlerTest extends BaseHandlerTestCase
{
    protected function getTrackingFile(): string
    {
        return \dirname($this->targetPath).\DIRECTORY_SEPARATOR.'.composer-downloads'.\DIRECTORY_SEPARATOR.'sub-package-name-4fcb9a7a2ac376c89d1d147894dca87b.json';
    }

    protected function getHandlerClass(): string
    {
        return FileHandler::class;
    }

    protected function getSubpackageType(): string
    {
        return 'file';
    }

    protected function getChecksum(): string
    {
        return 'eb2ddda74e9049129d10e5b75520a374820a3826ca86a99f72a300cfe57320cc';
    }

    protected function getExecutableType(): string
    {
        return 'boolean';
    }

    protected function getTrackingData(): array
    {
        return [
            'name' => "{$this->parentName}:{$this->id}",
            'url' => $this->url,
            'checksum' => $this->getChecksum(),
        ];
    }

    /**
     * @testWith [true, true]
     *           [true, false]
     *           [false, true]
     */
    public function testInstall(bool $hasHash, bool $isValid): void
    {
        $this->assertDownload();
        $this->assertValidateDownloadedFile($hasHash, $isValid);
        $this->assertBinariesInstaller($isValid);
        $this->assertMoveDownloadedFile($isValid);
        $this->assertRemoveDownloadedFile($isValid);
        $this->expectInvalidDownloadedFileException($isValid);
        $this->handler->install($this->composer, $this->io);
    }

    private function assertMoveDownloadedFile(bool $isValid): void
    {
        $this->filesystem
            ->expects($this->exactly($isValid))
            ->method('rename')
            ->with($this->getTmpFilePath(), $this->targetPath);
    }
}
