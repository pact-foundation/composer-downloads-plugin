<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Filter;

use LastCall\DownloadsPlugin\Filter\FilterInterface;
use LastCall\DownloadsPlugin\Filter\HashFilter;

class HashFilterTest extends BaseFilterTestCase
{
    private string $version = '1.2.3.0';
    private string $prettyVersion = 'v1.2.3';

    protected function setUp(): void
    {
        parent::setUp();
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
        $this->filter->filter([
            'hash' => $invalidHash,
        ]);
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
        $this->filter->filter([
            'hash' => [
                'algo' => $invalidAlgo,
                'value' => 'abc123',
            ],
        ]);
    }

    public function testNotSupportedAlgo(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('hash > algo', 'is not supported');
        $this->filter->filter([
            'hash' => [
                'algo' => 'not-supported',
                'value' => 'abc123',
            ],
        ]);
    }

    /**
     * @dataProvider getInvalidHashChildTests
     */
    public function testInvalidValue(mixed $invalidValue, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('hash > value', sprintf('must be string, "%s" given', $type));
        $this->filter->filter([
            'hash' => [
                'algo' => 'md5',
                'value' => $invalidValue,
            ],
        ]);
    }

    public function testValidHash(): void
    {
        $hash = $this->filter->filter([
            'hash' => [
                'algo' => $expectedAlgo = 'sha256',
                'value' => $expectedValue = '1d0f9d464f7330e866357380d76f5f68becf0ca84b205a2135fd392581ed0b1d',
            ],
        ]);
        $this->assertSame($expectedAlgo, $hash->algo);
        $this->assertSame($expectedValue, $hash->value);
    }

    protected function createFilter(): FilterInterface
    {
        return new HashFilter($this->name, $this->parent);
    }
}
