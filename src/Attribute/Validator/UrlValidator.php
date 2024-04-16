<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;

class UrlValidator extends AbstractValidator
{
    public function __construct(
        string $id,
        PackageInterface $parent,
        private AttributeManagerInterface $attributeManager
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

        $url = strtr($value, $this->attributeManager->get(Attribute::VARIABLES));
        if (false === filter_var($url, \FILTER_VALIDATE_URL)) {
            $this->throwException($this->getAttribute(), 'is invalid url');
        }

        return $url;
    }

    public function getAttribute(): string
    {
        return Attribute::URL->value;
    }
}
