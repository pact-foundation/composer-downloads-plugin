<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\PathValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;

class PathValidatorTest extends AbstractValidatorTestCase
{
    private string $attribute = 'path';

    public function testNull(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager->expects($this->never())->method('get');
        $this->expectUnexpectedValueException($this->attribute, 'is required');
        $this->validator->validate(null);
    }

    public function getInvalidPathTests(): array
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
     * @dataProvider getInvalidPathTests
     */
    public function testInvalidPath(mixed $invalidPath, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager->expects($this->never())->method('get');
        $this->expectUnexpectedValueException($this->attribute, sprintf('must be string, "%s" given', $type));
        $this->validator->validate($invalidPath);
    }

    public function getAbsolutePathTests(): array
    {
        return [
            ['D:\path\to\text.txt'],
            ['/path/to/note.md'],
        ];
    }

    /**
     * @dataProvider getAbsolutePathTests
     */
    public function testAbsolutePathFile(string $absolutePath): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::VARIABLES)->willReturn([]);
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException($this->attribute, 'must be relative path');
        $this->validator->validate($absolutePath);
    }

    public function getOutsidePathTests(): array
    {
        return [
            ['../../other/place', []],
            ['..../other/place', []],
            ["path/to/file\0", []],
            ["path/to/file\0/../../../other/place", []],
            ['path/to/{$file}', ['{$file}' => '../../../path/to/another/file']],
        ];
    }

    /**
     * @dataProvider getOutsidePathTests
     */
    public function testOutsidePath(string $outsidePath, array $variables): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::VARIABLES)->willReturn($variables);
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException($this->attribute, "must be inside relative to parent package's path");
        $this->validator->validate($outsidePath);
    }

    public function getFilterPathTests(): array
    {
        return [
            ['path/to/file', [], 'path/to/file'],
            ['path/to/file/{$version}', ['{$version}' => '1.2.3'], 'path/to/file/1.2.3'],
        ];
    }

    /**
     * @dataProvider getFilterPathTests
     */
    public function testFilterPath(string $path, array $variables, string $expectedPath): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::VARIABLES)->willReturn($variables);
        $this->parent->expects($this->never())->method('getName');
        $this->assertSame($expectedPath, $this->validator->validate($path));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new PathValidator($this->id, $this->parent, $this->attributeManager, $this->attribute);
    }
}
