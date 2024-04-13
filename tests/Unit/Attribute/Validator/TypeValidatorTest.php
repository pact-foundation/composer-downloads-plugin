<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\TypeValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Enum\Type;

class TypeValidatorTest extends AbstractValidatorTestCase
{
    public function getParseTypeFromUrlTests(): array
    {
        return [
            ['http://example.com/file.tar.gz', Type::TAR],
            ['http://example.com/file.tar.bz2', Type::TAR],
            ['http://example.com/file.tar.xz', Type::XZ],
            ['http://example.com/file.zip', Type::ZIP],
            ['http://example.com/file.rar', Type::RAR],
            ['http://example.com/file.tgz', Type::TAR],
            ['http://example.com/file.tar', Type::TAR],
            ['http://example.com/file.gz', Type::GZIP],
            ['http://example.com/file.phar', Type::PHAR],
            ['http://example.com/file', Type::FILE],
        ];
    }

    /**
     * @dataProvider getParseTypeFromUrlTests
     */
    public function testParseTypeFromUrl(string $url, Type $expectedType): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::URL)->willReturn($url);
        $this->assertSame($expectedType, $this->validator->validate(null));
    }

    public function getInvalidTypeTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidTypeTests
     */
    public function testInvalidType(mixed $invalidType, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager->expects($this->never())->method('get');
        $this->expectUnexpectedValueException('type', sprintf('must be string, "%s" given', $type));
        $this->validator->validate($invalidType);
    }

    public function testNotSupportedType(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager->expects($this->never())->method('get');
        $this->expectUnexpectedValueException('type', 'is not supported');
        $this->validator->validate('not supported');
    }

    public function getValidateTypeTests(): array
    {
        return [
            ['tar', Type::TAR],
            ['xz', Type::XZ],
            ['zip', Type::ZIP],
            ['rar', Type::RAR],
            ['gzip', Type::GZIP],
            ['phar', Type::PHAR],
            ['file', Type::FILE],
        ];
    }

    /**
     * @dataProvider getValidateTypeTests
     */
    public function testValidateType(string $value, Type $type): void
    {
        $this->attributeManager->expects($this->never())->method('get');
        $this->assertSame($type, $this->validator->validate($value));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new TypeValidator($this->id, $this->parent, $this->attributeManager);
    }
}
