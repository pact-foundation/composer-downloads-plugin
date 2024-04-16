<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Installer;

use Composer\IO\IOInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use LastCall\DownloadsPlugin\Installer\ExecutableInstaller;
use LastCall\DownloadsPlugin\Installer\ExecutableInstallerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem;

class ExecutableInstallerTest extends TestCase
{
    private ?FileSystem $fs = null;
    private IOInterface|MockObject $io;
    private ExtraDownloadInterface|MockObject $extraDownload;
    private ExecutableInstallerInterface $installer;
    private string $name = 'vendor/package-name:executable-file';
    private array $executablePaths = [
        false => '/path/to/files/file1',
        true => '/path/to/files/other/path/to/file2',
    ];

    protected function setUp(): void
    {
        $this->fs = new FileSystem();
        $this->io = $this->createMock(IOInterface::class);
        $this->extraDownload = $this->createMock(ExtraDownloadInterface::class);
        $this->installer = new ExecutableInstaller($this->io);
        $this->createDirectoryAndFiles();
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    public function testInstall(): void
    {
        $this->extraDownload
            ->expects($this->once())
            ->method('getExecutablePaths')
            ->willReturn(array_map(fn (string $path) => $this->fs->path($path), $this->executablePaths));
        if (\PHP_OS_FAMILY === 'Windows') {
            $this->extraDownload
                ->expects($this->once())
                ->method('getName')
                ->willReturn($this->name);
            $this->io
                ->expects($this->once())
                ->method('writeError')
                ->with('    Skipped installation of bin '.$this->fs->path('/path/to/files/other/path/to/file2.bat').' proxy for package '.$this->name.': a .bat proxy was already installed');
        }
        $this->installer->install($this->extraDownload);
        foreach ($this->executablePaths as $hasProxy => $path) {
            if (\PHP_OS_FAMILY === 'Windows') {
                $proxy = $path.'.bat';
                if (!$hasProxy) {
                    $this->assertStringEqualsFile(
                        $this->fs->path($proxy),
                        '@php "%~dp0file1" %*'
                    );
                }
            } else {
                $this->assertTrue(is_executable($this->fs->path($path)));
            }
        }
    }

    private function createDirectoryAndFiles(): void
    {
        foreach ($this->executablePaths as $hasProxy => $path) {
            $this->fs->createDirectory(\dirname($path), true);
            $content = implode(\PHP_EOL, [
                '#!/usr/bin/env php',
                '<?php',
                "echo 'Hello from php file!';",
            ]);
            $this->fs->createFile($path, $content);
            chmod($this->fs->path($path), 0600);
            if (\PHP_OS_FAMILY === 'Windows' && $hasProxy) {
                $proxy = $path.'.bat';
                $this->fs->createFile($proxy, 'proxy content');
            }
        }
    }
}
