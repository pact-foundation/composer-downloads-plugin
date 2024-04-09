<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

interface ValidatorInterface
{
    public function validate(mixed $value): mixed;

    public function getAttribute(): string;
}
