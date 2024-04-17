<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Factory;

use Composer\Package\Package;
use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Composer\Package\ExtraDownload;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Enum\Type;
use LastCall\DownloadsPlugin\Factory\ExtraDownloadFactory;
use LastCall\DownloadsPlugin\Factory\ExtraDownloadFactoryInterface;
use LastCall\DownloadsPlugin\Model\Hash;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExtraDownloadFactoryTest extends TestCase
{
    private PackageInterface $parent;
    protected AttributeManagerInterface|MockObject $attributeManager;
    private ExtraDownloadFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->parent = new Package('vendor/package-name', '1.0.0', 'v1.0.0');
        $this->attributeManager = $this->createMock(AttributeManagerInterface::class);
        $this->factory = $this->createFactory();
    }

    public function testCreate(): void
    {
        $attributesMap = $this->getAttributesMap();
        $this->attributeManager
            ->expects($this->exactly(\count($attributesMap)))
            ->method('get')
            ->willReturnMap($attributesMap);
        $extraDownload = $this->factory->create(
            $id = 'file-name',
            $this->parent
        );
        $this->assertInstanceOf($this->getExtraDownloadClass(), $extraDownload);
        $this->assertSame(sprintf('vendor/package-name:%s', $id), $extraDownload->getName());
        $this->assertSame(ExtraDownload::FAKE_VERSION, $extraDownload->getVersion());
        $this->assertSame('v1.2.3', $extraDownload->getPrettyVersion());
        $this->assertSame('http://example.com/file.zip', $extraDownload->getDistUrl());
        $this->assertSame('zip', $extraDownload->getDistType());
        $this->assertSame('path/to/dir', $extraDownload->getTargetDir());
        $this->assertSame('dist', $extraDownload->getInstallationSource());
        $this->assertSame('extra-download:archive', $extraDownload->getType());
    }

    protected function createFactory(): ExtraDownloadFactoryInterface
    {
        return new ExtraDownloadFactory($this->attributeManager);
    }

    protected function getExtraDownloadClass(): string
    {
        return ExtraDownload::class;
    }

    protected function getAttributesMap(): array
    {
        return [
            [Attribute::VERSION, 'v1.2.3'],
            [Attribute::HASH, new Hash('md5', 'text')],
            [Attribute::TYPE, Type::ZIP],
            [Attribute::EXECUTABLE, [
                'file1',
                'path/to/file2',
            ]],
            [Attribute::URL, 'http://example.com/file.zip'],
            [Attribute::PATH, 'path/to/dir'],
        ];
    }
}
