<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Model;

use LastCall\DownloadsPlugin\Model\Hash;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem as VirtualFileSystem;

class HashTest extends TestCase
{
    private ?VirtualFileSystem $fs = null;
    private string $tmpFile = '/path/to/vendor/composer/tmp-random';

    protected function setUp(): void
    {
        $this->fs = new VirtualFileSystem();
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
        $this->fs->createDirectory(\dirname($this->tmpFile), true);
        $this->fs->createFile($this->tmpFile, $content = 'hello world');
        $hash = new Hash('md5', $isValid ? md5($content) : 'not valid');
        $this->assertSame($isValid, $hash->verifyFile($this->getTmpFilePath()));
    }

    protected function getTmpFilePath(): string
    {
        return $this->fs->path($this->tmpFile);
    }
}
