<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Enum\Type;

class TypeValidator extends AbstractValidator
{
    public function __construct(
        string $id,
        PackageInterface $parent,
        private AttributeManagerInterface $attributeManager
    ) {
        parent::__construct($id, $parent);
    }

    public function validate(mixed $value): Type
    {
        if (null === $value) {
            return $this->parseUrl();
        }

        if (!\is_string($value)) {
            $this->throwException($this->getAttribute(), sprintf('must be string, "%s" given', get_debug_type($value)));
        }

        $type = Type::tryFrom($value);
        if (null === $type) {
            $this->throwException($this->getAttribute(), 'is not supported');
        }

        return $type;
    }

    public function getAttribute(): string
    {
        return Attribute::TYPE->value;
    }

    private function parseUrl(): Type
    {
        $parts = parse_url($this->attributeManager->get(Attribute::URL));
        $filename = pathinfo($parts['path'], \PATHINFO_BASENAME);
        if (preg_match('/\.(tar\.gz|tar\.bz2)$/', $filename)) {
            return Type::from('tar');
        }
        if (preg_match('/\.tar\.xz$/', $filename)) {
            return Type::from('xz');
        }
        $extension = pathinfo($parts['path'], \PATHINFO_EXTENSION);

        return Type::fromExtension($extension);
    }
}
