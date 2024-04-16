<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\ExecutableValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\PathValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Enum\Type;
use PHPUnit\Framework\MockObject\MockObject;

class ExecutableValidatorTest extends AbstractValidatorTestCase
{
    private PathValidator|MockObject $pathValidator;
    private string $path = 'path/to/file';

    protected function setUp(): void
    {
        $this->pathValidator = $this->createMock(PathValidator::class);
        parent::setUp();
    }

    public function getEmptyExecutableTests(): array
    {
        return [
            [Type::ZIP, null],
            [Type::RAR, null],
            [Type::TAR, null],
            [Type::XZ, null],
            [Type::FILE, null],
            [Type::GZIP, null],
            [Type::ZIP, []],
            [Type::RAR, []],
            [Type::TAR, []],
            [Type::XZ, []],
            [Type::FILE, false],
            [Type::GZIP, false],
        ];
    }

    /**
     * @dataProvider getEmptyExecutableTests
     */
    public function testEmptyExecutable(Type $type, mixed $values): void
    {
        $this->pathValidator->expects($this->never())->method('validate');
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn($type);
        $this->assertSame([], $this->validator->validate($values));
    }

    public function getInvalidExecutableArchiveTypeTests(): array
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
     * @dataProvider getInvalidExecutableArchiveTypeTests
     */
    public function testInvalidExecutableArchiveType(mixed $invalidExecutable, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->pathValidator->expects($this->never())->method('validate');
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn(Type::ZIP);
        $this->expectUnexpectedValueException('executable', sprintf('must be array, "%s" given', $type));
        $this->validator->validate($invalidExecutable);
    }

    public function getInvalidExecutableArchivePaths(): array
    {
        return [
            [[['not a string']]],
            [['/root/path/to/file']],
            [['../../outside/path/to/file']],
        ];
    }

    /**
     * @dataProvider getInvalidExecutableArchivePaths
     */
    public function testInvalidExecutableArchivePaths(array $invalidExecutablePaths): void
    {
        $extraFile = [
            'executable' => [
                ['not a string'],
                '/root/path/to/file',
                '../../outside/path/to/file',
            ],
        ];
        $this->pathValidator
            ->expects($this->once())
            ->method('validate')
            ->with($invalidExecutablePaths[0])
            ->willThrowException(new \Exception('path is invalid'));
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn(Type::ZIP);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('path is invalid');
        $this->validator->validate($invalidExecutablePaths);
    }

    public function getInvalidExecutableFileTests(): array
    {
        return [
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidExecutableFileTests
     */
    public function testInvalidExecutableFile(mixed $invalidExecutable, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->pathValidator->expects($this->never())->method('validate');
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn(Type::FILE);
        $this->expectUnexpectedValueException('executable', sprintf('must be boolean, "%s" given', $type));
        $this->validator->validate($invalidExecutable);
    }

    public function getInvalidExecutablePharTests(): array
    {
        return [
            [false, 'bool'],
            [123, 'int'],
            [12.3, 'float'],
            ['test', 'string'],
            [['key' => 'value'], 'array'],
            [(object) ['key' => 'value'], 'stdClass'],
        ];
    }

    /**
     * @dataProvider getInvalidExecutablePharTests
     */
    public function testInvalidExecutablePhar(mixed $invalidExecutable, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->pathValidator->expects($this->never())->method('validate');
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn(Type::PHAR);
        $this->expectUnexpectedValueException('executable', sprintf('must be true, "%s" given', $type));
        $this->validator->validate($invalidExecutable);
    }

    public function getValidateExecutableFileTests(): array
    {
        return [
            [Type::FILE, true],
            [Type::PHAR, true],
            [Type::GZIP, true],
            [Type::FILE, false],
            [Type::GZIP, false],
        ];
    }

    /**
     * @dataProvider getValidateExecutableFileTests
     */
    public function testValidateExecutableFile(Type $type, bool $executable): void
    {
        $this->pathValidator->expects($this->never())->method('validate');
        $this->attributeManager
            ->expects($this->exactly(1 + $executable))
            ->method('get')
            ->willReturnMap([
                [Attribute::TYPE, $type],
                [Attribute::PATH, $this->path],
            ]);
        $this->assertSame($executable ? [$this->path] : [], $this->validator->validate($executable));
    }

    public function getValidateExecutableArchiveTests(): array
    {
        return [
            [Type::ZIP],
            [Type::RAR],
            [Type::TAR],
            [Type::XZ],
        ];
    }

    /**
     * @dataProvider getValidateExecutableArchiveTests
     */
    public function testValidateExecutableArchive(Type $type): void
    {
        $executable = ['path/to/file', 'path/to/another/file'];
        $matcher = $this->exactly(\count($executable));
        $this->pathValidator
            ->expects($matcher)
            ->method('validate')
            ->with($this->callback(function (string $path) use ($matcher, $executable) {
                $this->assertSame($executable[$matcher->getInvocationCount() - 1], $path);

                return true;
            }));
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::TYPE)->willReturn($type);
        $this->assertSame($executable, $this->validator->validate($executable));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new ExecutableValidator($this->id, $this->parent, $this->attributeManager, $this->pathValidator);
    }
}
