<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;

class IgnoreValidator extends AbstractValidator
{
    public function __construct(
        string $id,
        PackageInterface $parent,
        private AttributeManagerInterface $attributeManager
    ) {
        parent::__construct($id, $parent);
    }

    public function validate(mixed $values): array
    {
        if (!$this->attributeManager->get(Attribute::TYPE)->isArchive() || null === $values) {
            return [];
        }

        if (!\is_array($values)) {
            $this->throwException($this->getAttribute(), sprintf('must be array, "%s" given', get_debug_type($values)));
        }

        $ignores = [];
        $variables = $this->attributeManager->get(Attribute::VARIABLES);
        foreach ($values as $value) {
            if (!\is_string($value)) {
                $this->throwException($this->getAttribute(), 'must be array of string');
            }
            $ignores[] = strtr($value, $variables);
        }

        return $ignores;
    }

    public function getAttribute(): string
    {
        return Attribute::IGNORE->value;
    }
}
