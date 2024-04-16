<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Attribute\Validator\VersionValidator;

class VersionValidatorTest extends AbstractValidatorTestCase
{
    public function getInvalidVersionTests(): array
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
     * @dataProvider getInvalidVersionTests
     */
    public function testInvalidVersion(mixed $invalidVersion, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('version', sprintf('must be string, "%s" given', $type));
        $this->validator->validate($invalidVersion);
    }

    public function getValidVersionTests(): array
    {
        return [
            [null],
            ['1.2.3'],
        ];
    }

    /**
     * @dataProvider getValidVersionTests
     */
    public function testValidVersion(?string $version): void
    {
        $this->assertSame($version, $this->validator->validate($version));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new VersionValidator($this->id, $this->parent);
    }
}
