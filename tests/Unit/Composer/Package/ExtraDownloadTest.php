<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Composer\Package;

use Composer\Package\Package;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownload;
use LastCall\DownloadsPlugin\Enum\Type;
use LastCall\DownloadsPlugin\Model\Hash;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExtraDownloadTest extends TestCase
{
    protected ExtraDownload $extraDownload;
    private PackageInterface $parent;
    private Hash|MockObject $hash;
    private string $parentName = 'vendor/package-name';
    protected string $name = 'normal-file';
    protected array $executable = [
        'file1',
        'path/to/file2',
    ];
    protected string $version = '1.2.3.0';
    protected string $url = 'http://example.com/file.zip';
    protected string $path = 'path/to/dir';
    protected Type $type = Type::PHAR;

    protected function setUp(): void
    {
        $this->parent = new Package($this->parentName, '1.2.3', 'v1.2.3');
        $this->hash = $this->createMock(Hash::class);
        $this->createExtraDownload($this->parent, $this->hash);
    }

    public function testConstruct(): void
    {
        $this->assertSame("{$this->parentName}:{$this->name}", $this->extraDownload->getName());
        $this->assertSame('dev-master', $this->extraDownload->getVersion());
        $this->assertSame($this->version, $this->extraDownload->getPrettyVersion());
        $this->assertSame('dist', $this->extraDownload->getInstallationSource());
        $this->assertSame($this->type->toDistType(), $this->extraDownload->getDistType());
        $this->assertSame($this->type->toPackageType()->value, $this->extraDownload->getType());
    }

    public function testGetTrackingChecksum(): void
    {
        $this->assertSame('19f9e3ee9a0d59bf200dd3fb08595548e25c16203c794235b7d8f816427ba4b4', $this->extraDownload->getTrackingChecksum());
    }

    public function testGetInstallPathWhenParentPackageIsNotInstalled(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Package "'.$this->parentName.'" is not installed');
        $this->extraDownload->getInstallPath();
    }

    public function testGetInstallPathWhenParentPackageIsInstalled(): void
    {
        $parentName = 'leongrdic/smplang';
        $parent = new Package($parentName, 'any version', 'any pretty version');
        $this->createExtraDownload($parent, $this->hash);
        $this->assertSame(
            realpath(__DIR__.'/../../../../vendor/composer/').'/../'.$parentName.'/'.$this->path,
            $this->extraDownload->getInstallPath()
        );
    }

    public function testGetExecutablePathsWhenParentPackageIsNotInstalled(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Package "'.$this->parentName.'" is not installed');
        $this->extraDownload->getExecutablePaths();
    }

    public function testGetExecutablePathsWhenParentPackageIsInstalled(): void
    {
        $parentName = 'leongrdic/smplang';
        $parent = new Package($parentName, 'any version', 'any pretty version');
        $this->createExtraDownload($parent, $this->hash);
        $this->assertSame([
            realpath(__DIR__.'/../../../../vendor/composer/').'/../'.$parentName.'/file1',
            realpath(__DIR__.'/../../../../vendor/composer/').'/../'.$parentName.'/path/to/file2',
        ], $this->extraDownload->getExecutablePaths());
    }

    public function testVerifyFileWithoutHash(): void
    {
        $this->createExtraDownload($this->parent, null);
        $this->assertTrue($this->extraDownload->verifyFile('/any/file'));
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testVerifyFileWithHash(bool $isValid): void
    {
        $path = '/path/to/file';
        $this->hash
            ->expects($this->once())
            ->method('verifyFile')
            ->with($path)
            ->willReturn($isValid);
        $this->assertSame($isValid, $this->extraDownload->verifyFile($path));
    }

    protected function createExtraDownload(PackageInterface $parent, ?Hash $hash): void
    {
        $this->extraDownload = new ExtraDownload(
            $parent,
            $this->name,
            $this->version,
            $hash,
            $this->type,
            $this->executable,
            $this->url,
            $this->path
        );
    }
}
