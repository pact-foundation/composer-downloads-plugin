<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Installer;

use Composer\Installer\InstallerInterface;
use Composer\Util\Filesystem;
use LastCall\DownloadsPlugin\Composer\Installer\FileInstaller;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use PHPUnit\Framework\MockObject\MockObject;
use React\Promise\Promise;

class FileInstallerTest extends AbstractInstallerTestCase
{
    private Filesystem|MockObject $filesystem;
    private string $tmpDir;
    private string $fileName = 'file.ext';
    private string $fileContents = 'file contents';

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        parent::setUp();
    }

    protected function createInstaller(): InstallerInterface
    {
        return new FileInstaller($this->io, $this->composer, $this->filesystem, $this->executableInstaller);
    }

    /**
     * @testWith ["extra-download:file", true]
     *           ["extra-download:archive", false]
     */
    public function testSupports(string $type, bool $supports): void
    {
        $this->assertSame($supports, $this->installer->supports($type));
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testInstallExtraDownload(bool $hasPackage): void
    {
        $this->composer
            ->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($this->downloadManager);
        $this->downloadManager
            ->expects($this->once())
            ->method('install')
            ->with($this->extraDownload, $this->callback(function (string $targetDir): bool {
                $this->assertTrue(str_contains($targetDir, FileInstaller::TMP_PREFIX));

                return true;
            }))
            ->willReturnCallback(function (ExtraDownloadInterface $extraDownload, string $tmpDir) {
                $this->tmpDir = $tmpDir;
                mkdir($this->tmpDir, 0777, true);
                file_put_contents($this->tmpDir.\DIRECTORY_SEPARATOR.$this->fileName, $this->fileContents);

                $this->filesystem
                    ->expects($this->once())
                    ->method('rename')
                    ->with($this->tmpDir.\DIRECTORY_SEPARATOR.$this->fileName, $this->fs->path($this->installPath));
                $this->filesystem
                    ->expects($this->once())
                    ->method('remove')
                    ->with($this->tmpDir);

                $downloaderPromise = new Promise(fn (callable $resolve) => $resolve(null));

                return $downloaderPromise;
            });
        $this->extraDownload
            ->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->fs->path($this->installPath));
        $this->executableInstaller
            ->expects($this->once())
            ->method('install')
            ->with($this->extraDownload);
        $this->repository
            ->expects($this->once())
            ->method('hasPackage')
            ->with($this->extraDownload)
            ->willReturn($hasPackage);
        $this->repository
            ->expects($this->exactly(!$hasPackage))
            ->method('addPackage')
            ->with($this->extraDownload);
        $installerPromise = $this->installer->install($this->repository, $this->extraDownload);
        $installerPromise->then(function ($result) {
            $this->assertNull($result);
        });
    }
}
