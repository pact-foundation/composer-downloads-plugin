<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Exception\UnexpectedValueException;

abstract class AbstractValidator implements ValidatorInterface
{
    public function __construct(
        protected string $id,
        protected PackageInterface $parent
    ) {
    }

    protected function throwException(string $attribute, string $reason): void
    {
        throw new UnexpectedValueException(sprintf('Attribute "%s" of extra file "%s" defined in package "%s" %s.', $attribute, $this->id, $this->parent->getName(), $reason));
    }
}
