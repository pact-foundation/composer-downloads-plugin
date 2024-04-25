<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Model;

use LastCall\DownloadsPlugin\Model\Hash;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem as VirtualFileSystem;

class HashTest extends TestCase
{
    private ?VirtualFileSystem $fs = null;
    private string $tmpFile = '/path/to/vendor/composer/tmp-random';
    private string $content = 'hello world';

    protected function setUp(): void
    {
        $this->fs = new VirtualFileSystem();
        $this->fs->createDirectory(\dirname($this->tmpFile), true);
        $this->fs->createFile($this->tmpFile, $this->content);
    }

    protected function tearDown(): void
    {
        $this->fs = null;
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testVerifyFile(bool $isValid): void
    {
        $hash = new Hash('md5', $isValid ? md5($this->content) : 'not valid');
        $this->assertSame($isValid, $hash->verifyFile($this->getTmpFilePath()));
    }

    public function testVerifyNotExistFile(): void
    {
        $hash = new Hash('any', 'any');
        $this->assertFalse($hash->verifyFile('/path/to/invalid/file.ext'));
    }

    public function testInvalidAlgo(): void
    {
        $this->expectException(\ValueError::class);
        $hash = new Hash('invalid', 'any');
        $hash->verifyFile($this->getTmpFilePath());
    }

    protected function getTmpFilePath(): string
    {
        return $this->fs->path($this->tmpFile);
    }
}
