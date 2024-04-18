<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Installer;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\Installer\InstallerInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use LastCall\DownloadsPlugin\Exception\ExtraDownloadHashMismatchException;
use LastCall\DownloadsPlugin\Installer\ExecutableInstallerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use VirtualFileSystem\FileSystem as VirtualFileSystem;

abstract class AbstractInstallerTestCase extends TestCase
{
    private ?VirtualFileSystem $fs = null;
    protected Composer|MockObject $composer;
    protected IOInterface|MockObject $io;
    protected DownloadManager|MockObject $downloadManager;
    protected ExecutableInstallerInterface|MockObject $executableInstaller;
    protected InstalledRepositoryInterface|MockObject $repository;
    protected ExtraDownloadInterface|MockObject $extraDownload;
    protected PackageInterface|MockObject $package;
    protected InstallerInterface $installer;
    protected string $installPath = '/path/to/package/files/new-file';
    private string $url = 'http://example.com/file.ext';
    private string $name = 'vendor/parent-package:extra-download-name';
    private string $targetDir = 'files/new-file';

    protected function setUp(): void
    {
        $this->fs = new VirtualFileSystem();
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->downloadManager = $this->createMock(DownloadManager::class);
        $this->executableInstaller = $this->createMock(ExecutableInstallerInterface::class);
        $this->repository = $this->createMock(InstalledRepositoryInterface::class);
        $this->extraDownload = $this->createMock(ExtraDownloadInterface::class);
        $this->package = $this->createMock(PackageInterface::class);
        $this->installer = $this->createInstaller();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testIsInstalledPackage(): void
    {
        $this->assertFalse($this->installer->isInstalled($this->repository, $this->package));
    }

    public function getIsInstalledExtraDownloadTests(): array
    {
        return [
            [true, true, true],
            [false, true, true],
            [true, false, false],
            [false, false, false],
        ];
    }

    /**
     * @dataProvider getIsInstalledExtraDownloadTests
     */
    public function testIsInstalledExtraDownload(bool $hasPackage, bool $fileExists, bool $isInstalled): void
    {
        $this->repository
            ->expects($this->once())
            ->method('hasPackage')
            ->with($this->extraDownload)
            ->willReturn($hasPackage);
        $this->extraDownload
            ->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->fs->path($this->installPath));
        if ($fileExists) {
            $this->fs->createDirectory(\dirname($this->installPath), true);
            $this->fs->createFile($this->installPath, 'downloaded file');
        }
        if ($fileExists) {
            $this->extraDownload
                ->expects($this->once())
                ->method('getName')
                ->willReturn($this->name);
        }
        if (!$hasPackage && $fileExists) {
            $this->extraDownload
                ->expects($this->once())
                ->method('getTargetDir')
                ->willReturn($this->targetDir);
            $this->io
                ->expects($this->once())
                ->method('write')
                ->with(
                    sprintf(
                        '<info>Extra file <comment>%s</comment> has been locally overriden in <comment>%s</comment>. To reset it, delete and reinstall.</info>',
                        $this->name,
                        $this->targetDir,
                    ),
                    true
                );
        }
        if ($hasPackage && $fileExists) {
            $this->io
                ->expects($this->once())
                ->method('write')
                ->with(
                    sprintf('<info>Skip extra file <comment>%s</comment></info>', $this->name),
                    true,
                    IOInterface::VERY_VERBOSE
                );
        }
        $this->assertSame($isInstalled, $this->installer->isInstalled($this->repository, $this->extraDownload));
    }

    public function testDownloadPackage(): void
    {
        $promise = $this->installer->download($this->package);
        $promise->then(function ($result) {
            $this->assertNull($result);
        });
    }

    public function testDownloadValidExtraFile(): void
    {
        $this->composer
            ->expects($this->once())
            ->method('getDownloadManager')
            ->willReturn($this->downloadManager);
        $targetDir = \dirname($this->installPath);
        $downloadedPath = '/path/to/vendor/composer/tmp-abc123.ext';
        $downloaderPromise = new Promise(fn (callable $resolve) => $resolve($downloadedPath));
        $this->downloadManager
            ->expects($this->once())
            ->method('download')
            ->with($this->extraDownload, $targetDir)
            ->willReturn($downloaderPromise);
        $this->extraDownload
            ->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->installPath);
        $this->extraDownload
            ->expects($this->once())
            ->method('verifyFile')
            ->with($downloadedPath)
            ->willReturn(true);
        $installerPromise = $this->installer->download($this->extraDownload);
        $installerPromise->then(function ($result) use ($downloadedPath) {
            $this->assertSame($downloadedPath, $result);
        });
    }

    public function testDownloadInvalidExtraFile(): void
    {
        $this->composer
            ->expects($this->exactly(2))
            ->method('getDownloadManager')
            ->willReturn($this->downloadManager);
        $targetDir = \dirname($this->installPath);
        $downloadedPath = '/path/to/vendor/composer/tmp-abc123.ext';
        $downloaderPromise = new Promise(fn (callable $resolve) => $resolve($downloadedPath));
        $this->downloadManager
            ->expects($this->once())
            ->method('download')
            ->with($this->extraDownload, $targetDir)
            ->willReturn($downloaderPromise);
        $this->extraDownload
            ->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->installPath);
        $this->extraDownload
            ->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->installPath);
        $this->extraDownload
            ->expects($this->once())
            ->method('getDistUrl')
            ->willReturn($this->url);
        $this->extraDownload
            ->expects($this->once())
            ->method('getName')
            ->willReturn($this->name);
        $this->extraDownload
            ->expects($this->once())
            ->method('verifyFile')
            ->with($downloadedPath)
            ->willReturn(false);
        $this->io
            ->expects($this->once())
            ->method('error')
            ->with(sprintf('    Extra file "%s" does not match hash value defined in "%s".', $this->url, $this->name));
        $this->downloadManager
            ->expects($this->once())
            ->method('cleanup')
            ->with('install', $this->extraDownload, $targetDir);
        $installerPromise = $this->installer->download($this->extraDownload);
        $installerPromise->then(null, function ($result) {
            $this->assertInstanceOf(ExtraDownloadHashMismatchException::class, $result);
        });
    }

    public function testPrepare(): void
    {
        $promise = $this->installer->prepare('install', $this->extraDownload);
        $promise->then(function ($result) {
            $this->assertNull($result);
        });
    }

    public function testCleanup(): void
    {
        $promise = $this->installer->cleanup('install', $this->extraDownload);
        $promise->then(function ($result) {
            $this->assertNull($result);
        });
    }

    public function testUpdate(): void
    {
        $promise = $this->installer->update($this->repository, $this->extraDownload, $this->extraDownload);
        $promise->then(function ($result) {
            $this->assertNull($result);
        });
    }

    public function testUninstall(): void
    {
        $promise = $this->installer->uninstall($this->repository, $this->extraDownload);
        $promise->then(function ($result) {
            $this->assertNull($result);
        });
    }

    public function testPackageInstallPath(): void
    {
        $this->assertNull($this->installer->getInstallPath($this->package));
    }

    public function testExtraDownloadInstallPath(): void
    {
        $this->extraDownload
            ->expects($this->once())
            ->method('getInstallPath')
            ->willReturn($this->installPath);
        $this->assertSame($this->installPath, $this->installer->getInstallPath($this->extraDownload));
    }

    public function testInstallPackage(): void
    {
        $promise = $this->installer->install($this->repository, $this->package);
        $promise->then(function ($result) {
            $this->assertNull($result);
        });
    }

    abstract protected function createInstaller(): InstallerInterface;
}
