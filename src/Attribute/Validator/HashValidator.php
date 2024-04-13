<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Model\Hash;

class HashValidator extends AbstractValidator
{
    public function validate(mixed $values): ?Hash
    {
        if (null === $values) {
            return null;
        }

        if (!\is_array($values)) {
            $this->throwException($this->getAttribute(), sprintf('must be array, "%s" given', get_debug_type($values)));
        }

        $algo = $values['algo'] ?? null;
        if (!\is_string($algo)) {
            $this->throwException('hash > algo', sprintf('must be string, "%s" given', get_debug_type($algo)));
        }

        if (!\in_array($algo, hash_algos(), true)) {
            $this->throwException('hash > algo', 'is not supported');
        }

        $value = $values['value'] ?? null;
        if (!\is_string($value)) {
            $this->throwException('hash > value', sprintf('must be string, "%s" given', get_debug_type($value)));
        }

        return new Hash($algo, $value);
    }

    public function getAttribute(): string
    {
        return Attribute::HASH->value;
    }
}
