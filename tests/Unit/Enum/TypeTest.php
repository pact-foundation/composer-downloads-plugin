<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Enum;

use LastCall\DownloadsPlugin\Enum\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function getIsArchiveTests(): array
    {
        return [
            [Type::ZIP, true],
            [Type::RAR, true],
            [Type::TAR, true],
            [Type::XZ, true],
            [Type::FILE, false],
            [Type::PHAR, false],
            [Type::GZIP, false],
        ];
    }

    /**
     * @dataProvider getIsArchiveTests
     */
    public function testIsArchive(Type $type, bool $isArchive): void
    {
        $this->assertSame($isArchive, $type->isArchive());
    }

    public function getFromExtensionTests(): array
    {
        return [
            ['zip', Type::ZIP],
            ['rar', Type::RAR],
            ['tgz', Type::TAR],
            ['tar', Type::TAR],
            // ['tar.gz', Type::TAR], // Handle in TypeValidator::parseUrl()
            // ['tar.bz2', Type::TAR], // Handle in TypeValidator::parseUrl()
            // ['tar.xz', Type::XZ], // Handle in TypeValidator::parseUrl()
            ['gz', Type::GZIP],
            ['phar', Type::PHAR],
            ['csv', Type::FILE],
        ];
    }

    /**
     * @dataProvider getFromExtensionTests
     */
    public function testFromExtension(string $extension, Type $type): void
    {
        $this->assertSame($type, Type::fromExtension($extension));
    }

    public function getToDistTypeTests(): array
    {
        return [
            [Type::ZIP, 'zip'],
            [Type::RAR, 'rar'],
            [Type::TAR, 'tar'],
            [Type::XZ, 'xz'],
            [Type::FILE, 'file'],
            [Type::PHAR, 'file'],
            [Type::GZIP, 'gzip'],
        ];
    }

    /**
     * @dataProvider getToDistTypeTests
     */
    public function testToDistType(Type $type, string $distType): void
    {
        $this->assertSame($distType, $type->toDistType());
    }

    public function getToPackageTypeTests(): array
    {
        return [
            [Type::ZIP, 'extra-download:archive'],
            [Type::RAR, 'extra-download:archive'],
            [Type::TAR, 'extra-download:archive'],
            [Type::XZ, 'extra-download:archive'],
            [Type::FILE, 'extra-download:file'],
            [Type::PHAR, 'extra-download:file'],
            [Type::GZIP, 'extra-download:file'],
        ];
    }

    /**
     * @dataProvider getToPackageTypeTests
     */
    public function testToPackageType(Type $type, string $packageType): void
    {
        $this->assertSame($packageType, $type->toPackageType()->value);
    }
}
