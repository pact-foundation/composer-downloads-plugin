<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Installer;

use Composer\Installer\InstallerInterface;
use LastCall\DownloadsPlugin\Composer\Installer\ArchiveInstaller;
use LastCall\DownloadsPlugin\Composer\Package\ExtraArchiveInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use React\Promise\Promise;

class ArchiveInstallerTest extends AbstractInstallerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->extraDownload = $this->createMock(ExtraArchiveInterface::class);
    }

    protected function createInstaller(): InstallerInterface
    {
        return new ArchiveInstaller($this->io, $this->composer, $this->executableInstaller);
    }

    /**
     * @testWith ["extra-download:file", false]
     *           ["extra-download:archive", true]
     */
    public function testSupports(string $type, bool $supports): void
    {
        $this->assertSame($supports, $this->installer->supports($type));
    }

    public function testInstallExtraDownload(): void
    {
        $package = $this->createMock(ExtraDownloadInterface::class);
        $promise = $this->installer->install($this->repository, $package);
        $promise->then(function ($result) {
            $this->assertNull($result);
        });
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testInstallExtraArchive(bool $hasPackage): void
    {
        $this->composer
            ->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($this->downloadManager);
        $downloaderPromise = new Promise(fn (callable $resolver) => $resolver(null));
        $this->downloadManager
            ->expects($this->once())
            ->method('install')
            ->with($this->extraDownload, $this->installPath)
            ->willReturn($downloaderPromise);
        $this->extraDownload
            ->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->installPath);
        $this->extraDownload
            ->expects($this->once())
            ->method('clean');
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
