<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\HashValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;

class HashValidatorTest extends AbstractValidatorTestCase
{
    public function testNull(): void
    {
        $this->assertNull($this->validator->validate(null));
    }

    public function getInvalidHashTests(): array
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
     * @dataProvider getInvalidHashTests
     */
    public function testInvalidHash(mixed $invalidHash, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('hash', sprintf('must be array, "%s" given', $type));
        $this->validator->validate($invalidHash);
    }

    public function getInvalidHashChildTests(): array
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
     * @dataProvider getInvalidHashChildTests
     */
    public function testInvalidAlgo(mixed $invalidAlgo, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('hash > algo', sprintf('must be string, "%s" given', $type));
        $this->validator->validate([
            'algo' => $invalidAlgo,
            'value' => 'abc123',
        ]);
    }

    public function testNotSupportedAlgo(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('hash > algo', 'is not supported');
        $this->validator->validate([
            'algo' => 'not-supported',
            'value' => 'abc123',
        ]);
    }

    /**
     * @dataProvider getInvalidHashChildTests
     */
    public function testInvalidValue(mixed $invalidValue, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('hash > value', sprintf('must be string, "%s" given', $type));
        $this->validator->validate([
            'algo' => 'md5',
            'value' => $invalidValue,
        ]);
    }

    public function testValidHash(): void
    {
        $hash = $this->validator->validate([
            'algo' => $expectedAlgo = 'sha256',
            'value' => $expectedValue = '1d0f9d464f7330e866357380d76f5f68becf0ca84b205a2135fd392581ed0b1d',
        ]);
        $reflection = new \ReflectionClass($hash);
        $this->assertSame($expectedAlgo, $reflection->getProperty('algo')->getValue($hash));
        $this->assertSame($expectedValue, $reflection->getProperty('value')->getValue($hash));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new HashValidator($this->id, $this->parent);
    }
}
