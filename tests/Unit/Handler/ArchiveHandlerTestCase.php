<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use LastCall\DownloadsPlugin\GlobCleaner;
use PHPUnit\Framework\MockObject\MockObject;

abstract class ArchiveHandlerTestCase extends BaseHandlerTestCase
{
    private GlobCleaner|MockObject $cleaner;

    protected function setUp(): void
    {
        $this->cleaner = $this->createMock(GlobCleaner::class);
        parent::setUp();
        $this->extraFile += [
            'ignore' => $this->ignore,
        ];
    }

    protected function getHandlerExtraArguments(): array
    {
        return [$this->cleaner];
    }

    protected function getTrackingFile(): string
    {
        return $this->targetPath.\DIRECTORY_SEPARATOR.'.composer-downloads'.\DIRECTORY_SEPARATOR.'sub-package-name-4fcb9a7a2ac376c89d1d147894dca87b.json';
    }

    protected function getExecutableType(): string
    {
        return 'array';
    }

    protected function getTrackingData(): array
    {
        return [
            'ignore' => $this->ignore,
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
        $this->assertExtract($isValid);
        $this->assertBinariesInstaller($isValid);
        $this->assertClean($isValid);
        $this->assertRemoveDownloadedFile($isValid);
        $this->expectInvalidDownloadedFileException($isValid);
        $this->handler->install($this->composer, $this->io);
    }

    private function assertExtract(bool $isValid): void
    {
        if ($isValid) {
            $this->downloadManager
                ->expects($this->once())
                ->method('install')
                ->with($this->subpackage, $this->targetPath)
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

    private function assertClean(bool $isValid): void
    {
        $this->cleaner
            ->expects($this->exactly($isValid))
            ->method('clean')
            ->with($this->targetPath, $this->ignore);
    }
}
