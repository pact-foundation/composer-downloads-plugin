<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use LastCall\DownloadsPlugin\Enum\Attribute;

class VersionValidator extends AbstractValidator
{
    public function validate(mixed $value): ?string
    {
        if (null !== $value && !\is_string($value)) {
            $this->throwException($this->getAttribute(), sprintf('must be string, "%s" given', get_debug_type($value)));
        }

        return $value;
    }

    public function getAttribute(): string
    {
        return Attribute::VERSION->value;
    }
}
