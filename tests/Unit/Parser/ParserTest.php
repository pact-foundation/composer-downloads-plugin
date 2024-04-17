<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Parser;

use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraArchive;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownload;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownloadInterface;
use LastCall\DownloadsPlugin\Parser\Parser;
use LastCall\DownloadsPlugin\Parser\ParserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private PackageInterface $package;
    private IOInterface|MockObject $io;
    private ParserInterface $parser;
    private array $extra = [
        'downloads' => [
            '*' => [
                'path' => 'common/path/to/dir',
            ],
            'file1' => [
                'url' => 'http://example.com/file1.zip',
                'version' => '1.2.3',
            ],
            'file2' => [
                'url' => 'http://example.com/file2.tar.gz',
                'executable' => [
                    'file1',
                    'path/to/file2',
                ],
            ],
            'file3' => [
                'type' => 'xz',
                'url' => 'http://example.com/{$id}.xz',
                'ignore' => [
                    'dir/*',
                    '!dir/file1',
                ],
                'path' => 'path/to/dir',
            ],
            'file4' => [
                'type' => 'file',
                'url' => 'http://example.com/file.ext',
                'hash' => [
                    'algo' => 'md5',
                    'value' => 'text',
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->package = new Package('vendor/package-name', '1.0.0', 'v1.0.0');
        $this->io = $this->createMock(IOInterface::class);
        $this->parser = new Parser($this->io);
    }

    public function getEmptyTests(): array
    {
        return [
            [[]],
            [['downloads' => null]],
            [['downloads' => []]],
            [['downloads' => ['*' => []]]],
        ];
    }

    /**
     * @dataProvider getEmptyTests
     */
    public function testParseEmpty(array $extra): void
    {
        $this->package->setExtra($extra);
        $this->assertSame([], $this->parser->parse($this->package));
    }

    public function testParseInvalid(): void
    {
        $this->extra['downloads']['file1']['url'] = '/path/to/file.zip';
        $this->package->setExtra($this->extra);
        $this->io
            ->expects($this->once())
            ->method('writeError')
            ->with('    Skipped download extra files for package vendor/package-name: Attribute "url" of extra file "file1" defined in package "vendor/package-name" is invalid url.');
        $this->assertSame([], $this->parser->parse($this->package));
    }

    public function testParseValid(): void
    {
        $this->package->setExtra($this->extra);
        $extraDownloads = $this->parser->parse($this->package);
        $this->assertCount(4, $extraDownloads);
        $this->assertExtraDownload(
            $extraDownloads[0],
            ExtraArchive::class,
            'file1',
            '1.2.3',
            'http://example.com/file1.zip',
            'zip',
            'common/path/to/dir',
            'extra-download:archive'
        );
        $this->assertExtraDownload(
            $extraDownloads[1],
            ExtraArchive::class,
            'file2',
            ExtraDownload::FAKE_VERSION,
            'http://example.com/file2.tar.gz',
            'tar',
            'common/path/to/dir',
            'extra-download:archive'
        );
        $this->assertExtraDownload(
            $extraDownloads[2],
            ExtraArchive::class,
            'file3',
            ExtraDownload::FAKE_VERSION,
            'http://example.com/file3.xz',
            'xz',
            'path/to/dir',
            'extra-download:archive'
        );
        $this->assertExtraDownload(
            $extraDownloads[3],
            ExtraDownload::class,
            'file4',
            ExtraDownload::FAKE_VERSION,
            'http://example.com/file.ext',
            'file',
            'common/path/to/dir',
            'extra-download:file'
        );
    }

    private function assertExtraDownload(
        ExtraDownloadInterface $extraDownload,
        string $class,
        string $id,
        string $version,
        string $url,
        string $distType,
        string $path,
        string $packageType,
    ): void {
        $this->assertInstanceOf($class, $extraDownload);
        $this->assertSame(sprintf('vendor/package-name:%s', $id), $extraDownload->getName());
        $this->assertSame(ExtraDownload::FAKE_VERSION, $extraDownload->getVersion());
        $this->assertSame($version, $extraDownload->getPrettyVersion());
        $this->assertSame($url, $extraDownload->getDistUrl());
        $this->assertSame($distType, $extraDownload->getDistType());
        $this->assertSame($path, $extraDownload->getTargetDir());
        $this->assertSame('dist', $extraDownload->getInstallationSource());
        $this->assertSame($packageType, $extraDownload->getType());
    }
}
