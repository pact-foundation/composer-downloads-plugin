<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Package\InstallPath;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\InstallPath\InstallPath;
use LastCall\DownloadsPlugin\Composer\Package\InstallPath\InstallPathInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstallPathTest extends TestCase
{
    private InstallPathInterface $installPath;
    private PackageInterface|MockObject $package;
    private string $relative = 'path/to/file';

    protected function setUp(): void
    {
        $this->package = $this->createMock(PackageInterface::class);
        $this->installPath = new InstallPath($this->package);
    }

    public function testConvertToAbsoluteWhenPackageIsNotInstalled(): void
    {
        $name = 'vendor/package-name';
        $this->package
            ->expects($this->once())
            ->method('getName')
            ->willReturn($name);
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Package "'.$name.'" is not installed');
        $this->installPath->convertToAbsolute($this->relative);
    }

    public function testConvertToAbsoluteWhenPackageIsInstalled(): void
    {
        $name = 'leongrdic/smplang';
        $this->package
            ->expects($this->once())
            ->method('getName')
            ->willReturn($name);
        $this->assertSame(
            realpath(__DIR__.'/../../../../../vendor/composer/').'/../'.$name.'/'.$this->relative,
            $this->installPath->convertToAbsolute($this->relative)
        );
    }
}
