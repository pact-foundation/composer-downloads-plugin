<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\IgnoreValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Enum\Type;

class IgnoreValidatorTest extends AbstractValidatorTestCase
{
    public function getNotArchiveTypeTests(): array
    {
        return [
            [Type::FILE],
            [Type::GZIP],
            [Type::PHAR],
        ];
    }

    /**
     * @dataProvider getNotArchiveTypeTests
     */
    public function testNotArchiveType(Type $type): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn($type);
        $this->assertSame([], $this->validator->validate(['file']));
    }

    public function getNullTests(): array
    {
        return [
            [Type::ZIP],
            [Type::RAR],
            [Type::TAR],
            [Type::XZ],
        ];
    }

    /**
     * @dataProvider getNullTests
     */
    public function testNull(Type $type): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn($type);
        $this->assertSame([], $this->validator->validate(null));
    }

    public function getEmptyIgnoreTests(): array
    {
        return [
            [Type::ZIP, []],
            [Type::RAR, []],
            [Type::TAR, []],
            [Type::XZ, []],
        ];
    }

    /**
     * @dataProvider getEmptyIgnoreTests
     */
    public function testEmptyIgnore(Type $type): void
    {
        $this->attributeManager
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [Attribute::TYPE, $type],
                [Attribute::VARIABLES, []],
            ]);
        $this->assertSame([], $this->validator->validate([]));
    }

    public function getInvalidIgnoreTests(): array
    {
        return [
            [true, 'bool'],
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidIgnoreTests
     */
    public function testInvalidIgnore(mixed $invalidIgnore, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn(Type::ZIP);
        $this->expectUnexpectedValueException('ignore', sprintf('must be array, "%s" given', $type));
        $this->validator->validate($invalidIgnore);
    }

    public function getInvalidIgnoreItemTests(): array
    {
        return [
            [[['not a string']]],
            [[123]],
            [[null]],
        ];
    }

    /**
     * @dataProvider getInvalidIgnoreItemTests
     */
    public function testInvalidIgnoreItem(mixed $invalidIginoreItem): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [Attribute::TYPE, Type::ZIP],
                [Attribute::VARIABLES, []],
            ]);
        $this->expectUnexpectedValueException('ignore', 'must be array of string');
        $this->validator->validate($invalidIginoreItem);
    }

    public function testValidateIgnore(): void
    {
        $ignores = ['dir/*', '!dir/file'];
        $this->attributeManager
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [Attribute::TYPE, Type::ZIP],
                [Attribute::VARIABLES, []],
            ]);
        $this->assertSame($ignores, $this->validator->validate($ignores));
    }

    public function testValidateIgnoreWithVariables(): void
    {
        $ignores = ['dir/*', '!dir/file{$extension}'];
        $variables = ['{$extension}' => '.txt'];
        $this->attributeManager
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [Attribute::TYPE, Type::ZIP],
                [Attribute::VARIABLES, $variables],
            ]);
        $validatedIgnores = ['dir/*', '!dir/file.txt'];
        $this->assertSame($validatedIgnores, $this->validator->validate($ignores));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new IgnoreValidator($this->id, $this->parent, $this->attributeManager);
    }
}
