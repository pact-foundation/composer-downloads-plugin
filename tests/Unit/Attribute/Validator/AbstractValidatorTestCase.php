<?php

namespace LastCall\DownloadsPlugin\Tests\Unit\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Exception\UnexpectedValueException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractValidatorTestCase extends TestCase
{
    protected PackageInterface|MockObject $parent;
    protected AttributeManagerInterface|MockObject $attributeManager;
    protected ValidatorInterface $validator;
    protected string $id = 'file-name';
    protected string $parentName = 'vendor/parent-package';
    protected string $parentPath = '/path/to/vendor/parent-package';

    protected function setUp(): void
    {
        $this->parent = $this->createMock(PackageInterface::class);
        $this->attributeManager = $this->createMock(AttributeManagerInterface::class);
        $this->validator = $this->createValidator();
    }

    protected function expectUnexpectedValueException(string $attribute, string $reason): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Attribute "%s" of extra file "%s" defined in package "%s" %s.', $attribute, $this->id, $this->parentName, $reason));
    }

    abstract protected function createValidator(): ValidatorInterface;
}
