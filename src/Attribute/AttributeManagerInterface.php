<?php

namespace LastCall\DownloadsPlugin\Attribute;

use LastCall\DownloadsPlugin\Attribute\Validator\ValidatorInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;

interface AttributeManagerInterface
{
    public function addValidator(ValidatorInterface $validator): void;

    public function get(Attribute $attribute): mixed;
}
