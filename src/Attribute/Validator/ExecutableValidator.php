<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Enum\Type;

class ExecutableValidator extends AbstractValidator
{
    public function __construct(
        string $id,
        PackageInterface $parent,
        private AttributeManagerInterface $attributeManager,
        private PathValidator $pathValidator
    ) {
        parent::__construct($id, $parent);
    }

    public function validate(mixed $values): array
    {
        $type = $this->attributeManager->get(Attribute::TYPE);
        if (!$type->isArchive()) {
            if (Type::PHAR === $type) {
                if (null !== $values && true !== $values) {
                    $this->throwException($this->getAttribute(), sprintf('must be true, "%s" given', get_debug_type($values)));
                } else {
                    $values = true;
                }
            } elseif (null !== $values && !\is_bool($values)) {
                $this->throwException($this->getAttribute(), sprintf('must be boolean, "%s" given', get_debug_type($values)));
            }

            return $values ? [$this->attributeManager->get(Attribute::PATH)] : [];
        }

        $values ??= [];
        if (!\is_array($values)) {
            $this->throwException($this->getAttribute(), sprintf('must be array, "%s" given', get_debug_type($values)));
        }
        array_walk($values, fn (mixed $value) => $this->pathValidator->validate($value));

        return $values;
    }

    public function getAttribute(): string
    {
        return Attribute::EXECUTABLE->value;
    }
}
