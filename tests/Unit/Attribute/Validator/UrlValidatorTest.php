<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use LastCall\DownloadsPlugin\Attribute\Validator\UrlValidator;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;

class UrlValidatorTest extends AbstractValidatorTestCase
{
    public function testNull(): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager->expects($this->never())->method('get');
        $this->expectUnexpectedValueException('url', 'is required');
        $this->validator->validate(null);
    }

    public function getInvalidUrlTypeTests(): array
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
     * @dataProvider getInvalidUrlTypeTests
     */
    public function testInvalidUrlType(mixed $invalidUrlType, string $type): void
    {
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->attributeManager->expects($this->never())->method('get');
        $this->expectUnexpectedValueException('url', sprintf('must be string, "%s" given', $type));
        $this->validator->validate($invalidUrlType);
    }

    public function getInvalidUrlTests(): array
    {
        return [
            [''],
            ['C:\Programs\PHP\php.ini'],
            ['/var/www/project/uploads'],
        ];
    }

    /**
     * @dataProvider getInvalidUrlTests
     */
    public function testInvalidUrl(string $invalidUrl): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::VARIABLES)->willReturn([]);
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('url', 'is invalid url');
        $this->validator->validate($invalidUrl);
    }

    public function getInvalidUrlSchemeTests(): array
    {
        return [
            ['file://server/path/to/file'],
            ['ssh://user@host:123/path'],
            ['mailto:jsmith@example.com?subject=Hello'],
        ];
    }

    /**
     * @dataProvider getInvalidUrlSchemeTests
     */
    public function testInvalidUrlScheme(string $invalidUrl): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::VARIABLES)->willReturn([]);
        $this->parent->expects($this->once())->method('getName')->willReturn($this->parentName);
        $this->expectUnexpectedValueException('url', 'has invalid scheme');
        $this->validator->validate($invalidUrl);
    }

    public function getValidateUrlTests(): array
    {
        return [
            ['http://example/file.rar', [], 'http://example/file.rar'],
            ['http://example/file-{$version}.rar', ['{$version}' => '1.2.3'], 'http://example/file-1.2.3.rar'],
        ];
    }

    /**
     * @dataProvider getValidateUrlTests
     */
    public function testValidateUrl(string $url, array $variables, string $expectedUrl): void
    {
        $this->attributeManager->expects($this->once())->method('get')->with(Attribute::VARIABLES)->willReturn($variables);
        $this->parent->expects($this->never())->method('getName');
        $this->assertSame($expectedUrl, $this->validator->validate($url));
    }

    protected function createValidator(): ValidatorInterface
    {
        return new UrlValidator($this->id, $this->parent, $this->attributeManager);
    }
}
