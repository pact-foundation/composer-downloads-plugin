<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use Symfony\Component\Filesystem\Path;

class PathValidator extends AbstractValidator
{
    public function __construct(
        string $id,
        PackageInterface $parent,
        private AttributeManagerInterface $attributeManager,
        private string $attribute = 'path',
    ) {
        parent::__construct($id, $parent);
    }

    public function validate(mixed $value): string
    {
        if (null === $value) {
            $this->throwException($this->getAttribute(), 'is required');
        }

        if (!\is_string($value)) {
            $this->throwException($this->getAttribute(), sprintf('must be string, "%s" given', get_debug_type($value)));
        }

        $path = strtr($value, $this->attributeManager->get(Attribute::VARIABLES));
        if (Path::isAbsolute($path)) {
            $this->throwException($this->getAttribute(), 'must be relative path');
        }

        if (preg_match("[\.\.|\0]", $path)) {
            $this->throwException($this->getAttribute(), "must be inside relative to parent package's path");
        }

        return $path;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }
}
