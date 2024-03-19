<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use LastCall\DownloadsPlugin\Handler\FileHandler;
use LastCall\DownloadsPlugin\Handler\GzipHandler;
use PHPUnit\Framework\Constraint\Callback;

class GzipHandlerTest extends FileHandlerTest
{
    protected function getHandlerClass(): string
    {
        return GzipHandler::class;
    }

    protected function getSubpackageType(): string
    {
        return 'gzip';
    }

    protected function getChecksum(): string
    {
        return 'bb11858b3513500b4c3d234a17a8ea5f6790444cb93c457259a861d1682aec60';
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
        $this->assertExtract($isValid);
        $this->assertBinariesInstaller($isValid);
        $this->assertMoveTempFile($isValid);
        $this->assertRemoveFile($isValid);
        $this->expectInvalidDownloadedFileException($isValid);
        $this->handler->install($this->composer, $this->io);
    }

    private function assertRemoveFile(bool $isValid): void
    {
        if ($isValid) {
            $this->filesystem
                ->expects($this->once())
                ->method('remove')
                ->with($this->isTempDir());
        } else {
            $this->assertRemoveDownloadedFile(false);
        }
    }

    private function assertExtract(bool $isValid): void
    {
        if ($isValid) {
            $this->downloadManager
                ->expects($this->once())
                ->method('install')
                ->with($this->subpackage, $this->isTempDir())
                ->willReturn($this->installPromise);
            $this->loop
                ->expects($this->at(1))
                ->method('wait')
                ->with([$this->installPromise]);
        } else {
            $this->downloadManager
                ->expects($this->never())
                ->method('install');
        }
    }

    private function assertMoveTempFile(bool $isValid): void
    {
        $this->filesystem
            ->expects($this->exactly($isValid))
            ->method('rename')
            ->with($this->isTempDir(), $this->targetPath);
    }

    private function isTempDir(): Callback
    {
        return $this->callback(function (string $dir): bool {
            $this->assertStringContainsString(FileHandler::TMP_PREFIX, $dir);

            return true;
        });
    }
}
