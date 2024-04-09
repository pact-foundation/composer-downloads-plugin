<?php

namespace LastCall\DownloadsPlugin\Attribute;

use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use LastCall\DownloadsPlugin\Exception\OutOfRangeException;

class AttributeManager implements AttributeManagerInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private array $validators = [];

    private array $values = [];

    public function __construct(private array $attributes)
    {
    }

    public function addValidator(ValidatorInterface $validator): void
    {
        $this->validators[$validator->getAttribute()] = $validator;
    }

    public function get(Attribute $attribute): mixed
    {
        if (\array_key_exists($attribute->value, $this->values)) {
            return $this->values[$attribute->value];
        }

        if (!isset($this->validators[$attribute->value])) {
            throw new OutOfRangeException(sprintf('Validator "%s" not found.', $attribute->value));
        }

        return $this->values[$attribute->value] = $this->validators[$attribute->value]->validate($this->attributes[$attribute->value] ?? null);
    }
}
