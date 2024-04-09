<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute;

use LastCall\DownloadsPlugin\Attribute\AttributeManager;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Exception\OutOfRangeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeManagerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private AttributeManagerInterface $manager;
    private array $attributes = [
        'path' => 'value',
    ];

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->manager = new AttributeManager($this->attributes);
    }

    public function testGetValidatedValue(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn('path');
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value')
            ->willReturnArgument(0);
        $this->manager->addValidator($this->validator);
        $this->assertSame('value', $this->manager->get(Attribute::PATH));
        $this->assertSame('value', $this->manager->get(Attribute::PATH));
    }

    public function testGetNull(): void
    {
        $this->validator
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn('hash');
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(null)
            ->willReturnArgument(0);
        $this->manager->addValidator($this->validator);
        $this->assertNull($this->manager->get(Attribute::HASH));
        $this->assertNull($this->manager->get(Attribute::HASH));
    }

    public function testGetInvalidValidator(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Validator "path" not found.');
        $this->manager->get(Attribute::PATH);
    }
}
