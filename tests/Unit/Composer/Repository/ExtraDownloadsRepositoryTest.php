<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Repository;

use Composer\Installer\InstallationManager;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Repository\InvalidRepositoryException;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use LastCall\DownloadsPlugin\Composer\Repository\ExtraDownloadsRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Seld\JsonLint\ParsingException;
use VirtualFileSystem\FileSystem;

class ExtraDownloadsRepositoryTest extends TestCase
{
    private ?FileSystem $fs = null;
    private PackageInterface|MockObject $package;
    private ExtraDownloadInterface|MockObject $extraDownload;
    private ExtraDownloadsRepository $repository;
    private string $path = '/path/to/vendor/composer/installed-extra-downloads.json';
    private string $name = 'vendor/package-name:extra-file';
    private string $trackingChecksum = '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08';

    protected function setUp(): void
    {
        $this->fs = new FileSystem();
        $this->package = $this->createMock(PackageInterface::class);
        $this->extraDownload = $this->createMock(ExtraDownloadInterface::class);
        $this->repository = new ExtraDownloadsRepository(new JsonFile($this->fs->path($this->path)));
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testAddPackage(): void
    {
        $this->repository->addPackage($this->package);
        $this->assertExtraDownloads([]);
    }

    public function testAddExtraDownload(): void
    {
        $this->extraDownload
            ->expects($this->once())
            ->method('getName')
            ->willReturn($this->name);
        $this->extraDownload
            ->expects($this->once())
            ->method('getTrackingChecksum')
            ->willReturn($this->trackingChecksum);
        $this->repository->addPackage($this->extraDownload);
        $this->assertExtraDownloads([
            $this->name => $this->trackingChecksum,
        ]);
    }

    public function testRemovePackage(): void
    {
        $this->repository->removePackage($this->package);
        $this->assertExtraDownloads([]);
    }

    public function testRemoveExtraDownload(): void
    {
        $this->extraDownload
            ->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($this->name);
        $this->extraDownload
            ->expects($this->once())
            ->method('getTrackingChecksum')
            ->willReturn($this->trackingChecksum);
        $this->repository->addPackage($this->extraDownload);
        $this->assertExtraDownloads([
            $this->name => $this->trackingChecksum,
        ]);
        $this->repository->removePackage($this->extraDownload);
        $this->assertExtraDownloads([]);
    }

    public function testHasPackage(): void
    {
        $this->assertFalse($this->repository->hasPackage($this->package));
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testHasExtraDownload(bool $sameTrackingChecksum): void
    {
        $this->extraDownload
            ->expects($this->exactly(3))
            ->method('getName')
            ->willReturn($this->name);
        $trackingChecksum = $sameTrackingChecksum ? $this->trackingChecksum : '2cf24dba5fb0a30e26e83b2ac5b9e29e1b161e5c1fa7425e73043362938b9824';
        $this->extraDownload
            ->expects($this->exactly(2))
            ->method('getTrackingChecksum')
            ->willReturnOnConsecutiveCalls($this->trackingChecksum, $trackingChecksum);
        $this->assertFalse($this->repository->hasPackage($this->extraDownload));
        $this->repository->addPackage($this->extraDownload);
        $this->assertSame($sameTrackingChecksum, $this->repository->hasPackage($this->extraDownload));
    }

    public function testIsTracked(): void
    {
        $this->extraDownload
            ->expects($this->exactly(3))
            ->method('getName')
            ->willReturn($this->name);
        $this->extraDownload
            ->expects($this->once())
            ->method('getTrackingChecksum')
            ->willReturn($this->trackingChecksum);
        $this->assertFalse($this->repository->isTracked($this->extraDownload));
        $this->repository->addPackage($this->extraDownload);
        $this->assertTrue($this->repository->isTracked($this->extraDownload));
    }

    public function testReloadFromInvalidFile(): void
    {
        $this->fs->createDirectory(\dirname($this->path), true);
        $this->fs->createFile($this->path, 'not json data');
        $this->expectException(InvalidRepositoryException::class);
        $this->expectExceptionMessage('Invalid repository data in '.$this->fs->path($this->path).', packages could not be loaded: ['.ParsingException::class.'] "'.$this->fs->path($this->path).'" does not contain valid JSON');
        $this->repository->reload();
    }

    public function testReloadFromJsonString(): void
    {
        $this->fs->createDirectory(\dirname($this->path), true);
        $this->fs->createFile($this->path, json_encode('some text'));
        $this->expectException(InvalidRepositoryException::class);
        $this->expectExceptionMessage('Invalid repository data in '.$this->fs->path($this->path).', packages could not be loaded: ['.\UnexpectedValueException::class.'] Could not parse package list from the repository');
        $this->repository->reload();
    }

    public function testReload(): void
    {
        $this->fs->createDirectory(\dirname($this->path), true);
        $this->fs->createFile($this->path, json_encode([
            $this->name => $this->trackingChecksum,
        ]));
        $this->repository->reload();
        $this->assertExtraDownloads([
            $this->name => $this->trackingChecksum,
        ]);
    }

    public function testIsFresh(): void
    {
        $this->assertTrue($this->repository->isFresh());
        $this->fs->createDirectory(\dirname($this->path), true);
        $this->fs->createFile($this->path, 'data');
        $this->assertFalse($this->repository->isFresh());
    }

    public function testWrite(): void
    {
        $this->extraDownload
            ->expects($this->once())
            ->method('getName')
            ->willReturn($this->name);
        $this->extraDownload
            ->expects($this->once())
            ->method('getTrackingChecksum')
            ->willReturn($this->trackingChecksum);
        $this->repository->addPackage($this->extraDownload);
        $this->assertFileDoesNotExist($this->fs->path($this->path));
        $this->repository->write(true, $this->createMock(InstallationManager::class));
        $this->assertFileExists($this->fs->path($this->path));
        $this->assertJsonStringEqualsJsonString(json_encode([
            $this->name => $this->trackingChecksum,
        ]), file_get_contents($this->fs->path($this->path)));
    }

    private function assertExtraDownloads(array $extraDownloads): void
    {
        $reflection = new \ReflectionProperty($this->repository, 'extraDownloads');
        $this->assertSame($extraDownloads, $reflection->getValue($this->repository));
    }

    public function testGetCanonicalPackages(): void
    {
        $this->assertSame([], $this->repository->getCanonicalPackages());
    }

    public function testGetDevPackageNames(): void
    {
        $this->assertSame([], $this->repository->getDevPackageNames());
    }

    public function testCount(): void
    {
        $this->assertSame(0, $this->repository->count());
    }

    public function testGetRepoName(): void
    {
        $this->assertSame('installed extra downloads', $this->repository->getRepoName());
    }

    public function testGetDevMode(): void
    {
        $this->assertNull($this->repository->getDevMode());
    }

    public function testFindPackage(): void
    {
        $this->assertNull($this->repository->findPackage('any package name', 'any constraint'));
    }

    public function testFindPackages(): void
    {
        $this->assertSame([], $this->repository->findPackages('any package name', 'any constraint'));
    }

    public function testGetPackages(): void
    {
        $this->assertSame([], $this->repository->getPackages());
    }

    public function testGetProviders(): void
    {
        $this->assertSame([], $this->repository->getProviders('any package name'));
    }

    public function testLoadPackages(): void
    {
        $this->assertSame([], $this->repository->loadPackages([], [], [], []));
    }

    public function testSearch(): void
    {
        $this->assertSame([], $this->repository->search('package query'));
    }
}
