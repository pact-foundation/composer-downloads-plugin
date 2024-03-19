<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Handler;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Util\Filesystem;
use Composer\Util\Loop;
use LastCall\DownloadsPlugin\BinariesInstaller;
use LastCall\DownloadsPlugin\Exception\InvalidDownloadedFileException;
use LastCall\DownloadsPlugin\Handler\HandlerInterface;
use LastCall\DownloadsPlugin\Model\Hash;
use LastCall\DownloadsPlugin\Model\Version;
use LastCall\DownloadsPlugin\Subpackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;
use VirtualFileSystem\FileSystem as VirtualFileSystem;

abstract class BaseHandlerTestCase extends TestCase
{
    private ?VirtualFileSystem $fs = null;
    protected Composer|MockObject $composer;
    protected IOInterface|MockObject $io;
    protected DownloadManager|MockObject $downloadManager;
    private BinariesInstaller|MockObject $binariesInstaller;
    protected PromiseInterface|MockObject $downloadPromise;
    protected PromiseInterface|MockObject $installPromise;
    protected Loop|MockObject $loop;
    protected Filesystem|MockObject $filesystem;
    protected Subpackage $subpackage;
    protected HandlerInterface $handler;
    private string $parentPath = '/path/to/package';
    protected string $id = 'sub-package-name';
    protected string $url = 'http://example.com/file.ext';
    protected string $path = 'files/new-file';
    protected array $extraFile;
    protected string $targetPath;
    protected array $ignore = ['file.*', '!file.ext'];
    protected string $parentName = 'vendor/parent-package';
    private string $tmpFile = '/path/to/vendor/composer/tmp-random';

    protected function setUp(): void
    {
        $this->fs = new VirtualFileSystem(); // Keep virtual file system alive during test
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->downloadManager = $this->createMock(DownloadManager::class);
        $this->binariesInstaller = $this->createMock(BinariesInstaller::class);
        $this->downloadPromise = $this->createMock(PromiseInterface::class);
        $this->installPromise = $this->createMock(PromiseInterface::class);
        $this->loop = $this->createMock(Loop::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->extraFile = [
            'id' => $this->id,
            'url' => $this->url,
            'path' => $this->path,
        ];
        $this->targetPath = $this->parentPath.\DIRECTORY_SEPARATOR.$this->path;
        $this->subpackage = new Subpackage(
            new Package($this->parentName, '1.0.0', 'v1.0.0'),
            $this->parentPath,
            $this->id,
            $this->getSubpackageType(),
            ['file1', 'dir/file2'],
            $this->ignore,
            $this->url,
            $this->path,
            new Version('1.2.3.0', 'v1.2.3'),
        );
        $this->handler = $this->createHandler();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testGetSubpackage(): void
    {
        $this->assertSame($this->subpackage, $this->handler->getSubpackage());
    }

    public function testGetTrackingData(): void
    {
        $this->assertSame($this->getTrackingData(), $this->handler->getTrackingData());
    }

    public function testGetChecksum(): void
    {
        $this->assertSame($this->getChecksum(), $this->handler->getChecksum());
    }

    public function testGetTrackingFile(): void
    {
        $this->assertSame($this->getTrackingFile(), $this->handler->getTrackingFile());
    }

    protected function assertBinariesInstaller(bool $isValid): void
    {
        $this->binariesInstaller
            ->expects($this->exactly($isValid))
            ->method('install')
            ->with($this->isInstanceOf(Subpackage::class), $this->io);
    }

    protected function assertDownload(): void
    {
        $this->composer->expects($this->any())->method('getDownloadManager')->willReturn($this->downloadManager);
        $this->downloadPromise
            ->expects($this->once())
            ->method('then')
            ->willReturnCallback(fn (callable $callback) => $callback($this->getTmpFilePath()));
        $this->downloadManager
            ->expects($this->once())
            ->method('download')
            ->with($this->subpackage, \dirname($this->targetPath))
            ->willReturn($this->downloadPromise);
        $this->loop
            ->expects($this->at(0))
            ->method('wait')
            ->with([$this->downloadPromise]);
        $this->composer->expects($this->any())->method('getLoop')->willReturn($this->loop);
    }

    protected function assertValidateDownloadedFile(bool $hasHash, bool $isValid): void
    {
        $this->fs->createDirectory(\dirname($this->tmpFile), true);
        $this->fs->createFile($this->tmpFile, $content = 'hello world');
        if ($hasHash) {
            $this->subpackage->setHash(new Hash('md5', $isValid ? md5($content) : 'not valid'));
        } else {
            $this->subpackage->setHash(null);
        }
    }

    protected function assertRemoveDownloadedFile(bool $isValid): void
    {
        $this->filesystem
            ->expects($this->exactly(!$isValid))
            ->method('remove')
            ->with($this->getTmpFilePath());
    }

    protected function expectInvalidDownloadedFileException(bool $isValid): void
    {
        if (!$isValid) {
            $this->expectException(InvalidDownloadedFileException::class);
            $this->expectExceptionMessage(sprintf('Extra file "%s" does not match hash value defined in "%s".', $this->url, $this->id));
        }
    }

    abstract protected function getHandlerClass(): string;

    protected function getHandlerExtraArguments(): array
    {
        return [];
    }

    abstract protected function getTrackingFile(): string;

    abstract protected function getSubpackageType(): string;

    abstract protected function getChecksum(): string;

    abstract protected function getExecutableType(): string;

    abstract protected function getTrackingData(): array;

    protected function createHandler(): HandlerInterface
    {
        $class = $this->getHandlerClass();

        return new $class($this->subpackage, $this->binariesInstaller, $this->filesystem, ...$this->getHandlerExtraArguments());
    }

    protected function getTmpFilePath(): string
    {
        return $this->fs->path($this->tmpFile);
    }
}
